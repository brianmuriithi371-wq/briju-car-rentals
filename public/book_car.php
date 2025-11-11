<?php
include '../config/config.php';
include '../includes/mpesa.php';
include '../includes/bank_payment.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    header("Location: login.php");
    exit();
}

$car_id = $_GET['car_id'];
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $pickup_location = $_POST['pickup_location'];
    $service_type = $_POST['service_type'] ?? 'self-drive';
    $payment_method = $_POST['payment_method'] ?? 'mpesa';

    $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
    $total_amount = $days * $car['price_per_day'];

    try {
        // Create booking first
        $stmt = $pdo->prepare("INSERT INTO bookings (client_id, car_id, start_date, end_date, total_amount, service_type, pickup_location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $car_id, $start_date, $end_date, $total_amount, $service_type, $pickup_location]);
        $booking_id = $pdo->lastInsertId();

        // Create payment record
        $transaction_id = generateTransactionId();
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, transaction_id, payment_method, amount, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$booking_id, $transaction_id, $payment_method, $total_amount]);

        // Process payment based on method
        if ($payment_method == 'mpesa') {
            $phone_number = $_POST['phone_number'] ?? '';
            if (empty($phone_number)) {
                throw new Exception("Phone number is required for M-Pesa payment");
            }

            $mpesa = new MpesaAPI();
            $formatted_phone = formatPhoneNumber($phone_number);
            $account_ref = 'Booking#' . $booking_id;

            $stk_response = $mpesa->stkPush($formatted_phone, $total_amount, $account_ref);

            if (isset($stk_response['error'])) {
                throw new Exception("M-Pesa payment failed: " . $stk_response['error']);
            }

            // Update payment with checkout request ID
            $checkout_request_id = $stk_response['CheckoutRequestID'] ?? '';
            $stmt = $pdo->prepare("UPDATE payments SET transaction_id = ?, phone_number = ?, notes = ? WHERE booking_id = ?");
            $stmt->execute([$checkout_request_id, $formatted_phone, 'STK Push initiated', $booking_id]);

            $_SESSION['success'] = "Booking created! Please complete payment via M-Pesa prompt on your phone.";
            header("Location: payment_status.php?booking_id=" . $booking_id);
            exit();

        } elseif ($payment_method == 'bank_atm') {
            $account_number = $_POST['account_number'] ?? '';
            if (empty($account_number)) {
                throw new Exception("Account number is required for bank ATM payment");
            }

            $bank_payment = new BankPaymentAPI();
            $account_number = formatAccountNumber($account_number);

            if (!validateAccountNumber($account_number)) {
                throw new Exception("Invalid account number format");
            }

            $payment_result = $bank_payment->processATMPayment($account_number, $total_amount);

            if ($payment_result['success']) {
                // Update payment and booking status
                $stmt = $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW(), bank_reference = ?, account_number = ? WHERE booking_id = ?");
                $stmt->execute([$payment_result['reference_number'], $account_number, $booking_id]);

                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
                $stmt->execute([$booking_id]);

                // Mark car as unavailable
                $stmt = $pdo->prepare("UPDATE cars SET is_available = 0 WHERE id = ?");
                $stmt->execute([$car_id]);

                $_SESSION['success'] = "Payment completed successfully! Booking confirmed.";
                header("Location: dashboard.php");
                exit();
            } else {
                throw new Exception("Bank payment failed: " . $payment_result['error']);
            }
        }

    } catch(PDOException $e) {
        $error = "Booking failed: " . $e->getMessage();
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg-custom">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-car text-primary me-2"></i>
                        Book Car: <?php echo $car['brand'] . ' ' . $car['model']; ?> (<?php echo $car['year']; ?>)
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger fade-in">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Car Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-info-circle me-1"></i>Car Details
                                    </h6>
                                    <p class="mb-1"><i class="fas fa-cog text-car-accent me-2"></i><?php echo ucfirst($car['transmission']); ?></p>
                                    <p class="mb-1"><i class="fas fa-gas-pump text-car-accent me-2"></i><?php echo ucfirst($car['fuel_type']); ?></p>
                                    <p class="mb-1"><i class="fas fa-users text-car-accent me-2"></i><?php echo $car['seats']; ?> seats</p>
                                    <p class="mb-0"><i class="fas fa-dollar-sign text-success me-2"></i>$<?php echo $car['price_per_day']; ?>/day</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-calculator me-1"></i>Booking Calculator
                                    </h6>
                                    <p class="mb-1">Select dates to calculate total</p>
                                    <div class="alert alert-info">
                                        <strong>Estimated Total: $<span id="totalAmount" class="text-success fw-bold">0.00</span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">
                                    <i class="fas fa-calendar-alt text-primary me-1"></i>Start Date
                                </label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                <div class="invalid-feedback">Please select a start date.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">
                                    <i class="fas fa-calendar-check text-primary me-1"></i>End Date
                                </label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                <div class="invalid-feedback">Please select an end date.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-concierge-bell text-primary me-1"></i>Service Type
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="service_type" id="self_drive" value="self-drive" checked>
                                        <label class="form-check-label" for="self_drive">
                                            <i class="fas fa-steering-wheel me-1"></i>Self-Drive
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="service_type" id="chauffeur" value="chauffeur">
                                        <label class="form-check-label" for="chauffeur">
                                            <i class="fas fa-user-tie me-1"></i>Chauffeur Service
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="pickup_location" class="form-label">
                                <i class="fas fa-map-marker-alt text-primary me-1"></i>Pickup Location
                            </label>
                            <input type="text" name="pickup_location" id="pickup_location" class="form-control" required placeholder="Enter pickup address" autocomplete="address">
                            <div class="invalid-feedback">Please provide a pickup location.</div>
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-credit-card text-primary me-1"></i>Payment Method
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check payment-method-option" data-method="mpesa">
                                        <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa" checked>
                                        <label class="form-check-label" for="mpesa">
                                            <i class="fas fa-mobile-alt me-1 text-success"></i>M-Pesa
                                            <small class="text-muted d-block">Mobile money payment</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check payment-method-option" data-method="bank_atm">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bank_atm" value="bank_atm">
                                        <label class="form-check-label" for="bank_atm">
                                            <i class="fas fa-university me-1 text-info"></i>Bank ATM
                                            <small class="text-muted d-block">Direct bank transfer</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- M-Pesa Payment Fields -->
                        <div id="mpesa-fields" class="payment-fields">
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">
                                    <i class="fas fa-phone text-primary me-1"></i>M-Pesa Phone Number
                                </label>
                                <input type="tel" name="phone_number" id="phone_number" class="form-control" placeholder="0712345678 or 254712345678" pattern="[0-9+\-\s]+" required>
                                <div class="invalid-feedback">Please enter a valid M-Pesa phone number.</div>
                                <small class="text-muted">Enter the phone number registered with M-Pesa</small>
                            </div>
                        </div>

                        <!-- Bank ATM Payment Fields -->
                        <div id="bank-atm-fields" class="payment-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="account_number" class="form-label">
                                    <i class="fas fa-credit-card text-primary me-1"></i>Account Number
                                </label>
                                <input type="text" name="account_number" id="account_number" class="form-control" placeholder="Enter your bank account number" pattern="[0-9]{10,16}" required>
                                <div class="invalid-feedback">Please enter a valid account number (10-16 digits).</div>
                                <small class="text-muted">Account number from your bank card or account</small>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Payment Information:</strong>
                            <ul class="mb-0 mt-2">
                                <li>M-Pesa payments are processed instantly</li>
                                <li>Bank ATM payments may take 1-2 business days</li>
                                <li>All payments are secure and encrypted</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate total amount based on dates
const startDate = document.querySelector('input[name="start_date"]');
const endDate = document.querySelector('input[name="end_date"]');
const totalAmount = document.getElementById('totalAmount');
const pricePerDay = <?php echo $car['price_per_day']; ?>;

function calculateTotal() {
    if(startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const days = (end - start) / (1000 * 60 * 60 * 24);
        if(days > 0) {
            totalAmount.textContent = (days * pricePerDay).toFixed(2);
        }
    }
}

startDate.addEventListener('change', calculateTotal);
endDate.addEventListener('change', calculateTotal);

// Payment method toggle functionality
const paymentMethodOptions = document.querySelectorAll('input[name="payment_method"]');
const mpesaFields = document.getElementById('mpesa-fields');
const bankAtmFields = document.getElementById('bank-atm-fields');

paymentMethodOptions.forEach(option => {
    option.addEventListener('change', function() {
        if (this.value === 'mpesa') {
            mpesaFields.style.display = 'block';
            bankAtmFields.style.display = 'none';
            document.getElementById('phone_number').required = true;
            document.getElementById('account_number').required = false;
        } else if (this.value === 'bank_atm') {
            mpesaFields.style.display = 'none';
            bankAtmFields.style.display = 'block';
            document.getElementById('phone_number').required = false;
            document.getElementById('account_number').required = true;
        }
    });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

    if (paymentMethod === 'mpesa') {
        const phoneInput = document.getElementById('phone_number');
        if (!phoneInput.value.trim()) {
            e.preventDefault();
            phoneInput.focus();
            alert('Please enter your M-Pesa phone number');
            return false;
        }
    } else if (paymentMethod === 'bank_atm') {
        const accountInput = document.getElementById('account_number');
        if (!accountInput.value.trim()) {
            e.preventDefault();
            accountInput.focus();
            alert('Please enter your account number');
            return false;
        }
    }
});
</script>

<?php include 'footer.php'; ?>

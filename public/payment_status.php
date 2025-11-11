<?php
include '../config/config.php';
include '../includes/mpesa.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    header("Location: login.php");
    exit();
}

$booking_id = $_GET['booking_id'] ?? 0;

// Get booking and payment details
$stmt = $pdo->prepare("
    SELECT b.*, c.brand, c.model, c.year, p.*
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.id = ? AND b.client_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}

// Check payment status
$payment_status = $booking['status'] ?? 'pending';
$can_retry_payment = in_array($payment_status, ['failed', 'cancelled']);
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg-custom">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card text-primary me-2"></i>
                        Payment Status
                    </h4>
                </div>
                <div class="card-body p-4">

                    <!-- Booking Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-car me-1"></i>Booking Details
                                    </h6>
                                    <p class="mb-1"><strong>Car:</strong> <?php echo $booking['brand'] . ' ' . $booking['model'] . ' (' . $booking['year'] . ')'; ?></p>
                                    <p class="mb-1"><strong>Duration:</strong> <?php echo date('M d', strtotime($booking['start_date'])) . ' - ' . date('M d, Y', strtotime($booking['end_date'])); ?></p>
                                    <p class="mb-1"><strong>Service:</strong> <?php echo ucfirst($booking['service_type']); ?></p>
                                    <p class="mb-0"><strong>Total Amount:</strong> <span class="text-success fw-bold">$<?php echo number_format($booking['total_amount'], 2); ?></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-info-circle me-1"></i>Payment Information
                                    </h6>
                                    <p class="mb-1"><strong>Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'] ?? 'Not selected')); ?></p>
                                    <p class="mb-1"><strong>Transaction ID:</strong> <?php echo $booking['transaction_id'] ?? 'N/A'; ?></p>
                                    <?php if ($booking['phone_number']): ?>
                                        <p class="mb-1"><strong>Phone:</strong> <?php echo $booking['phone_number']; ?></p>
                                    <?php endif; ?>
                                    <p class="mb-0"><strong>Status:</strong>
                                        <span class="badge
                                            <?php
                                            switch($payment_status) {
                                                case 'completed': echo 'bg-success'; break;
                                                case 'processing': echo 'bg-warning'; break;
                                                case 'pending': echo 'bg-info'; break;
                                                case 'failed': echo 'bg-danger'; break;
                                                case 'cancelled': echo 'bg-secondary'; break;
                                                default: echo 'bg-light text-dark';
                                            }
                                            ?>
                                        ">
                                            <?php echo ucfirst($payment_status); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Status Messages -->
                    <?php if ($payment_status == 'pending'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-clock me-2"></i>
                            <strong>M-Pesa Payment Pending</strong>
                            <p class="mb-0 mt-2">
                                A payment request has been sent to your phone. Please check your M-Pesa messages and complete the payment.
                                The booking will be confirmed once payment is received.
                            </p>
                        </div>

                        <div class="text-center mb-4">
                            <button onclick="checkPaymentStatus()" class="btn btn-primary me-2" id="checkStatusBtn">
                                <i class="fas fa-sync-alt me-1"></i>Check Payment Status
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>

                    <?php elseif ($payment_status == 'processing'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            <strong>Payment Processing</strong>
                            <p class="mb-0 mt-2">
                                Your payment is being processed. This may take a few moments.
                            </p>
                        </div>

                        <div class="text-center mb-4">
                            <button onclick="checkPaymentStatus()" class="btn btn-primary me-2" id="checkStatusBtn">
                                <i class="fas fa-sync-alt me-1"></i>Refresh Status
                            </button>
                        </div>

                    <?php elseif ($payment_status == 'completed'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Payment Successful!</strong>
                            <p class="mb-0 mt-2">
                                Your booking has been confirmed. You will receive a confirmation email shortly.
                                <?php if ($booking['mpesa_receipt_number']): ?>
                                    <br><strong>M-Pesa Receipt:</strong> <?php echo $booking['mpesa_receipt_number']; ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="text-center mb-4">
                            <a href="dashboard.php" class="btn btn-success">
                                <i class="fas fa-tachometer-alt me-1"></i>Go to Dashboard
                            </a>
                        </div>

                    <?php elseif ($payment_status == 'failed'): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Payment Failed</strong>
                            <p class="mb-0 mt-2">
                                Your payment could not be processed. Please try again or contact support.
                            </p>
                        </div>

                        <div class="text-center mb-4">
                            <a href="book_car.php?car_id=<?php echo $booking['car_id']; ?>" class="btn btn-primary me-2">
                                <i class="fas fa-redo me-1"></i>Try Again
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="fas fa-question-circle me-2"></i>
                            <strong>Unknown Payment Status</strong>
                            <p class="mb-0 mt-2">
                                We're unable to determine the payment status. Please contact support for assistance.
                            </p>
                        </div>

                        <div class="text-center mb-4">
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Instructions for M-Pesa -->
                    <?php if ($payment_status == 'pending' && $booking['payment_method'] == 'mpesa'): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-mobile-alt text-success me-2"></i>
                                    M-Pesa Payment Instructions
                                </h6>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li>Check your phone for an M-Pesa payment prompt</li>
                                    <li>Enter your M-Pesa PIN to complete the payment</li>
                                    <li>Wait for confirmation message</li>
                                    <li>Click "Check Payment Status" above to verify</li>
                                </ol>
                                <div class="alert alert-warning">
                                    <small>
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <strong>Note:</strong> If you don't receive the prompt within 2 minutes,
                                        please try booking again or contact support.
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkPaymentStatus() {
    const btn = document.getElementById('checkStatusBtn');
    const originalText = btn.innerHTML;

    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Checking...';

    // Make AJAX request to check status
    fetch('check_payment_status.php?booking_id=<?php echo $booking_id; ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.status === 'completed') {
                    // Payment completed - reload page to show success
                    location.reload();
                } else {
                    // Show current status
                    btn.innerHTML = '<i class="fas fa-info-circle me-1"></i>Status: ' + data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }, 3000);
                }
            } else {
                btn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error checking status';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Connection error';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }, 3000);
        });
}

// Auto-check status every 30 seconds for pending payments
<?php if ($payment_status == 'pending' || $payment_status == 'processing'): ?>
setInterval(checkPaymentStatus, 30000);
<?php endif; ?>
</script>

<?php include 'footer.php'; ?>

<?php
include '../config/config.php';

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

    $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
    $total_amount = $days * $car['price_per_day'];

    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (client_id, car_id, start_date, end_date, total_amount, service_type, pickup_location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $car_id, $start_date, $end_date, $total_amount, $service_type, $pickup_location]);

        // Mark car as unavailable
        $stmt = $pdo->prepare("UPDATE cars SET is_available = 0 WHERE id = ?");
        $stmt->execute([$car_id]);

        $_SESSION['success'] = "Car booked successfully!";
        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = "Booking failed: " . $e->getMessage();
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

                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Booking confirmation is subject to car availability. You will receive a confirmation email once approved.
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Confirm Booking
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
</script>

<?php include 'footer.php'; ?>
<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle car status updates
if(isset($_POST['update_car_status'])) {
    $car_id = $_POST['car_id'];
    $is_available = $_POST['is_available'];

    $stmt = $pdo->prepare("UPDATE cars SET is_available = ? WHERE id = ?");
    $stmt->execute([$is_available, $car_id]);

    header("Location: cars.php");
    exit();
}

// Get all cars with owner details
$query = "
    SELECT c.*, u.full_name as owner_name, u.email as owner_email,
           (SELECT COUNT(*) FROM bookings WHERE car_id = c.id AND status IN ('confirmed', 'active')) as active_bookings
    FROM cars c
    JOIN users u ON c.owner_id = u.id
    ORDER BY c.created_at DESC
";

$cars = $pdo->query($query)->fetchAll();

// Get car locations for tracking
$car_locations = $pdo->query("
    SELECT cl.*, c.brand, c.model, c.license_plate, u.full_name as owner_name
    FROM car_locations cl
    JOIN cars c ON cl.car_id = c.id
    JOIN users u ON c.owner_id = u.id
    ORDER BY cl.timestamp DESC
    LIMIT 20
")->fetchAll();

include 'header.php';
?>

<div class="admin-layout">
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h4><i class="fas fa-crown me-2"></i>Admin Panel</h4>
            <small>Briju Car Rental</small>
        </div>
        <nav class="admin-nav">
            <a href="index.php" class="admin-nav-link">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="bookings.php" class="admin-nav-link">
                <i class="fas fa-calendar-check"></i>
                Bookings
            </a>
            <a href="cars.php" class="admin-nav-link active">
                <i class="fas fa-car"></i>
                Cars & Tracking
            </a>
            <a href="users.php" class="admin-nav-link">
                <i class="fas fa-users"></i>
                Users
            </a>
            <a href="payments.php" class="admin-nav-link">
                <i class="fas fa-credit-card"></i>
                Payments
            </a>
            <a href="reports.php" class="admin-nav-link">
                <i class="fas fa-chart-bar"></i>
                Reports
            </a>
            <a href="../logout.php" class="admin-nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <h1><i class="fas fa-car me-2"></i>Cars & Tracking Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Cars & Tracking</li>
                    </ol>
                </nav>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon primary">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3><?php echo count($cars); ?></h3>
                        <p>Total Cars</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3><?php echo count(array_filter($cars, function($car) { return $car['is_available']; })); ?></h3>
                        <p>Available Cars</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon warning">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h3><?php echo count(array_filter($cars, function($car) { return !$car['is_available']; })); ?></h3>
                        <p>Rented Cars</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon info">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3><?php echo count($car_locations); ?></h3>
                        <p>Location Updates</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Cars List -->
                <div class="col-lg-8 mb-4">
                    <div class="admin-table">
                        <div class="admin-table-header">
                            <h5><i class="fas fa-car me-2"></i>All Cars</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Owner</th>
                                        <th>Price/Day</th>
                                        <th>Status</th>
                                        <th>Active Bookings</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cars as $car): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($car['license_plate']); ?> • <?php echo $car['year']; ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($car['owner_name']); ?></td>
                                        <td>$<?php echo number_format($car['price_per_day'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $car['is_available'] ? 'success' : 'danger'; ?>">
                                                <?php echo $car['is_available'] ? 'Available' : 'Rented'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $car['active_bookings']; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="action-btn edit" onclick="viewCar(<?php echo $car['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                                    <input type="hidden" name="is_available" value="<?php echo $car['is_available'] ? 0 : 1; ?>">
                                                    <button type="submit" name="update_car_status" class="action-btn <?php echo $car['is_available'] ? 'reject' : 'approve'; ?>"
                                                            onclick="return confirm('<?php echo $car['is_available'] ? 'Mark as unavailable?' : 'Mark as available?'; ?>')">
                                                        <i class="fas fa-<?php echo $car['is_available'] ? 'ban' : 'check'; ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Car Tracking -->
                <div class="col-lg-4 mb-4">
                    <div class="admin-table">
                        <div class="admin-table-header">
                            <h5><i class="fas fa-map-marker-alt me-2"></i>Recent Locations</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Location</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($car_locations as $location): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo htmlspecialchars($location['brand'] . ' ' . $location['model']); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo number_format($location['latitude'], 6); ?>, <?php echo number_format($location['longitude'], 6); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo date('H:i', strtotime($location['timestamp'])); ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div class="admin-table mt-3">
                        <div class="admin-table-header">
                            <h6><i class="fas fa-map me-2"></i>Live Tracking</h6>
                        </div>
                        <div style="height: 300px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center text-muted">
                                <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                                <p>Interactive map will be integrated here</p>
                                <small>Real-time car tracking visualization</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Car Details Modal -->
<div class="modal fade" id="carModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Car Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="carDetails">
                <!-- Car details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewCar(carId) {
    // Load car details via AJAX
    fetch('get_car_details.php?id=' + carId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('carDetails').innerHTML = data;
            new bootstrap.Modal(document.getElementById('carModal')).show();
        });
}
</script>

<?php include 'footer.php'; ?>

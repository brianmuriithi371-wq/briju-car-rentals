<?php
include '../config/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if($user_type == 'client') {
    // Client dashboard - show bookings
    $stmt = $pdo->prepare("SELECT b.*, c.brand, c.model, c.license_plate FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.client_id = ? ORDER BY b.created_at DESC");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();
} elseif($user_type == 'owner') {
    // Owner dashboard - show cars and bookings
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE owner_id = ?");
    $stmt->execute([$user_id]);
    $cars = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT b.*, c.brand, c.model, u.full_name as client_name FROM bookings b JOIN cars c ON b.car_id = c.id JOIN users u ON b.client_id = u.id WHERE c.owner_id = ? ORDER BY b.created_at DESC");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();
} elseif($user_type == 'admin') {
    // Admin dashboard - show all data
    $users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $cars_count = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
    $bookings_count = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
}
?>

<?php include 'header.php'; ?>
<style>
.dashboard-page {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.dashboard-header {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0.05;
    border-radius: 15px;
}

.dashboard-header .row {
    position: relative;
    z-index: 1;
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ff6b35, #f7931e);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.stat-card i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.8;
}

.stat-card h5 {
    font-weight: 600;
    margin-bottom: 1rem;
    color: #64748b;
}

.stat-card h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.dashboard-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.dashboard-section h4 {
    color: #1e293b;
    font-weight: 700;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.dashboard-section h4 i {
    margin-right: 0.5rem;
    color: #ff6b35;
}

.table {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-weight: 600;
    padding: 1rem;
}

.table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.badge {
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.btn-primary {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
    background: linear-gradient(135deg, #e55a2b 0%, #e8890b 100%);
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #64748b;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state a {
    color: #ff6b35;
    text-decoration: none;
    font-weight: 600;
}

.empty-state a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .dashboard-page {
        padding: 1rem;
    }

    .dashboard-header {
        padding: 1.5rem;
    }

    .dashboard-stats {
        grid-template-columns: 1fr;
    }

    .stat-card {
        padding: 1.5rem;
    }

    .dashboard-section {
        padding: 1.5rem;
    }
}
</style>

<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <div class="row">
                <div class="col-md-8">
                    <h2 class="mb-4">Dashboard - Welcome, <?php echo $_SESSION['username']; ?></h2>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-<?php echo $_SESSION['user_type'] == 'admin' ? 'danger' : ($_SESSION['user_type'] == 'owner' ? 'success' : 'primary'); ?> fs-6 p-2">
                        <i class="fas fa-<?php echo $_SESSION['user_type'] == 'admin' ? 'crown' : ($_SESSION['user_type'] == 'owner' ? 'car' : 'user'); ?> me-1"></i>
                        <?php echo ucfirst($_SESSION['user_type']); ?> Account
                    </div>
                </div>
            </div>
        </div>
    
    <?php if($user_type == 'client'): ?>
        <!-- Client Quick Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h5>Total Bookings</h5>
                <h2><?php echo count($bookings); ?></h2>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h5>Confirmed</h5>
                <h2><?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'confirmed'; })); ?></h2>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h5>Pending</h5>
                <h2><?php echo count(array_filter($bookings, function($b) { return $b['status'] == 'pending'; })); ?></h2>
            </div>
        </div>

        <div class="dashboard-section">
            <h4><i class="fas fa-list-alt"></i>My Bookings</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-car me-1"></i>Car</th>
                            <th><i class="fas fa-calendar me-1"></i>Dates</th>
                            <th><i class="fas fa-concierge-bell me-1"></i>Service</th>
                            <th><i class="fas fa-dollar-sign me-1"></i>Total Amount</th>
                            <th><i class="fas fa-info-circle me-1"></i>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($bookings)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No bookings found. <a href="index.php">Browse cars</a> to make your first booking!</p>
                        </div>
                        <?php else: ?>
                            <?php foreach($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['brand'] . ' ' . $booking['model']; ?></td>
                                <td><?php echo $booking['start_date'] . ' to ' . $booking['end_date']; ?></td>
                                <td><span class="badge bg-info"><?php echo ucfirst($booking['service_type']); ?></span></td>
                                <td>$<?php echo $booking['total_amount']; ?></td>
                                <td><span class="badge bg-<?php
                                    switch($booking['status']) {
                                        case 'confirmed': echo 'success'; break;
                                        case 'pending': echo 'warning'; break;
                                        case 'cancelled': echo 'danger'; break;
                                        default: echo 'secondary';
                                    }
                                ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    <?php elseif($user_type == 'owner'): ?>
        <!-- Owner Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="fas fa-car fa-2x mb-3"></i>
                        <h5>My Cars</h5>
                        <h2><?php echo count($cars); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card text-white bg-info">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check fa-2x mb-3"></i>
                        <h5>Total Bookings</h5>
                        <h2><?php echo count($bookings); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card text-white bg-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x mb-3"></i>
                        <h5>Revenue</h5>
                        <h2>$<?php echo array_sum(array_column($bookings, 'total_amount')); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h4><i class="fas fa-car text-primary me-2"></i>My Cars</h4>
                <div class="mb-3">
                    <a href="add_car.php" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-2"></i>Add New Car
                    </a>
                    <a href="owner/gps.php" class="btn btn-info">
                        <i class="fas fa-map-marker-alt me-2"></i>GPS Tracking
                    </a>
                </div>
                <?php if(empty($cars)): ?>
                <div class="card dashboard-card">
                    <div class="card-body text-center text-muted py-4">
                        <i class="fas fa-car fa-2x mb-2"></i><br>
                        No cars added yet. <a href="add_car.php">Add your first car</a> to start earning!
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach($cars as $car): ?>
                    <div class="card dashboard-card mb-3">
                        <div class="card-body">
                            <h5><?php echo $car['brand'] . ' ' . $car['model']; ?> (<?php echo $car['year']; ?>)</h5>
                            <p><i class="fas fa-id-card text-car-accent me-2"></i>License: <?php echo $car['license_plate']; ?></p>
                            <p><i class="fas fa-dollar-sign text-success me-2"></i>Price: $<?php echo $car['price_per_day']; ?>/day</p>
                            <p>Status: <span class="badge bg-<?php echo $car['is_available'] ? 'success' : 'danger'; ?>">
                                <?php echo $car['is_available'] ? 'Available' : 'Rented'; ?>
                            </span></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <h4><i class="fas fa-calendar-check text-primary me-2"></i>Car Bookings</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-car me-1"></i>Car</th>
                                <th><i class="fas fa-user me-1"></i>Client</th>
                                <th><i class="fas fa-calendar me-1"></i>Dates</th>
                                <th><i class="fas fa-dollar-sign me-1"></i>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($bookings)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No bookings yet. Your cars will appear here once rented!
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['brand'] . ' ' . $booking['model']; ?></td>
                                    <td><?php echo $booking['client_name']; ?></td>
                                    <td><?php echo $booking['start_date'] . ' to ' . $booking['end_date']; ?></td>
                                    <td>$<?php echo $booking['total_amount']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    <?php elseif($user_type == 'admin'): ?>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card text-white bg-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-3"></i>
                        <h5>Total Users</h5>
                        <h2><?php echo $users_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="fas fa-car fa-2x mb-3"></i>
                        <h5>Total Cars</h5>
                        <h2><?php echo $cars_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card text-white bg-info">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check fa-2x mb-3"></i>
                        <h5>Total Bookings</h5>
                        <h2><?php echo $bookings_count; ?></h2>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
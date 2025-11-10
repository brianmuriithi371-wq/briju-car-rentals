<?php
include '../../config/config.php';

requireAdmin();

// Get statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE user_type != 'admin'")->fetchColumn(),
    'total_cars' => $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn(),
    'total_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'active_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'active'")->fetchColumn(),
    'pending_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(total_amount) FROM bookings WHERE status IN ('completed', 'active')")->fetchColumn() ?? 0,
    'available_cars' => $pdo->query("SELECT COUNT(*) FROM cars WHERE is_available = 1")->fetchColumn(),
    'rented_cars' => $pdo->query("SELECT COUNT(*) FROM cars WHERE is_available = 0")->fetchColumn()
];

// Recent bookings
$recent_bookings = $pdo->query("
    SELECT b.*, u.full_name as client_name, c.brand, c.model, c.license_plate
    FROM bookings b
    JOIN users u ON b.client_id = u.id
    JOIN cars c ON b.car_id = c.id
    ORDER BY b.created_at DESC
    LIMIT 10
")->fetchAll();

// Recent users
$recent_users = $pdo->query("
    SELECT * FROM users
    WHERE user_type != 'admin'
    ORDER BY created_at DESC
    LIMIT 5
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
            <a href="index.php" class="admin-nav-link active">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="bookings.php" class="admin-nav-link">
                <i class="fas fa-calendar-check"></i>
                Bookings
            </a>
            <a href="cars.php" class="admin-nav-link">
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
                <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard Overview</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>

            <!-- Statistics Cards -->
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-card-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo number_format($stats['total_users']); ?></h3>
                    <p>Total Users</p>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon success">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3><?php echo number_format($stats['total_cars']); ?></h3>
                    <p>Total Cars</p>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon info">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3><?php echo number_format($stats['total_bookings']); ?></h3>
                    <p>Total Bookings</p>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon warning">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3><?php echo number_format($stats['active_bookings']); ?></h3>
                    <p>Active Bookings</p>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3><?php echo number_format($stats['pending_bookings']); ?></h3>
                    <p>Pending Bookings</p>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon info">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3><?php echo number_format($stats['available_cars']); ?></h3>
                    <p>Available Cars</p>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon primary">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <h3><?php echo number_format($stats['rented_cars']); ?></h3>
                    <p>Rented Cars</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <!-- Recent Bookings -->
                <div class="col-lg-8 mb-4">
                    <div class="admin-table">
                        <div class="admin-table-header">
                            <h5><i class="fas fa-clock me-2"></i>Recent Bookings</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Car</th>
                                        <th>Dates</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></td>
                                        <td><?php echo $booking['start_date'] . ' to ' . $booking['end_date']; ?></td>
                                        <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $booking['status']; ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="action-btn edit" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if($booking['status'] == 'pending'): ?>
                                            <button class="action-btn approve" onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'confirmed')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="action-btn reject" onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="col-lg-4 mb-4">
                    <div class="admin-table">
                        <div class="admin-table-header">
                            <h5><i class="fas fa-user-plus me-2"></i>Recent Users</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['user_type'] == 'owner' ? 'success' : 'primary'; ?>">
                                                <?php echo ucfirst($user['user_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update booking status
function updateBookingStatus(bookingId, status) {
    if(confirm('Are you sure you want to ' + status + ' this booking?')) {
        fetch('update_booking_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=' + bookingId + '&status=' + status
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error updating booking status');
            }
        });
    }
}

// View booking details
function viewBooking(bookingId) {
    window.location.href = 'bookings.php?view=' + bookingId;
}
</script>

<?php include 'footer.php'; ?>

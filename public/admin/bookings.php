<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle status updates
if(isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $booking_id]);

    // Update car availability
    if($status == 'confirmed') {
        $stmt = $pdo->prepare("UPDATE cars SET is_available = 0 WHERE id = (SELECT car_id FROM bookings WHERE id = ?)");
        $stmt->execute([$booking_id]);
    } elseif($status == 'completed' || $status == 'cancelled') {
        $stmt = $pdo->prepare("UPDATE cars SET is_available = 1 WHERE id = (SELECT car_id FROM bookings WHERE id = ?)");
        $stmt->execute([$booking_id]);
    }

    header("Location: bookings.php");
    exit();
}

// Get all bookings with details
$query = "
    SELECT b.*, u.full_name as client_name, u.email as client_email, u.phone as client_phone,
           c.brand, c.model, c.license_plate, c.price_per_day,
           owner.full_name as owner_name
    FROM bookings b
    JOIN users u ON b.client_id = u.id
    JOIN cars c ON b.car_id = c.id
    JOIN users owner ON c.owner_id = owner.id
    ORDER BY b.created_at DESC
";

$bookings = $pdo->query($query)->fetchAll();

// Filter by status if requested
$status_filter = $_GET['status'] ?? 'all';
if($status_filter != 'all') {
    $bookings = array_filter($bookings, function($booking) use ($status_filter) {
        return $booking['status'] == $status_filter;
    });
}

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
            <a href="bookings.php" class="admin-nav-link active">
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
                <h1><i class="fas fa-calendar-check me-2"></i>Bookings Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Bookings</li>
                    </ol>
                </nav>
            </div>

            <!-- Filters -->
            <div class="mb-4">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="bookings.php?status=all" class="btn btn-outline-primary <?php echo $status_filter == 'all' ? 'active' : ''; ?>">All (<?php echo count($pdo->query("SELECT * FROM bookings")->fetchAll()); ?>)</a>
                    <a href="bookings.php?status=pending" class="btn btn-outline-warning <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">Pending (<?php echo count(array_filter($pdo->query("SELECT * FROM bookings")->fetchAll(), function($b) { return $b['status'] == 'pending'; })); ?>)</a>
                    <a href="bookings.php?status=confirmed" class="btn btn-outline-success <?php echo $status_filter == 'confirmed' ? 'active' : ''; ?>">Confirmed (<?php echo count(array_filter($pdo->query("SELECT * FROM bookings")->fetchAll(), function($b) { return $b['status'] == 'confirmed'; })); ?>)</a>
                    <a href="bookings.php?status=active" class="btn btn-outline-info <?php echo $status_filter == 'active' ? 'active' : ''; ?>">Active (<?php echo count(array_filter($pdo->query("SELECT * FROM bookings")->fetchAll(), function($b) { return $b['status'] == 'active'; })); ?>)</a>
                    <a href="bookings.php?status=completed" class="btn btn-outline-secondary <?php echo $status_filter == 'completed' ? 'active' : ''; ?>">Completed (<?php echo count(array_filter($pdo->query("SELECT * FROM bookings")->fetchAll(), function($b) { return $b['status'] == 'completed'; })); ?>)</a>
                    <a href="bookings.php?status=cancelled" class="btn btn-outline-danger <?php echo $status_filter == 'cancelled' ? 'active' : ''; ?>">Cancelled (<?php echo count(array_filter($pdo->query("SELECT * FROM bookings")->fetchAll(), function($b) { return $b['status'] == 'cancelled'; })); ?>)</a>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="admin-table">
                <div class="admin-table-header">
                    <h5><i class="fas fa-list me-2"></i>All Bookings</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Car</th>
                                <th>Owner</th>
                                <th>Dates</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($booking['client_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['client_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['license_plate']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($booking['owner_name']); ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo $booking['start_date']; ?></strong><br>
                                        <small>to <?php echo $booking['end_date']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($booking['service_type']); ?></span>
                                </td>
                                <td>
                                    <strong>$<?php echo number_format($booking['total_amount'], 2); ?></strong><br>
                                    <small class="text-muted">$<?php echo $booking['price_per_day']; ?>/day</small>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="action-btn edit" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($booking['status'] == 'pending'): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" name="update_status" class="action-btn approve" onclick="return confirm('Confirm this booking?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" name="update_status" class="action-btn reject" onclick="return confirm('Cancel this booking?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        <?php elseif($booking['status'] == 'confirmed'): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" name="update_status" class="action-btn approve" onclick="return confirm('Mark as active?')">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                        <?php elseif($booking['status'] == 'active'): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" name="update_status" class="action-btn approve" onclick="return confirm('Mark as completed?')">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetails">
                <!-- Booking details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewBooking(bookingId) {
    // Load booking details via AJAX
    fetch('get_booking_details.php?id=' + bookingId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('bookingDetails').innerHTML = data;
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        });
}
</script>

<?php include 'footer.php'; ?>

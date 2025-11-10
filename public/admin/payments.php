<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get payment/transaction data (simulated since we don't have a payments table yet)
$payments = $pdo->query("
    SELECT b.id as booking_id, b.total_amount, b.status, b.created_at,
           u.full_name as client_name, c.brand, c.model
    FROM bookings b
    JOIN users u ON b.client_id = u.id
    JOIN cars c ON b.car_id = c.id
    WHERE b.status IN ('confirmed', 'completed', 'active')
    ORDER BY b.created_at DESC
    LIMIT 50
")->fetchAll();

// Calculate payment statistics
$total_revenue = array_sum(array_column($payments, 'total_amount'));
$completed_payments = count(array_filter($payments, function($p) { return $p['status'] == 'completed'; }));
$pending_payments = count(array_filter($payments, function($p) { return $p['status'] == 'confirmed' || $p['status'] == 'active'; }));

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
            <a href="cars.php" class="admin-nav-link">
                <i class="fas fa-car"></i>
                Cars & Tracking
            </a>
            <a href="users.php" class="admin-nav-link">
                <i class="fas fa-users"></i>
                Users
            </a>
            <a href="payments.php" class="admin-nav-link active">
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
                <h1><i class="fas fa-credit-card me-2"></i>Payment Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Payments</li>
                    </ol>
                </nav>
            </div>

            <!-- Payment Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-card-icon success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-card-icon info">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3><?php echo $completed_payments; ?></h3>
                        <p>Completed Payments</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-card-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3><?php echo $pending_payments; ?></h3>
                        <p>Pending Payments</p>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Payment System Information</h6>
                        <p class="mb-0">This system currently uses booking amounts as payment records. For a production system, consider integrating with payment gateways like Stripe, PayPal, or local payment processors.</p>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="admin-table">
                <div class="admin-table-header">
                    <h5><i class="fas fa-credit-card me-2"></i>Payment Transactions</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Client</th>
                                <th>Car</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($payments as $payment): ?>
                            <tr>
                                <td>#<?php echo $payment['booking_id']; ?></td>
                                <td><?php echo htmlspecialchars($payment['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['brand'] . ' ' . $payment['model']); ?></td>
                                <td><strong>$<?php echo number_format($payment['total_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="status-badge <?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                                <td>
                                    <button class="action-btn edit" onclick="viewPayment(<?php echo $payment['booking_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($payment['status'] == 'confirmed'): ?>
                                    <button class="action-btn approve" onclick="markAsPaid(<?php echo $payment['booking_id']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Integration Setup -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="admin-table">
                        <div class="admin-table-header">
                            <h5><i class="fas fa-cogs me-2"></i>Payment Gateway Setup</h5>
                        </div>
                        <div class="p-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fab fa-stripe fa-3x text-primary mb-3"></i>
                                        <h6>Stripe</h6>
                                        <p class="text-muted small">Popular payment processor</p>
                                        <button class="btn btn-outline-primary btn-sm">Configure</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fab fa-paypal fa-3x text-info mb-3"></i>
                                        <h6>PayPal</h6>
                                        <p class="text-muted small">Global payment solution</p>
                                        <button class="btn btn-outline-info btn-sm">Configure</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-mobile-alt fa-3x text-success mb-3"></i>
                                        <h6>M-Pesa</h6>
                                        <p class="text-muted small">Mobile money payments</p>
                                        <button class="btn btn-outline-success btn-sm">Configure</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetails">
                <!-- Payment details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewPayment(bookingId) {
    // Load payment details via AJAX
    fetch('get_payment_details.php?id=' + bookingId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('paymentDetails').innerHTML = data;
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        });
}

function markAsPaid(bookingId) {
    if(confirm('Mark this payment as completed?')) {
        fetch('mark_payment_paid.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=' + bookingId
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error updating payment status');
            }
        });
    }
}
</script>

<?php include 'footer.php'; ?>

<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get report data
$monthly_revenue = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
           SUM(total_amount) as revenue,
           COUNT(*) as bookings
    FROM bookings
    WHERE status IN ('completed', 'active')
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
")->fetchAll();

$car_popularity = $pdo->query("
    SELECT c.brand, c.model, COUNT(b.id) as booking_count,
           SUM(b.total_amount) as total_revenue
    FROM cars c
    LEFT JOIN bookings b ON c.id = b.car_id AND b.status IN ('confirmed', 'active', 'completed')
    GROUP BY c.id, c.brand, c.model
    ORDER BY booking_count DESC
    LIMIT 10
")->fetchAll();

$user_stats = $pdo->query("
    SELECT user_type, COUNT(*) as count
    FROM users
    WHERE user_type != 'admin'
    GROUP BY user_type
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
            <a href="reports.php" class="admin-nav-link active">
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
                <h1><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </nav>
            </div>

            <!-- Export Options -->
            <div class="mb-4">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>
                    <button class="btn btn-success" onclick="exportReport('excel')">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                    <button class="btn btn-info" onclick="exportReport('csv')">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </button>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-container">
                <!-- Revenue Chart -->
                <div class="chart-card">
                    <h6><i class="fas fa-chart-line me-2"></i>Monthly Revenue</h6>
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>

                <!-- User Distribution -->
                <div class="chart-card">
                    <h6><i class="fas fa-users me-2"></i>User Distribution</h6>
                    <canvas id="userChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Reports Tables -->
            <div class="row mt-4">
                <!-- Monthly Revenue Table -->
                <div class="col-lg-6 mb-4">
                    <div class="admin-table">
                        <div class="admin-table-header">
                            <h5><i class="fas fa-calendar me-2"></i>Monthly Revenue</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($monthly_revenue as $month): ?>
                                    <tr>
                                        <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                        <td><?php echo $month['bookings']; ?></td>
                                        <td>$<?php echo number_format($month['revenue'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Car Popularity -->
                <div class="col-lg-6 mb-4">
                    <div class="admin-table">
                        <div class="admin-table-header">
                            <h5><i class="fas fa-car me-2"></i>Car Popularity</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Car Model</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($car_popularity as $car): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></td>
                                        <td><?php echo $car['booking_count']; ?></td>
                                        <td>$<?php echo number_format($car['total_revenue'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3>$<?php echo number_format(array_sum(array_column($monthly_revenue, 'revenue')), 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon info">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3><?php echo array_sum(array_column($monthly_revenue, 'bookings')); ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon warning">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3><?php echo count($car_popularity); ?></h3>
                        <p>Active Cars</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-card-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php echo array_sum(array_column($user_stats, 'count')); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($m) { return date('M Y', strtotime($m['month'] . '-01')); }, $monthly_revenue)); ?>,
        datasets: [{
            label: 'Revenue ($)',
            data: <?php echo json_encode(array_column($monthly_revenue, 'revenue')); ?>,
            borderColor: '#ff6b35',
            backgroundColor: 'rgba(255, 107, 53, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});

// User Distribution Chart
const userCtx = document.getElementById('userChart').getContext('2d');
const userChart = new Chart(userCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_map(function($u) { return ucfirst($u['user_type']) . 's'; }, $user_stats)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($user_stats, 'count')); ?>,
            backgroundColor: ['#ff6b35', '#00d4ff', '#64748b'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function exportReport(format) {
    // Implement export functionality
    alert('Export functionality for ' + format.toUpperCase() + ' will be implemented');
}
</script>

<?php include 'footer.php'; ?>

<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle user status updates
if(isset($_POST['update_user_status'])) {
    $user_id = $_POST['user_id'];
    $is_active = $_POST['is_active'];

    // Note: You might want to add an 'is_active' column to users table
    // For now, we'll just show the functionality
    header("Location: users.php");
    exit();
}

// Get all users except admins
$users = $pdo->query("
    SELECT u.*,
           (SELECT COUNT(*) FROM cars WHERE owner_id = u.id) as cars_count,
           (SELECT COUNT(*) FROM bookings WHERE client_id = u.id) as bookings_count
    FROM users u
    WHERE u.user_type != 'admin'
    ORDER BY u.created_at DESC
")->fetchAll();

// Separate clients and owners
$clients = array_filter($users, function($user) { return $user['user_type'] == 'client'; });
$owners = array_filter($users, function($user) { return $user['user_type'] == 'owner'; });

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
            <a href="users.php" class="admin-nav-link active">
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
                <h1><i class="fas fa-users me-2"></i>User Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </nav>
            </div>

            <!-- User Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-card-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php echo count($users); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-card-icon success">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3><?php echo count($clients); ?></h3>
                        <p>Clients</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-card-icon info">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3><?php echo count($owners); ?></h3>
                        <p>Car Owners</p>
                    </div>
                </div>
            </div>

            <!-- Users Tabs -->
            <div class="admin-table">
                <div class="admin-table-header">
                    <h5><i class="fas fa-users me-2"></i>All Users</h5>
                </div>

                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="userTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-users" type="button" role="tab">All Users (<?php echo count($users); ?>)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="clients-tab" data-bs-toggle="tab" data-bs-target="#clients" type="button" role="tab">Clients (<?php echo count($clients); ?>)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="owners-tab" data-bs-toggle="tab" data-bs-target="#owners" type="button" role="tab">Owners (<?php echo count($owners); ?>)</button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content p-3" id="userTabsContent">
                    <!-- All Users -->
                    <div class="tab-pane fade show active" id="all-users" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Phone</th>
                                        <th>Cars/Bookings</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['user_type'] == 'owner' ? 'success' : 'primary'; ?>">
                                                <?php echo ucfirst($user['user_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if($user['user_type'] == 'owner'): ?>
                                                <span class="badge bg-info"><?php echo $user['cars_count']; ?> cars</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning"><?php echo $user['bookings_count']; ?> bookings</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <button class="action-btn edit" onclick="viewUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="action-btn edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Clients -->
                    <div class="tab-pane fade" id="clients" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Bookings</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($clients as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                        <td><span class="badge bg-warning"><?php echo $user['bookings_count']; ?> bookings</span></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <button class="action-btn edit" onclick="viewUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="action-btn edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Owners -->
                    <div class="tab-pane fade" id="owners" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Cars</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($owners as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                        <td><span class="badge bg-info"><?php echo $user['cars_count']; ?> cars</span></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <button class="action-btn edit" onclick="viewUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="action-btn edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
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
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetails">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewUser(userId) {
    // Load user details via AJAX
    fetch('get_user_details.php?id=' + userId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('userDetails').innerHTML = data;
            new bootstrap.Modal(document.getElementById('userModal')).show();
        });
}

function editUser(userId) {
    // For now, just view - you can extend this to edit functionality
    viewUser(userId);
}
</script>

<?php include 'footer.php'; ?>

<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    exit('Unauthorized');
}

if(isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    $stmt = $pdo->prepare("
        SELECT b.*, u.full_name as client_name, u.email as client_email, u.phone as client_phone,
               c.brand, c.model, c.license_plate, c.price_per_day, c.description,
               owner.full_name as owner_name, owner.email as owner_email, owner.phone as owner_phone
        FROM bookings b
        JOIN users u ON b.client_id = u.id
        JOIN cars c ON b.car_id = c.id
        JOIN users owner ON c.owner_id = owner.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if($booking) {
        ?>
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-user me-2"></i>Client Information</h6>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['client_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['client_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['client_phone']); ?></p>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-user-tie me-2"></i>Owner Information</h6>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['owner_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['owner_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['owner_phone']); ?></p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-car me-2"></i>Car Details</h6>
                <p><strong>Car:</strong> <?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></p>
                <p><strong>License Plate:</strong> <?php echo htmlspecialchars($booking['license_plate']); ?></p>
                <p><strong>Price per Day:</strong> $<?php echo number_format($booking['price_per_day'], 2); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($booking['description']); ?></p>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-calendar me-2"></i>Booking Details</h6>
                <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                <p><strong>Start Date:</strong> <?php echo $booking['start_date']; ?></p>
                <p><strong>End Date:</strong> <?php echo $booking['end_date']; ?></p>
                <p><strong>Service Type:</strong> <?php echo ucfirst($booking['service_type']); ?></p>
                <p><strong>Status:</strong>
                    <span class="status-badge <?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($booking['total_amount'], 2); ?></p>
                <p><strong>Pickup Location:</strong> <?php echo htmlspecialchars($booking['pickup_location'] ?? 'N/A'); ?></p>
                <p><strong>Drop-off Location:</strong> <?php echo htmlspecialchars($booking['dropoff_location'] ?? 'N/A'); ?></p>
                <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></p>
            </div>
        </div>
        <?php
    } else {
        echo '<p class="text-danger">Booking not found.</p>';
    }
}
?>

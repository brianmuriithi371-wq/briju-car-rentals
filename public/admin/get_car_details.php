<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    exit('Unauthorized');
}

if(isset($_GET['id'])) {
    $car_id = $_GET['id'];

    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name as owner_name, u.email as owner_email, u.phone as owner_phone
        FROM cars c
        JOIN users u ON c.owner_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();

    if($car) {
        // Get recent bookings for this car
        $stmt = $pdo->prepare("
            SELECT b.*, u.full_name as client_name
            FROM bookings b
            JOIN users u ON b.client_id = u.id
            WHERE b.car_id = ?
            ORDER BY b.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$car_id]);
        $recent_bookings = $stmt->fetchAll();

        // Get location history
        $stmt = $pdo->prepare("
            SELECT * FROM car_locations
            WHERE car_id = ?
            ORDER BY timestamp DESC
            LIMIT 10
        ");
        $stmt->execute([$car_id]);
        $locations = $stmt->fetchAll();
        ?>
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-car me-2"></i>Car Information</h6>
                <p><strong>Brand & Model:</strong> <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></p>
                <p><strong>Year:</strong> <?php echo $car['year']; ?></p>
                <p><strong>License Plate:</strong> <?php echo htmlspecialchars($car['license_plate']); ?></p>
                <p><strong>Color:</strong> <?php echo htmlspecialchars($car['color']); ?></p>
                <p><strong>Transmission:</strong> <?php echo ucfirst($car['transmission']); ?></p>
                <p><strong>Fuel Type:</strong> <?php echo ucfirst($car['fuel_type']); ?></p>
                <p><strong>Seats:</strong> <?php echo $car['seats']; ?></p>
                <p><strong>Price per Day:</strong> $<?php echo number_format($car['price_per_day'], 2); ?></p>
                <p><strong>Status:</strong>
                    <span class="badge bg-<?php echo $car['is_available'] ? 'success' : 'danger'; ?>">
                        <?php echo $car['is_available'] ? 'Available' : 'Rented'; ?>
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-user-tie me-2"></i>Owner Information</h6>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($car['owner_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($car['owner_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($car['owner_phone']); ?></p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <h6><i class="fas fa-file-alt me-2"></i>Description</h6>
                <p><?php echo htmlspecialchars($car['description']); ?></p>
            </div>
        </div>

        <?php if($car['latitude'] && $car['longitude']): ?>
        <div class="row mt-3">
            <div class="col-12">
                <h6><i class="fas fa-map-marker-alt me-2"></i>Location</h6>
                <p>Latitude: <?php echo $car['latitude']; ?>, Longitude: <?php echo $car['longitude']; ?></p>
            </div>
        </div>
        <?php endif; ?>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-calendar-check me-2"></i>Recent Bookings</h6>
                <?php if(empty($recent_bookings)): ?>
                    <p class="text-muted">No bookings yet.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($recent_bookings as $booking): ?>
                        <li class="list-group-item px-0">
                            <small>
                                <strong><?php echo htmlspecialchars($booking['client_name']); ?></strong><br>
                                <?php echo $booking['start_date']; ?> to <?php echo $booking['end_date']; ?><br>
                                <span class="badge bg-<?php
                                    switch($booking['status']) {
                                        case 'confirmed': echo 'success'; break;
                                        case 'pending': echo 'warning'; break;
                                        case 'cancelled': echo 'danger'; break;
                                        default: echo 'secondary';
                                    }
                                ?>"><?php echo ucfirst($booking['status']); ?></span>
                            </small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <h6><i class="fas fa-route me-2"></i>Location History</h6>
                <?php if(empty($locations)): ?>
                    <p class="text-muted">No location data available.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($locations as $location): ?>
                        <li class="list-group-item px-0">
                            <small>
                                Lat: <?php echo number_format($location['latitude'], 6); ?><br>
                                Lng: <?php echo number_format($location['longitude'], 6); ?><br>
                                <span class="text-muted"><?php echo date('M d, H:i', strtotime($location['timestamp'])); ?></span>
                            </small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    } else {
        echo '<p class="text-danger">Car not found.</p>';
    }
}
?>

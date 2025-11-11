<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'owner') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get owner's cars with latest location
$query = "
    SELECT c.*, cl.latitude, cl.longitude, cl.timestamp
    FROM cars c
    LEFT JOIN (
        SELECT car_id, latitude, longitude, timestamp
        FROM car_locations
        WHERE (car_id, timestamp) IN (
            SELECT car_id, MAX(timestamp)
            FROM car_locations
            GROUP BY car_id
        )
    ) cl ON c.id = cl.car_id
    WHERE c.owner_id = ?
    ORDER BY c.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$cars = $stmt->fetchAll();

$page_title = "GPS Tracking - My Cars";
include '../header.php';
?>

<style>
.gps-page {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.map-container {
    height: 600px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.car-list {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    padding: 2rem;
}

.car-item {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.car-item:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.location-info {
    font-size: 0.9rem;
    color: #6c757d;
}

.location-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
}

.no-location {
    color: #dc3545;
    font-style: italic;
}
</style>

<div class="gps-page">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4"><i class="fas fa-map-marker-alt text-primary me-2"></i>GPS Tracking - My Cars</h2>
            </div>
        </div>

        <!-- Map Container -->
        <div class="row mb-4">
            <div class="col-12">
                <div id="map" class="map-container"></div>
            </div>
        </div>

        <!-- Cars List -->
        <div class="row">
            <div class="col-12">
                <div class="car-list">
                    <h4 class="mb-3"><i class="fas fa-car me-2"></i>My Cars & Locations</h4>

                    <?php if(empty($cars)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-car fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No cars found</h5>
                            <p class="text-muted">You haven't added any cars yet.</p>
                            <a href="add_car.php" class="btn btn-primary">Add Your First Car</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach($cars as $car): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="car-item">
                                        <h6><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h6>
                                        <p class="mb-2"><strong>License:</strong> <?php echo htmlspecialchars($car['license_plate']); ?></p>

                                        <?php if($car['latitude'] && $car['longitude']): ?>
                                            <div class="location-info">
                                                <i class="fas fa-map-marker-alt text-success me-1"></i>
                                                <strong>Last Location:</strong><br>
                                                Lat: <?php echo number_format($car['latitude'], 6); ?><br>
                                                Lng: <?php echo number_format($car['longitude'], 6); ?><br>
                                                <small>Updated: <?php echo date('M d, Y H:i', strtotime($car['timestamp'])); ?></small>
                                            </div>
                                            <div class="mt-2">
                                                <span class="location-badge">
                                                    <i class="fas fa-check-circle me-1"></i>Tracked
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <div class="location-info no-location">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                No location data available
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAPS_API_KEY; ?>&callback=initMap"></script>

<script>
let map;
let markers = [];

function initMap() {
    // Default center (can be adjusted)
    const defaultCenter = { lat: -1.2864, lng: 36.8172 }; // Nairobi coordinates as example

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 10,
        center: defaultCenter,
        styles: [
            {
                featureType: 'poi',
                stylers: [{ visibility: 'off' }]
            }
        ]
    });

    // Add markers for cars with location data
    <?php foreach($cars as $car): ?>
        <?php if($car['latitude'] && $car['longitude']): ?>
            addMarker({
                lat: <?php echo $car['latitude']; ?>,
                lng: <?php echo $car['longitude']; ?>,
                title: '<?php echo addslashes($car['brand'] . ' ' . $car['model']); ?>',
                license: '<?php echo addslashes($car['license_plate']); ?>',
                lastUpdate: '<?php echo date('M d, Y H:i', strtotime($car['timestamp'])); ?>'
            });
        <?php endif; ?>
    <?php endforeach; ?>

    // Fit map to show all markers
    if (markers.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        markers.forEach(marker => bounds.extend(marker.getPosition()));
        map.fitBounds(bounds);

        // Ensure minimum zoom level
        google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
            if (map.getZoom() > 15) {
                map.setZoom(15);
            }
        });
    }
}

function addMarker(carData) {
    const marker = new google.maps.Marker({
        position: { lat: carData.lat, lng: carData.lng },
        map: map,
        title: carData.title,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
            scaledSize: new google.maps.Size(32, 32)
        }
    });

    const infoWindow = new google.maps.InfoWindow({
        content: `
            <div style="max-width: 200px;">
                <h6>${carData.title}</h6>
                <p><strong>License:</strong> ${carData.license}</p>
                <p><small><strong>Last Update:</strong><br>${carData.lastUpdate}</small></p>
            </div>
        `
    });

    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });

    markers.push(marker);
}

// Handle map loading errors
window.gm_authFailure = function() {
    document.getElementById('map').innerHTML = `
        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <h5>Map Loading Failed</h5>
                <p>Please check your internet connection or contact support.</p>
            </div>
        </div>
    `;
};
</script>

<?php include '../footer.php'; ?>

<?php include '../config/config.php'; ?>
<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveShare - Find Your Perfect Ride</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --accent: #f59e0b;
            --light: #f8fafc;
            --dark: #1e293b;
            --success: #10b981;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #334155;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(30, 41, 59, 0.85), rgba(30, 41, 59, 0.9)), url('https://images.unsplash.com/photo-1486496572940-2bb2341fdbdf?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0;
            position: relative;
        }

        .hero-section h1 {
            font-weight: 700;
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
        }

        .hero-section .lead {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-btn {
            background-color: var(--accent);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .hero-btn:hover {
            background-color: #e69007;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-weight: 700;
            color: var(--dark);
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .section-header h2:after {
            content: '';
            position: absolute;
            width: 60%;
            height: 4px;
            background: var(--accent);
            bottom: -10px;
            left: 20%;
            border-radius: 2px;
        }

        .section-header p {
            color: var(--secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Car Cards */
        .car-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
            height: 100%;
        }

        .car-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }

        .car-card-img {
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .car-card-body {
            padding: 1.5rem;
        }

        .car-card-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
            font-size: 1.25rem;
        }

        .car-card-features {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .car-feature {
            display: flex;
            align-items: center;
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .car-feature i {
            margin-right: 5px;
            color: var(--primary);
        }

        .car-price {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .car-card-btn {
            background-color: var(--primary);
            border: none;
            width: 100%;
            padding: 10px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .car-card-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Map Section */
        .map-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-bottom: 3rem;
        }

        #map {
            height: 400px;
            width: 100%;
        }

        /* Features Section */
        .features-section {
            background-color: white;
            padding: 80px 0;
        }

        .feature-card {
            text-align: center;
            padding: 2rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 1.75rem;
        }

        .feature-card h4 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--secondary);
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 80px 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Animation */
        .fade-in {
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }

            .hero-section {
                padding: 80px 0;
            }

            .stat-number {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section text-center fade-in">
        <div class="container">
            <h1 class="display-4 fw-bold">Find Your Perfect Ride</h1>
            <p class="lead">Quality cars from trusted owners across the city. Book in minutes, drive in style.</p>
            <a href="#cars-section" class="btn hero-btn">Browse Cars <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose DriveShare</h2>
                <p>Experience the convenience of peer-to-peer car rental with our premium service</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Verified Owners</h4>
                        <p>All car owners are thoroughly vetted to ensure safety and reliability for your journey.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h4>Best Prices</h4>
                        <p>Save up to 35% compared to traditional rental companies with no hidden fees.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h4>Wide Selection</h4>
                        <p>Choose from hundreds of vehicles - from economy cars to luxury vehicles.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Cars Section -->
    <section id="cars-section" class="container my-5 py-5">
        <div class="section-header">
            <h2>Available Cars</h2>
            <p>Browse our selection of quality vehicles available for rent</p>
        </div>
        <div class="row g-4" id="cars-container">
            <?php
            $stmt = $pdo->query("SELECT c.*, u.full_name as owner_name FROM cars c JOIN users u ON c.owner_id = u.id WHERE c.is_available = 1 LIMIT 6");
            while($car = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card car-card">
                    <div class="position-relative">
                        <img src="<?php echo $car['image_path'] ?: 'images/default-car.jpg'; ?>" class="car-card-img" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-success">Available</span>
                        </div>
                    </div>
                    <div class="car-card-body">
                        <h5 class="car-card-title"><?php echo $car['brand'] . ' ' . $car['model']; ?> (<?php echo $car['year']; ?>)</h5>

                        <div class="car-card-features">
                            <div class="car-feature">
                                <i class="fas fa-cog"></i>
                                <span><?php echo ucfirst($car['transmission']); ?></span>
                            </div>
                            <div class="car-feature">
                                <i class="fas fa-gas-pump"></i>
                                <span><?php echo ucfirst($car['fuel_type']); ?></span>
                            </div>
                            <div class="car-feature">
                                <i class="fas fa-users"></i>
                                <span><?php echo $car['seats']; ?> seats</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="car-price">$<?php echo $car['price_per_day']; ?>/day</div>
                            <div class="text-muted small">Owner: <?php echo $car['owner_name']; ?></div>
                        </div>

                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'client'): ?>
                            <a href="book_car.php?car_id=<?php echo $car['id']; ?>" class="btn car-card-btn text-white">Book Now</a>
                        <?php else: ?>
                            <a href="login.php" class="btn car-card-btn text-white">Login to Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="text-center mt-5">
            <a href="cars.php" class="btn btn-outline-primary btn-lg">View All Cars <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number">150+</div>
                        <div class="stat-label">Available Cars</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number">25+</div>
                        <div class="stat-label">Cities</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="container my-5 py-5">
        <div class="section-header">
            <h2>Find Cars Near You</h2>
            <p>Locate available vehicles in your area with our interactive map</p>
        </div>
        <div class="map-container">
            <div id="map"></div>
        </div>
    </section>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    function initMap() {
        const map = L.map('map').setView([40.7128, -74.0060], 12); // Default to New York

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Custom car icon
        const carIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Add car markers
        <?php
        $stmt = $pdo->query("SELECT * FROM cars WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND is_available = 1");
        while($car = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
        L.marker([<?php echo $car['latitude']; ?>, <?php echo $car['longitude']; ?>], {icon: carIcon})
            .addTo(map)
            .bindPopup(`
                <div class="p-2">
                    <h5 class="mb-1"><?php echo $car['brand'] . ' ' . $car['model']; ?></h5>
                    <p class="mb-1">$<?php echo $car['price_per_day']; ?>/day</p>
                    <a href="book_car.php?car_id=<?php echo $car['id']; ?>" class="btn btn-sm btn-primary mt-1">Book Now</a>
                </div>
            `);
        <?php endwhile; ?>

        // Get user's location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLocation = [position.coords.latitude, position.coords.longitude];

                    L.marker(userLocation, {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    })
                    .addTo(map)
                    .bindPopup("Your Location");

                    map.setView(userLocation, 13);
                },
                () => {
                    console.log("Geolocation failed");
                }
            );
        }
    }

    document.addEventListener('DOMContentLoaded', initMap);
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>

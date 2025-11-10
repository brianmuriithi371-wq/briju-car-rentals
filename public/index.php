<?php include '../config/config.php'; ?>
<?php include 'header.php'; ?>

    <!-- Additional CSS for index page -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
     <link rel="icon" type="image/png" sizes="96x96" href="../assets/imgs/icons8-car-96.png">
    <link rel="stylesheet" href="assets/css/index.css">
    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="fade-in">Find Your Perfect Ride</h1>
                    <p class="lead fade-in">Quality cars from trusted owners across the city. Book in minutes, drive in style.</p>
                    <a href="#cars-section" class="btn hero-btn fade-in">Browse Cars <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
                <div class="col-lg-6">
                    <!-- Quick Booking Form -->
                    <div class="booking-form fade-in">
                        <h3 class="mb-4">Book Your Car</h3>
                        <form id="bookingForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pick-up Location</label>
                                    <input type="text" class="form-control" placeholder="Enter location">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Drop-off Location</label>
                                    <input type="text" class="form-control" placeholder="Enter location">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pick-up Date</label>
                                    <input type="date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Drop-off Date</label>
                                    <input type="date" class="form-control">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn hero-btn w-100">Check Availability</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
                        <img src="<?php echo $car['image_path'] ? '../' . $car['image_path'] : 'images/default-car.jpg'; ?>" class="car-card-img" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
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
    // Animated counter for stats section
    function animateCounter(elementId, finalValue, duration) {
        let element = document.getElementById(elementId);
        let startValue = 0;
        let increment = finalValue / (duration / 16); // 60fps
        let currentValue = startValue;

        function updateCounter() {
            currentValue += increment;
            if (currentValue < finalValue) {
                element.textContent = Math.floor(currentValue);
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = finalValue;
            }
        }

        updateCounter();
    }

    // Initialize counters when stats section is in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter('stat1', 500, 2000);
                animateCounter('stat2', 150, 1500);
                animateCounter('stat3', 25, 1500);
                animateCounter('stat4', 98, 1000);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    observer.observe(document.querySelector('.stats-section'));

    function initMap() {
        const map = L.map('map').setView([-1.286389, 36.817223], 12); // Default to Nairobi, Kenya

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

    // Form submission handler
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Thank you! We will check availability and contact you shortly.');
    });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php include '../config/config.php'; ?>
<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Cars - DriveShare</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #001135ff;
            --primary-dark: #012b19ff;
            --secondary: #64748b;
            --accent: #5a3b07ff;
            --light: #f8fafc;
            --dark: #030f24ff;
            --success: #023524ff;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #091a33ff;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .page-header h1 {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Filters Section */
        .filters-section {
            background: white;
            padding: 30px 0;
            box-shadow: 0 2px 4px rgba(8, 22, 100, 0.45);
        }

        .filter-form {
            max-width: 1200px;
            margin: 0 auto;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-btn {
            background-color: var(--primary);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Cars Grid */
        .cars-grid {
            padding: 50px 0;
        }

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

        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary);
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            padding: 40px 0;
        }

        .pagination {
            --bs-pagination-color: var(--primary);
            --bs-pagination-hover-color: white;
            --bs-pagination-hover-bg: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                min-width: auto;
            }

            .car-card-features {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>All Available Cars</h1>
            <p>Find the perfect vehicle for your next adventure. Browse our complete collection of rental cars.</p>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="filters-section">
        <div class="container">
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search" class="form-label fw-bold">
                            <i class="fas fa-search me-2"></i>Search Cars
                        </label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Brand, model, or location..."
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="transmission" class="form-label fw-bold">
                            <i class="fas fa-cog me-2"></i>Transmission
                        </label>
                        <select name="transmission" id="transmission" class="form-select">
                            <option value="">All Types</option>
                            <option value="manual" <?php echo (isset($_GET['transmission']) && $_GET['transmission'] == 'manual') ? 'selected' : ''; ?>>Manual</option>
                            <option value="automatic" <?php echo (isset($_GET['transmission']) && $_GET['transmission'] == 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="fuel_type" class="form-label fw-bold">
                            <i class="fas fa-gas-pump me-2"></i>Fuel Type
                        </label>
                        <select name="fuel_type" id="fuel_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="petrol" <?php echo (isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'petrol') ? 'selected' : ''; ?>>Petrol</option>
                            <option value="diesel" <?php echo (isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                            <option value="electric" <?php echo (isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'electric') ? 'selected' : ''; ?>>Electric</option>
                            <option value="hybrid" <?php echo (isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="max_price" class="form-label fw-bold">
                            <i class="fas fa-dollar-sign me-2"></i>Max Price/Day
                        </label>
                        <input type="number" name="max_price" id="max_price" class="form-control" placeholder="Any"
                               value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>" min="0">
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn filter-btn text-white">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Cars Grid -->
    <section class="cars-grid">
        <div class="container">
            <div class="row g-4">
                <?php
                // Build query with filters
                $where_conditions = ["c.is_available = 1"];
                $params = [];

                if (!empty($_GET['search'])) {
                    $search = '%' . $_GET['search'] . '%';
                    $where_conditions[] = "(c.brand LIKE ? OR c.model LIKE ? OR c.license_plate LIKE ? OR u.full_name LIKE ?)";
                    $params = array_merge($params, [$search, $search, $search, $search]);
                }

                if (!empty($_GET['transmission'])) {
                    $where_conditions[] = "c.transmission = ?";
                    $params[] = $_GET['transmission'];
                }

                if (!empty($_GET['fuel_type'])) {
                    $where_conditions[] = "c.fuel_type = ?";
                    $params[] = $_GET['fuel_type'];
                }

                if (!empty($_GET['max_price'])) {
                    $where_conditions[] = "c.price_per_day <= ?";
                    $params[] = $_GET['max_price'];
                }

                $where_clause = implode(' AND ', $where_conditions);

                // Pagination
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $per_page = 12;
                $offset = ($page - 1) * $per_page;

                // Get total count
                $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM cars c JOIN users u ON c.owner_id = u.id WHERE $where_clause");
                $count_stmt->execute($params);
                $total_cars = $count_stmt->fetchColumn();
                $total_pages = ceil($total_cars / $per_page);

                // Get cars
                $stmt = $pdo->prepare("SELECT c.*, u.full_name as owner_name FROM cars c JOIN users u ON c.owner_id = u.id WHERE $where_clause ORDER BY c.created_at DESC LIMIT $per_page OFFSET $offset");
                $stmt->execute($params);
                $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($cars)):
                ?>
                <div class="col-12">
                    <div class="no-results">
                        <i class="fas fa-car"></i>
                        <h3>No cars found</h3>
                        <p>Try adjusting your search criteria or browse all available cars.</p>
                        <a href="cars.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach($cars as $car): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
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
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-wrapper">
                <nav aria-label="Car pagination">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php
include '../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'owner') {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $license_plate = $_POST['license_plate'];
    $color = $_POST['color'];
    $seats = $_POST['seats'];
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $price_per_day = $_POST['price_per_day'];
    $description = $_POST['description'];
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    // Handle image upload
    $image_path = null;
    if(isset($_FILES['car_image']) && $_FILES['car_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if(in_array($_FILES['car_image']['type'], $allowed_types) && $_FILES['car_image']['size'] <= $max_size) {
            $upload_dir = '../assets/uploads/';
            $file_extension = pathinfo($_FILES['car_image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('car_') . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;

            if(move_uploaded_file($_FILES['car_image']['tmp_name'], $target_path)) {
                $image_path = 'assets/uploads/' . $file_name;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO cars (owner_id, brand, model, year, license_plate, color, seats, transmission, fuel_type, price_per_day, description, image_path, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $brand, $model, $year, $license_plate, $color, $seats, $transmission, $fuel_type, $price_per_day, $description, $image_path, $latitude, $longitude]);

        $_SESSION['success'] = "Car added successfully!";
        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = "Failed to add car: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                    <h4 class="mb-0">Add New Car</h4>
                    <p class="mb-0">List your car for rental</p>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="user-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">
                                        <i class="fas fa-car text-success me-2"></i>Brand *
                                    </label>
                                    <input type="text" name="brand" id="brand" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="model" class="form-label">
                                        <i class="fas fa-car-side text-success me-2"></i>Model *
                                    </label>
                                    <input type="text" name="model" id="model" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="year" class="form-label">
                                        <i class="fas fa-calendar text-success me-2"></i>Year *
                                    </label>
                                    <input type="number" name="year" id="year" class="form-control" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="color" class="form-label">
                                        <i class="fas fa-palette text-success me-2"></i>Color
                                    </label>
                                    <input type="text" name="color" id="color" class="form-control" placeholder="e.g., Red, Blue">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="seats" class="form-label">
                                        <i class="fas fa-users text-success me-2"></i>Seats
                                    </label>
                                    <input type="number" name="seats" id="seats" class="form-control" min="1" max="20">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transmission" class="form-label">
                                        <i class="fas fa-cog text-success me-2"></i>Transmission *
                                    </label>
                                    <select name="transmission" id="transmission" class="form-control" required>
                                        <option value="">Select transmission</option>
                                        <option value="manual">Manual</option>
                                        <option value="automatic">Automatic</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fuel_type" class="form-label">
                                        <i class="fas fa-gas-pump text-success me-2"></i>Fuel Type *
                                    </label>
                                    <select name="fuel_type" id="fuel_type" class="form-control" required>
                                        <option value="">Select fuel type</option>
                                        <option value="petrol">Petrol</option>
                                        <option value="diesel">Diesel</option>
                                        <option value="electric">Electric</option>
                                        <option value="hybrid">Hybrid</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="license_plate" class="form-label">
                                        <i class="fas fa-id-card text-success me-2"></i>License Plate *
                                    </label>
                                    <input type="text" name="license_plate" id="license_plate" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price_per_day" class="form-label">
                                        <i class="fas fa-dollar-sign text-success me-2"></i>Price per Day ($) *
                                    </label>
                                    <input type="number" name="price_per_day" id="price_per_day" class="form-control" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt text-success me-2"></i>Description
                            </label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Describe your car, features, condition, etc."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="car_image" class="form-label">
                                <i class="fas fa-image text-success me-2"></i>Car Image
                            </label>
                            <input type="file" name="car_image" id="car_image" class="form-control" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 5MB</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">
                                        <i class="fas fa-map-marker-alt text-success me-2"></i>Latitude
                                    </label>
                                    <input type="number" name="latitude" id="latitude" class="form-control" step="any" placeholder="Optional">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">
                                        <i class="fas fa-map-marker-alt text-success me-2"></i>Longitude
                                    </label>
                                    <input type="number" name="longitude" id="longitude" class="form-control" step="any" placeholder="Optional">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary" onclick="getCurrentLocation()">
                                <i class="fas fa-crosshairs me-2"></i>Get Current Location
                            </button>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-plus-circle me-2"></i>Add Car
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Get current location
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
            },
            (error) => {
                alert('Unable to retrieve your location. Please enter coordinates manually.');
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Image preview
document.getElementById('car_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Could add image preview here if needed
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'footer.php'; ?>

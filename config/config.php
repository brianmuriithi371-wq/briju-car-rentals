<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'briju_car_rental');
define('DB_USER', 'root');
define('DB_PASS', '');

// Google Maps API Key (replace with your actual key)
define('GMAPS_API_KEY', 'your_google_maps_api_key_here');

// M-Pesa API Configuration (replace with your actual credentials)
define('MPESA_CONSUMER_KEY', 'your_mpesa_consumer_key_here');
define('MPESA_CONSUMER_SECRET', 'your_mpesa_consumer_secret_here');
define('MPESA_SHORTCODE', 'your_mpesa_shortcode_here');
define('MPESA_PASSKEY', 'your_mpesa_passkey_here');
define('MPESA_ENVIRONMENT', 'sandbox'); // Change to 'production' for live

// Bank ATM Configuration (for simulation)
define('BANK_API_ENDPOINT', 'https://api.example-bank.com/payment');
define('BANK_API_KEY', 'your_bank_api_key_here');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();

// Admin authentication helper functions
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../admin/login.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}
?>
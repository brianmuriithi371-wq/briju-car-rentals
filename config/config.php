<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'briju_car_rental');
define('DB_USER', 'root');
define('DB_PASS', '');

// Google Maps API Key (replace with your actual key)
define('GMAPS_API_KEY', 'your_google_maps_api_key_here');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();
?>
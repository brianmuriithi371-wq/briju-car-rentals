<?php
// update_location.php - For mobile app or car tracking device
include '../config/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $car_id = $_POST['car_id'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    // Update car's current location
    $stmt = $pdo->prepare("UPDATE cars SET latitude = ?, longitude = ? WHERE id = ?");
    $stmt->execute([$latitude, $longitude, $car_id]);
    
    // Log location history
    $stmt = $pdo->prepare("INSERT INTO car_locations (car_id, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->execute([$car_id, $latitude, $longitude]);
    
    echo json_encode(['status' => 'success']);
}
?>
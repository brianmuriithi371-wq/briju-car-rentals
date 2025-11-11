<?php
include '../config/config.php';

header('Content-Type: application/json');

if (!isset($_GET['username']) || empty(trim($_GET['username']))) {
    echo json_encode(['available' => false, 'error' => 'Username parameter missing']);
    exit;
}

$username = trim($_GET['username']);

// Basic validation
if (strlen($username) < 3) {
    echo json_encode(['available' => false, 'error' => 'Username too short']);
    exit;
}

if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
    echo json_encode(['available' => false, 'error' => 'Invalid username format']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        echo json_encode(['available' => false]);
    } else {
        echo json_encode(['available' => true]);
    }
} catch (PDOException $e) {
    error_log("Username check error: " . $e->getMessage());
    echo json_encode(['available' => false, 'error' => 'Database error']);
}
?>

<?php
include '../../config/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $status = $_POST['status'] ?? '';

    $valid_statuses = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];

    if(!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $booking_id]);

        // If booking is confirmed, mark car as unavailable
        if($status == 'confirmed') {
            $stmt = $pdo->prepare("UPDATE cars SET is_available = 0 WHERE id = (SELECT car_id FROM bookings WHERE id = ?)");
            $stmt->execute([$booking_id]);
        }

        // If booking is completed or cancelled, mark car as available
        if($status == 'completed' || $status == 'cancelled') {
            $stmt = $pdo->prepare("UPDATE cars SET is_available = 1 WHERE id = (SELECT car_id FROM bookings WHERE id = ?)");
            $stmt->execute([$booking_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>

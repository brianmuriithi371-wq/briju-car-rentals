<?php
// AJAX endpoint to check payment status
header('Content-Type: application/json');

include '../config/config.php';
include '../includes/mpesa.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$booking_id = $_GET['booking_id'] ?? 0;

if (!$booking_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
    exit();
}

// Get payment details
$stmt = $pdo->prepare("
    SELECT p.*, b.client_id
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    WHERE p.booking_id = ? AND b.client_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    echo json_encode(['success' => false, 'error' => 'Payment not found']);
    exit();
}

// If M-Pesa payment, check with M-Pesa API
if ($payment['payment_method'] == 'mpesa' && $payment['status'] == 'pending') {
    $mpesa = new MpesaAPI();
    $status_response = $mpesa->querySTKStatus($payment['transaction_id']);

    if (isset($status_response['ResponseCode']) && $status_response['ResponseCode'] == '0') {
        $result_code = $status_response['ResultCode'] ?? null;

        if ($result_code == '0') {
            // Payment successful
            $stmt = $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW() WHERE booking_id = ?");
            $stmt->execute([$booking_id]);

            $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$booking_id]);

            $stmt = $pdo->prepare("UPDATE cars SET is_available = 0 WHERE id = (SELECT car_id FROM bookings WHERE id = ?)");
            $stmt->execute([$booking_id]);

            echo json_encode(['success' => true, 'status' => 'completed']);
            exit();
        } elseif (in_array($result_code, ['1', '1032', '1037', '2001'])) {
            // Payment failed
            $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE booking_id = ?");
            $stmt->execute([$booking_id]);

            echo json_encode(['success' => true, 'status' => 'failed']);
            exit();
        }
    }
}

echo json_encode([
    'success' => true,
    'status' => $payment['status'],
    'message' => 'Status checked successfully'
]);
?>

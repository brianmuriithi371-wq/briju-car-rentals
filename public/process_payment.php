<?php
include '../config/config.php';

if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = $_POST['booking_id'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$amount = $_POST['amount'] ?? 0;

// Validate required fields
if(empty($booking_id) || empty($payment_method) || empty($amount)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Verify booking belongs to user and get booking details
$stmt = $pdo->prepare("SELECT b.*, c.brand, c.model, u.full_name as owner_name, u.phone as owner_phone
                      FROM bookings b
                      JOIN cars c ON b.car_id = c.id
                      JOIN users u ON c.owner_id = u.id
                      WHERE b.id = ? AND b.client_id = ?");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if(!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
    exit();
}

// Check if payment already exists
$stmt = $pdo->prepare("SELECT id FROM payments WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$existing_payment = $stmt->fetch();

if($existing_payment) {
    echo json_encode(['success' => false, 'message' => 'Payment already exists for this booking']);
    exit();
}

try {
    // Generate transaction ID
    $transaction_id = 'TXN_' . time() . '_' . $booking_id;

    // Insert payment record
    $stmt = $pdo->prepare("INSERT INTO payments (booking_id, transaction_id, payment_method, amount, status, phone_number, account_number, created_at)
                          VALUES (?, ?, ?, ?, 'pending', ?, ?, NOW())");

    $phone_number = $_POST['phone_number'] ?? null;
    $account_number = $_POST['account_number'] ?? null;

    // Set phone_number for M-Pesa, account_number for bank ATM
    if($payment_method === 'mpesa') {
        $phone_number = $_POST['phone_number'] ?? '';
    } elseif($payment_method === 'bank_atm') {
        $account_number = $_POST['account_number'] ?? '';
    }

    $stmt->execute([$booking_id, $transaction_id, $payment_method, $amount, $phone_number, $account_number]);

    $payment_id = $pdo->lastInsertId();

    // Process payment based on method
    $response = ['success' => true, 'message' => 'Payment initiated successfully'];

    switch($payment_method) {
        case 'mpesa':
            // Include M-Pesa processing
            include '../includes/mpesa.php';
            $mpesa = new MpesaPayment();
            $result = $mpesa->initiateSTKPush($phone_number, $amount, $booking_id, $transaction_id);

            if($result['success']) {
                $response['message'] = 'M-Pesa STK Push sent. Please check your phone and enter your PIN.';
            } else {
                // Update payment status to failed
                $pdo->prepare("UPDATE payments SET status = 'failed' WHERE id = ?")->execute([$payment_id]);
                $response = ['success' => false, 'message' => $result['message']];
            }
            break;

        case 'bank_atm':
            // Include bank payment processing
            include '../includes/bank_payment.php';
            $bank_payment = new BankPayment();
            $result = $bank_payment->processPayment($account_number, $amount, $booking_id, $transaction_id);

            if($result['success']) {
                $response['message'] = 'Bank payment initiated. Please complete the transaction at your nearest ATM.';
            } else {
                // Update payment status to failed
                $pdo->prepare("UPDATE payments SET status = 'failed' WHERE id = ?")->execute([$payment_id]);
                $response = ['success' => false, 'message' => $result['message']];
            }
            break;

        case 'cash':
            $response['message'] = 'Cash payment recorded. Please contact the car owner to arrange payment.';
            // For cash, we might want to keep it as pending until confirmed by owner
            break;

        case 'card':
            // For demo purposes, simulate card payment success
            $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW() WHERE id = ?")->execute([$payment_id]);
            $response['message'] = 'Card payment processed successfully!';
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid payment method'];
    }

    echo json_encode($response);

} catch(Exception $e) {
    error_log('Payment processing error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing payment']);
}
?>

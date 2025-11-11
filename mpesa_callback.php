<?php
// M-Pesa Payment Callback Handler
include 'config/config.php';
include 'includes/mpesa.php';

// Get the callback data
$callbackData = file_get_contents('php://input');
$callbackData = json_decode($callbackData, true);

// Log the callback for debugging
file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . ' - ' . json_encode($callbackData) . PHP_EOL, FILE_APPEND);

if (!$callbackData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid callback data']);
    exit;
}

// Extract callback details
$stkCallback = $callbackData['Body']['stkCallback'] ?? null;
if (!$stkCallback) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid STK callback']);
    exit;
}

$merchantRequestId = $stkCallback['MerchantRequestID'];
$checkoutRequestId = $stkCallback['CheckoutRequestID'];
$resultCode = $stkCallback['ResultCode'];
$resultDesc = $stkCallback['ResultDesc'];

try {
    // Find the payment record by checkout request ID
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_id = ?");
    $stmt->execute([$checkoutRequestId]);
    $payment = $stmt->fetch();

    if (!$payment) {
        // Try to find by merchant request ID if checkout ID not found
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE notes LIKE ?");
        $stmt->execute(['%' . $merchantRequestId . '%']);
        $payment = $stmt->fetch();
    }

    if ($payment) {
        if ($resultCode == 0) {
            // Payment successful
            $callbackMetadata = $stkCallback['CallbackMetadata']['Item'];

            $amount = 0;
            $mpesaReceiptNumber = '';
            $transactionDate = null;
            $phoneNumber = '';

            foreach ($callbackMetadata as $item) {
                switch ($item['Name']) {
                    case 'Amount':
                        $amount = $item['Value'];
                        break;
                    case 'MpesaReceiptNumber':
                        $mpesaReceiptNumber = $item['Value'];
                        break;
                    case 'TransactionDate':
                        $transactionDate = date('Y-m-d H:i:s', strtotime($item['Value']));
                        break;
                    case 'PhoneNumber':
                        $phoneNumber = $item['Value'];
                        break;
                }
            }

            // Update payment record
            $stmt = $pdo->prepare("
                UPDATE payments SET
                    status = 'completed',
                    payment_date = ?,
                    mpesa_receipt_number = ?,
                    phone_number = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$transactionDate, $mpesaReceiptNumber, $phoneNumber, $payment['id']]);

            // Update booking status to confirmed
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$payment['booking_id']]);

            // Mark car as unavailable
            $stmt = $pdo->prepare("
                UPDATE cars SET is_available = 0
                WHERE id = (SELECT car_id FROM bookings WHERE id = ?)
            ");
            $stmt->execute([$payment['booking_id']]);

            // Log success
            file_put_contents('mpesa_callback_log.txt',
                date('Y-m-d H:i:s') . ' - Payment completed: ' . $mpesaReceiptNumber . PHP_EOL,
                FILE_APPEND
            );

        } else {
            // Payment failed
            $stmt = $pdo->prepare("
                UPDATE payments SET
                    status = 'failed',
                    notes = CONCAT(IFNULL(notes, ''), ' | Callback: ', ?),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$resultDesc, $payment['id']]);

            // Log failure
            file_put_contents('mpesa_callback_log.txt',
                date('Y-m-d H:i:s') . ' - Payment failed: ' . $resultDesc . PHP_EOL,
                FILE_APPEND
            );
        }
    } else {
        // Payment record not found
        file_put_contents('mpesa_callback_log.txt',
            date('Y-m-d H:i:s') . ' - Payment record not found for: ' . $checkoutRequestId . PHP_EOL,
            FILE_APPEND
        );
    }

    // Respond to M-Pesa
    http_response_code(200);
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback received successfully']);

} catch (Exception $e) {
    // Log error
    file_put_contents('mpesa_callback_log.txt',
        date('Y-m-d H:i:s') . ' - Error: ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );

    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>

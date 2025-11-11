<?php
// Bank ATM Payment Simulation Class
class BankPaymentAPI {
    private $api_endpoint;
    private $api_key;

    public function __construct() {
        $this->api_endpoint = BANK_API_ENDPOINT;
        $this->api_key = BANK_API_KEY;
    }

    // Simulate bank ATM payment processing
    public function processATMPayment($account_number, $amount, $description = 'Car Rental Payment') {
        // Simulate API call to bank
        // In a real implementation, this would make an actual API call

        // Simulate processing delay
        sleep(2);

        // Simulate success/failure randomly (90% success rate)
        $success = rand(1, 10) > 1;

        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'BANK' . date('YmdHis') . rand(1000, 9999),
                'reference_number' => 'REF' . rand(100000, 999999),
                'status' => 'completed',
                'message' => 'Payment processed successfully via ATM',
                'processed_at' => date('Y-m-d H:i:s')
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Payment failed - Insufficient funds or invalid account',
                'status' => 'failed'
            ];
        }
    }

    // Check payment status (simulation)
    public function checkPaymentStatus($transaction_id) {
        // Simulate status check
        sleep(1);

        // Simulate different statuses
        $statuses = ['completed', 'processing', 'failed'];
        $random_status = $statuses[array_rand($statuses)];

        return [
            'transaction_id' => $transaction_id,
            'status' => $random_status,
            'last_checked' => date('Y-m-d H:i:s')
        ];
    }

    // Get supported banks (Kenya)
    public function getSupportedBanks() {
        return [
            ['code' => 'KCB', 'name' => 'Kenya Commercial Bank'],
            ['code' => 'EQUITY', 'name' => 'Equity Bank'],
            ['code' => 'COOP', 'name' => 'Cooperative Bank'],
            ['code' => 'STANBIC', 'name' => 'Stanbic Bank'],
            ['code' => 'BARCLAYS', 'name' => 'Barclays Bank'],
            ['code' => 'ABSA', 'name' => 'Absa Bank'],
            ['code' => 'DTB', 'name' => 'Diamond Trust Bank'],
            ['code' => 'NCBA', 'name' => 'NCBA Bank']
        ];
    }
}

// Helper functions for bank payments
function validateAccountNumber($account_number) {
    // Basic validation - account number should be 10-16 digits
    return preg_match('/^\d{10,16}$/', $account_number);
}

function formatAccountNumber($account_number) {
    // Remove spaces and dashes
    return preg_replace('/[\s\-]/', '', $account_number);
}

function generateBankReference() {
    return 'BANKREF' . date('YmdHis') . rand(1000, 9999);
}
?>

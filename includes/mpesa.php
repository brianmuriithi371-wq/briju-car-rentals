<?php
// M-Pesa API Integration Class
class MpesaAPI {
    private $consumer_key;
    private $consumer_secret;
    private $shortcode;
    private $passkey;
    private $environment;
    private $access_token;

    public function __construct() {
        $this->consumer_key = MPESA_CONSUMER_KEY;
        $this->consumer_secret = MPESA_CONSUMER_SECRET;
        $this->shortcode = MPESA_SHORTCODE;
        $this->passkey = MPESA_PASSKEY;
        $this->environment = MPESA_ENVIRONMENT;
        $this->access_token = $this->getAccessToken();
    }

    // Get M-Pesa access token
    private function getAccessToken() {
        $url = $this->environment === 'production'
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }

        return null;
    }

    // Initiate STK Push
    public function stkPush($phone, $amount, $account_reference, $transaction_desc = 'Car Rental Payment') {
        if (!$this->access_token) {
            return ['error' => 'Failed to get access token'];
        }

        $url = $this->environment === 'production'
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        // Format phone number (remove + and ensure it starts with 254)
        $phone = preg_replace('/^\+?254/', '254', $phone);
        if (!preg_match('/^254\d{9}$/', $phone)) {
            return ['error' => 'Invalid phone number format'];
        }

        $data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)$amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => 'https://' . $_SERVER['HTTP_HOST'] . '/mpesa_callback.php',
            'AccountReference' => $account_reference,
            'TransactionDesc' => $transaction_desc
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            return json_decode($response, true);
        }

        return ['error' => 'STK Push failed', 'response' => $response];
    }

    // Query STK Push status
    public function querySTKStatus($checkout_request_id) {
        if (!$this->access_token) {
            return ['error' => 'Failed to get access token'];
        }

        $url = $this->environment === 'production'
            ? 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkout_request_id
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// Helper function to generate transaction ID
function generateTransactionId() {
    return 'TXN' . date('YmdHis') . rand(1000, 9999);
}

// Helper function to format phone number for M-Pesa
function formatPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/\D/', '', $phone);

    // If starts with 0, replace with 254
    if (substr($phone, 0, 1) === '0') {
        $phone = '254' . substr($phone, 1);
    }

    // If starts with +, remove it
    if (substr($phone, 0, 1) === '+') {
        $phone = substr($phone, 1);
    }

    // If doesn't start with 254, add it
    if (substr($phone, 0, 3) !== '254') {
        $phone = '254' . $phone;
    }

    return $phone;
}
?>

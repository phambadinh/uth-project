<?php
/**
 * Payment Gateway Configuration
 * VNPay & MoMo Integration
 */

// ============================================================
// VNPAY CONFIGURATION (Sandbox - Test Environment)
// ============================================================

// VNPay Merchant Info
define('VNPAY_TMN_CODE', 'DEMOSHOP');           // Terminal ID (Sandbox)
define('VNPAY_HASH_SECRET', 'RAOEXHYVSDDIIENYWSLDIIZTANXUXZFJ'); // Secret Key
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');

// Return & IPN URLs
define('VNPAY_RETURN_URL', BASE_URL . '/payment/vnpay-return.php');
define('VNPAY_IPN_URL', BASE_URL . '/payment/vnpay-ipn.php');

// VNPay API Version
define('VNPAY_VERSION', '2.1.0');
define('VNPAY_COMMAND', 'pay');
define('VNPAY_CURRENCY_CODE', 'VND');
define('VNPAY_LOCALE', 'vn'); // vn hoặc en

// ============================================================
// MOMO CONFIGURATION (Test Environment)
// ============================================================

// MoMo Merchant Info
define('MOMO_PARTNER_CODE', 'MOMOBKUN20180529');
define('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j');
define('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

// MoMo API Endpoint
define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
define('MOMO_QUERY_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/query');

// MoMo Return & Notify URLs
define('MOMO_RETURN_URL', BASE_URL . '/payment/momo-return.php');
define('MOMO_NOTIFY_URL', BASE_URL . '/payment/momo-notify.php');

// MoMo Request Type
define('MOMO_REQUEST_TYPE', 'captureWallet'); // captureWallet or payWithATM

// ============================================================
// BANK TRANSFER INFO (Manual Payment)
// ============================================================

define('BANK_NAME', 'Vietcombank');
define('BANK_ACCOUNT_NUMBER', '1234567890');
define('BANK_ACCOUNT_NAME', 'TRUONG DAI HOC GIAO THONG VAN TAI TP HCM');
define('BANK_BRANCH', 'Chi nhánh Sài Gòn');

// ============================================================
// PAYMENT SETTINGS
// ============================================================

// Currency
define('CURRENCY', 'VND');
define('CURRENCY_SYMBOL', 'đ');

// Order prefix
define('ORDER_PREFIX', 'UTH-');

// Payment timeout (minutes)
define('PAYMENT_TIMEOUT', 15);

// Minimum order amount
define('MIN_ORDER_AMOUNT', 10000); // 10,000 VND

// Maximum order amount
define('MAX_ORDER_AMOUNT', 50000000); // 50,000,000 VND

// ============================================================
// HELPER FUNCTIONS FOR PAYMENT
// ============================================================

/**
 * Generate VNPay Secure Hash
 */
function vnpaySecureHash($data) {
    ksort($data);
    $hashdata = '';
    foreach ($data as $key => $value) {
        if ($value != '' && substr($key, 0, 4) !== 'vnp_') {
            $hashdata .= $key . '=' . $value . '&';
        }
    }
    $hashdata = rtrim($hashdata, '&');
    return hash_hmac('sha512', $hashdata, VNPAY_HASH_SECRET);
}

/**
 * Generate MoMo Signature
 */
function momoSignature($data) {
    $rawHash = "accessKey=" . $data['accessKey'] .
               "&amount=" . $data['amount'] .
               "&extraData=" . $data['extraData'] .
               "&ipnUrl=" . $data['ipnUrl'] .
               "&orderId=" . $data['orderId'] .
               "&orderInfo=" . $data['orderInfo'] .
               "&partnerCode=" . $data['partnerCode'] .
               "&redirectUrl=" . $data['redirectUrl'] .
               "&requestId=" . $data['requestId'] .
               "&requestType=" . $data['requestType'];
    
    return hash_hmac('sha256', $rawHash, MOMO_SECRET_KEY);
}

/**
 * Generate unique order ID
 */
function generateOrderId() {
    return ORDER_PREFIX . date('YmdHis') . rand(1000, 9999);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . CURRENCY_SYMBOL;
}

/**
 * Validate payment amount
 */
function validatePaymentAmount($amount) {
    if ($amount < MIN_ORDER_AMOUNT) {
        return ['valid' => false, 'message' => 'Số tiền tối thiểu là ' . formatCurrency(MIN_ORDER_AMOUNT)];
    }
    
    if ($amount > MAX_ORDER_AMOUNT) {
        return ['valid' => false, 'message' => 'Số tiền tối đa là ' . formatCurrency(MAX_ORDER_AMOUNT)];
    }
    
    return ['valid' => true];
}

/**
 * Get payment method name
 */
function getPaymentMethodName($method) {
    $methods = [
        'vnpay' => 'VNPay',
        'momo' => 'Ví MoMo',
        'bank_transfer' => 'Chuyển khoản ngân hàng'
    ];
    
    return $methods[$method] ?? 'Không xác định';
}

/**
 * Get order status name
 */
function getOrderStatusName($status) {
    $statuses = [
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
        'refunded' => 'Đã hoàn tiền'
    ];
    
    return $statuses[$status] ?? 'Không xác định';
}

/**
 * Get order status color
 */
function getOrderStatusColor($status) {
    $colors = [
        'pending' => '#ff9800',
        'paid' => '#4caf50',
        'cancelled' => '#f44336',
        'refunded' => '#9e9e9e'
    ];
    
    return $colors[$status] ?? '#000000';
}

// ============================================================
// PRODUCTION MODE CHECK
// ============================================================

// Set to TRUE when going to production
define('PAYMENT_PRODUCTION_MODE', false);

if (PAYMENT_PRODUCTION_MODE) {
    // TODO: Change to production credentials
    // VNPAY_TMN_CODE = 'YOUR_PRODUCTION_TMN_CODE';
    // VNPAY_HASH_SECRET = 'YOUR_PRODUCTION_SECRET';
    // VNPAY_URL = 'https://pay.vnpay.vn/vpcpay.html';
    
    // MOMO_PARTNER_CODE = 'YOUR_PRODUCTION_PARTNER_CODE';
    // MOMO_ACCESS_KEY = 'YOUR_PRODUCTION_ACCESS_KEY';
    // MOMO_SECRET_KEY = 'YOUR_PRODUCTION_SECRET_KEY';
    // MOMO_ENDPOINT = 'https://payment.momo.vn/v2/gateway/api/create';
}

?>

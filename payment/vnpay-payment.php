<?php
require_once '../config/config.php';

$orderId = (int)($_GET['order_id'] ?? 0);

// Get order details
$stmt = $pdo->prepare(
    "SELECT o.*, c.title as course_title \
    FROM orders o\n+    JOIN order_items oi ON o.id = oi.order_id\n+    JOIN courses c ON oi.course_id = c.id\n+    WHERE o.id = ?"
);
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ../pages/courses.php');
    exit;
}

// Use constants from config/constants.php (set in project)
$vnp_TmnCode = defined('VNPAY_TMN_CODE') ? VNPAY_TMN_CODE : 'YOUR_TMN_CODE';
$vnp_HashSecret = defined('VNPAY_HASH_SECRET') ? VNPAY_HASH_SECRET : 'YOUR_HASH_SECRET';
$vnp_Url = defined('VNPAY_URL') ? VNPAY_URL : 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
$vnp_ReturnUrl = defined('VNPAY_RETURN_URL') ? VNPAY_RETURN_URL : (BASE_URL . '/payment/vnpay-return.php');

// Build VNPay URL
$vnp_TxnRef = $order['order_code'] ?? uniqid('order_');
$vnp_Amount = (int)round($order['final_amount'] * 100); // VNPay uses cents-like units
$vnp_OrderInfo = "Thanh toan khoa hoc: " . ($order['course_title'] ?? 'Khóa học');
$vnp_OrderType = 'billpayment';
$vnp_Locale = 'vn';
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_ReturnUrl,
    "vnp_TxnRef" => $vnp_TxnRef
);

ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
$vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

// Redirect to VNPay
header('Location: ' . $vnp_Url);
exit;
?>

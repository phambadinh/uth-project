<?php
require_once '../config/config.php';

$vnp_HashSecret = "YOUR_HASH_SECRET";

// Get all VNPay return params
$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}
unset($inputData['vnp_SecureHash']);
ksort($inputData);

$hashData = "";
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// Verify signature
if ($secureHash == $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        // Payment successful
        $orderCode = $_GET['vnp_TxnRef'];
        $transactionNo = $_GET['vnp_TransactionNo'];
        
        // Update order status
        $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE order_code = ?");
        $stmt->execute([$orderCode]);
        
        // Update payment status
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET status = 'completed', 
                transaction_id = ?,
                completed_at = NOW()
            WHERE order_id = (SELECT id FROM orders WHERE order_code = ?)
        ");
        $stmt->execute([$transactionNo, $orderCode]);
        
        // Get course_id and user_id
        $stmt = $pdo->prepare("
            SELECT o.user_id, oi.course_id 
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.order_code = ?
        ");
        $stmt->execute([$orderCode]);
        $orderInfo = $stmt->fetch();
        
        // Auto-enroll student
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (user_id, course_id, progress, enrolled_at)
            VALUES (?, ?, 0, NOW())
            ON DUPLICATE KEY UPDATE enrolled_at = NOW()
        ");
        $stmt->execute([$orderInfo['user_id'], $orderInfo['course_id']]);
        
        // Redirect to success page
        header('Location: payment-success.php?order=' . $orderCode);
        exit;
    } else {
        // Payment failed
        header('Location: payment-failed.php');
        exit;
    }
} else {
    // Invalid signature
    header('Location: payment-failed.php');
    exit;
}
?>

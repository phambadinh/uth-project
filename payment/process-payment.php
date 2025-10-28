<?php
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$courseId = (int)$_POST['course_id'];
$amount = (int)$_POST['amount'];
$paymentMethod = $_POST['payment_method'];
$userId = $_SESSION['user_id'];

// Create order
$orderCode = 'ORD' . date('YmdHis') . rand(1000, 9999);

$stmt = $pdo->prepare("
    INSERT INTO orders (user_id, order_code, total_amount, final_amount, status, created_at)
    VALUES (?, ?, ?, ?, 'pending', NOW())
");
$stmt->execute([$userId, $orderCode, $amount, $amount]);
$orderId = $pdo->lastInsertId();

// Create order item
$stmt = $pdo->prepare("
    INSERT INTO order_items (order_id, course_id, price)
    VALUES (?, ?, ?)
");
$stmt->execute([$orderId, $courseId, $amount]);

// Create payment record
$stmt = $pdo->prepare("
    INSERT INTO payments (user_id, course_id, order_id, payment_method, amount, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$stmt->execute([$userId, $courseId, $orderId, $paymentMethod, $amount]);
$paymentId = $pdo->lastInsertId();

// Redirect based on payment method
switch ($paymentMethod) {
    case 'vnpay':
        header('Location: vnpay-payment.php?order_id=' . $orderId);
        break;
    case 'momo':
        header('Location: momo-payment.php?order_id=' . $orderId);
        break;
    case 'bank_transfer':
        header('Location: bank-transfer.php?order_id=' . $orderId);
        break;
    default:
        header('Location: ../pages/courses.php');
}
exit;
?>

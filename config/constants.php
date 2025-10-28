<?php
/**
 * Constants for Categories, Levels, Colors, Icons
 */

// ============================================================
// PHẦN CỦA BẠN (GIỮ NGUYÊN)
// ============================================================

// 6 khóa học chính
define('CATEGORIES', [
    'HTML' => 'HTML - Ngôn ngữ đánh dấu',
    'CSS' => 'CSS - Thiết kế giao diện',
    'JavaScript' => 'JavaScript - Lập trình web động',
    'PHP' => 'PHP - Lập trình Backend',
    'Python' => 'Python - Lập trình đa năng',
    'C++' => 'C++ - Lập trình hệ thống'
]);

// Icons cho từng khóa học (emoji hoặc class icon)
define('CATEGORY_ICONS', [
    'HTML' => '🌐',
    'CSS' => '🎨',
    'JavaScript' => '⚡',
    'PHP' => '🐘',
    'Python' => '🐍',
    'C++' => '⚙️'
]);

// Màu sắc brand cho từng ngôn ngữ
define('CATEGORY_COLORS', [
    'HTML' => '#e34c26',      // Orange
    'CSS' => '#264de4',        // Blue
    'JavaScript' => '#f0db4f', // Yellow
    'PHP' => '#777bb4',        // Purple
    'Python' => '#3776ab',     // Blue
    'C++' => '#00599c'         // Dark Blue
]);

// Course levels
define('LEVELS', [
    'Beginner' => 'Cơ bản',
    'Intermediate' => 'Trung cấp',
    'Advanced' => 'Nâng cao'
]);

// User roles
define('ROLES', [
    'admin' => 'Quản trị viên',
    'instructor' => 'Giảng viên',
    'student' => 'Học viên'
]);

// Quiz question types
define('QUIZ_TYPES', [
    'multiple_choice' => 'Trắc nghiệm nhiều lựa chọn',
    'true_false' => 'Đúng/Sai',
    'short_answer' => 'Câu trả lời ngắn'
]);

// Payment methods
define('PAYMENT_METHODS', [
    'vnpay' => 'VNPay',
    'momo' => 'Ví MoMo',
    'bank_transfer' => 'Chuyển khoản ngân hàng'
]);

// Order status
define('ORDER_STATUS', [
    'pending' => 'Chờ xử lý',
    'paid' => 'Đã thanh toán',
    'cancelled' => 'Đã hủy',
    'refunded' => 'Đã hoàn tiền'
]);

// Certificate templates
define('CERTIFICATE_TYPES', [
    'completion' => 'Hoàn thành khóa học',
    'excellence' => 'Xuất sắc',
    'participation' => 'Tham gia'
]);

// ============================================================
// BỔ SUNG THÊM (COPY PHẦN NÀY VÀO CUỐI FILE)
// ============================================================

// Site Information
define('SITE_NAME', 'UTH Learning');
define('SITE_EMAIL', 'support@uth.edu.vn');
define('SITE_PHONE', '0123 456 789');
define('SITE_ADDRESS', 'Đại học Giao thông Vận tải TP.HCM');
define('SITE_DESCRIPTION', 'Nền tảng học lập trình trực tuyến hàng đầu Việt Nam');

// URLs (QUAN TRỌNG - Thay đổi theo project của bạn)
define('BASE_URL', 'http://localhost/uth-project');
define('ASSETS_URL', BASE_URL . '/assets');

// Pagination
define('ITEMS_PER_PAGE', 12);
define('COURSES_PER_PAGE', 12);
define('LESSONS_PER_PAGE', 20);

// Upload Settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// VNPay Configuration (Sandbox - Test)
define('VNPAY_TMN_CODE', 'DEMOSHOP');
define('VNPAY_HASH_SECRET', 'RAOEXHYVSDDIIENYWSLDIIZTANXUXZFJ');
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
define('VNPAY_RETURN_URL', BASE_URL . '/payment/vnpay-return.php');

// MoMo Configuration (Test)
define('MOMO_PARTNER_CODE', 'MOMO');
define('MOMO_ACCESS_KEY', 'F8BBA842ECF85');
define('MOMO_SECRET_KEY', 'K951B6PE1waDMi640xX08PD3vg6EkVlz');
define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
define('MOMO_RETURN_URL', BASE_URL . '/payment/momo-return.php');

// Email Settings (PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@uth.edu.vn');
define('SMTP_FROM_NAME', 'UTH Learning');

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
?>

<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../includes/functions.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            
            echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng']);
        }
        break;
        
    case 'register':
        // Registration logic here
        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>

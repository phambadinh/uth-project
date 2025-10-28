<?php
/**
 * Helper Functions
 * UTH Learning System
 */

/**
 * Time ago function
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'vừa xong';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' phút trước';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' giờ trước';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' ngày trước';
    } else {
        return date('d/m/Y', $time);
    }
}

/**
 * Format price
 */
function formatPrice($price) {
    if ($price == 0) {
        return '<span class="free-badge">Miễn phí</span>';
    }
    return number_format($price, 0, ',', '.') . 'đ';
}

/**
 * Generate slug from string
 */
function generateSlug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    
    // Vietnamese characters
    $vietnamese = [
        'á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ',
        'đ',
        'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ',
        'í', 'ì', 'ỉ', 'ĩ', 'ị',
        'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ',
        'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự',
        'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'
    ];
    
    $latin = [
        'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
        'd',
        'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
        'i', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
        'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
        'y', 'y', 'y', 'y', 'y'
    ];
    
    $string = str_replace($vietnamese, $latin, $string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    
    return trim($string, '-');
}

/**
 * Sanitize input
 */
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is instructor
 */
function isInstructor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'instructor';
}

/**
 * Truncate text
 */
function truncate($text, $length = 100) {
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text;
}

/**
 * Database helper functions (counts and course retrieval)
 */
function countCourses($category = null) {
    global $pdo;
    try {
        if ($category) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE category = ? AND status='published'");
            $stmt->execute([$category]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM courses WHERE status='published'");
        }
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error in real app; return 0 for safety
        return 0;
    }
}

function getTotalStudents() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

function getTotalInstructors() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'");
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

function getAllCourses($category = null, $search = null, $limit = 6, $offset = 0) {
    global $pdo;
    try {
        $params = [];
        $sql = "SELECT c.*, u.fullname AS instructor_name FROM courses c LEFT JOIN users u ON c.instructor_id = u.id WHERE c.status = 'published'";
        if ($category) {
            $sql .= " AND c.category = ?";
            $params[] = $category;
        }
        if ($search) {
            $sql .= " AND (c.title LIKE ? OR c.description LIKE ? OR u.fullname LIKE ? )";
            $like = '%' . $search . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit; $params[] = (int)$offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Format large numbers for display (e.g., 1200 -> 1.2k)
 */
function formatNumber($num) {
    if (!is_numeric($num)) return $num;
    if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
    if ($num >= 1000) return round($num / 1000, 1) . 'k';
    return (string) $num;
}

/**
 * Require login helper — redirects to login if not authenticated
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Require instructor role
 */
function requireInstructor() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * sanitize alias for clean (used across codebase)
 */
function sanitize($data) {
    return clean($data);
}

/**
 * Get submissions for a user (for student/my-excercises.php)
 */
function getUserSubmissions($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT s.*, e.title as exercise_title, c.title as course_title, s.score, s.max_points, s.submitted_at FROM submissions s JOIN exercises e ON s.exercise_id = e.id JOIN courses c ON e.course_id = c.id WHERE s.user_id = ? ORDER BY s.submitted_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
?>

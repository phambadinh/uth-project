<?php
$pageTitle = "Quản lý người dùng";
include 'includes/admin-header.php';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    if ($stmt->execute([$userId])) {
        $_SESSION['flash_message'] = 'Đã xóa người dùng thành công';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: users.php');
    exit;
}

// Get filters
$role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($role) {
    $sql .= " AND role = ?";
    $params[] = $role;
}

if ($search) {
    $sql .= " AND (fullname LIKE ? OR email LIKE ? OR username LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get counts
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalInstructors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
?>

<div class="admin-page-header">
    <div>
        <h1>Quản lý người dùng</h1>
        <p>Tổng số: <?= number_format($totalUsers) ?> người dùng</p>
    </div>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-box-small">
        <h4><?= number_format($totalStudents) ?></h4>
        <p>Học viên</p>
    </div>
    <div class="stat-box-small">
        <h4><?= number_format($totalInstructors) ?></h4>
        <p>Giảng viên</p>
    </div>
    <div class="stat-box-small">
        <h4><?= number_format($totalAdmins) ?></h4>
        <p>Quản trị viên</p>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
<div class="alert alert-<?= $_SESSION['flash_type'] ?>">
    <?= $_SESSION['flash_message'] ?>
</div>
<?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
endif; ?>

<!-- Filters -->
<div class="admin-card">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <input type="text" name="search" placeholder="Tìm kiếm theo tên, email..." 
                   value="<?= htmlspecialchars($search) ?>" class="form-control">
        </div>
        
        <div class="filter-group">
            <select name="role" class="form-control">
                <option value="">Tất cả vai trò</option>
                <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Học viên</option>
                <option value="instructor" <?= $role === 'instructor' ? 'selected' : '' ?>>Giảng viên</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        
        <button type="submit" class="btn-primary">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
        
        <a href="users.php" class="btn-outline">
            <i class="fas fa-redo"></i> Reset
        </a>
    </form>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đầy đủ</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Ngày đăng ký</th>
                    <th>Lần đăng nhập cuối</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td>
                        <div class="user-info">
                            <img src="<?= ASSETS_URL ?>/images/avatars/default.png" alt="Avatar" class="table-avatar">
                            <strong><?= htmlspecialchars($user['fullname']) ?></strong>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <?php
                        $roleColors = ['student' => 'primary', 'instructor' => 'success', 'admin' => 'danger'];
                        $roleLabels = ['student' => 'Học viên', 'instructor' => 'Giảng viên', 'admin' => 'Admin'];
                        ?>
                        <span class="badge badge-<?= $roleColors[$user['role']] ?>">
                            <?= $roleLabels[$user['role']] ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                    <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Chưa đăng nhập' ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="user-edit.php?id=<?= $user['id'] ?>" class="btn-icon" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($user['role'] !== 'admin' && $user['id'] !== $_SESSION['user_id']): ?>
                            <a href="users.php?action=delete&id=<?= $user['id'] ?>" 
                               class="btn-icon btn-danger" 
                               onclick="return confirmDelete('Xóa người dùng <?= htmlspecialchars($user['fullname']) ?>?')"
                               title="Xóa">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>

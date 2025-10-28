<?php
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

$pageTitle = "Khóa học lập trình - UTH Learning";

// Lấy filter parameters
$category = $_GET['category'] ?? '';
$level = $_GET['level'] ?? '';
$price = $_GET['price'] ?? '';
$search = $_GET['q'] ?? '';
$sort = $_GET['sort'] ?? 'popular';

// Build query
$sql = "SELECT c.*, u.fullname as instructor_name 
        FROM courses c 
        LEFT JOIN users u ON c.instructor_id = u.id 
        WHERE c.status='published'";
$params = [];

if ($category) {
    $sql .= " AND c.category = ?";
    $params[] = $category;
}
if ($level) {
    $sql .= " AND c.level = ?";
    $params[] = $level;
}
if ($price === 'free') {
    $sql .= " AND c.price = 0";
} elseif ($price === 'paid') {
    $sql .= " AND c.price > 0";
}
if ($search) {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Sorting
switch ($sort) {
    case 'newest':
        $sql .= " ORDER BY c.created_at DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY c.rating DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY c.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY c.price DESC";
        break;
    default:
        $sql .= " ORDER BY c.students DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="courses-page">
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Khám phá khóa học lập trình</h1>
            <p>Chọn từ <?= count($courses) ?> khóa học chất lượng cao</p>
        </div>
    </section>

    <div class="container">
        <div class="courses-layout">
            <!-- Sidebar Filters -->
            <aside class="courses-sidebar">
                <div class="filter-section">
                    <h3>Danh mục</h3>
                    <ul class="filter-list">
                        <li>
                            <a href="courses.php" class="<?= !$category ? 'active' : '' ?>">
                                Tất cả
                            </a>
                        </li>
                        <?php foreach (CATEGORIES as $slug => $name): ?>
                        <li>
                            <a href="?category=<?= $slug ?>" class="<?= $category === $slug ? 'active' : '' ?>">
                                <?= CATEGORY_ICONS[$slug] ?> <?= $slug ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="filter-section">
                    <h3>Cấp độ</h3>
                    <ul class="filter-list">
                        <li>
                            <a href="?<?= http_build_query(array_merge($_GET, ['level' => ''])) ?>" 
                               class="<?= !$level ? 'active' : '' ?>">
                                Tất cả
                            </a>
                        </li>
                        <?php foreach (LEVELS as $key => $value): ?>
                        <li>
                            <a href="?<?= http_build_query(array_merge($_GET, ['level' => $key])) ?>"
                               class="<?= $level === $key ? 'active' : '' ?>">
                                <?= $value ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="filter-section">
                    <h3>Giá</h3>
                    <ul class="filter-list">
                        <li>
                            <a href="?<?= http_build_query(array_merge($_GET, ['price' => ''])) ?>"
                               class="<?= !$price ? 'active' : '' ?>">
                                Tất cả
                            </a>
                        </li>
                        <li>
                            <a href="?<?= http_build_query(array_merge($_GET, ['price' => 'free'])) ?>"
                               class="<?= $price === 'free' ? 'active' : '' ?>">
                                Miễn phí
                            </a>
                        </li>
                        <li>
                            <a href="?<?= http_build_query(array_merge($_GET, ['price' => 'paid'])) ?>"
                               class="<?= $price === 'paid' ? 'active' : '' ?>">
                                Có phí
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="courses-main">
                <!-- Toolbar -->
                <div class="courses-toolbar">
                    <div class="results-count">
                        <strong><?= count($courses) ?></strong> khóa học
                    </div>
                    <div class="sort-dropdown">
                        <label>Sắp xếp:</label>
                        <select onchange="window.location.href='?<?= http_build_query(array_merge($_GET, ['sort' => ''])) ?>' + this.value">
                            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Phổ biến nhất</option>
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Đánh giá cao</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                        </select>
                    </div>
                </div>

                <!-- Courses Grid -->
                <?php if (empty($courses)): ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 64px; color: #ccc;"></i>
                    <h3>Không tìm thấy khóa học</h3>
                    <p>Thử điều chỉnh bộ lọc hoặc tìm kiếm của bạn</p>
                    <a href="courses.php" class="btn-primary">Xem tất cả khóa học</a>
                </div>
                <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                    <a href="course-detail.php?id=<?= $course['id'] ?>" class="course-card">
                        <img src="<?= htmlspecialchars($course['thumbnail']) ?>" 
                             alt="<?= htmlspecialchars($course['title']) ?>" 
                             class="course-image">
                        <div class="course-badge" style="background: <?= CATEGORY_COLORS[$course['category']] ?? '#0056d2' ?>">
                            <?= CATEGORY_ICONS[$course['category']] ?? '📚' ?> <?= $course['category'] ?>
                        </div>
                        <div class="course-content">
                            <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
                            <p class="course-instructor">
                                <i class="fas fa-user-circle"></i> 
                                <?= htmlspecialchars($course['instructor_name'] ?? 'UTH Team') ?>
                            </p>
                            <div class="course-meta">
                                <span class="meta-item">
                                    <i class="fas fa-star"></i> <?= number_format($course['rating'], 1) ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-users"></i> <?= formatNumber($course['students']) ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-signal"></i> <?= LEVELS[$course['level']] ?>
                                </span>
                            </div>
                            <div class="course-footer">
                                <span class="course-duration">
                                    <i class="fas fa-clock"></i> <?= $course['duration'] ?>
                                </span>
                                <?php if ($course['price'] > 0): ?>
                                    <span class="course-price"><?= number_format($course['price']) ?>đ</span>
                                <?php else: ?>
                                    <span class="course-price free">Miễn phí</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<style>
.courses-page { background: #f5f7fa; min-height: 100vh; padding: 40px 0; }
.page-header { background: linear-gradient(135deg, #0056d2, #004aad); color: #fff; padding: 60px 0; text-align: center; margin-bottom: 40px; }
.page-header h1 { font-size: 40px; margin-bottom: 12px; }
.courses-layout { display: grid; grid-template-columns: 280px 1fr; gap: 32px; }
.courses-sidebar { background: #fff; padding: 24px; border-radius: 12px; height: fit-content; position: sticky; top: 100px; }
.filter-section { margin-bottom: 32px; }
.filter-section h3 { font-size: 18px; margin-bottom: 16px; color: #1f1f1f; }
.filter-list { list-style: none; padding: 0; }
.filter-list li { margin-bottom: 8px; }
.filter-list a { display: block; padding: 10px 12px; color: #545454; text-decoration: none; border-radius: 6px; transition: all 0.2s; }
.filter-list a:hover { background: #f5f7fa; color: #0056d2; }
.filter-list a.active { background: #e8f0fe; color: #0056d2; font-weight: 600; }
.courses-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 16px; background: #fff; border-radius: 8px; }
.sort-dropdown select { padding: 8px 12px; border: 1px solid #d1d7dc; border-radius: 4px; font-size: 14px; }
.courses-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.course-card { background: #fff; border-radius: 12px; overflow: hidden; text-decoration: none; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; }
.course-card:hover { transform: translateY(-8px); box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
.course-image { width: 100%; height: 180px; object-fit: cover; }
.course-badge { position: absolute; top: 12px; left: 12px; color: #fff; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.course-content { padding: 20px; }
.course-title { font-size: 18px; font-weight: 600; color: #1f1f1f; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.course-instructor { font-size: 14px; color: #545454; margin-bottom: 12px; }
.course-meta { display: flex; gap: 16px; margin-bottom: 16px; }
.meta-item { font-size: 13px; color: #545454; }
.course-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #e5e5e5; }
.course-duration { font-size: 14px; color: #545454; }
.course-price { font-size: 18px; font-weight: 700; color: #1f1f1f; }
.course-price.free { color: #0cae74; }
.no-results { text-align: center; padding: 80px 20px; background: #fff; border-radius: 12px; }
.no-results h3 { margin: 24px 0 12px; }
@media (max-width: 768px) {
    .courses-layout { grid-template-columns: 1fr; }
    .courses-sidebar { position: static; }
    .courses-grid { grid-template-columns: 1fr; }
}
</style>

<?php include '../includes/footer.php'; ?>

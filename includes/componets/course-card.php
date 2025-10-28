<?php
/**
 * Reusable Course Card Component
 * Usage: include this file inside a loop
 */
if (!isset($course)) return;
?>

<a href="<?= BASE_URL ?>/pages/course-detail.php?id=<?= $course['id'] ?>" class="course-card">
    <div class="course-image-wrapper">
        <img src="<?= htmlspecialchars($course['thumbnail']) ?>" 
             alt="<?= htmlspecialchars($course['title']) ?>" 
             class="course-image">
        <div class="course-badge" style="background: <?= CATEGORY_COLORS[$course['category']] ?? '#0056d2' ?>">
            <?= CATEGORY_ICONS[$course['category']] ?? '📚' ?> <?= htmlspecialchars($course['category']) ?>
        </div>
    </div>
    
    <div class="course-content">
        <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
        
        <p class="course-instructor">
            <i class="fas fa-user-circle"></i> 
            <?= htmlspecialchars($course['instructor_name'] ?? 'UTH Team') ?>
        </p>
        
        <div class="course-meta">
            <span class="meta-item">
                <i class="fas fa-star"></i> 
                <?= number_format($course['rating'], 1) ?>
            </span>
            <span class="meta-item">
                <i class="fas fa-users"></i> 
                <?= formatNumber($course['students']) ?>
            </span>
            <span class="meta-item">
                <i class="fas fa-signal"></i> 
                <?= LEVELS[$course['level']] ?? $course['level'] ?>
            </span>
        </div>
        
        <div class="course-footer">
            <span class="course-duration">
                <i class="fas fa-clock"></i> <?= htmlspecialchars($course['duration']) ?>
            </span>
            <?php if ($course['price'] > 0): ?>
                <span class="course-price"><?= number_format($course['price']) ?>đ</span>
            <?php else: ?>
                <span class="course-price free">Miễn phí</span>
            <?php endif; ?>
        </div>
    </div>
</a>

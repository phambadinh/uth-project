<?php
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$lessonId = (int)($_GET['id'] ?? 0);
$courseId = (int)($_GET['course'] ?? 0);

// Get lesson details
if ($lessonId) {
    $stmt = $pdo->prepare("
        SELECT l.*, c.title as course_title, c.category 
        FROM lessons l
        JOIN courses c ON l.course_id = c.id
        WHERE l.id = ?
    ");
    $stmt->execute([$lessonId]);
    $lesson = $stmt->fetch();
    $courseId = $lesson['course_id'];
} else {
    // Get first lesson of course
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY order_num ASC LIMIT 1");
    $stmt->execute([$courseId]);
    $lesson = $stmt->fetch();
    $lessonId = $lesson['id'];
}

if (!$lesson) {
    header('Location: ../student/dashboard.php');
    exit;
}

// Get all lessons in course
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY order_num ASC");
$stmt->execute([$courseId]);
$allLessons = $stmt->fetchAll();

// Check enrollment
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user_id'], $courseId]);
$enrollment = $stmt->fetch();

if (!$enrollment && !$lesson['is_free']) {
    header('Location: ../pages/course-detail.php?id=' . $courseId);
    exit;
}

// Get video progress
$stmt = $pdo->prepare("SELECT * FROM video_progress WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$_SESSION['user_id'], $lessonId]);
$videoProgress = $stmt->fetch();

$pageTitle = htmlspecialchars($lesson['title']) . " - UTH Learning";

include '../includes/header.php';
?>

<div class="learning-page">
    <div class="learning-layout">
        <!-- Sidebar with lesson list -->
        <aside class="learning-sidebar">
            <div class="sidebar-header">
                <a href="../pages/course-detail.php?id=<?= $courseId ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i> Quay lại khóa học
                </a>
                <h3><?= htmlspecialchars($lesson['course_title']) ?></h3>
            </div>

            <div class="lessons-list">
                <?php foreach ($allLessons as $index => $l): ?>
                <a href="lesson.php?id=<?= $l['id'] ?>" 
                   class="lesson-item <?= $l['id'] == $lessonId ? 'active' : '' ?> <?= $l['is_free'] || $enrollment ? '' : 'locked' ?>">
                    <div class="lesson-item-left">
                        <span class="lesson-number"><?= $index + 1 ?></span>
                        <div class="lesson-item-info">
                            <h4><?= htmlspecialchars($l['title']) ?></h4>
                            <p><i class="fas fa-clock"></i> <?= $l['duration'] ?> phút</p>
                        </div>
                    </div>
                    <div class="lesson-item-right">
                        <?php if ($l['is_free'] || $enrollment): ?>
                            <i class="fas fa-play-circle"></i>
                        <?php else: ?>
                            <i class="fas fa-lock"></i>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- Main video area -->
        <main class="learning-main">
            <div class="video-container">
                <?php if ($lesson['video_type'] === 'youtube'): ?>
                    <iframe id="videoPlayer" 
                            width="100%" 
                            height="100%" 
                            src="https://www.youtube.com/embed/<?= getYoutubeId($lesson['video_url']) ?>?enablejsapi=1" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                <?php elseif ($lesson['video_type'] === 'vimeo'): ?>
                    <iframe id="videoPlayer" 
                            src="https://player.vimeo.com/video/<?= getVimeoId($lesson['video_url']) ?>" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            allow="autoplay; fullscreen; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <video id="videoPlayer" controls width="100%" height="100%">
                        <source src="<?= htmlspecialchars($lesson['video_url']) ?>" type="video/mp4">
                        Trình duyệt của bạn không hỗ trợ video.
                    </video>
                <?php endif; ?>
            </div>

            <div class="lesson-content">
                <div class="lesson-header">
                    <div>
                        <h1><?= htmlspecialchars($lesson['title']) ?></h1>
                        <p class="lesson-meta">
                            <span class="badge" style="background: <?= CATEGORY_COLORS[$lesson['category']] ?>">
                                <?= CATEGORY_ICONS[$lesson['category']] ?> <?= $lesson['category'] ?>
                            </span>
                            <span><i class="fas fa-clock"></i> <?= $lesson['duration'] ?> phút</span>
                        </p>
                    </div>
                    <div class="lesson-actions">
                        <?php if ($enrollment): ?>
                        <button onclick="markAsComplete()" class="btn-success" id="completeBtn">
                            <i class="fas fa-check"></i> Đánh dấu hoàn thành
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="lesson-tabs">
                    <button class="tab-btn active" onclick="showTab('overview')">Tổng quan</button>
                    <button class="tab-btn" onclick="showTab('notes')">Ghi chú</button>
                    <button class="tab-btn" onclick="showTab('resources')">Tài nguyên</button>
                </div>

                <div id="overview" class="tab-content active">
                    <h3>Nội dung bài học</h3>
                    <div class="lesson-description">
                        <?= nl2br(htmlspecialchars($lesson['content'])) ?>
                    </div>
                </div>

                <div id="notes" class="tab-content">
                    <h3>Ghi chú của bạn</h3>
                    <textarea id="lessonNotes" class="form-control" rows="10" placeholder="Viết ghi chú tại đây..."></textarea>
                    <button onclick="saveNotes()" class="btn-primary mt-3">
                        <i class="fas fa-save"></i> Lưu ghi chú
                    </button>
                </div>

                <div id="resources" class="tab-content">
                    <h3>Tài nguyên</h3>
                    <div class="resources-list">
                        <div class="resource-item">
                            <i class="fas fa-file-pdf"></i>
                            <div>
                                <h4>Slide bài giảng</h4>
                                <p>PDF - 2.5 MB</p>
                            </div>
                            <a href="#" class="btn-outline btn-sm">Tải về</a>
                        </div>
                        <div class="resource-item">
                            <i class="fas fa-code"></i>
                            <div>
                                <h4>Source code</h4>
                                <p>ZIP - 1.2 MB</p>
                            </div>
                            <a href="#" class="btn-outline btn-sm">Tải về</a>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="lesson-navigation">
                    <?php
                    $currentIndex = array_search($lessonId, array_column($allLessons, 'id'));
                    $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
                    $nextLesson = $currentIndex < count($allLessons) - 1 ? $allLessons[$currentIndex + 1] : null;
                    ?>
                    
                    <?php if ($prevLesson): ?>
                    <a href="lesson.php?id=<?= $prevLesson['id'] ?>" class="btn-outline">
                        <i class="fas fa-chevron-left"></i> Bài trước
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($nextLesson): ?>
                    <a href="lesson.php?id=<?= $nextLesson['id'] ?>" class="btn-primary">
                        Bài tiếp theo <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
let lessonId = <?= $lessonId ?>;
let userId = <?= $_SESSION['user_id'] ?>;
let videoStartTime = <?= $videoProgress['progress_seconds'] ?? 0 ?>;

// Track video progress every 10 seconds
setInterval(() => {
    let currentTime = getCurrentVideoTime();
    if (currentTime > 0) {
        saveVideoProgress(currentTime);
    }
}, 10000);

function getCurrentVideoTime() {
    // For HTML5 video
    const video = document.getElementById('videoPlayer');
    if (video && video.currentTime) {
        return Math.floor(video.currentTime);
    }
    return 0;
}

function saveVideoProgress(currentTime) {
    fetch('../api/video_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'save_progress',
            lesson_id: lessonId,
            progress_seconds: currentTime
        })
    });
}

function markAsComplete() {
    fetch('../api/video_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'mark_complete',
            lesson_id: lessonId
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('completeBtn').innerHTML = '<i class="fas fa-check"></i> Đã hoàn thành';
            document.getElementById('completeBtn').disabled = true;
        }
    });
}

function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function saveNotes() {
    const notes = document.getElementById('lessonNotes').value;
    fetch('../api/video_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'save_notes',
            lesson_id: lessonId,
            notes: notes
        })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
    });
}
</script>

<style>
.learning-page { background: #1f1f1f; min-height: 100vh; }
.learning-layout { display: grid; grid-template-columns: 350px 1fr; height: calc(100vh - 72px); }
.learning-sidebar { background: #2d2d2d; color: #fff; overflow-y: auto; }
.sidebar-header { padding: 24px; border-bottom: 1px solid #3d3d3d; }
.back-link { color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.sidebar-header h3 { font-size: 18px; }
.lessons-list { padding: 16px; }
.lesson-item { display: flex; justify-content: space-between; align-items: center; padding: 16px; border-radius: 8px; margin-bottom: 8px; text-decoration: none; color: #ccc; transition: all 0.2s; }
.lesson-item:hover { background: #3d3d3d; }
.lesson-item.active { background: #0056d2; color: #fff; }
.lesson-item.locked { opacity: 0.5; cursor: not-allowed; }
.lesson-item-left { display: flex; align-items: center; gap: 12px; flex: 1; }
.lesson-number { width: 32px; height: 32px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }
.lesson-item-info h4 { font-size: 14px; margin-bottom: 4px; }
.lesson-item-info p { font-size: 12px; opacity: 0.7; }
.learning-main { background: #fff; overflow-y: auto; }
.video-container { position: relative; padding-top: 56.25%; background: #000; }
.video-container iframe, .video-container video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
.lesson-content { padding: 40px; max-width: 900px; margin: 0 auto; }
.lesson-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
.lesson-header h1 { font-size: 32px; margin-bottom: 12px; }
.lesson-meta { display: flex; gap: 16px; align-items: center; }
.badge { padding: 6px 12px; border-radius: 20px; color: #fff; font-size: 12px; font-weight: 600; }
.lesson-tabs { border-bottom: 2px solid #e5e5e5; margin-bottom: 32px; display: flex; gap: 32px; }
.tab-btn { background: none; border: none; padding: 16px 0; font-size: 16px; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s; }
.tab-btn.active { color: #0056d2; border-bottom-color: #0056d2; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.lesson-description { line-height: 1.8; color: #545454; }
.resources-list { display: flex; flex-direction: column; gap: 16px; }
.resource-item { display: flex; align-items: center; gap: 16px; padding: 20px; background: #f5f7fa; border-radius: 8px; }
.resource-item i { font-size: 32px; color: #0056d2; }
.resource-item > div { flex: 1; }
.resource-item h4 { margin-bottom: 4px; }
.resource-item p { font-size: 14px; color: #545454; }
.lesson-navigation { display: flex; justify-content: space-between; margin-top: 48px; padding-top: 32px; border-top: 1px solid #e5e5e5; }
.btn-success { background: #0cae74; color: #fff; }
.mt-3 { margin-top: 16px; }
@media (max-width: 768px) {
    .learning-layout { grid-template-columns: 1fr; }
    .learning-sidebar { display: none; }
}
</style>

<?php 
function getYoutubeId($url) {
    preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
    return $matches[1] ?? '';
}

function getVimeoId($url) {
    preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
    return $matches[1] ?? '';
}

include '../includes/footer.php'; 
?>

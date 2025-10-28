<?php
require_once '../config/config.php';

// Simple assignment submission page
// Students can upload files or write text answers
// Instructors can grade and provide feedback

$pageTitle = "Bài tập - UTH Learning";
include '../includes/header.php';
?>

<div class="assignment-page">
    <div class="container">
        <h1>Bài tập: Xây dựng Landing Page</h1>
        
        <div class="assignment-content">
            <h3>Mô tả</h3>
            <p>Xây dựng một landing page responsive cho sản phẩm công nghệ...</p>
            
            <h3>Yêu cầu</h3>
            <ul>
                <li>Sử dụng HTML5 semantic tags</li>
                <li>Responsive trên mobile/tablet/desktop</li>
                <li>Có animation khi scroll</li>
            </ul>
            
            <h3>Nộp bài</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Upload file ZIP</label>
                    <input type="file" name="assignment_file" accept=".zip" class="form-control">
                </div>
                <div class="form-group">
                    <label>Hoặc nhập URL Github</label>
                    <input type="url" name="github_url" class="form-control">
                </div>
                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="notes" class="form-control" rows="4"></textarea>
                </div>
                <button type="submit" class="btn-primary">Nộp bài</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

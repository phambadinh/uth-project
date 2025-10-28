<?php
require_once '../config/config.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get instructors list
$stmt = $pdo->query("
    SELECT DISTINCT u.id, u.fullname, u.avatar 
    FROM users u 
    WHERE u.role = 'instructor'
    ORDER BY u.fullname
");
$instructors = $stmt->fetchAll();

$pageTitle = "Nhắn tin - UTH Learning";
include '../includes/header.php';
?>

<div class="chat-page">
    <div class="container-fluid">
        <div class="chat-container">
            <!-- Sidebar - Conversations -->
            <aside class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h2>Tin nhắn</h2>
                    <button class="btn-icon" id="newChatBtn">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>

                <div class="chat-search">
                    <input type="text" placeholder="Tìm kiếm cuộc trò chuyện..." class="form-control">
                </div>

                <div class="conversations-list" id="conversationsList">
                    <div class="loading-conversations">
                        <i class="fas fa-spinner fa-spin"></i> Đang tải...
                    </div>
                </div>
            </aside>

            <!-- Main Chat Area -->
            <main class="chat-main">
                <div class="chat-placeholder">
                    <i class="fas fa-comments"></i>
                    <h3>Chọn một cuộc trò chuyện</h3>
                    <p>Chọn một cuộc trò chuyện từ danh sách bên trái hoặc bắt đầu cuộc trò chuyện mới</p>
                </div>

                <div class="chat-active" style="display: none;">
                    <div class="chat-header">
                        <div class="chat-user-info">
                            <img src="" alt="" class="chat-avatar">
                            <div>
                                <h3 class="chat-user-name"></h3>
                                <p class="chat-user-status">Đang hoạt động</p>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="btn-icon"><i class="fas fa-phone"></i></button>
                            <button class="btn-icon"><i class="fas fa-video"></i></button>
                            <button class="btn-icon"><i class="fas fa-ellipsis-v"></i></button>
                        </div>
                    </div>

                    <div class="chat-messages" id="chatMessages">
                        <!-- Messages will be loaded here -->
                    </div>

                    <div class="chat-input-area">
                        <form id="chatForm">
                            <input type="hidden" id="recipientId">
                            <input type="hidden" id="conversationId">
                            <button type="button" class="btn-icon"><i class="fas fa-paperclip"></i></button>
                            <input type="text" id="messageInput" placeholder="Nhập tin nhắn..." class="form-control" required>
                            <button type="submit" class="btn-icon btn-send"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<style>
.chat-page { padding: 0; background: #f5f7fa; height: calc(100vh - 72px); }
.chat-container { display: flex; height: 100%; }

.chat-sidebar { width: 350px; background: #fff; border-right: 1px solid var(--gray-200); display: flex; flex-direction: column; }
.chat-sidebar-header { padding: 20px; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; }
.chat-sidebar-header h2 { font-size: 20px; }

.chat-search { padding: 16px; border-bottom: 1px solid var(--gray-200); }

.conversations-list { flex: 1; overflow-y: auto; }
.conversation-item { padding: 16px; border-bottom: 1px solid var(--gray-200); cursor: pointer; transition: background 0.2s; display: flex; gap: 12px; align-items: center; }
.conversation-item:hover { background: var(--gray-50); }
.conversation-item.active { background: #e8f0fe; }
.conversation-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; }
.conversation-info { flex: 1; }
.conversation-name { font-weight: 600; margin-bottom: 4px; }
.conversation-last-message { font-size: 13px; color: var(--gray-600); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conversation-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
.conversation-time { font-size: 12px; color: var(--gray-500); }
.unread-badge { background: var(--primary); color: #fff; font-size: 11px; padding: 2px 6px; border-radius: 10px; font-weight: 600; }

.chat-main { flex: 1; display: flex; flex-direction: column; background: #fff; }

.chat-placeholder { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--gray-500); }
.chat-placeholder i { font-size: 64px; margin-bottom: 20px; }

.chat-active { flex: 1; display: flex; flex-direction: column; }

.chat-header { padding: 20px 24px; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; }
.chat-user-info { display: flex; gap: 12px; align-items: center; }
.chat-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; }
.chat-user-name { font-size: 18px; font-weight: 600; margin-bottom: 4px; }
.chat-user-status { font-size: 13px; color: var(--success); }

.chat-messages { flex: 1; padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; }

.message { display: flex; gap: 12px; max-width: 70%; }
.message.sent { margin-left: auto; flex-direction: row-reverse; }
.message-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.message-content { background: var(--gray-100); padding: 12px 16px; border-radius: 16px; }
.message.sent .message-content { background: var(--primary); color: #fff; }
.message-text { line-height: 1.5; }
.message-time { font-size: 11px; color: var(--gray-500); margin-top: 4px; }
.message.sent .message-time { color: rgba(255,255,255,0.7); }

.chat-input-area { padding: 20px 24px; border-top: 1px solid var(--gray-200); }
.chat-input-area form { display: flex; gap: 12px; align-items: center; }
.chat-input-area input[type="text"] { flex: 1; }
.btn-send { background: var(--primary); color: #fff; }
.btn-send:hover { background: var(--primary-dark); }

@media (max-width: 767px) {
    .chat-sidebar { width: 100%; }
    .chat-main { position: fixed; top: 72px; left: 0; right: 0; bottom: 0; z-index: 100; display: none; }
    .chat-main.active { display: flex; }
}
</style>

<script src="<?= ASSETS_URL ?>/js/chat.js"></script>

<?php include '../includes/footer.php'; ?>

<?php
/**
 * Chat API
 * Real-time messaging between students and instructors
 */

header('Content-Type: application/json');
require_once '../config/config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_conversations':
            // Get all conversations for user
            $stmt = $pdo->prepare("
                SELECT 
                    c.*,
                    CASE 
                        WHEN c.user1_id = ? THEN u2.fullname
                        ELSE u1.fullname
                    END as contact_name,
                    CASE 
                        WHEN c.user1_id = ? THEN u2.avatar
                        ELSE u1.avatar
                    END as contact_avatar,
                    CASE 
                        WHEN c.user1_id = ? THEN u2.id
                        ELSE u1.id
                    END as contact_id,
                    (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = 0) as unread_count,
                    (SELECT message FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
                FROM chat_conversations c
                JOIN users u1 ON c.user1_id = u1.id
                JOIN users u2 ON c.user2_id = u2.id
                WHERE c.user1_id = ? OR c.user2_id = ?
                ORDER BY c.last_message_at DESC
            ");
            $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
            $conversations = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'conversations' => $conversations
            ]);
            break;
            
        case 'get_messages':
            // Get messages in a conversation
            $conversationId = (int)($_GET['conversation_id'] ?? 0);
            
            if (!$conversationId) {
                throw new Exception('Conversation ID is required');
            }
            
            // Verify user is part of conversation
            $stmt = $pdo->prepare("
                SELECT * FROM chat_conversations 
                WHERE id = ? AND (user1_id = ? OR user2_id = ?)
            ");
            $stmt->execute([$conversationId, $userId, $userId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Access denied');
            }
            
            // Get messages
            $stmt = $pdo->prepare("
                SELECT m.*, u.fullname as sender_name, u.avatar as sender_avatar
                FROM chat_messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$conversationId]);
            $messages = $stmt->fetchAll();
            
            // Mark as read
            $stmt = $pdo->prepare("
                UPDATE chat_messages 
                SET is_read = 1 
                WHERE conversation_id = ? AND sender_id != ?
            ");
            $stmt->execute([$conversationId, $userId]);
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            break;
            
        case 'send_message':
            // Send a new message
            $recipientId = (int)($_POST['recipient_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            
            if (!$recipientId || !$message) {
                throw new Exception('Recipient and message are required');
            }
            
            // Check if conversation exists
            $stmt = $pdo->prepare("
                SELECT id FROM chat_conversations 
                WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
            ");
            $stmt->execute([$userId, $recipientId, $recipientId, $userId]);
            $conversation = $stmt->fetch();
            
            if (!$conversation) {
                // Create new conversation
                $stmt = $pdo->prepare("
                    INSERT INTO chat_conversations (user1_id, user2_id, created_at, last_message_at)
                    VALUES (?, ?, NOW(), NOW())
                ");
                $stmt->execute([$userId, $recipientId]);
                $conversationId = $pdo->lastInsertId();
            } else {
                $conversationId = $conversation['id'];
                
                // Update last_message_at
                $stmt = $pdo->prepare("UPDATE chat_conversations SET last_message_at = NOW() WHERE id = ?");
                $stmt->execute([$conversationId]);
            }
            
            // Insert message
            $stmt = $pdo->prepare("
                INSERT INTO chat_messages (conversation_id, sender_id, message, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$conversationId, $userId, $message]);
            $messageId = $pdo->lastInsertId();
            
            // Get the inserted message with sender info
            $stmt = $pdo->prepare("
                SELECT m.*, u.fullname as sender_name, u.avatar as sender_avatar
                FROM chat_messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.id = ?
            ");
            $stmt->execute([$messageId]);
            $newMessage = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message_id' => $messageId,
                'conversation_id' => $conversationId,
                'message' => $newMessage
            ]);
            break;
            
        case 'mark_read':
            // Mark messages as read
            $conversationId = (int)($_POST['conversation_id'] ?? 0);
            
            if (!$conversationId) {
                throw new Exception('Conversation ID is required');
            }
            
            $stmt = $pdo->prepare("
                UPDATE chat_messages 
                SET is_read = 1 
                WHERE conversation_id = ? AND sender_id != ?
            ");
            $stmt->execute([$conversationId, $userId]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'get_unread_count':
            // Get total unread messages count
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM chat_messages m
                JOIN chat_conversations c ON m.conversation_id = c.id
                WHERE (c.user1_id = ? OR c.user2_id = ?) 
                AND m.sender_id != ? 
                AND m.is_read = 0
            ");
            $stmt->execute([$userId, $userId, $userId]);
            $result = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'unread_count' => (int)$result['count']
            ]);
            break;
            
        case 'search_users':
            // Search users to start a conversation
            $search = trim($_GET['search'] ?? '');
            
            if (strlen($search) < 2) {
                throw new Exception('Search term must be at least 2 characters');
            }
            
            $stmt = $pdo->prepare("
                SELECT id, username, fullname, avatar, role
                FROM users
                WHERE (fullname LIKE ? OR username LIKE ?) 
                AND id != ?
                AND status = 'active'
                LIMIT 10
            ");
            $searchTerm = "%$search%";
            $stmt->execute([$searchTerm, $searchTerm, $userId]);
            $users = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'users' => $users
            ]);
            break;
            
        case 'delete_message':
            // Delete a message (only sender can delete)
            $messageId = (int)($_POST['message_id'] ?? 0);
            
            if (!$messageId) {
                throw new Exception('Message ID is required');
            }
            
            // Check if user owns the message
            $stmt = $pdo->prepare("SELECT sender_id FROM chat_messages WHERE id = ?");
            $stmt->execute([$messageId]);
            $message = $stmt->fetch();
            
            if (!$message || $message['sender_id'] != $userId) {
                throw new Exception('Access denied');
            }
            
            // Delete message
            $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE id = ?");
            $stmt->execute([$messageId]);
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$chatId = (int)($_GET['chat_id'] ?? 0);
$lastMessageId = (int)($_GET['last_message_id'] ?? 0);

if (!$chatId) {
    echo json_encode(['success' => false, 'error' => 'Chat ID required']);
    exit();
}

$db = Database::getInstance();

try {
    // Get new messages since last check
    $stmt = $db->getConnection()->prepare("
        SELECT m.*, u.username as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.chat_id = ? AND m.id > ? 
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$chatId, $lastMessageId]);
    $messages = $stmt->fetchAll();
    
    // Mark messages as read for current user
    if (!empty($messages)) {
        $db->markMessagesAsRead($chatId, $_SESSION['user_id']);
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

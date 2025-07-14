<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send') {
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        
        if (!$chatId || !$receiverId || !$message) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit();
        }
        
        // Send message
        $result = $db->sendMessage($chatId, $_SESSION['user_id'], $receiverId, $message);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
        
    } elseif ($action === 'update_status') {
        // Update user online status
        $result = $db->updateUserStatus($_SESSION['user_id'], true);
        echo json_encode(['success' => $result]);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>

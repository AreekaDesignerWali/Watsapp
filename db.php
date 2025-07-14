<?php
require_once 'config.php';

class Database {
    private $connection;
    private static $instance = null;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // User operations
    public function createUser($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashedPassword]);
    }
    
    public function getUserByEmail($email) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function getUserById($id) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateUserStatus($userId, $isOnline) {
        $stmt = $this->connection->prepare("UPDATE users SET is_online = ?, last_seen = NOW() WHERE id = ?");
        return $stmt->execute([$isOnline, $userId]);
    }
    
    // Chat operations
    public function createOrGetChat($user1Id, $user2Id) {
        // Check if chat exists
        $stmt = $this->connection->prepare("SELECT id FROM chats WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
        $stmt->execute([$user1Id, $user2Id, $user2Id, $user1Id]);
        $chat = $stmt->fetch();
        
        if ($chat) {
            return $chat['id'];
        }
        
        // Create new chat
        $stmt = $this->connection->prepare("INSERT INTO chats (user1_id, user2_id) VALUES (?, ?)");
        $stmt->execute([$user1Id, $user2Id]);
        return $this->connection->lastInsertId();
    }
    
    public function getUserChats($userId) {
        $stmt = $this->connection->prepare("
            SELECT c.*, 
                   CASE 
                       WHEN c.user1_id = ? THEN u2.username 
                       ELSE u1.username 
                   END as contact_name,
                   CASE 
                       WHEN c.user1_id = ? THEN u2.id 
                       ELSE u1.id 
                   END as contact_id,
                   CASE 
                       WHEN c.user1_id = ? THEN u2.is_online 
                       ELSE u1.is_online 
                   END as contact_online,
                   CASE 
                       WHEN c.user1_id = ? THEN u2.last_seen 
                       ELSE u1.last_seen 
                   END as contact_last_seen,
                   m.message as last_message,
                   m.created_at as last_message_time
            FROM chats c
            JOIN users u1 ON c.user1_id = u1.id
            JOIN users u2 ON c.user2_id = u2.id
            LEFT JOIN messages m ON c.id = m.chat_id AND m.id = (
                SELECT MAX(id) FROM messages WHERE chat_id = c.id
            )
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY c.updated_at DESC
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll();
    }
    
    // Message operations
    public function sendMessage($chatId, $senderId, $receiverId, $message) {
        $stmt = $this->connection->prepare("INSERT INTO messages (chat_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$chatId, $senderId, $receiverId, $message]);
        
        if ($result) {
            // Update chat timestamp
            $stmt = $this->connection->prepare("UPDATE chats SET updated_at = NOW() WHERE id = ?");
            $stmt->execute([$chatId]);
        }
        
        return $result;
    }
    
    public function getChatMessages($chatId, $limit = 50) {
        $stmt = $this->connection->prepare("
            SELECT m.*, u.username as sender_name 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE m.chat_id = ? 
            ORDER BY m.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$chatId, $limit]);
        return array_reverse($stmt->fetchAll());
    }
    
    public function markMessagesAsRead($chatId, $userId) {
        $stmt = $this->connection->prepare("UPDATE messages SET is_read = 1 WHERE chat_id = ? AND receiver_id = ?");
        return $stmt->execute([$chatId, $userId]);
    }
    
    // Contact operations
    public function getAllUsers($excludeUserId) {
        $stmt = $this->connection->prepare("SELECT id, username, email, is_online, last_seen FROM users WHERE id != ? ORDER BY username");
        $stmt->execute([$excludeUserId]);
        return $stmt->fetchAll();
    }
}
?>

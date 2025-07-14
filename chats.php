<?php
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get contact ID from URL
$contactId = isset($_GET['contact_id']) ? (int)$_GET['contact_id'] : 0;

if (!$contactId) {
    header('Location: home.php');
    exit();
}

$db = Database::getInstance();
$contact = $db->getUserById($contactId);
$currentUser = $db->getUserById($_SESSION['user_id']);

if (!$contact) {
    header('Location: home.php');
    exit();
}

// Get or create chat
$chatId = $db->createOrGetChat($_SESSION['user_id'], $contactId);
$messages = $db->getChatMessages($chatId);

// Mark messages as read
$db->markMessagesAsRead($chatId, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($contact['username']); ?> - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #efeae2;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4d4d4' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: #25D366;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .back-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.1);
        }

        .contact-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #fff, #f0f0f0);
            color: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            position: relative;
        }

        .online-indicator {
            width: 12px;
            height: 12px;
            background: #4fc3f7;
            border-radius: 50%;
            border: 2px solid white;
            position: absolute;
            bottom: 0;
            right: 0;
        }

        .contact-info h3 {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .contact-status {
            font-size: 13px;
            opacity: 0.9;
        }

        .chat-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .action-btn:hover {
            background: rgba(255,255,255,0.1);
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            max-width: 70%;
            padding: 8px 12px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            position: relative;
            word-wrap: break-word;
            animation: messageSlide 0.3s ease-out;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.sent {
            align-self: flex-end;
            background: #dcf8c6;
            color: #111b21;
            border-bottom-right-radius: 4px;
        }

        .message.received {
            align-self: flex-start;
            background: white;
            color: #111b21;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .message-time {
            font-size: 11px;
            color: #667781;
            margin-top: 4px;
            text-align: right;
        }

        .message.received .message-time {
            text-align: left;
        }

        .message-input-container {
            background: #f0f2f5;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            font-size: 15px;
            background: white;
            resize: none;
            max-height: 100px;
            min-height: 45px;
            outline: none;
        }

        .message-input:focus {
            box-shadow: 0 0 0 2px #25D366;
        }

        .send-btn {
            width: 45px;
            height: 45px;
            background: #25D366;
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .send-btn:hover {
            background: #128C7E;
            transform: scale(1.05);
        }

        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .typing-indicator {
            align-self: flex-start;
            background: white;
            padding: 12px 16px;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            display: none;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #667781;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes typing {
            0%, 80%, 100% {
                transform: scale(0);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .empty-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #667781;
            padding: 40px;
        }

        .empty-chat-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .chat-header {
                padding: 12px 15px;
            }
            
            .messages-container {
                padding: 15px;
            }
            
            .message-input-container {
                padding: 10px 15px;
            }
            
            .message {
                max-width: 85%;
            }
        }

        /* Scrollbar styling */
        .messages-container::-webkit-scrollbar {
            width: 6px;
        }

        .messages-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .messages-container::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.2);
            border-radius: 3px;
        }

        .messages-container::-webkit-scrollbar-thumb:hover {
            background: rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="chat-header">
        <button class="back-btn" onclick="goBack()">←</button>
        <div class="contact-avatar">
            <?php echo strtoupper(substr($contact['username'], 0, 1)); ?>
            <?php if ($contact['is_online']): ?>
                <div class="online-indicator"></div>
            <?php endif; ?>
        </div>
        <div class="contact-info">
            <h3><?php echo htmlspecialchars($contact['username']); ?></h3>
            <div class="contact-status" id="contactStatus">
                <?php if ($contact['is_online']): ?>
                    Online
                <?php else: ?>
                    Last seen <?php echo date('M j, H:i', strtotime($contact['last_seen'])); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="chat-actions">
            <button class="action-btn" title="Call">📞</button>
            <button class="action-btn" title="Video Call">📹</button>
            <button class="action-btn" title="More">⋮</button>
        </div>
    </div>

    <div class="messages-container" id="messagesContainer">
        <?php if (empty($messages)): ?>
            <div class="empty-chat">
                <div class="empty-chat-icon">💬</div>
                <h3>Start a conversation</h3>
                <p>Send a message to <?php echo htmlspecialchars($contact['username']); ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($message['message']); ?>
                    <div class="message-time">
                        <?php echo date('H:i', strtotime($message['created_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="typing-indicator" id="typingIndicator">
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>
    </div>

    <div class="message-input-container">
        <textarea class="message-input" id="messageInput" placeholder="Type a message..." rows="1"></textarea>
        <button class="send-btn" id="sendBtn" onclick="sendMessage()">➤</button>
    </div>

    <script>
        // Global variables
        const chatId = <?php echo $chatId; ?>;
        const contactId = <?php echo $contactId; ?>;
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        let messageInterval;
        let isTyping = false;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
            startMessagePolling();
            setupMessageInput();
        });

        function goBack() {
            window.location.href = 'home.php';
        }

        function scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            container.scrollTop = container.scrollHeight;
        }

        function setupMessageInput() {
            const input = document.getElementById('messageInput');
            const sendBtn = document.getElementById('sendBtn');
            
            // Auto-resize textarea
            input.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                
                // Enable/disable send button
                sendBtn.disabled = this.value.trim() === '';
            });
            
            // Send on Enter (but not Shift+Enter)
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // Initial state
            sendBtn.disabled = true;
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;
            sendBtn.innerHTML = '⏳';
            
            // Add message to UI immediately
            addMessageToUI(message, 'sent', new Date());
            input.value = '';
            input.style.height = 'auto';
            scrollToBottom();
            
            // Send to server
            fetch('send_messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send&chat_id=${chatId}&receiver_id=${contactId}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Message sent successfully
                } else {
                    console.error('Failed to send message:', data.error);
                    // Remove message from UI if failed
                    const messages = document.querySelectorAll('.message.sent');
                    const lastMessage = messages[messages.length - 1];
                    if (lastMessage) {
                        lastMessage.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '➤';
            });
        }

        function addMessageToUI(message, type, timestamp) {
            const container = document.getElementById('messagesContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            const time = timestamp.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            
            messageDiv.innerHTML = `
                ${message}
                <div class="message-time">${time}</div>
            `;
            
            // Remove empty chat message if exists
            const emptyChat = container.querySelector('.empty-chat');
            if (emptyChat) {
                emptyChat.remove();
            }
            
            container.appendChild(messageDiv);
            scrollToBottom();
        }

        function startMessagePolling() {
            messageInterval = setInterval(fetchNewMessages, 2000); // Poll every 2 seconds
        }

        function fetchNewMessages() {
            fetch(`receive_messages.php?chat_id=${chatId}&last_message_id=${getLastMessageId()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            if (message.sender_id != currentUserId) {
                                addMessageToUI(message.message, 'received', new Date(message.created_at));
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                });
        }

        function getLastMessageId() {
            const messages = document.querySelectorAll('.message');
            return messages.length;
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (messageInterval) {
                clearInterval(messageInterval);
            }
        });

        // Handle visibility change
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (messageInterval) {
                    clearInterval(messageInterval);
                }
            } else {
                startMessagePolling();
            }
        });
    </script>
</body>
</html>

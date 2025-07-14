<?php
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$user = $db->getUserById($_SESSION['user_id']);
$chats = $db->getUserChats($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            overflow: hidden;
        }

        .app-container {
            display: flex;
            height: 100vh;
            background: white;
        }

        /* Sidebar */
        .sidebar {
            width: 350px;
            background: white;
            border-right: 1px solid #e9edef;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            background: #f0f2f5;
            border-bottom: 1px solid #e9edef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .user-details h3 {
            color: #111b21;
            font-size: 16px;
            font-weight: 500;
        }

        .user-details p {
            color: #667781;
            font-size: 13px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .header-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #54656f;
            transition: background-color 0.2s;
        }

        .header-btn:hover {
            background: #f5f6f6;
        }

        .search-container {
            padding: 12px 16px;
            background: white;
        }

        .search-box {
            width: 100%;
            padding: 8px 16px 8px 40px;
            border: none;
            background: #f0f2f5;
            border-radius: 8px;
            font-size: 14px;
            position: relative;
        }

        .search-box:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 2px #25D366;
        }

        .chats-list {
            flex: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f2f5;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-item:hover {
            background: #f5f6f6;
        }

        .chat-item.active {
            background: #e7f3ff;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            flex-shrink: 0;
        }

        .chat-info {
            flex: 1;
            min-width: 0;
        }

        .chat-name {
            font-size: 16px;
            font-weight: 500;
            color: #111b21;
            margin-bottom: 2px;
        }

        .chat-last-message {
            font-size: 14px;
            color: #667781;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .chat-time {
            font-size: 12px;
            color: #667781;
        }

        .online-indicator {
            width: 12px;
            height: 12px;
            background: #25D366;
            border-radius: 50%;
            border: 2px solid white;
            position: absolute;
            bottom: 0;
            right: 0;
        }

        /* Main Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #efeae2;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4d4d4' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .welcome-screen {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
        }

        .welcome-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
        }

        .welcome-title {
            font-size: 2rem;
            color: #41525d;
            margin-bottom: 15px;
            font-weight: 300;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            color: #667781;
            line-height: 1.6;
            max-width: 400px;
        }

        .new-chat-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .new-chat-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(37, 211, 102, 0.6);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: absolute;
                z-index: 100;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .chat-area {
                width: 100%;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #25D366;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                        <p>Online</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="header-btn" onclick="showContacts()" title="New Chat">
                        👥
                    </button>
                    <button class="header-btn" onclick="logout()" title="Logout">
                        🚪
                    </button>
                </div>
            </div>

            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search or start new chat" id="searchBox">
            </div>

            <div class="chats-list" id="chatsList">
                <?php if (empty($chats)): ?>
                    <div style="padding: 40px 20px; text-align: center; color: #667781;">
                        <p>No chats yet</p>
                        <p style="font-size: 14px; margin-top: 8px;">Start a new conversation</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($chats as $chat): ?>
                        <div class="chat-item" onclick="openChat(<?php echo $chat['contact_id']; ?>, '<?php echo htmlspecialchars($chat['contact_name']); ?>')">
                            <div class="chat-avatar" style="position: relative;">
                                <?php echo strtoupper(substr($chat['contact_name'], 0, 1)); ?>
                                <?php if ($chat['contact_online']): ?>
                                    <div class="online-indicator"></div>
                                <?php endif; ?>
                            </div>
                            <div class="chat-info">
                                <div class="chat-name"><?php echo htmlspecialchars($chat['contact_name']); ?></div>
                                <div class="chat-last-message">
                                    <?php echo $chat['last_message'] ? htmlspecialchars(substr($chat['last_message'], 0, 50)) . '...' : 'No messages yet'; ?>
                                </div>
                            </div>
                            <div class="chat-meta">
                                <div class="chat-time">
                                    <?php 
                                    if ($chat['last_message_time']) {
                                        echo date('H:i', strtotime($chat['last_message_time']));
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-area">
            <div class="welcome-screen">
                <div class="welcome-icon">💬</div>
                <h2 class="welcome-title">WhatsApp Clone</h2>
                <p class="welcome-subtitle">
                    Send and receive messages without keeping your phone online.<br>
                    Use WhatsApp Clone on up to 4 linked devices and 1 phone at the same time.
                </p>
            </div>
        </div>
    </div>

    <button class="new-chat-btn" onclick="showContacts()" title="New Chat">
        ➕
    </button>

    <script>
        // Global variables
        let currentChatId = null;
        let currentContactId = null;
        let messageInterval = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Update user online status
            updateOnlineStatus();
            
            // Set interval to update online status
            setInterval(updateOnlineStatus, 30000); // Every 30 seconds
        });

        function updateOnlineStatus() {
            fetch('send_messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_status'
            });
        }

        function openChat(contactId, contactName) {
            currentContactId = contactId;
            
            // Update active chat in sidebar
            document.querySelectorAll('.chat-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Load chat interface
            window.location.href = `chats.php?contact_id=${contactId}`;
        }

        function showContacts() {
            window.location.href = 'contacts.php';
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        // Search functionality
        document.getElementById('searchBox').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const chatItems = document.querySelectorAll('.chat-item');
            
            chatItems.forEach(item => {
                const chatName = item.querySelector('.chat-name').textContent.toLowerCase();
                if (chatName.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Mobile responsive
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.querySelector('.sidebar').classList.remove('active');
            }
        });
    </script>
</body>
</html>

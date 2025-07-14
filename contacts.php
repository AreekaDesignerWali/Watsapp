<?php
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$users = $db->getAllUsers($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        .header {
            background: #25D366;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .header-title {
            font-size: 20px;
            font-weight: 500;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            min-height: calc(100vh - 80px);
        }

        .search-section {
            padding: 20px;
            border-bottom: 1px solid #e9edef;
        }

        .search-box {
            width: 100%;
            padding: 12px 20px;
            border: 1px solid #e9edef;
            border-radius: 25px;
            font-size: 16px;
            background: #f0f2f5;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            background: white;
            border-color: #25D366;
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
        }

        .contacts-list {
            padding: 0;
        }

        .contact-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f2f5;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .contact-item:hover {
            background: #f5f6f6;
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .contact-avatar {
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
            position: relative;
        }

        .online-indicator {
            width: 14px;
            height: 14px;
            background: #25D366;
            border-radius: 50%;
            border: 3px solid white;
            position: absolute;
            bottom: -2px;
            right: -2px;
        }

        .contact-info {
            flex: 1;
            min-width: 0;
        }

        .contact-name {
            font-size: 16px;
            font-weight: 500;
            color: #111b21;
            margin-bottom: 4px;
        }

        .contact-email {
            font-size: 14px;
            color: #667781;
        }

        .contact-status {
            font-size: 13px;
            color: #667781;
            margin-top: 2px;
        }

        .contact-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .last-seen {
            font-size: 12px;
            color: #667781;
        }

        .chat-btn {
            background: #25D366;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-btn:hover {
            background: #128C7E;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #667781;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #667781;
        }

        .loading-spinner {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #25D366;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .container {
                margin: 0;
            }
            
            .header {
                padding: 15px;
            }
            
            .search-section {
                padding: 15px;
            }
            
            .contact-item {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="back-btn" onclick="goBack()">←</button>
        <h1 class="header-title">Select Contact</h1>
    </div>

    <div class="container">
        <div class="search-section">
            <input type="text" class="search-box" placeholder="Search contacts..." id="searchBox">
        </div>

        <div class="contacts-list" id="contactsList">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <h3>No contacts found</h3>
                    <p>No other users are available to chat with.</p>
                </div>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <div class="contact-item" onclick="startChat(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                        <div class="contact-avatar">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            <?php if ($user['is_online']): ?>
                                <div class="online-indicator"></div>
                            <?php endif; ?>
                        </div>
                        <div class="contact-info">
                            <div class="contact-name"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="contact-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="contact-status">
                                <?php if ($user['is_online']): ?>
                                    <span style="color: #25D366;">Online</span>
                                <?php else: ?>
                                    Last seen <?php echo date('M j, H:i', strtotime($user['last_seen'])); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="contact-meta">
                            <button class="chat-btn" onclick="event.stopPropagation(); startChat(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                Chat
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function goBack() {
            window.location.href = 'home.php';
        }

        function startChat(contactId, contactName) {
            window.location.href = `chats.php?contact_id=${contactId}`;
        }

        // Search functionality
        document.getElementById('searchBox').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const contactItems = document.querySelectorAll('.contact-item');
            
            contactItems.forEach(item => {
                const contactName = item.querySelector('.contact-name').textContent.toLowerCase();
                const contactEmail = item.querySelector('.contact-email').textContent.toLowerCase();
                
                if (contactName.includes(searchTerm) || contactEmail.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

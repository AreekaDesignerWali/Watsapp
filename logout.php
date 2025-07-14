<?php
require_once 'config.php';
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    // Update user offline status
    $db = Database::getInstance();
    $db->updateUserStatus($_SESSION['user_id'], false);
    
    // Destroy session
    session_destroy();
}

// Redirect to index page
header('Location: index.php');
exit();
?>

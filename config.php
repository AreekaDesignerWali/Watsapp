<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dbs9vsi6erudg3');
define('DB_USER', 'uc7ggok7oyoza');
define('DB_PASS', 'gqypavorhbbc');

// Application configuration
define('APP_NAME', 'WhatsApp Clone');
define('APP_URL', 'http://localhost');

// Session configuration
ini_set('session.cookie_lifetime', 86400); // 24 hours
session_start();

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

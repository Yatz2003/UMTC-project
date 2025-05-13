<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cooknect');

// Site configuration
define('SITE_NAME', 'Cooknect');
define('SITE_URL', 'http://localhost/cooknect');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/cooknect');
define('UPLOAD_PATH', BASE_PATH . '/uploads/');

define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Email configuration
define('MAIL_FROM', 'noreply@cooknect.com');
define('MAIL_FROM_NAME', 'Cooknect Team');

// Start session
session_start();

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
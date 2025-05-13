<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

session_start();
session_unset();
session_destroy();

header('Location: ' . SITE_URL . '/auth/login.php');
exit;
?>
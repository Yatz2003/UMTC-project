<?php
require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser($id = null) {
    global $db;
    $id = $id ?? $_SESSION['user_id'];
    
    $sql = "SELECT * FROM users WHERE user_id = ?";
    return $db->fetch($sql, [$id]);
}

function sanitize($data) {
    global $db;
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadFile($file, $type = 'recipe') {
    $targetDir = UPLOAD_PATH . ($type === 'recipe' ? 'recipes/' : 'profiles/');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, GIF allowed.'];
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['error' => 'File too large. Max 5MB allowed.'];
    }
    
    $filename = uniqid() . '.' . $ext;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => $filename];
    }
    
    return ['error' => 'Error uploading file.'];
}

function generatePasswordResetToken($email) {
    global $db;
    
    $user = $db->fetch("SELECT user_id FROM users WHERE email = ?", [$email]);
    if (!$user) return false;
    
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $db->query(
        "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)",
        [$user['user_id'], $token, $expires]
    );
    
    return $token;
}

function validatePasswordResetToken($token) {
    global $db;
    
    $result = $db->fetch(
        "SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()",
        [$token]
    );
    
    return $result ? $result['user_id'] : false;
}
?>
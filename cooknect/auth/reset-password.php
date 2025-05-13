<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$token = $_GET['token'] ?? '';
$user_id = validatePasswordResetToken($token);

if (!$user_id) {
    header('Location: forgot-password.php?error=invalid_token');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords don't match";
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $db->query(
            "UPDATE users SET password_hash = ? WHERE user_id = ?",
            [$password_hash, $user_id]
        );
        
        // Delete the used token
        $db->query(
            "DELETE FROM password_reset_tokens WHERE token = ?",
            [$token]
        );
        
        $success = true;
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <h2>Reset Password</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p>Password reset successfully! <a href="login.php">Login here</a></p>
        </div>
    <?php else: ?>
        <form method="POST" action="reset-password.php?token=<?php echo $token; ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
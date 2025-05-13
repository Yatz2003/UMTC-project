<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $token = generatePasswordResetToken($email);
        
        if ($token) {
            $resetLink = SITE_URL . "/auth/reset-password.php?token=$token";
            
            // In a real app, you would send an email here
            $message = "Password reset link has been sent to your email. For demo purposes: <a href='$resetLink'>$resetLink</a>";
        } else {
            $message = "Email not found in our system.";
        }
    } else {
        $message = "Please enter a valid email address.";
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <h2>Forgot Password</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-info">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="forgot-password.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Send Reset Link</button>
    </form>
    
    <p>Remember your password? <a href="login.php">Login here</a></p>
</div>

<?php include '../includes/footer.php'; ?>
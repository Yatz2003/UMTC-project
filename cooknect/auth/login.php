<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $user = $db->fetch("SELECT * FROM users WHERE username = ?", [$username]);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        // Update last login
        $db->query("UPDATE users SET last_login = NOW() WHERE user_id = ?", [$user['user_id']]);
        
        header('Location: ../index.php');
        exit;
    } else {
        $errors[] = "Invalid username or password";
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <h2>Login to Cooknect</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <p>Forgot password? <a href="forgot-password.php">Reset it here</a></p>
</div>

<?php include '../includes/footer.php'; ?>
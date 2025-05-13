<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords don't match";
    
    // Check if username/email exists
    $userExists = $db->fetch("SELECT user_id FROM users WHERE username = ? OR email = ?", [$username, $email]);
    if ($userExists) $errors[] = "Username or email already exists";
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $db->query(
            "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)",
            [$username, $email, $password_hash]
        );
        
        $success = true;
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <h2>Join Cooknect</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p>Registration successful! <a href="login.php">Login here</a></p>
        </div>
    <?php else: ?>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
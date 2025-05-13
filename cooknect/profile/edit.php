<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get current user data
$user = $db->fetch(
    "SELECT username, email, bio, profile_pic FROM users WHERE user_id = ?",
    [$user_id]
);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $bio = sanitize($_POST['bio']);
    $errors = [];

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }

    // Check if username or email is already taken by another user
    $existing_user = $db->fetch(
        "SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?",
        [$username, $email, $user_id]
    );
    if ($existing_user) {
        $errors[] = "Username or email is already taken.";
    }

    // Handle profile picture upload
    $profile_pic = $user['profile_pic'];
    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading profile picture: " . getUploadErrorMessage($_FILES['profile_pic']['error']);
        } else {
            $file = [
                'name' => $_FILES['profile_pic']['name'],
                'type' => $_FILES['profile_pic']['type'],
                'tmp_name' => $_FILES['profile_pic']['tmp_name'],
                'error' => $_FILES['profile_pic']['error'],
                'size' => $_FILES['profile_pic']['size']
            ];
            $result = uploadFile($file, 'profile');
            if (isset($result['error'])) {
                $errors[] = "Failed to upload profile picture: " . $result['error'];
            } else {
                $profile_pic = $result['success'];
            }
        }
    }

    if (empty($errors)) {
        try {
            // Update user profile
            $db->query(
                "UPDATE users SET username = ?, email = ?, bio = ?, profile_pic = ? WHERE user_id = ?",
                [$username, $email, $bio, $profile_pic, $user_id]
            );

            // Update session username if changed
            $_SESSION['username'] = $username;

            $_SESSION['success'] = "Profile updated successfully!";

            // Debug: Log the redirect URL
            $redirect_url = SITE_URL . '/profile/view.php?id=' . $user_id;
            file_put_contents(__DIR__ . '/../debug.log', "Redirecting to: $redirect_url\n", FILE_APPEND);

            // Redirect to profile page
            if ($user_id) {
                header('Location: ' . $redirect_url);
            } else {
                header('Location: ' . SITE_URL . '/index.php');
            }
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Helper function for upload error messages
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "File is too large.";
        case UPLOAD_ERR_PARTIAL:
            return "File was only partially uploaded.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing temporary folder.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk.";
        case UPLOAD_ERR_EXTENSION:
            return "File upload stopped by extension.";
        default:
            return "Unknown upload error.";
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Edit Profile</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Profile Picture (JPG/PNG/GIF, max 5MB)</label>
            <input type="file" name="profile_pic" accept="image/jpeg,image/png,image/gif">
            <?php if (!empty($user['profile_pic'])): ?>
                <p>Current picture:</p>
                <img src="<?= SITE_URL ?>/uploads/profiles/<?= $user['profile_pic'] ?>" alt="Current profile picture" style="max-width: 100px; margin-top: 10px;">
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= SITE_URL ?>/profile/view.php?id=<?= $user_id ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
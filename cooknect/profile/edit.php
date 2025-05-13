<?php
// Initialize session and required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

// Set upload directory and ensure it exists
$uploadDir = __DIR__ . '/../uploads/profiles/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $_SESSION['error'] = "Failed to create upload directory";
        header('Location: ' . SITE_URL . '/profile.php');
        exit;
    }
}

// Get current user data
$user_id = $_SESSION['user_id'];
$user = $db->fetch("SELECT * FROM users WHERE user_id = ?", [$user_id]);

if (!$user) {
    $_SESSION['error'] = "User not found";
    header('Location: ' . SITE_URL . '/profile.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = trim(sanitize($_POST['username']));
    $email = trim(sanitize($_POST['email']));
    $bio = trim(sanitize($_POST['bio']));
    $profile_pic = $user['profile_pic'];

    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username must be less than 50 characters";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (strlen($bio) > 500) {
        $errors[] = "Bio must be less than 500 characters";
    }

    try {
        if (empty($errors)) {
            // Handle file upload
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                $fileType = mime_content_type($_FILES['profile_pic']['tmp_name']);
                
                if (!array_key_exists($fileType, $allowedTypes)) {
                    throw new Exception('Only JPG, PNG, and GIF images are allowed');
                }

                // Generate secure filename
                $extension = $allowedTypes[$fileType];
                $filename = 'profile_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                $destination = $uploadDir . $filename;

                // Validate image dimensions
                list($width, $height) = getimagesize($_FILES['profile_pic']['tmp_name']);
                if ($width > 2000 || $height > 2000) {
                    throw new Exception('Image dimensions must be less than 2000x2000 pixels');
                }

                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
                    // Delete old profile picture if it exists and isn't default
                    if ($user['profile_pic'] && $user['profile_pic'] !== 'default.jpg') {
                        $oldFile = $uploadDir . $user['profile_pic'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    $profile_pic = $filename;
                } else {
                    throw new Exception('Failed to move uploaded file');
                }
            }

            // Update database
            $db->query(
                "UPDATE users SET username = ?, email = ?, bio = ?, profile_pic = ? WHERE user_id = ?",
                [$username, $email, $bio, $profile_pic, $user_id]
            );

            // Update session
            $_SESSION['username'] = $username;
            $_SESSION['profile_pic'] = $profile_pic;
            $_SESSION['success'] = "Profile updated successfully!";
            
            header('Location: ' . SITE_URL . '/profile.php');
            exit;
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
            header('Location: ' . SITE_URL . '/profile/edit.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: ' . SITE_URL . '/profile/edit.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="profile-edit-container">
    <h1>Edit Profile</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="profile-edit-form">
        <div class="form-group">
            <label for="username">Username*</label>
            <input type="text" id="username" name="username" 
                   value="<?= htmlspecialchars($user['username']); ?>" required
                   maxlength="50">
            <small class="form-text">Maximum 50 characters</small>
        </div>
        
        <div class="form-group">
            <label for="email">Email*</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($user['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4" maxlength="500"><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            <small class="form-text">Maximum 500 characters</small>
        </div>
        
        <div class="form-group">
            <label for="profile_pic">Profile Picture</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/jpeg,image/png,image/gif">
            <?php if (!empty($user['profile_pic'])): ?>
                <div class="current-avatar">
                    <p>Current Avatar:</p>
                    <img src="<?= SITE_URL ?>/uploads/profiles/<?= htmlspecialchars($user['profile_pic']); ?>" 
                         alt="Current profile" class="current-avatar-img">
                </div>
            <?php endif; ?>
            <small class="form-text">Allowed formats: JPG, PNG, GIF (Max 2MB)</small>
        </div>
        
 <div class="recipe-edit-container">
    <h1>Edit Recipe</h1>
    
    <form method="POST" enctype="multipart/form-data" class="recipe-edit-form">
        <!-- Your form fields here -->
        
        <div class="form-actions">
            <button type="submit" class="btn-save-recipe">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="<?= SITE_URL ?>/recipes/view.php?id=<?= $recipe['recipe_id'] ?>" 
               class="btn-cancel">
               <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>
<link rel="stylesheet" href="Assets/css/style.css">
<?php include __DIR__ . '/../includes/footer.php'; ?>
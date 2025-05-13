<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$recipe_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get recipe data
$recipe = $db->fetch(
    "SELECT * FROM recipes WHERE recipe_id = ? AND user_id = ?", 
    [$recipe_id, $user_id]
);

if (!$recipe) {
    $_SESSION['error'] = "Recipe not found or you don't have permission";
    header('Location: ' . SITE_URL . '/recipes/view.php?id=' . $recipe_id);
    exit;
}

// Get categories
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
$selected_categories = array_column(
    $db->fetchAll(
        "SELECT category_id FROM recipe_categories WHERE recipe_id = ?", 
        [$recipe_id]
    ),
    'category_id'
);

// Get current images
$current_images = $db->fetchAll(
    "SELECT * FROM recipe_images WHERE recipe_id = ? ORDER BY is_primary DESC",
    [$recipe_id]
);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $ingredients = sanitize($_POST['ingredients']);
    $instructions = sanitize($_POST['instructions']);
    $prep_time = (int)$_POST['prep_time'];
    $cook_time = (int)$_POST['cook_time'];
    $servings = (int)$_POST['servings'];
    $difficulty = $_POST['difficulty'];
    $new_categories = $_POST['categories'] ?? [];
    $errors = [];

    // Validate inputs
    if (empty($title)) $errors[] = "Title is required";
    if (empty($ingredients)) $errors[] = "Ingredients are required";
    if (empty($instructions)) $errors[] = "Instructions are required";
    if ($prep_time < 0) $errors[] = "Prep time must be positive";
    if ($cook_time < 0) $errors[] = "Cook time must be positive";
    if ($servings <= 0) $errors[] = "Servings must be at least 1";
    if (!in_array($difficulty, ['Easy', 'Medium', 'Hard'])) $errors[] = "Invalid difficulty level";

    // Handle image upload
    $image_paths = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                $errors[] = "Error uploading file " . $_FILES['images']['name'][$key] . ": " . getUploadErrorMessage($_FILES['images']['error'][$key]);
                continue;
            }
            $file = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key]
            ];
            $result = uploadFile($file, 'recipe');
            if (isset($result['error'])) {
                $errors[] = "Failed to upload " . $file['name'] . ": " . $result['error'];
            } else {
                $image_paths[] = $result['success'];
            }
            if (count($image_paths) >= 5) break;
        }
    }

    if (empty($errors)) {
        try {
            // Update recipe
            $db->query(
                "UPDATE recipes SET 
                    title = ?, description = ?, ingredients = ?, instructions = ?,
                    prep_time = ?, cook_time = ?, servings = ?, difficulty = ?
                 WHERE recipe_id = ?",
                [
                    $title, $description, $ingredients, $instructions,
                    $prep_time, $cook_time, $servings, $difficulty,
                    $recipe_id
                ]
            );

            // Update categories
            $db->query("DELETE FROM recipe_categories WHERE recipe_id = ?", [$recipe_id]);
            foreach ($new_categories as $category_id) {
                $db->query(
                    "INSERT INTO recipe_categories (recipe_id, category_id) VALUES (?, ?)",
                    [$recipe_id, $category_id]
                );
            }

            // Update images
            if (!empty($image_paths)) {
                // Delete existing images
                $db->query("DELETE FROM recipe_images WHERE recipe_id = ?", [$recipe_id]);
                // Insert new images, first one is primary
                $db->query(
                    "INSERT INTO recipe_images (recipe_id, image_path, is_primary) VALUES (?, ?, ?)",
                    [$recipe_id, $image_paths[0], true]
                );
                for ($i = 1; $i < count($image_paths); $i++) {
                    $db->query(
                        "INSERT INTO recipe_images (recipe_id, image_path) VALUES (?, ?)",
                        [$recipe_id, $image_paths[$i]]
                    );
                }
            }

            $_SESSION['success'] = "Recipe updated successfully!";
            header('Location: ' . SITE_URL . '/recipes/view.php?id=' . $recipe_id);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating recipe: " . $e->getMessage();
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
    <h1>Edit Recipe</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($recipe['description']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Images (max 5, JPG/PNG/GIF, max 5MB)</label>
            <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/gif">
            <?php if (!empty($current_images)): ?>
                <p>Current images:</p>
                <?php foreach ($current_images as $image): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/recipes/<?php echo $image['image_path']; ?>" alt="Current image" style="max-width: 100px; margin-right: 10px;">
                <?php endforeach; ?>
                <p><small>Uploading new images will replace existing ones.</small></p>
            <?php endif; ?>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Prep Time (mins)</label>
                <input type="number" name="prep_time" value="<?= $recipe['prep_time'] ?>">
            </div>
            <div class="form-group">
                <label>Cook Time (mins)</label>
                <input type="number" name="cook_time" value="<?= $recipe['cook_time'] ?>">
            </div>
            <div class="form-group">
                <label>Servings</label>
                <input type="number" name="servings" value="<?= $recipe['servings'] ?>">
            </div>
            <div class="form-group">
                <label>Difficulty</label>
                <select name="difficulty">
                    <option value="Easy" <?= $recipe['difficulty'] === 'Easy' ? 'selected' : '' ?>>Easy</option>
                    <option value="Medium" <?= $recipe['difficulty'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Hard" <?= $recipe['difficulty'] === 'Hard' ? 'selected' : '' ?>>Hard</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Ingredients (one per line)</label>
            <textarea name="ingredients" rows="6" required><?= htmlspecialchars($recipe['ingredients']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Instructions (one step per line)</label>
            <textarea name="instructions" rows="8" required><?= htmlspecialchars($recipe['instructions']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Categories</label>
            <div class="category-options">
                <?php foreach ($categories as $category): ?>
                    <label>
                        <input type="checkbox" name="categories[]" value="<?= $category['category_id'] ?>"
                            <?= in_array($category['category_id'], $selected_categories) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= SITE_URL ?>/recipes/view.php?id=<?= $recipe_id ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
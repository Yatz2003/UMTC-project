<?php
// Use absolute paths consistently
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$errors = [];
$success = false;

// Get categories for the form
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $ingredients = sanitize($_POST['ingredients']);
    $instructions = sanitize($_POST['instructions']);
    $prep_time = (int)$_POST['prep_time'];
    $cook_time = (int)$_POST['cook_time'];
    $servings = (int)$_POST['servings'];
    $difficulty = $_POST['difficulty'];
    $selected_categories = $_POST['categories'] ?? [];
    
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
            // Insert recipe
            $db->query(
                "INSERT INTO recipes (user_id, title, description, ingredients, instructions, 
                 prep_time, cook_time, servings, difficulty, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [$_SESSION['user_id'], $title, $description, $ingredients, $instructions,
                 $prep_time, $cook_time, $servings, $difficulty]
            );
            
            $recipe_id = $db->lastInsertId();
            
            // Insert categories
            foreach ($selected_categories as $category_id) {
                $db->query(
                    "INSERT INTO recipe_categories (recipe_id, category_id) VALUES (?, ?)",
                    [$recipe_id, $category_id]
                );
            }
            
            // Insert images
            if (!empty($image_paths)) {
                // First image is primary
                $db->query(
                    "INSERT INTO recipe_images (recipe_id, image_path, is_primary) VALUES (?, ?, ?)",
                    [$recipe_id, $image_paths[0], true]
                );
                
                // Additional images
                for ($i = 1; $i < count($image_paths); $i++) {
                    $db->query(
                        "INSERT INTO recipe_images (recipe_id, image_path) VALUES (?, ?)",
                        [$recipe_id, $image_paths[$i]]
                    );
                }
                $_SESSION['success'] = "Recipe and images uploaded successfully!";
            } else {
                $_SESSION['success'] = "Recipe created successfully! No images uploaded.";
            }
            
            header("Location: view.php?id=$recipe_id");
            exit;
        } catch (Exception $e) {
            $errors[] = "Error creating recipe: " . $e->getMessage();
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

include $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/header.php';
?>

<div class="recipe-form-container">
    <h1>Post a New Recipe</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="recipe-form">
        <div class="form-row responsive-row">
            <div class="form-group">
                <label for="title">Recipe Title*</label>
                <input type="text" id="title" name="title" required value="<?php echo $_POST['title'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="difficulty">Difficulty</label>
                <select id="difficulty" name="difficulty">
                    <option value="Easy" <?= ($_POST['difficulty'] ?? '') === 'Easy' ? 'selected' : '' ?>>Easy</option>
                    <option value="Medium" <?= ($_POST['difficulty'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Hard" <?= ($_POST['difficulty'] ?? '') === 'Hard' ? 'selected' : '' ?>>Hard</option>
                </select>
            </div>
        </div>
        
        <div class="form-row responsive-row">
            <div class="form-group">
                <label for="prep_time">Prep Time (minutes)</label>
                <input type="number" id="prep_time" name="prep_time" min="0" value="<?= $_POST['prep_time'] ?? 0 ?>">
            </div>
            
            <div class="form-group">
                <label for="cook_time">Cook Time (minutes)</label>
                <input type="number" id="cook_time" name="cook_time" min="0" value="<?= $_POST['prep_time'] ?? 0 ?>">
            </div>
            
            <div class="form-group">
                <label for="servings">Servings</label>
                <input type="number" id="servings" name="servings" min="1" value="<?= $_POST['servings'] ?? 1 ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= $_POST['description'] ?? '' ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="ingredients">Ingredients*</label>
            <textarea id="ingredients" name="ingredients" rows="6" required><?= $_POST['ingredients'] ?? '' ?></textarea>
            <small class="hint">Enter each ingredient on a new line</small>
        </div>
        
        <div class="form-group">
            <label for="instructions">Instructions*</label>
            <textarea id="instructions" name="instructions" rows="8" required><?= $_POST['instructions'] ?? '' ?></textarea>
            <small class="hint">Enter each step on a new line</small>
        </div>
        
        <div class="form-group">
            <label for="categories">Categories</label>
            <div class="category-checkboxes">
                <?php foreach ($categories as $category): ?>
                    <div class="checkbox-item">
                        <input type="checkbox" id="category_<?= $category['category_id'] ?>" 
                               name="categories[]" value="<?= $category['category_id'] ?>"
                               <?= in_array($category['category_id'], $_POST['categories'] ?? []) ? 'checked' : '' ?>>
                        <label for="category_<?= $category['category_id'] ?>"><?= $category['name'] ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="images">Images (max 5, JPG/PNG/GIF, max 5MB)</label>
            <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/gif">
            <small class="hint">First image will be used as the main image</small>
            <div class="image-preview-container" id="imagePreview"></div>
        </div>
        
        <button type="submit" class="btn btn-primary">Post Recipe</button>
    </form>
</div>

<script>
document.getElementById('images').addEventListener('change', function() {
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = '';
    
    if (this.files) {
        Array.from(this.files).forEach(file => {
            if (file.type.match('image.*')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-image">×</button>
                    `;
                    previewContainer.appendChild(previewDiv);
                    
                    previewDiv.querySelector('.remove-image').addEventListener('click', function() {
                        previewDiv.remove();
                    });
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php 
include $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/footer.php';
?>
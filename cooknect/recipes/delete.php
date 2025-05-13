<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$recipe_id = $_GET['id'] ?? 0;
$recipe = $db->fetch("SELECT * FROM recipes WHERE recipe_id = ?", [$recipe_id]);

// Verify recipe exists and user has permission
if (!$recipe) {
    $_SESSION['error'] = "Recipe not found";
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

if ($_SESSION['user_id'] != $recipe['user_id'] && !isset($_SESSION['is_admin'])) {
    $_SESSION['error'] = "You don't have permission to delete this recipe";
    header('Location: ' . SITE_URL . '/recipes/view.php?id=' . $recipe_id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete associated records first (foreign key constraints)
        $db->query("DELETE FROM recipe_categories WHERE recipe_id = ?", [$recipe_id]);
        $db->query("DELETE FROM recipe_images WHERE recipe_id = ?", [$recipe_id]);
        $db->query("DELETE FROM comments WHERE recipe_id = ?", [$recipe_id]);
        $db->query("DELETE FROM reactions WHERE recipe_id = ?", [$recipe_id]);
        $db->query("DELETE FROM saved_recipes WHERE recipe_id = ?", [$recipe_id]);
        
        // Now delete the recipe
        $db->query("DELETE FROM recipes WHERE recipe_id = ?", [$recipe_id]);
        
        $_SESSION['success'] = "Recipe deleted successfully";
        header('Location: ' . SITE_URL . '/profile.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting recipe: " . $e->getMessage();
        header('Location: ' . SITE_URL . '/recipes/view.php?id=' . $recipe_id);
        exit;
    }
}

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="delete-confirmation">
    <h1>Delete Recipe</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="confirmation-box">
        <p>Are you sure you want to delete "<strong><?= htmlspecialchars($recipe['title']); ?></strong>"?</p>
        <p>This action cannot be undone.</p>
        
        <form method="POST">
            <div class="form-actions">
                <a href="<?= SITE_URL . '/recipes/view.php?id=' . $recipe_id; ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-danger">Delete Permanently</button>
            </div>
        </form>
    </div>
</div>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
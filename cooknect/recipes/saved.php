<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get saved recipes
$saved_recipes = $db->fetchAll(
    "SELECT r.*, u.username, u.profile_pic,
     (SELECT COUNT(*) FROM reactions WHERE recipe_id = r.recipe_id) AS reaction_count,
     (SELECT COUNT(*) FROM comments WHERE recipe_id = r.recipe_id) AS comment_count
     FROM saved_recipes sr
     JOIN recipes r ON sr.recipe_id = r.recipe_id
     JOIN users u ON r.user_id = u.user_id
     WHERE sr.user_id = ?
     ORDER BY sr.saved_at DESC
     LIMIT ? OFFSET ?",
    [$_SESSION['user_id'], $limit, $offset]
);

// Get total count for pagination
$total_recipes = $db->fetch(
    "SELECT COUNT(*) AS total 
     FROM saved_recipes 
     WHERE user_id = ?",
    [$_SESSION['user_id']]
)['total'];
$total_pages = ceil($total_recipes / $limit);

include '../../includes/header.php';
?>

<div class="saved-recipes-container">
    <h1>Your Saved Recipes</h1>
    
    <?php if (empty($saved_recipes)): ?>
        <div class="no-recipes">
            <p>You haven't saved any recipes yet.</p>
            <p>Browse <a href="<?php echo SITE_URL; ?>/index.php">recipes</a> and save your favorites to find them here later.</p>
        </div>
    <?php else: ?>
        <div class="recipe-list">
            <?php foreach ($saved_recipes as $recipe): ?>
                <div class="recipe-card">
                    <div class="recipe-header">
                        <div class="user-info">
                            <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $recipe['profile_pic']; ?>" alt="<?php echo $recipe['username']; ?>">
                            <span><?php echo $recipe['username']; ?></span>
                        </div>
                        <span class="recipe-date">Saved on <?php echo date('M j, Y', strtotime($recipe['saved_at'])); ?></span>
                    </div>
                    
                    <div class="recipe-body">
                        <h2><a href="<?php echo SITE_URL; ?>/recipes/view.php?id=<?php echo $recipe['recipe_id']; ?>"><?php echo $recipe['title']; ?></a></h2>
                        
                        <?php if (!empty($recipe['description'])): ?>
                            <p class="recipe-description"><?php echo $recipe['description']; ?></p>
                        <?php endif; ?>
                        
                        <div class="recipe-meta">
                            <span><?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> mins</span>
                            <span><?php echo $recipe['servings']; ?> servings</span>
                            <span><?php echo $recipe['difficulty']; ?></span>
                        </div>
                    </div>
                    
                    <div class="recipe-footer">
                        <div class="reactions">
                            <button class="reaction-btn" data-recipe="<?php echo $recipe['recipe_id']; ?>">
                                <span class="icon">👍</span>
                                <span class="count"><?php echo $recipe['reaction_count']; ?></span>
                            </button>
                        </div>
                        
                        <div class="comments">
                            <a href="<?php echo SITE_URL; ?>/recipes/view.php?id=<?php echo $recipe['recipe_id']; ?>#comments">
                                <span class="icon">💬</span>
                                <span class="count"><?php echo $recipe['comment_count']; ?></span>
                            </a>
                        </div>
                        
                        <div class="save">
                            <button class="save-btn" data-recipe="<?php echo $recipe['recipe_id']; ?>" data-saved="true">
                                <span class="icon">❤️</span>
                                <span class="text">Saved</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="prev">Previous</a>
                <?php endif; ?>
                
                <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="next">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
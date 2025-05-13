<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$user_id = $_GET['id'] ?? $_SESSION['user_id'];
$user = $db->fetch("SELECT * FROM users WHERE user_id = ?", [$user_id]);

if (!$user) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$is_current_user = ($user['user_id'] == $_SESSION['user_id']);

// Get user's recipes
$recipes = $db->fetchAll(
    "SELECT r.*, 
     (SELECT COUNT(*) FROM reactions WHERE recipe_id = r.recipe_id) AS reaction_count,
     (SELECT COUNT(*) FROM comments WHERE recipe_id = r.recipe_id) AS comment_count
     FROM recipes r
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC
     LIMIT 10",
    [$user_id]
);

// Get saved recipes if it's the current user
$saved_recipes = [];
if ($is_current_user) {
    $saved_recipes = $db->fetchAll(
        "SELECT r.*, u.username, u.profile_pic,
         (SELECT COUNT(*) FROM reactions WHERE recipe_id = r.recipe_id) AS reaction_count,
         (SELECT COUNT(*) FROM comments WHERE recipe_id = r.recipe_id) AS comment_count
         FROM saved_recipes sr
         JOIN recipes r ON sr.recipe_id = r.recipe_id
         JOIN users u ON r.user_id = u.user_id
         WHERE sr.user_id = ?
         ORDER BY sr.saved_at DESC
         LIMIT 10",
        [$user_id]
    );
}

include 'includes/header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $user['profile_pic']; ?>" alt="<?php echo $user['username']; ?>">
        </div>
        
        <div class="profile-info">
            <h1><?php echo $user['username']; ?></h1>
            
            <?php if (!empty($user['bio'])): ?>
                <p class="profile-bio"><?php echo $user['bio']; ?></p>
            <?php endif; ?>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($recipes); ?></span>
                    <span class="stat-label">Recipes</span>
                </div>
            </div>
            
            <?php if ($is_current_user): ?>
                <div class="profile-actions">
                    <a href="<?php echo SITE_URL; ?>/profile/edit.php" class="btn btn-primary">Edit Profile</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="profile-section">
            <h2>My Recipes</h2>
            
            <?php if (empty($recipes)): ?>
                <div class="no-recipes">
                    <p><?php echo $is_current_user ? 'You haven\'t posted any recipes yet.' : 'This user hasn\'t posted any recipes yet.'; ?></p>
                    <?php if ($is_current_user): ?>
                        <a href="<?php echo SITE_URL; ?>/recipes/create.php" class="btn btn-primary">Post Your First Recipe</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="recipe-list">
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="recipe-card">
                            <div class="recipe-header">
                                <div class="user-info">
                                    <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $user['profile_pic']; ?>" alt="<?php echo $user['username']; ?>">
                                    <span><?php echo $user['username']; ?></span>
                                </div>
                                <span class="recipe-date"><?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></span>
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
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="view-all">
                    <a href="<?php echo SITE_URL; ?>/search.php?user=<?php echo $user_id; ?>" class="btn btn-outline">View All Recipes</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($is_current_user && !empty($saved_recipes)): ?>
            <div class="profile-section">
                <h2>Saved Recipes</h2>
                
                <div class="recipe-list">
                    <?php foreach ($saved_recipes as $recipe): ?>
                        <div class="recipe-card">
                            <div class="recipe-header">
                                <div class="user-info">
                                    <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $recipe['profile_pic']; ?>" alt="<?php echo $recipe['username']; ?>">
                                    <span><?php echo $recipe['username']; ?></span>
                                </div>
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
                
                <div class="view-all">
                    <a href="<?php echo SITE_URL; ?>/profile/saved.php" class="btn btn-outline">View All Saved Recipes</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
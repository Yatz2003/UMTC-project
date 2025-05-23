<?php
// Use absolute paths consistently
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/functions.php';

$recipe_id = $_GET['id'] ?? 0;
$recipe = $db->fetch(
    "SELECT r.*, u.username, u.profile_pic, u.bio AS user_bio
     FROM recipes r
     JOIN users u ON r.user_id = u.user_id
     WHERE r.recipe_id = ?",
    [$recipe_id]
);

if (!$recipe) {
    $_SESSION['error'] = "Recipe not found.";
    header('Location: /cooknect/index.php');
    exit;
}

// Get recipe categories
$categories = $db->fetchAll(
    "SELECT c.* FROM categories c
     JOIN recipe_categories rc ON c.category_id = rc.category_id
     WHERE rc.recipe_id = ?",
    [$recipe_id]
);

// Get recipe images
$images = $db->fetchAll(
    "SELECT * FROM recipe_images WHERE recipe_id = ? ORDER BY is_primary DESC",
    [$recipe_id]
);

// Get comments
$comments = $db->fetchAll(
    "SELECT c.*, u.username, u.profile_pic
     FROM comments c
     JOIN users u ON c.user_id = u.user_id
     WHERE c.recipe_id = ? AND c.parent_comment_id IS NULL
     ORDER BY c.created_at DESC",
    [$recipe_id]
);

// Get comment count
$comment_count = $db->fetch(
    "SELECT COUNT(*) AS count FROM comments WHERE recipe_id = ?",
    [$recipe_id]
)['count'];

// Get reaction count
$reaction_count = $db->fetch(
    "SELECT COUNT(*) AS count FROM reactions WHERE recipe_id = ?",
    [$recipe_id]
)['count'];

// Check if current user has saved this recipe
$is_saved = false;
if (isLoggedIn()) {
    $saved = $db->fetch(
        "SELECT save_id FROM saved_recipes WHERE user_id = ? AND recipe_id = ?",
        [$_SESSION['user_id'], $recipe_id]
    );
    $is_saved = (bool)$saved;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isLoggedIn()) {
    $comment = sanitize($_POST['comment']);
    $parent_id = $_POST['parent_id'] ?? null;
    
    if (!empty($comment)) {
        $db->query(
            "INSERT INTO comments (user_id, recipe_id, parent_comment_id, content)
             VALUES (?, ?, ?, ?)",
            [$_SESSION['user_id'], $recipe_id, $parent_id, $comment]
        );
        
        header("Location: /cooknect/recipes/view.php?id=$recipe_id#comments");
        exit;
    }
}

include $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/header.php';
?>

<div class="container">
    <div class="recipe-view">
        <div class="recipe-header">
            <div class="user-info">
                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $recipe['profile_pic'] ?: 'default.jpg'; ?>" alt="Profile picture of <?php echo htmlspecialchars($recipe['username'] ?: 'Unknown'); ?>">
                <div>
                    <h3><?php echo htmlspecialchars($recipe['username'] ?: 'Unknown'); ?></h3>
                    <span class="recipe-date">Posted on <?php echo date('F j, Y', strtotime($recipe['created_at'])); ?></span>
                </div>
            </div>
            
            <?php if (isLoggedIn() && ($_SESSION['user_id'] == $recipe['user_id'] || isset($_SESSION['is_admin']))): ?>
                <div class="recipe-actions">
                    <div class="recipe-actions-menu">
                        <a href="/cooknect/recipes/edit.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-primary btn-edit-recipe">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="/cooknect/recipes/delete.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-outline delete-action" 
                           onclick="return confirm('Are you sure you want to delete this recipe?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
        
        <?php if (!empty($recipe['description'])): ?>
            <p class="recipe-description"><?php echo htmlspecialchars($recipe['description']); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($images)): ?>
            <div class="recipe-images">
                <div class="main-image">
                    <img src="<?php echo SITE_URL; ?>/uploads/recipes/<?php echo $images[0]['image_path'] ?: 'default-recipe.jpg'; ?>" alt="Main image of <?php echo htmlspecialchars($recipe['title']); ?>">
                </div>
                
                <?php if (count($images) > 1): ?>
                    <div class="image-thumbnails">
                        <?php foreach (array_slice($images, 1) as $image): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/recipes/<?php echo $image['image_path'] ?: 'default-recipe.jpg'; ?>" alt="Thumbnail of <?php echo htmlspecialchars($recipe['title']); ?>">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="recipe-images">
                <div class="main-image">
                    <img src="<?php echo SITE_URL; ?>/uploads/recipes/default-recipe.jpg" alt="Default image for <?php echo htmlspecialchars($recipe['title']); ?>">
                </div>
            </div>
        <?php endif; ?>
        
        <div class="recipe-meta">
            <div class="meta-item">
                <span class="label">Prep Time:</span>
                <span class="value"><?php echo $recipe['prep_time']; ?> mins</span>
            </div>
            
            <div class="meta-item">
                <span class="label">Cook Time:</span>
                <span class="value"><?php echo $recipe['cook_time']; ?> mins</span>
            </div>
            
            <div class="meta-item">
                <span class="label">Total Time:</span>
                <span class="value"><?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> mins</span>
            </div>
            
            <div class="meta-item">
                <span class="label">Servings:</span>
                <span class="value"><?php echo $recipe['servings']; ?></span>
            </div>
            
            <div class="meta-item">
                <span class="label">Difficulty:</span>
                <span class="value"><?php echo $recipe['difficulty']; ?></span>
            </div>
        </div>
        
        <div class="recipe-tags">
            <?php foreach ($categories as $category): ?>
                <a href="/cooknect/index.php?category=<?php echo $category['category_id']; ?>" class="tag"><?php echo htmlspecialchars($category['name']); ?></a>
            <?php endforeach; ?>
        </div>
        
        <div class="recipe-content">
            <div class="ingredients">
                <h2>Ingredients</h2>
                <ul>
                    <?php
                    $ingredients = explode("\n", $recipe['ingredients']);
                    foreach ($ingredients as $ingredient):
                        if (trim($ingredient)):
                    ?>
                        <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ul>
            </div>
            
            <div class="instructions">
                <h2>Instructions</h2>
                <ol>
                    <?php
                    $instructions = explode("\n", $recipe['instructions']);
                    foreach ($instructions as $instruction):
                        if (trim($instruction)):
                    ?>
                        <li><?php echo htmlspecialchars(trim($instruction)); ?></li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ol>
            </div>
        </div>
        
        <div class="recipe-engagement">
            <div class="engagement-item reactions">
                <button class="reaction-btn" data-recipe="<?php echo $recipe_id; ?>">
                    <span class="icon">🔥</span>
                    <span class="count"><?php echo $reaction_count; ?></span>
                </button>
            </div>
            
            <div class="engagement-item save">
                <button class="save-btn" data-recipe="<?php echo $recipe_id; ?>" data-saved="<?php echo $is_saved ? 'true' : 'false'; ?>">
                    <span class="icon"><?php echo $is_saved ? '❤️' : '♡'; ?></span>
                    <span class="text"><?php echo $is_saved ? 'Saved' : 'Save'; ?></span>
                </button>
            </div>
            
            <div class="engagement-item share">
                <button class="share-btn" data-url="<?php echo SITE_URL . '/recipes/view.php?id=' . $recipe_id; ?>">
                    <span class="icon">🔗</span>
                    <span class="text">Share</span>
                </button>
            </div>
        </div>
        
        <div class="recipe-comments" id="comments">
            <h2>Comments (<?php echo $comment_count; ?>)</h2>
            
            <?php if (isLoggedIn()): ?>
                <form method="POST" class="comment-form">
                    <div class="form-group">
                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo getUser()['profile_pic'] ?: 'default.jpg'; ?>" alt="Profile picture of <?php echo htmlspecialchars($_SESSION['username'] ?: 'Unknown'); ?>">
                        <textarea name="comment" placeholder="Add a comment..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
            <?php else: ?>
                <div class="login-prompt">
                    <p><a href="/cooknect/auth/login.php">Login</a> to post a comment</p>
                </div>
            <?php endif; ?>
            
            <div class="comments-list">
                <?php if (empty($comments)): ?>
                    <p class="no-comments">No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $comment['profile_pic'] ?: 'default.jpg'; ?>" alt="Profile picture of <?php echo htmlspecialchars($comment['username'] ?: 'Unknown'); ?>">
                                <div>
                                    <h4><?php echo htmlspecialchars($comment['username'] ?: 'Unknown'); ?></h4>
                                    <span class="comment-date"><?php echo date('F j, Y g:i a', strtotime($comment['created_at'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="comment-body">
                                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            </div>
                            
                            <div class="comment-footer">
                                <?php if (isLoggedIn()): ?>
                                    <button class="reply-btn" data-comment="<?php echo $comment['comment_id']; ?>">Reply</button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Reply form (hidden by default) -->
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" class="reply-form" style="display: none;">
                                    <input type="hidden" name="parent_id" value="<?php echo $comment['comment_id']; ?>">
                                    <div class="form-group">
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo getUser()['profile_pic'] ?: 'default.jpg'; ?>" alt="Profile picture of <?php echo htmlspecialchars($_SESSION['username'] ?: 'Unknown'); ?>">
                                        <textarea name="comment" placeholder="Write a reply..." required></textarea>
                                    </div>
                                    <div class="reply-actions">
                                        <button type="submit" class="btn btn-primary">Post Reply</button>
                                        <button type="button" class="btn btn-secondary cancel-reply">Cancel</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                            
                            <!-- Replies -->
                            <?php
                            $replies = $db->fetchAll(
                                "SELECT c.*, u.username, u.profile_pic
                                 FROM comments c
                                 JOIN users u ON c.user_id = u.user_id
                                 WHERE c.parent_comment_id = ?
                                 ORDER BY c.created_at ASC",
                                [$comment['comment_id']]
                            );
                            
                            if (!empty($replies)):
                            ?>
                                <div class="comment-replies">
                                    <?php foreach ($replies as $reply): ?>
                                        <div class="comment reply">
                                            <div class="comment-header">
                                                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $reply['profile_pic'] ?: 'default.jpg'; ?>" alt="Profile picture of <?php echo htmlspecialchars($reply['username'] ?: 'Unknown'); ?>">
                                                <div>
                                                    <h4><?php echo htmlspecialchars($reply['username'] ?: 'Unknown'); ?></h4>
                                                    <span class="comment-date"><?php echo date('F j, Y g:i a', strtotime($reply['created_at'])); ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="comment-body">
                                                <p><?php echo htmlspecialchars($reply['content']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/footer.php'; ?>
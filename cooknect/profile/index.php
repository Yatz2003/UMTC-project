<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get recipes (with optional filtering/sorting)
$sort = $_GET['sort'] ?? 'newest';
$category_id = $_GET['category'] ?? null;

$sql = "SELECT r.*, u.username, u.profile_pic, ri.image_path,
        (SELECT COUNT(*) FROM reactions WHERE recipe_id = r.recipe_id) AS reaction_count,
        (SELECT COUNT(*) FROM comments WHERE recipe_id = r.recipe_id) AS comment_count
        FROM recipes r
        JOIN users u ON r.user_id = u.user_id
        LEFT JOIN recipe_images ri ON r.recipe_id = ri.recipe_id AND ri.is_primary = 1";

if ($category_id) {
    $sql .= " JOIN recipe_categories rc ON r.recipe_id = rc.recipe_id
              WHERE rc.category_id = ?";
}

switch ($sort) {
    case 'popular':
        $sql .= " ORDER BY reaction_count DESC";
        break;
    case 'commented':
        $sql .= " ORDER BY comment_count DESC";
        break;
    default:
        $sql .= " ORDER BY r.created_at DESC";
}

$sql .= " LIMIT ? OFFSET ?";

$params = [];
if ($category_id) {
    $params = [$category_id, $limit, $offset];
} else {
    $params = [$limit, $offset];
}

$recipes = $db->fetchAll($sql, $params);

// Get categories for sidebar
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

include 'includes/header.php';
?>

<div class="container">
    <div class="flex-container">
        <div class="main-content">
            <div class="recipe-section">
                <h1>Latest Recipes</h1>
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort" onchange="window.location.href='?sort=' + this.value">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        <option value="commented" <?php echo $sort === 'commented' ? 'selected' : ''; ?>>Most Commented</option>
                    </select>
                </div>

                <?php if (empty($recipes)): ?>
                    <div class="no-recipes">
                        <p>No recipes found. Be the first to <a href="recipes/create.php">post a recipe</a>!</p>
                    </div>
                <?php else: ?>
                    <div class="recipe-list">
                        <?php foreach ($recipes as $recipe): 
                            $hasReacted = isLoggedIn() ? hasUserReacted($recipe['recipe_id'], $_SESSION['user_id']) : false;
                        ?>
                            <div class="recipe-card">
                                <div class="recipe-header">
                                    <div class="user-info">
                                        <img src="<?php echo SITE_URL; ?>/Uploads/profiles/<?php echo $recipe['profile_pic'] ?: 'default.jpg'; ?>" alt="Profile picture of <?php echo htmlspecialchars($recipe['username']); ?>">
                                        <span><?php echo htmlspecialchars($recipe['username']); ?></span>
                                    </div>
                                    <span class="recipe-date"><?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></span>
                                </div>
                                
                                <div class="recipe-body">
                                    <div class="recipe-image">
                                        <a href="<?php echo SITE_URL; ?>/recipes/view.php?id=<?php echo $recipe['recipe_id']; ?>">
                                            <img src="<?php echo SITE_URL; ?>/Uploads/recipes/<?php echo $recipe['image_path'] ?: 'default-recipe.jpg'; ?>" 
                                                 alt="Image of <?php echo htmlspecialchars($recipe['title']); ?>" 
                                                 class="recipe-img">
                                        </a>
                                    </div>
                                    <h2><a href="<?php echo SITE_URL; ?>/recipes/view.php?id=<?php echo $recipe['recipe_id']; ?>">
                                        <?php echo htmlspecialchars($recipe['title']); ?>
                                    </a></h2>
                                    
                                    <?php if (!empty($recipe['description'])): ?>
                                        <p class="recipe-description"><?php echo htmlspecialchars($recipe['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="recipe-meta">
                                        <span><i class="far fa-clock"></i> <?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> mins</span>
                                        <span><i class="fas fa-utensils"></i> <?php echo $recipe['servings']; ?> servings</span>
                                        <span><i class="fas fa-tachometer-alt"></i> <?php echo $recipe['difficulty']; ?></span>
                                    </div>
                                </div>
                                
                                <div class="recipe-footer">
                                    <div class="reactions">
                                        <button class="reaction-btn <?php echo $hasReacted ? 'reacted' : ''; ?>" 
                                                data-recipe-id="<?php echo $recipe['recipe_id']; ?>" 
                                                <?php echo !isLoggedIn() ? 'disabled' : ''; ?>>
                                            <i class="fas fa-heart"></i>
                                            <span class="reaction-count"><?php echo $recipe['reaction_count']; ?></span>
                                        </button>
                                    </div>
                                    
                                    <div class="comments">
                                        <a href="<?php echo SITE_URL; ?>/recipes/view.php?id=<?php echo $recipe['recipe_id']; ?>#comments" class="comment-btn">
                                            <i class="fas fa-comment"></i>
                                            <span><?php echo $recipe['comment_count']; ?></span>
                                        </a>
                                    </div>
                                    
                                    <div class="share">
                                        <button class="share-btn" data-recipe-id="<?php echo $recipe['recipe_id']; ?>">
                                            <i class="fas fa-share"></i>
                                            <span>Share</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $sort ? '&sort=' . $sort : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>" class="prev">Previous</a>
                        <?php endif; ?>
                        
                        <span>Page <?php echo $page; ?></span>
                        
                        <?php if (count($recipes) === $limit): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $sort ? '&sort=' . $sort : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>" class="next">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <aside class="sidebar">
            <?php if (isLoggedIn()): ?>
                <div class="sidebar-section welcome-section">
                    <h3>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                    <a href="recipes/create.php" class="btn btn-primary">Post a Recipe</a>
                    <a href="profile/index.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn btn-secondary">Your Profile</a>
                </div>
            <?php else: ?>
                <div class="sidebar-section">
                    <h3>Join Cooknect</h3>
                    <p>Share your favorite recipes with the community!</p>
                    <a href="auth/register.php" class="btn btn-primary">Sign Up</a>
                    <a href="auth/login.php" class="btn btn-secondary">Login</a>
                </div>
            <?php endif; ?>
            
            <div class="sidebar-section">
                <h3>Categories</h3>
                <ul class="category-list">
                    <li><a href="index.php">All Recipes</a></li>
                    <?php foreach ($categories as $category): ?>
                        <li><a href="index.php?category=<?php echo $category['category_id']; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <h3>Popular Tags</h3>
                <div class="tag-cloud">
                    <a href="#">Vegetarian</a>
                    <a href="#">Quick Meals</a>
                    <a href="#">Desserts</a>
                    <a href="#">Healthy</a>
                    <a href="#">Italian</a>
                    <a href="#">Asian</a>
                    <a href="#">Budget</a>
                    <a href="#">Family</a>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
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

$sql = "SELECT r.*, u.username, u.profile_pic, 
        (SELECT COUNT(*) FROM reactions WHERE recipe_id = r.recipe_id) AS reaction_count,
        (SELECT COUNT(*) FROM comments WHERE recipe_id = r.recipe_id) AS comment_count
        FROM recipes r
        JOIN users u ON r.user_id = u.user_id";

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
    <div class="main-content">
        <div class="content-header">
            <h1>Latest Recipes</h1>
            <div class="sort-options">
                <span>Sort by:</span>
                <a href="?sort=newest" class="<?php echo $sort === 'newest' ? 'active' : ''; ?>">Newest</a>
                <a href="?sort=popular" class="<?php echo $sort === 'popular' ? 'active' : ''; ?>">Most Popular</a>
                <a href="?sort=commented" class="<?php echo $sort === 'commented' ? 'active' : ''; ?>">Most Commented</a>
            </div>
        </div>

        <?php if (empty($recipes)): ?>
            <div class="no-recipes">
                <p>No recipes found. Be the first to <a href="recipes/create.php">post a recipe</a>!</p>
            </div>
        <?php else: ?>
            <div class="recipe-list">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <div class="recipe-header">
                            <div class="user-info">
                                <img src="uploads/profiles/<?php echo $recipe['profile_pic']; ?>" alt="<?php echo $recipe['username']; ?>">
                                <span><?php echo $recipe['username']; ?></span>
                            </div>
                            <span class="recipe-date"><?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></span>
                        </div>
                        
                        <div class="recipe-body">
                            <h2><a href="recipes/view.php?id=<?php echo $recipe['recipe_id']; ?>"><?php echo $recipe['title']; ?></a></h2>
                            
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
                                <a href="recipes/view.php?id=<?php echo $recipe['recipe_id']; ?>#comments">
                                    <span class="icon">💬</span>
                                    <span class="count"><?php echo $recipe['comment_count']; ?></span>
                                </a>
                            </div>
                            
                            <div class="share">
                                <button class="share-btn" data-url="<?php echo SITE_URL . '/recipes/view.php?id=' . $recipe['recipe_id']; ?>">
                                    <span class="icon">↗️</span>
                                    <span>Share</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="prev">Previous</a>
                <?php endif; ?>
                
                <span>Page <?php echo $page; ?></span>
                
                <?php if (count($recipes) === $limit): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="next">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="sidebar">
        <?php if (isLoggedIn()): ?>
            <div class="sidebar-section">
                <h3>Welcome back, <?php echo $_SESSION['username']; ?>!</h3>
                <a href="recipes/create.php" class="btn btn-primary">Post a Recipe</a>
                <a href="profile.php" class="btn btn-secondary">Your Profile</a>
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
                    <li><a href="index.php?category=<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></a></li>
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
    </div>
</div>

<?php include 'includes/footer.php'; ?>
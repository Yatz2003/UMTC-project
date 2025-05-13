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

// Fallback categories if database query returns empty
if (empty($categories)) {
    $categories = [
        ['category_id' => 1, 'name' => 'Breakfast'],
        ['category_id' => 2, 'name' => 'Lunch'],
        ['category_id' => 3, 'name' => 'Dinner'],
        ['category_id' => 4, 'name' => 'Dessert'],
        ['category_id' => 5, 'name' => 'Snacks']
    ];
}

// Build base URL for sorting links to preserve category and page
$base_sort_url = "?";
if ($category_id) {
    $base_sort_url .= "category=$category_id&";
}
if ($page > 1) {
    $base_sort_url .= "page=$page&";
}

include 'includes/header.php';
?>

<div class="container">
    <div class="main-content">
        <div class="content-header">
            <h1>Latest Recipes</h1>
            <div class="sort-options-box">
                <span class="sort-label">Sort by:</span>
                <div class="sort-buttons">
                    <a href="<?php echo $base_sort_url; ?>sort=newest" class="sort-btn <?php echo $sort === 'newest' ? 'active' : ''; ?>">Newest</a>
                    <a href="<?php echo $base_sort_url; ?>sort=popular" class="sort-btn <?php echo $sort === 'popular' ? 'active' : ''; ?>">Most Popular</a>
                    <a href="<?php echo $base_sort_url; ?>sort=commented" class="sort-btn <?php echo $sort === 'commented' ? 'active' : ''; ?>">Most Commented</a>
                </div>
            </div>
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
                                <img src="<?php echo SITE_URL; ?>/Uploads/profiles/<?php echo $recipe['profile_pic'] ?: 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($recipe['username']); ?>">
                                <span><?php echo htmlspecialchars($recipe['username']); ?></span>
                            </div>
                            <span class="recipe-date"><?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></span>
                        </div>
                        
                        <div class="recipe-body">
                            <div class="recipe-image">
                                <a href="<?php echo SITE_URL; ?>/recipes/view.php?id=<?php echo $recipe['recipe_id']; ?>">
                                    <img src="<?php echo SITE_URL; ?>/Uploads/recipes/<?php echo $recipe['image_path'] ?: 'default-recipe.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($recipe['title']); ?>" 
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
                                <span><?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> mins</span>
                                <span><?php echo $recipe['servings']; ?> servings</span>
                                <span><?php echo $recipe['difficulty']; ?></span>
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
                                    <span class="comment-count"><?php echo $recipe['comment_count']; ?></span>
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
                    <a href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>" class="prev">Previous</a>
                <?php endif; ?>
                
                <span>Page <?php echo $page; ?></span>
                
                <?php if (count($recipes) === $limit): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>" class="next">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="sidebar">
        <?php if (isLoggedIn()): ?>
            <div class="sidebar-section">
                <h3>Welcome back, <?php echo $_SESSION['username']; ?>!</h3>
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
            <div class="category-tabs">
                <a href="index.php?<?php echo $sort ? 'sort=' . $sort : ''; ?>" class="category-tab <?php echo !$category_id ? 'active' : ''; ?>">All Recipes</a>
                <?php foreach ($categories as $category): ?>
                    <a href="index.php?category=<?php echo $category['category_id']; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>" 
                       class="category-tab <?php echo $category_id == $category['category_id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
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

<style>
.recipe-card {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
}
.recipe-image {
    margin-bottom: 15px;
}
.recipe-img {
    max-width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
}
.recipe-description {
    margin-bottom: 15px;
}
.recipe-meta {
    margin-top: 15px;
}

/* Sort Options Styling */
.sort-options-box {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px 15px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
}

.sort-label {
    font-weight: 500;
    color: #333;
}

.sort-buttons {
    display: flex;
    gap: 8px;
}

.sort-btn {
    padding: 6px 12px;
    border-radius: 4px;
    background-color: #f8f9fa;
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    display: inline-block;
}

.sort-btn:hover {
    background-color: #e9ecef;
    color: #495057;
}

.sort-btn.active {
    background-color: #ff6f61;
    color: white;
}

/* Category Tabs Styling */
.category-tabs {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 8px;
    padding-bottom: 10px;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f1f1f1;
}

.category-tabs::-webkit-scrollbar {
    height: 8px;
}

.category-tabs::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.category-tabs::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 4px;
}

.category-tab {
    display: inline-block;
    padding: 6px 12px;
    background-color: #f8f9fa;
    color: #6c757d;
    text-decoration: none;
    border-radius: 4px;
    white-space: nowrap;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.category-tab:hover {
    background-color: #e9ecef;
    color: #495057;
}

.category-tab.active {
    background-color: #ff6f61;
    color: white;
}

@media (max-width: 768px) {
    .sort-options-box {
        flex-wrap: wrap;
        justify-content: center;
    }

    .sort-buttons {
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
    }

    .sort-btn {
        padding: 5px 10px;
        font-size: 13px;
    }

    .category-tabs {
        gap: 6px;
    }

    .category-tab {
        padding: 5px 10px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .sort-options-box {
        padding: 8px;
    }

    .sort-btn {
        padding: 4px 8px;
        font-size: 12px;
    }

    .category-tab {
        padding: 4px 8px;
        font-size: 12px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$query = $_GET['q'] ?? '';
$category_id = $_GET['category'] ?? null;
$user_id = $_GET['user'] ?? null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build search query
$sql = "SELECT r.*, u.username, u.profile_pic, 
        (SELECT COUNT(*) FROM reactions WHERE recipe_id = r.recipe_id) AS reaction_count,
        (SELECT COUNT(*) FROM comments WHERE recipe_id = r.recipe_id) AS comment_count
        FROM recipes r
        JOIN users u ON r.user_id = u.user_id";
        
$where = [];
$params = [];

if (!empty($query)) {
    $where[] = "(r.title LIKE ? OR r.description LIKE ? OR r.ingredients LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
    $params[] = "%$query%";
}

if ($category_id) {
    $where[] = "r.recipe_id IN (SELECT recipe_id FROM recipe_categories WHERE category_id = ?)";
    $params[] = $category_id;
}

if ($user_id) {
    $where[] = "r.user_id = ?";
    $params[] = $user_id;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$recipes = $db->fetchAll($sql, $params);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) AS total FROM recipes r";
if (!empty($where)) {
    $count_sql .= " WHERE " . implode(" AND ", $where);
}
$total_recipes = $db->fetch($count_sql, array_slice($params, 0, -2))['total'];
$total_pages = ceil($total_recipes / $limit);

// Get categories for sidebar
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

include 'includes/header.php';
?>

<div class="container">
    <div class="main-content">
        <div class="search-header">
            <h1>Search Recipes</h1>
            
            <form method="GET" action="search.php" class="search-form">
                <div class="search-input">
                    <input type="text" name="q" placeholder="Search recipes..." value="<?php echo htmlspecialchars($query); ?>">
                    <button type="submit">🔍</button>
                </div>
                
                <?php if ($category_id): ?>
                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                <?php endif; ?>
                
                <?php if ($user_id): ?>
                    <input type="hidden" name="user" value="<?php echo $user_id; ?>">
                <?php endif; ?>
            </form>
            
            <?php if (!empty($query) || $category_id || $user_id): ?>
                <p class="search-results-count">
                    <?php echo $total_recipes; ?> result<?php echo $total_recipes != 1 ? 's' : ''; ?> found
                    <?php if (!empty($query)): ?>
                        for "<?php echo htmlspecialchars($query); ?>"
                    <?php endif; ?>
                    <?php if ($category_id): ?>
                        in category "<?php echo $db->fetch("SELECT name FROM categories WHERE category_id = ?", [$category_id])['name']; ?>"
                    <?php endif; ?>
                    <?php if ($user_id): ?>
                        by user "<?php echo $db->fetch("SELECT username FROM users WHERE user_id = ?", [$user_id])['username']; ?>"
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        
        <?php if (empty($recipes)): ?>
            <div class="no-recipes">
                <p>No recipes found matching your search.</p>
                <a href="<?php echo SITE_URL; ?>/recipes/create.php" class="btn btn-primary">Post a Recipe</a>
            </div>
        <?php else: ?>
            <div class="recipe-list">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <div class="recipe-header">
                            <div class="user-info">
                                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $recipe['profile_pic']; ?>" alt="<?php echo $recipe['username']; ?>">
                                <span><?php echo $recipe['username']; ?></span>
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
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="prev">Previous</a>
                    <?php endif; ?>
                    
                    <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="next">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div class="sidebar">
        <div class="sidebar-section">
            <h3>Search Filters</h3>
            <form method="GET" action="search.php">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($query); ?>">
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="search.php">
                <a href="search.php" class="btn btn-secondary">Reset Filters</a>
            </form>
        </div>
        
        <div class="sidebar-section">
            <h3>Popular Tags</h3>
            <div class="tag-cloud">
                <a href="search.php?q=vegetarian">Vegetarian</a>
                <a href="search.php?q=quick">Quick Meals</a>
                <a href="search.php?q=dessert">Desserts</a>
                <a href="search.php?q=healthy">Healthy</a>
                <a href="search.php?q=italian">Italian</a>
                <a href="search.php?q=asian">Asian</a>
                <a href="search.php?q=budget">Budget</a>
                <a href="search.php?q=family">Family</a>
            </div>
        </div>
        
        <div class="sidebar-section">
            <h3>Search Tips</h3>
            <ul class="search-tips">
                <li>Use quotes for exact matches: "chocolate cake"</li>
                <li>Search by ingredient: ingredient:chicken</li>
                <li>Filter by time: time:&lt;30</li>
                <li>Combine terms: vegetarian AND italian</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$recipe_id = $input['recipe_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save recipe
    $db->query(
        "INSERT INTO saved_recipes (user_id, recipe_id) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE saved_at = NOW()",
        [$_SESSION['user_id'], $recipe_id]
    );
    
    echo json_encode([
        'success' => true,
        'action' => 'saved'
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Unsave recipe
    $db->query(
        "DELETE FROM saved_recipes WHERE user_id = ? AND recipe_id = ?",
        [$_SESSION['user_id'], $recipe_id]
    );
    
    echo json_encode([
        'success' => true,
        'action' => 'unsaved'
    ]);
}
?>
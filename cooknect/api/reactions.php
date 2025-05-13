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
$type = $input['type'] ?? 'like';

// Check if user already reacted
$existing = $db->fetch(
    "SELECT reaction_id FROM reactions WHERE user_id = ? AND recipe_id = ?",
    [$_SESSION['user_id'], $recipe_id]
);

if ($existing) {
    // Remove reaction
    $db->query(
        "DELETE FROM reactions WHERE reaction_id = ?",
        [$existing['reaction_id']]
    );
} else {
    // Add reaction
    $db->query(
        "INSERT INTO reactions (user_id, recipe_id, type) VALUES (?, ?, ?)",
        [$_SESSION['user_id'], $recipe_id, $type]
    );
}

// Get updated count
$count = $db->fetch(
    "SELECT COUNT(*) AS count FROM reactions WHERE recipe_id = ?",
    [$recipe_id]
)['count'];

echo json_encode([
    'success' => true,
    'count' => $count
]);
?>
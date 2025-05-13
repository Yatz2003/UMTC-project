<?php
session_start();
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$recipe_id = $_POST['recipe_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$recipe_id) {
    echo json_encode(['success' => false, 'message' => 'No recipe ID']);
    exit;
}

$db = new Database();

// Check if the user already reacted
$existing = $db->fetch(
    "SELECT * FROM reactions WHERE recipe_id = ? AND user_id = ?",
    [$recipe_id, $user_id]
);

if ($existing) {
    // Remove like
    $db->execute(
        "DELETE FROM reactions WHERE recipe_id = ? AND user_id = ?",
        [$recipe_id, $user_id]
    );
} else {
    // Add like
    $db->execute(
        "INSERT INTO reactions (recipe_id, user_id) VALUES (?, ?)",
        [$recipe_id, $user_id]
    );
}

// Get updated count
$count = $db->fetchColumn(
    "SELECT COUNT(*) FROM reactions WHERE recipe_id = ?",
    [$recipe_id]
);

echo json_encode([
    'success' => true,
    'reaction_count' => $count
]);

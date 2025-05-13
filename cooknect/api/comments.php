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
$content = sanitize($input['content'] ?? '');
$parent_id = $input['parent_id'] ?? null;

if (empty($content)) {
    echo json_encode(['error' => 'Comment cannot be empty']);
    exit;
}

$db->query(
    "INSERT INTO comments (user_id, recipe_id, parent_comment_id, content)
     VALUES (?, ?, ?, ?)",
    [$_SESSION['user_id'], $recipe_id, $parent_id, $content]
);

$comment_id = $db->lastInsertId();

$comment = $db->fetch(
    "SELECT c.*, u.username, u.profile_pic
     FROM comments c
     JOIN users u ON c.user_id = u.user_id
     WHERE c.comment_id = ?",
    [$comment_id]
);

echo json_encode([
    'success' => true,
    'comment' => $comment
]);
?>
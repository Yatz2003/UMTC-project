<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$comment_id = $_GET['id'] ?? 0;
$comment = $db->fetch(
    "SELECT c.* FROM comments c 
     WHERE c.comment_id = ? AND c.user_id = ?",
    [$comment_id, $_SESSION['user_id']]
);

if (!$comment) {
    $_SESSION['error'] = "Comment not found or you don't have permission";
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? SITE_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = sanitize($_POST['content']);
    
    $db->query(
        "UPDATE comments SET content = ? WHERE comment_id = ?",
        [$content, $comment_id]
    );
    
    $_SESSION['success'] = "Comment updated successfully";
    header('Location: ' . SITE_URL . '/recipes/view.php?id=' . $comment['recipe_id']);
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/header.php';
?>

<div class="comment-edit-container">
    <h1>Edit Comment</h1>
    
    <form method="POST">
        <div class="form-group">
            <textarea name="content" required><?= htmlspecialchars($comment['content']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Comment</button>
        <a href="<?= SITE_URL ?>/recipes/view.php?id=<?= $comment['recipe_id'] ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/cooknect/includes/footer.php'; ?>
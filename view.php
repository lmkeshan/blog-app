<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT blog_posts.*, users.username 
                        FROM blog_posts 
                        JOIN users ON blog_posts.user_id = users.id 
                        WHERE blog_posts.id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: index.php");
    exit();
}

$post = $result->fetch_assoc();
$stmt->close();

$in_library = false;
$lib_stmt = $conn->prepare("SELECT id FROM libraries WHERE user_id = ? AND post_id = ?");
$lib_stmt->bind_param("ii", $_SESSION['user_id'], $post_id);
$lib_stmt->execute();
if ($lib_stmt->get_result()->num_rows > 0) {
    $in_library = true;
}
$lib_stmt->close();

$page_title = htmlspecialchars($post['title']) . " - Blog App";
$css_file   = "view.css";
require_once 'includes/header.php';
?>

<div class="single-post-container">
    <a href="index.php" class="back-link">&larr; Back to All Posts</a>

    <article class="post-article">
        <div style="margin-bottom: 10px;">
            <span style="background-color: var(--border-color); color: var(--accent-yellow); font-size: 0.8rem; padding: 3px 10px; border-radius: 4px; font-weight: 600;">
                <?php echo htmlspecialchars($post['category'] ?? 'General'); ?>
            </span>
        </div>

        <div class="post-header-wrap">
            <h1 class="post-main-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            
            <a href="library_action.php?post_id=<?php echo $post['id']; ?>" class="btn btn-secondary">
                <?php echo $in_library ? '🔖 Saved in Library' : '🔖 Add to Library'; ?>
            </a>
        </div>
        
        <div class="article-meta">
            Published by <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
            on <?php echo date('F j, Y \a\t g:i a', strtotime($post['created_at'])); ?>
        </div>

        <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" 
                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                 class="article-hero-img">
        <?php endif; ?>

        <div class="article-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>

        <?php if ($_SESSION['user_id'] === $post['user_id']): ?>
            <div class="post-actions">
                <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">Edit Post</a>
                <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php require_once 'includes/footer.php'; ?>
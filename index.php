<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT blog_posts.*, users.username 
        FROM blog_posts 
        JOIN users ON blog_posts.user_id = users.id 
        ORDER BY blog_posts.created_at DESC";

$result = $conn->query($sql);

$page_title = "Home - Blog App";
$css_file   = "index.css";
require_once 'includes/header.php';
?>

<div class="page-header">
    <h2 class="page-title">Recent Blog Posts</h2>
</div>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="posts-grid">
        <?php while ($post = $result->fetch_assoc()): ?>
            <article class="post-card">
                <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
                    <a href="view.php?id=<?php echo $post['id']; ?>">
                        <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             class="post-card-img">
                    </a>
                <?php endif; ?>

                <div class="post-card-body">
                    <h3 class="post-title">
                        <a href="view.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                    </h3>
                    <div class="post-meta">
                        By <strong><?php echo htmlspecialchars($post['username']); ?></strong> • <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                    </div>
                    <p class="post-excerpt"><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 120))); ?>...</p>
                    <a href="view.php?id=<?php echo $post['id']; ?>" class="post-read-more">Read Article &rarr;</a>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>No blog posts found. <a href="create.php" style="color: var(--accent-yellow);">Create your first post</a>!</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
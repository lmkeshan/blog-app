<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT blog_posts.*, users.username, libraries.created_at AS saved_at 
        FROM libraries 
        JOIN blog_posts ON libraries.post_id = blog_posts.id 
        JOIN users ON blog_posts.user_id = users.id 
        WHERE libraries.user_id = ? 
        ORDER BY libraries.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$page_title = "My Library - Blog App";
$css_file   = "library.css";
require_once 'includes/header.php';
?>

<div class="library-header">
    <h2 class="page-title">📚 My Reading Library</h2>
</div>

<?php if ($result->num_rows > 0): ?>
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
                        By <strong><?php echo htmlspecialchars($post['username']); ?></strong> • Saved on <?php echo date('M j, Y', strtotime($post['saved_at'])); ?>
                    </div>
                    <p class="post-excerpt"><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 120))); ?>...</p>
                    
                    <div>
                        <a href="library_action.php?post_id=<?php echo $post['id']; ?>" class="remove-link">Remove from Library</a>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>Your library is empty. Explore posts on the <a href="index.php" style="color: var(--accent-yellow);">home page</a> and save them for later!</p>
<?php endif; ?>

<?php $stmt->close(); ?>

<?php require_once 'includes/footer.php'; ?>
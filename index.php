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
require_once 'includes/header.php';
?>

<main>
    <h2>Recent Blog Posts</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($post = $result->fetch_assoc()): ?>
            <article style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                <!-- Thumbnail Display -->
                <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
                    <a href="view.php?id=<?php echo $post['id']; ?>">
                        <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             style="width: 100%; max-height: 250px; object-fit: cover; border-radius: 4px; margin-bottom: 15px;">
                    </a>
                <?php endif; ?>

                <h3><a href="view.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: #1a0dab;"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                <p style="color: #666; font-size: 0.9em;">
                    By <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
                    on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                </p>
                <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...</p>
                <a href="view.php?id=<?php echo $post['id']; ?>">Read More &rarr;</a>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No blog posts found. Be the first to <a href="create.php">create one</a>!</p>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
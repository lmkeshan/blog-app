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
require_once 'includes/header.php';
?>

<main>
    <h2> My Reading Library</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($post = $result->fetch_assoc()): ?>
            <article style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
                    <a href="view.php?id=<?php echo $post['id']; ?>">
                        <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 4px; margin-bottom: 15px;">
                    </a>
                <?php endif; ?>

                <h3><a href="view.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: #1a0dab;"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                <p style="color: #666; font-size: 0.9em;">
                    By <strong><?php echo htmlspecialchars($post['username']); ?></strong> | 
                    Saved on <?php echo date('F j, Y', strtotime($post['saved_at'])); ?>
                </p>
                <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 150))); ?>...</p>
                <a href="library_action.php?post_id=<?php echo $post['id']; ?>" style="color: red;">Remove from Library</a>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Your library is empty. Explore posts on the <a href="index.php">home page</a> and save them for later!</p>
    <?php endif; ?>

    <?php $stmt->close(); ?>
</main>

<?php require_once 'includes/footer.php'; ?>
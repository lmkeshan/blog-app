<?php
session_start();
require_once 'config/db.php';

// Protect page: Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch blog posts from database along with author usernames
$sql = "SELECT blog_posts.*, users.username 
        FROM blog_posts 
        JOIN users ON blog_posts.user_id = users.id 
        ORDER BY blog_posts.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Blog App</title>
</head>
<body style="font-family: sans-serif; max-width: 800px; margin: 30px auto; padding: 0 20px;">

    <!-- Navigation Header -->
    <header style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 10px;">
        <h1>My Blog</h1>
        <div>
            <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</span> |
            <a href="create.php">Create Post</a> |
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <!-- Main Content Feed -->
    <main style="margin-top: 30px;">
        <h2>Recent Blog Posts</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($post = $result->fetch_assoc()): ?>
                <article style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p style="color: #666; font-size: 0.9em;">
                        By <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
                        on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                    </p>
                    <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...</p>
                    <a href="view.php?id=<?php echo $post['id']; ?>">Read More</a>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No blog posts found. Be the first to <a href="create.php">create one</a>!</p>
        <?php endif; ?>
    </main>

</body>
</html>
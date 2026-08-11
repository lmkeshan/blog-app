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

// Fetch post details along with author's username
$stmt = $conn->prepare("SELECT blog_posts.*, users.username 
                        FROM blog_posts 
                        JOIN users ON blog_posts.user_id = users.id 
                        WHERE blog_posts.id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

// If no post exists with that ID, redirect back to index.php
if ($result->num_rows !== 1) {
    header("Location: index.php");
    exit();
}

$post = $result->fetch_assoc();
$stmt->close();

// Check if current post is saved in user's library
$in_library = false;
$lib_stmt = $conn->prepare("SELECT id FROM libraries WHERE user_id = ? AND post_id = ?");
$lib_stmt->bind_param("ii", $_SESSION['user_id'], $post_id);
$lib_stmt->execute();
if ($lib_stmt->get_result()->num_rows > 0) {
    $in_library = true;
}
$lib_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Blog App</title>
</head>
<body style="font-family: sans-serif; max-width: 800px; margin: 30px auto; padding: 0 20px;">

    <!-- Navigation Header -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <p><a href="index.php">&larr; Back to All Posts</a></p>
        <p><a href="library.php">📚 View My Library</a></p>
    </div>

    <!-- Single Post Article -->
    <article style="border: 1px solid #ddd; padding: 25px; border-radius: 5px; margin-top: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <h2 style="margin-top: 0;"><?php echo htmlspecialchars($post['title']); ?></h2>
            
            <!-- Bookmark / Library Action Button -->
            <a href="library_action.php?post_id=<?php echo $post['id']; ?>" 
               style="padding: 8px 14px; background-color: #f0f0f0; border: 1px solid #ccc; text-decoration: none; border-radius: 4px; color: #333; font-size: 0.9em; white-space: nowrap;">
                <?php echo $in_library ? '🔖 Saved in Library' : '🔖 Add to Library'; ?>
            </a>
        </div>
        
        <p style="color: #666; font-size: 0.9em; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: -10px;">
            Published by <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
            on <?php echo date('F j, Y \a\t g:i a', strtotime($post['created_at'])); ?>
        </p>

        <!-- Use nl2br() to preserve line breaks typed in textareas -->
        <div style="line-height: 1.6; font-size: 1.05em; margin-top: 20px;">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>

        <!-- Post Action Controls (Only show Edit/Delete if logged-in user is the author) -->
        <?php if ($_SESSION['user_id'] === $post['user_id']): ?>
            <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee;">
                <a href="edit.php?id=<?php echo $post['id']; ?>" style="margin-right: 15px;">Edit Post</a>
                <a href="delete.php?id=<?php echo $post['id']; ?>" style="color: red;" onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
            </div>
        <?php endif; ?>
    </article>

</body>
</html>
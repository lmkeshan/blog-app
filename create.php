<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($content)) {
        $error = "Please fill in both the title and content.";
    } else {
        // Insert new blog post using Prepared Statement
        $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $content);

        if ($stmt->execute()) {
            // Redirect to homepage after successful post creation
            header("Location: index.php");
            exit();
        } else {
            $error = "Failed to create post. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Blog App</title>
</head>
<body style="font-family: sans-serif; max-width: 600px; margin: 30px auto; padding: 0 20px;">

    <h2>Create a New Blog Post</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- Post Form -->
    <form action="create.php" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="title">Title</label><br>
            <input type="text" id="title" name="title" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="content">Content</label><br>
            <textarea id="content" name="content" rows="8" required style="width: 100%; padding: 8px;"></textarea>
        </div>

        <button type="submit" style="padding: 10px 15px; cursor: pointer;">Publish Post</button>
    </form>

</body>
</html>
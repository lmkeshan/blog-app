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
$user_id = $_SESSION['user_id'];
$error   = "";

$stmt = $conn->prepare("SELECT id, title, content, user_id FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: index.php");
    exit();
}

$post = $result->fetch_assoc();
$stmt->close();

// Authorization Check: Ensure logged-in user owns this post
if ($post['user_id'] !== $user_id) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $error = "Please fill in both the title and content.";
    } else {
        $update_stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $update_stmt->bind_param("ssii", $title, $content, $post_id, $user_id);

        if ($update_stmt->execute()) {
            header("Location: view.php?id=" . $post_id);
            exit();
        } else {
            $error = "Failed to update post. Please try again.";
        }

        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Blog App</title>
</head>
<body style="font-family: sans-serif; max-width: 600px; margin: 30px auto; padding: 0 20px;">

    <h2>Edit Blog Post</h2>
    <p><a href="view.php?id=<?php echo $post['id']; ?>">&larr; Cancel and Back to Post</a></p>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- Edit Form -->
    <form action="edit.php?id=<?php echo $post['id']; ?>" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="title">Title</label><br>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="content">Content</label><br>
            <textarea id="content" name="content" rows="8" required style="width: 100%; padding: 8px;"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>

        <button type="submit" style="padding: 10px 15px; cursor: pointer;">Update Post</button>
    </form>

</body>
</html>
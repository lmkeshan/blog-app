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

$stmt = $conn->prepare("SELECT id, title, content, thumbnail, user_id FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: index.php");
    exit();
}

$post = $result->fetch_assoc();
$stmt->close();

if ($post['user_id'] !== $user_id) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $thumbnail_filename = $post['thumbnail'];

    if (empty($title) || empty($content)) {
        $error = "Please fill in both the title and content.";
    } else {
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['thumbnail']['tmp_name'];
            $file_name     = $_FILES['thumbnail']['name'];
            $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($file_ext, $allowed_extensions)) {
                $new_filename = uniqid('thumb_', true) . '.' . $file_ext;
                $upload_file_path = 'uploads/' . $new_filename;

                if (move_uploaded_file($file_tmp_path, $upload_file_path)) {
                    if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])) {
                        unlink('uploads/' . $post['thumbnail']);
                    }
                    $thumbnail_filename = $new_filename;
                } else {
                    $error = "Failed to upload new thumbnail.";
                }
            } else {
                $error = "Invalid file format. Allowed: JPG, PNG, WEBP, GIF.";
            }
        }

        if (empty($error)) {
            $update_stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ?, thumbnail = ? WHERE id = ? AND user_id = ?");
            $update_stmt->bind_param("sssii", $title, $content, $thumbnail_filename, $post_id, $user_id);

            if ($update_stmt->execute()) {
                header("Location: view.php?id=" . $post_id);
                exit();
            } else {
                $error = "Failed to update post. Please try again.";
            }

            $update_stmt->close();
        }
    }
}

$page_title = "Edit Post - Blog App";
$css_file   = "edit.css"; // Dynamically loads assets/css/edit.css
require_once 'includes/header.php';
?>

<div class="form-container">
    <h2 style="color: var(--text-main); margin-bottom: 10px;">Edit Blog Post</h2>
    <p style="margin-bottom: 25px;"><a href="view.php?id=<?php echo $post['id']; ?>" style="color: var(--text-muted);">&larr; Cancel and Back to Post</a></p>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $post['id']; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>

        <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
            <div class="form-group">
                <label>Current Thumbnail:</label><br>
                <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" alt="Current thumbnail" class="current-thumb-preview">
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="thumbnail">Change Thumbnail (Optional)</label>
            <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Update Post</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
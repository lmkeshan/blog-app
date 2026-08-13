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
        // Check if user uploaded a new thumbnail
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['thumbnail']['tmp_name'];
            $file_name     = $_FILES['thumbnail']['name'];
            $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($file_ext, $allowed_extensions)) {
                $new_filename = uniqid('thumb_', true) . '.' . $file_ext;
                $upload_file_path = 'uploads/' . $new_filename;

                if (move_uploaded_file($file_tmp_path, $upload_file_path)) {
                    // Delete old thumbnail file if exists
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
require_once 'includes/header.php';
?>

<main style="max-width: 600px; margin: 0 auto;">
    <h2>Edit Blog Post</h2>
    <p><a href="view.php?id=<?php echo $post['id']; ?>">&larr; Cancel and Back to Post</a></p>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $post['id']; ?>" method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 15px;">
            <label for="title">Title</label><br>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required style="width: 100%; padding: 8px;">
        </div>

        <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
            <div style="margin-bottom: 15px;">
                <label>Current Thumbnail:</label><br>
                <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" alt="Current thumbnail" style="max-width: 150px; border-radius: 4px; margin-top: 5px;">
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label for="thumbnail">Change Thumbnail (Optional)</label><br>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="content">Content</label><br>
            <textarea id="content" name="content" rows="8" required style="width: 100%; padding: 8px;"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>

        <button type="submit" style="padding: 10px 15px; cursor: pointer;">Update Post</button>
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>
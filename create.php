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
    $thumbnail_filename = null;

    if (empty($title) || empty($content)) {
        $error = "Please fill in both the title and content.";
    } else {
        // Handle User Thumbnail Upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['thumbnail']['tmp_name'];
            $file_name     = $_FILES['thumbnail']['name'];
            $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($file_ext, $allowed_extensions)) {
                // Generate a unique filename to prevent user uploads from overwriting each other
                $thumbnail_filename = uniqid('thumb_', true) . '.' . $file_ext;
                $upload_file_path   = 'uploads/' . $thumbnail_filename;

                if (!move_uploaded_file($file_tmp_path, $upload_file_path)) {
                    $error = "Error saving uploaded image.";
                }
            } else {
                $error = "Invalid file type. Allowed formats: JPG, JPEG, PNG, WEBP, GIF.";
            }
        }

        // Insert into database if no errors
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, title, content, thumbnail) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $title, $content, $thumbnail_filename);

            if ($stmt->execute()) {
                header("Location: index.php");
                exit();
            } else {
                $error = "Failed to create post. Please try again.";
            }

            $stmt->close();
        }
    }
}

$page_title = "Create Post - Blog App";
require_once 'includes/header.php';
?>

<main style="max-width: 600px; margin: 0 auto;">
    <h2>Create a New Blog Post</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- Note: enctype="multipart/form-data" is REQUIRED for file uploads -->
    <form action="create.php" method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 15px;">
            <label for="title">Title</label><br>
            <input type="text" id="title" name="title" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="thumbnail">Thumbnail Image (Optional)</label><br>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="content">Content</label><br>
            <textarea id="content" name="content" rows="8" required style="width: 100%; padding: 8px;"></textarea>
        </div>

        <button type="submit" style="padding: 10px 15px; cursor: pointer;">Publish Post</button>
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>
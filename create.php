<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$categories = [
    'Science', 'Technology', 'Business', 'Finance', 'Education',
    'Health & Wellness', 'Travel', 'Lifestyle', 'Food', 'Entertainment',
    'Sports', 'News', 'Arts & Culture', 'Fashion', 'Automotive',
    'Gaming', 'Career', 'Environment', 'Astrology'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title']);
    $category  = trim($_POST['category'] ?? 'Technology');
    $content   = trim($_POST['content']);
    $user_id   = $_SESSION['user_id'];
    $thumbnail_filename = null;

    if (empty($title) || empty($content)) {
        $error = "Please fill in both the title and content.";
    } elseif (!in_array($category, $categories)) {
        $error = "Please select a valid category.";
    } else {
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['thumbnail']['tmp_name'];
            $file_name     = $_FILES['thumbnail']['name'];
            $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($file_ext, $allowed_extensions)) {
                $thumbnail_filename = uniqid('thumb_', true) . '.' . $file_ext;
                $upload_file_path   = 'uploads/' . $thumbnail_filename;

                if (!move_uploaded_file($file_tmp_path, $upload_file_path)) {
                    $error = "Error saving uploaded image.";
                }
            } else {
                $error = "Invalid file type. Allowed formats: JPG, JPEG, PNG, WEBP, GIF.";
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, title, category, content, thumbnail) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $title, $category, $content, $thumbnail_filename);

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
$css_file   = "create.css"; 
require_once 'includes/header.php';
?>

<div class="form-container">
    <h2 style="color: var(--text-main); margin-bottom: 10px;">Create a New Blog Post</h2>
    <p style="margin-bottom: 25px;"><a href="index.php" style="color: var(--text-muted);">&larr; Back to Home</a></p>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="create.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="form-control" placeholder="Enter post title" required>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category" class="form-control" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="thumbnail">Thumbnail Image (Optional)</label>
            <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" class="form-control" rows="10" placeholder="Write your post content here..." required></textarea>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Publish Post</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
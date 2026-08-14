<?php
session_start();
require_once 'config/db.php';

// Check login status
$is_logged_in = isset($_SESSION['user_id']);

// Fetch all posts for both visitors and logged-in users
$sql = "SELECT blog_posts.*, users.username 
        FROM blog_posts 
        JOIN users ON blog_posts.user_id = users.id 
        ORDER BY blog_posts.created_at DESC";

$result = $conn->query($sql);

$page_title = "Home - Blog App";
$css_file   = "index.css";
require_once 'includes/header.php';
?>

<!-- Optional welcome banner for visitors -->
<?php if (!$is_logged_in): ?>
    <div class="welcome-banner" style="background-color: var(--bg-card); border: 1px solid var(--border-color); padding: 25px; border-radius: var(--border-radius); text-align: center; margin-bottom: 30px;">
        <h1 style="color: var(--text-main); font-size: 2rem; margin-bottom: 10px;">Welcome to <span style="color: var(--accent-yellow);">DevBlog</span></h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 15px;">Explore our latest articles below. Sign in or register to read full posts and publish your own!</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="login.php" class="btn">Sign In</a>
            <a href="register.php" class="btn btn-secondary" style="background-color: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 8px 16px; border-radius: 6px; text-decoration: none;">Create Account</a>
        </div>
    </div>
<?php endif; ?>

<div class="page-header">
    <h2 class="page-title">Recent Blog Posts</h2>
</div>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="posts-grid">
        <?php while ($post = $result->fetch_assoc()): ?>
            <?php 
                // Determine target link: view.php if logged in, login.php if visitor
                $post_url = $is_logged_in ? "view.php?id=" . $post['id'] : "login.php"; 
            ?>
            <article class="post-card">
                <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
                    <a href="<?php echo $post_url; ?>">
                        <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             class="post-card-img">
                    </a>
                <?php endif; ?>

                <div class="post-card-body">
                    <h3 class="post-title">
                        <a href="<?php echo $post_url; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                    </h3>
                    <div class="post-meta">
                        By <strong><?php echo htmlspecialchars($post['username']); ?></strong> • <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                    </div>
                    <p class="post-excerpt"><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 120))); ?>...</p>
                    
                    <a href="<?php echo $post_url; ?>" class="post-read-more">
                        <?php echo $is_logged_in ? "Read Article &rarr;" : "Sign in to Read &rarr;"; ?>
                    </a>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>No blog posts found. <a href="create.php" style="color: var(--accent-yellow);">Create your first post</a>!</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
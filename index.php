<?php
session_start();
require_once 'config/db.php';

$is_logged_in = isset($_SESSION['user_id']);

$categories = [
    'Science', 'Technology', 'Business', 'Finance', 'Education',
    'Health & Wellness', 'Travel', 'Lifestyle', 'Food', 'Entertainment',
    'Sports', 'News', 'Arts & Culture', 'Fashion', 'Automotive',
    'Gaming', 'Career', 'Environment', 'Astrology'
];

$selected_category = isset($_GET['cat']) ? trim($_GET['cat']) : 'All';

// Fetch filtered posts
if ($selected_category !== 'All' && in_array($selected_category, $categories)) {
    $stmt = $conn->prepare("SELECT blog_posts.*, users.username 
                            FROM blog_posts 
                            JOIN users ON blog_posts.user_id = users.id 
                            WHERE blog_posts.category = ?
                            ORDER BY blog_posts.created_at DESC");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT blog_posts.*, users.username 
            FROM blog_posts 
            JOIN users ON blog_posts.user_id = users.id 
            ORDER BY blog_posts.created_at DESC";
    $result = $conn->query($sql);
}

$page_title = "Home - Blog App";
$css_file   = "index.css";
require_once 'includes/header.php';
?>

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

<div class="category-filter-bar" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 25px;">
    <?php 
        $all_active_style = ($selected_category === 'All') 
            ? "background-color: var(--accent-yellow); color: #000; font-weight: bold;" 
            : "background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);";
    ?>
    <a href="index.php" style="padding: 6px 14px; border-radius: 20px; text-decoration: none; font-size: 0.85rem; <?php echo $all_active_style; ?>">
        All
    </a>
    
    <?php foreach ($categories as $cat): ?>
        <?php 
            $active_style = ($selected_category === $cat) 
                ? "background-color: var(--accent-yellow); color: #000; font-weight: bold;" 
                : "background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);";
        ?>
        <a href="index.php?cat=<?php echo urlencode($cat); ?>" 
           style="padding: 6px 14px; border-radius: 20px; text-decoration: none; font-size: 0.85rem; <?php echo $active_style; ?>">
            <?php echo htmlspecialchars($cat); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="posts-grid">
        <?php while ($post = $result->fetch_assoc()): ?>
            <?php 
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
                    <span style="display: inline-block; background-color: var(--border-color); color: var(--accent-yellow); font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; margin-bottom: 8px; font-weight: 600;">
                        <?php echo htmlspecialchars($post['category'] ?? 'General'); ?>
                    </span>

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
    <p>No blog posts found in this category. <a href="create.php" style="color: var(--accent-yellow);">Create your first post</a>!</p>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
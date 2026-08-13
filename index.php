<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

if ($is_logged_in) {
    // Fetch blog posts for logged-in users
    $sql = "SELECT blog_posts.*, users.username 
            FROM blog_posts 
            JOIN users ON blog_posts.user_id = users.id 
            ORDER BY blog_posts.created_at DESC";
    $result = $conn->query($sql);
    
    $page_title = "Home - Blog App";
    $css_file   = "index.css";
} else {
    // Visitor page setup
    $page_title = "Welcome - Blog App";
    $css_file   = "landing.css";
}

require_once 'includes/header.php';
?>

<?php if (!$is_logged_in): ?>

    <!-- Landing Page View for Visitors -->
    <div class="landing-container">
        <!-- Hero Section -->
        <section class="hero-section">
            <h1 class="hero-title">Share Your Thoughts with the <span>World</span></h1>
            <p class="hero-description">
                A minimal, fast, and modern platform to write articles, read insights, and connect with developers and creators.
            </p>
            <div class="hero-buttons">
                <a href="register.php" class="btn">Get Started for Free</a>
                <a href="login.php" class="btn btn-secondary">Sign In</a>
            </div>
        </section>

        <!-- Feature Highlights -->
        <section class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">✍️</div>
                <h3>Write & Publish</h3>
                <p>Create rich, nicely formatted articles with custom thumbnails and an intuitive editor.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌐</div>
                <h3>Reach an Audience</h3>
                <p>Share your technical insights, tutorials, and stories with a dedicated community.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Fast & Minimal</h3>
                <p>Enjoy a clean, distraction-free reading and writing environment built for speed.</p>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <h2 class="section-title">How It Works</h2>
            <div class="steps-grid">
                <div class="step">
                    <span class="step-num">1</span>
                    <h4>Create Your Account</h4>
                    <p>Register in seconds with your username and email address.</p>
                </div>
                <div class="step">
                    <span class="step-num">2</span>
                    <h4>Draft Your Article</h4>
                    <p>Write content and attach cover photos directly from your dashboard.</p>
                </div>
                <div class="step">
                    <span class="step-num">3</span>
                    <h4>Publish & Discover</h4>
                    <p>Post live instantly and read what other creators are publishing.</p>
                </div>
            </div>
        </section>
    </div>

<?php else: ?>

    <!-- Dashboard Feed View for Logged-In Users -->
    <div class="page-header">
        <h2 class="page-title">Recent Blog Posts</h2>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="posts-grid">
            <?php while ($post = $result->fetch_assoc()): ?>
                <article class="post-card">
                    <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
                        <a href="view.php?id=<?php echo $post['id']; ?>">
                            <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                 class="post-card-img">
                        </a>
                    <?php endif; ?>

                    <div class="post-card-body">
                        <h3 class="post-title">
                            <a href="view.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                        </h3>
                        <div class="post-meta">
                            By <strong><?php echo htmlspecialchars($post['username']); ?></strong> • <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                        </div>
                        <p class="post-excerpt"><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 120))); ?>...</p>
                        <a href="view.php?id=<?php echo $post['id']; ?>" class="post-read-more">Read Article &rarr;</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No blog posts found. <a href="create.php" style="color: var(--accent-yellow);">Create your first post</a>!</p>
    <?php endif; ?>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
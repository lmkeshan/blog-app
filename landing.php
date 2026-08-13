<?php
session_start();
require_once 'config/db.php';

$page_title = "Welcome to DevBlog";
$css_file   = "landing.css"; // Dynamically loads assets/css/landing.css
require_once 'includes/header.php';
?>

<div class="landing-container">
    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title">Share Your Thoughts with the <span>World</span></h1>
        <p class="hero-description">
            A minimal, fast, and modern platform to write articles, read insights, and connect with developers and creators.
        </p>
        <div class="hero-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php" class="btn">Go to Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn">Get Started for Free</a>
                <a href="login.php" class="btn btn-secondary">Sign In</a>
            <?php endif; ?>
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

<?php require_once 'includes/footer.php'; ?>
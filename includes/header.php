<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get current filename to highlight active navbar link
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Blog App'; ?></title>
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="assets/css/global.css">
    
    <!-- Page Specific CSS -->
    <?php if (isset($css_file)): ?>
        <link rel="stylesheet" href="assets/css/<?php echo htmlspecialchars($css_file); ?>">
    <?php endif; ?>
</head>
<body>

    <header class="site-header">
        <div class="nav-container">
            <a href="index.php" class="brand-logo">Dev<span>Blog</span></a>
            
            <nav>
                <ul class="nav-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><span class="user-welcome">Hello, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span></li>
                        <li><a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="library.php" class="<?php echo $current_page === 'library.php' ? 'active' : ''; ?>">My Library</a></li>
                        <li><a href="create.php" class="btn">+ Create Post</a></li>
                        <li><a href="logout.php" style="color: #ff6b6b;">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php" class="btn">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-wrapper">
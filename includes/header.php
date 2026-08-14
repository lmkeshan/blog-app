<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current filename to highlight active navbar link
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch avatar if user is logged in and DB connection exists
$user_avatar = null;
if (isset($_SESSION['user_id']) && isset($conn)) {
    $nav_user_id = $_SESSION['user_id'];
    $nav_stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $nav_stmt->bind_param("i", $nav_user_id);
    $nav_stmt->execute();
    $nav_res = $nav_stmt->get_result()->fetch_assoc();
    if ($nav_res) {
        $user_avatar = $nav_res['avatar'];
    }
    $nav_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Blog App'; ?></title>
    
    <!-- Global & Header CSS -->
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/header.css">
    
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
                        <li>
                            <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a>
                        </li>
                        <li>
                            <a href="library.php" class="<?php echo $current_page === 'library.php' ? 'active' : ''; ?>">My Library</a>
                        </li>
                        <li>
                            <a href="create.php" class="btn">+ Create Post</a>
                        </li>
                        <li>
                            <a href="logout.php" style="color: #ff6b6b;">Logout</a>
                        </li>
                        
                        <!-- Profile Icon placed at the edge (No name displayed) -->
                        <li>
                            <a href="profile.php" class="nav-profile-btn <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" title="My Profile (<?php echo htmlspecialchars($_SESSION['username']); ?>)">
                                <?php if (!empty($user_avatar) && file_exists('uploads/' . $user_avatar)): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($user_avatar); ?>" 
                                         alt="Profile" 
                                         class="nav-avatar-img">
                                <?php else: ?>
                                    <span class="nav-avatar-fallback">
                                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php" class="btn">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-wrapper">
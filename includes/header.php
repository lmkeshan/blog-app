<?php
// Ensure session is started if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Blog App'; ?></title>
</head>
<body style="font-family: sans-serif; max-width: 800px; margin: 30px auto; padding: 0 20px;">

    <!-- Global Navigation Header -->
    <header style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 12px; margin-bottom: 25px;">
        <h1 style="margin: 0;"><a href="index.php" style="text-decoration: none; color: #333;">My Blog</a></h1>
        
        <nav style="display: flex; align-items: center; gap: 15px;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</span>
                <a href="index.php">Home</a>
                <a href="library.php" style="font-weight: bold; color: #0066cc;">📚 My Library</a>
                <a href="create.php">Create Post</a>
                <a href="logout.php" style="color: #cc0000;">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
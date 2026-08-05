<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if an 'id' parameter is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Always redirect back to home page after deletion
header("Location: index.php");
exit();
?>
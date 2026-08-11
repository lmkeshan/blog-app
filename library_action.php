<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['post_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = (int)$_GET['post_id'];

// 1. Check if the post is already in the user's library
$check_stmt = $conn->prepare("SELECT id FROM libraries WHERE user_id = ? AND post_id = ?");
$check_stmt->bind_param("ii", $user_id, $post_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Already in library -> REMOVE IT
    $delete_stmt = $conn->prepare("DELETE FROM libraries WHERE user_id = ? AND post_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $post_id);
    $delete_stmt->execute();
    $delete_stmt->close();
} else {
    // Not in library -> ADD IT
    $insert_stmt = $conn->prepare("INSERT INTO libraries (user_id, post_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $user_id, $post_id);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$check_stmt->close();

$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: " . $redirect);
exit();
?>
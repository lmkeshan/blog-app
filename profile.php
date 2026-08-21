<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$success_msg = "";
$error_msg   = "";

$pwd_success_msg = "";
$pwd_error_msg   = "";

$user_stmt = $conn->prepare("SELECT username, email, avatar FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username    = trim($_POST['username']);
    $new_email       = trim($_POST['email']);
    $avatar_filename = $user['avatar'];

    if (empty($new_username) || empty($new_email)) {
        $error_msg = "Username and Email cannot be empty.";
    } else {

        $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_msg = "Username or Email is already in use by another account.";
        } else {

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath   = $_FILES['avatar']['tmp_name'];
                $fileName      = $_FILES['avatar']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($fileExtension, $allowed)) {
                    $new_avatar_name = 'avatar_' . $user_id . '_' . time() . '.' . $fileExtension;
                    $upload_dir      = './uploads/';

                    if (move_uploaded_file($fileTmpPath, $upload_dir . $new_avatar_name)) {
                        // Delete old avatar file if exists
                        if (!empty($user['avatar']) && file_exists($upload_dir . $user['avatar'])) {
                            unlink($upload_dir . $user['avatar']);
                        }
                        $avatar_filename = $new_avatar_name;
                    } else {
                        $error_msg = "Failed to move uploaded avatar image.";
                    }
                } else {
                    $error_msg = "Invalid avatar image format. Allowed: JPG, PNG, WEBP, GIF.";
                }
            }

            if (empty($error_msg)) {
                $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, avatar = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $new_username, $new_email, $avatar_filename, $user_id);

                if ($update_stmt->execute()) {
                    $_SESSION['username'] = $new_username; // Sync session username
                    $success_msg = "Profile updated successfully!";
                    // Refresh local $user array data
                    $user['username'] = $new_username;
                    $user['email']    = $new_email;
                    $user['avatar']   = $avatar_filename;
                } else {
                    $error_msg = "Failed to update profile details.";
                }
                $update_stmt->close();
            }
        }
        $check_stmt->close();
    }
}

//password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $pwd_error_msg = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $pwd_error_msg = "New password and confirmation do not match.";
    } elseif (strlen($new_password) < 6) {
        $pwd_error_msg = "New password must be at least 6 characters long.";
    } else {
  
        $pwd_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $pwd_stmt->bind_param("i", $user_id);
        $pwd_stmt->execute();
        $db_user = $pwd_stmt->get_result()->fetch_assoc();
        $pwd_stmt->close();

        if ($db_user && password_verify($current_password, $db_user['password'])) {
    
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            $update_pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_pwd_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_pwd_stmt->execute()) {
                $pwd_success_msg = "Password updated successfully!";
            } else {
                $pwd_error_msg = "Failed to update password. Please try again.";
            }
            $update_pwd_stmt->close();
        } else {
            $pwd_error_msg = "Current password is incorrect.";
        }
    }
}
//delete post
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['post_id'])) {
    $delete_id = (int)$_GET['post_id'];

    $get_post = $conn->prepare("SELECT thumbnail FROM blog_posts WHERE id = ? AND user_id = ?");
    $get_post->bind_param("ii", $delete_id, $user_id);
    $get_post->execute();
    $post_res = $get_post->get_result();

    if ($post_res->num_rows === 1) {
        $post_data = $post_res->fetch_assoc();
        
        if (!empty($post_data['thumbnail']) && file_exists('uploads/' . $post_data['thumbnail'])) {
            unlink('uploads/' . $post_data['thumbnail']);
        }

        $del_stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ? AND user_id = ?");
        $del_stmt->bind_param("ii", $delete_id, $user_id);
        $del_stmt->execute();
        $del_stmt->close();

        header("Location: profile.php?msg=deleted");
        exit();
    }
}

// Fetch all posts authored by this user
$posts_stmt = $conn->prepare("SELECT * FROM blog_posts WHERE user_id = ? ORDER BY created_at DESC");
$posts_stmt->bind_param("i", $user_id);
$posts_stmt->execute();
$my_posts = $posts_stmt->get_result();

$page_title = "My Profile - Blog App";
$css_file   = "profile.css";
require_once 'includes/header.php';
?>

<div class="profile-layout">
    <div class="profile-card">
        <div class="profile-header">
            <div class="avatar-wrapper">
                <?php if (!empty($user['avatar']) && file_exists('uploads/' . $user['avatar'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="profile-avatar">
                <?php else: ?>
                    <div class="profile-avatar-placeholder">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <?php if (!empty($success_msg) || isset($_GET['msg'])): ?>
            <div class="alert-success">
                <?php echo !empty($success_msg) ? htmlspecialchars($success_msg) : "Post deleted successfully!"; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <!-- Edit Profile Form -->
        <form action="profile.php" method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control profile-input" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control profile-input" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="avatar">Change Avatar</label>
                <input type="file" id="avatar" name="avatar" class="form-control profile-file-input" accept="image/*">
            </div>

            <button type="submit" name="update_profile" class="btn" style="width: 100%;">Save Changes</button>
        </form>

        <hr class="profile-divider">

        <!-- Change Password Form -->
        <div class="password-card-header">
            <h3>Change Password</h3>
        </div>

        <?php if (!empty($pwd_success_msg)): ?>
            <div class="alert-success"><?php echo htmlspecialchars($pwd_success_msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($pwd_error_msg)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($pwd_error_msg); ?></div>
        <?php endif; ?>

        <form action="profile.php" method="POST" class="profile-form password-form">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-control profile-input" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control profile-input" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control profile-input" required minlength="6">
            </div>

            <button type="submit" name="update_password" class="btn btn-secondary" style="width: 100%;">Update Password</button>
        </form>
    </div>

    <!-- Articles Section -->
    <div class="my-posts-section">
        <div class="section-header">
            <h3>My Published Articles</h3>
            <a href="create.php" class="btn btn-sm">+ New Post</a>
        </div>

        <?php if ($my_posts && $my_posts->num_rows > 0): ?>
            <div class="manage-posts-list">
                <?php while ($post = $my_posts->fetch_assoc()): ?>
                    <div class="manage-post-card">
                        <div class="manage-post-info">
                            <?php if (!empty($post['thumbnail']) && file_exists('uploads/' . $post['thumbnail'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($post['thumbnail']); ?>" 
                                     alt="Post Image" class="manage-post-thumb">
                            <?php else: ?>
                                <div class="manage-post-thumb-placeholder">📄</div>
                            <?php endif; ?>
                            
                            <div class="manage-post-details">
                                <h4 class="manage-post-title">
                                    <a href="view.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                                </h4>
                                <span class="manage-post-date">Published: <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="manage-post-actions">
                            <a href="edit.php?id=<?php echo $post['id']; ?>" class="action-btn edit-btn">Edit</a>
                            <a href="profile.php?action=delete&post_id=<?php echo $post['id']; ?>" 
                               class="action-btn delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-posts-box">
                <p>You haven't written any posts yet.</p>
                <a href="create.php" style="color: var(--accent-yellow);">Write your first article &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
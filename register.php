<?php

    session_start();
    require_once 'config/db.php';

    $errors = [];
    $success = "";

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($username) || empty($email) || empty($password)) {
            $errors[] = "All fields are required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }

        if (empty($errors)){
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check_stmt->bind_param("ss", $username, $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $errors[] = "Username or email is already registered.";
            }else{
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $success = "Account created successfully! You can now <a href='login.php'>Login here</a>.";
                }else{
                    $errors[] = "Something went wrong. Please try again.";
                }

                $insert_stmt->close();
            }
            $check_stmt->close();
        }

    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blog Application</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="form-container" style="max-width: 400px; margin: 50px auto; font-family: sans-serif;">
        <h2>Create an Account</h2>

        <?php if (!empty($errors)): ?>
            <div style="color: red; margin-bottom: 15px;">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="color: green; margin-bottom: 15px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            
            <div style="margin-bottom: 15px;">
                <label for="username">Username</label><br>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="email">Email</label><br>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="password">Password</label><br>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="confirm_password">Confirm Password</label><br>
                <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 8px;">
            </div>

            <button type="submit" style="padding: 10px 15px; cursor: pointer;">Register</button>
        </form>

        <p style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></p>
    </div>

</body>
</html>
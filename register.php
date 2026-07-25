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

        <!-- Display Error Messages -->
        <?php if (!empty($errors)): ?>
            <div style="color: red; margin-bottom: 15px;">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Display Success Message -->
        <?php if (!empty($success)): ?>
            <div style="color: green; margin-bottom: 15px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
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
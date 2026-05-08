<?php
require_once 'connect.php';

$message = '';
if (isset($_POST['reset_request'])) {
    $email = htmlspecialchars($_POST['email']);

    // In a real app, you would generate a token and send an email here.
    // For now, we will just check if the user exists.
    $stmt = $databaseConnexion->prepare("SELECT * FROM admins WHERE email = ? UNION SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email, $email]);

    if ($stmt->rowCount() > 0) {
        $message = "A reset link has been sent to your email (Demo).";
    } else {
        $message = "Email not found in our records.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h3>Reset Password</h3>
        <p style="font-size: 14px; color: #666;">Enter your email to receive a reset link.</p>
        <?php if ($message)
            echo "<p style='color:blue; font-size:13px;'>$message</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="reset_request">Send Reset Link</button>
        </form>
        <p><a href="home.php" style="font-size: 13px; color: #2563eb; text-decoration:none;">Back to Login</a></p>
    </div>
</body>
</html>
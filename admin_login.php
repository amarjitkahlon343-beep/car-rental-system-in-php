<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Updated Table Name to 'admins'
    $stmt = $conn->prepare("SELECT id, fullname, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // 2. Verify Password Hash
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['role'] = 'admin'; // We manually set this as 'admin' since they are in the admins table

            header("Location: Admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin email not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | PRO-CAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #1a1d20; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .admin-card { width: 100%; max-width: 400px; border: none; border-radius: 1rem; background: #ffffff; }
        .btn-admin { background: #212529; color: white; border: none; }
        .btn-admin:hover { background: #000000; color: white; }
    </style>
</head>
<body>

<div class="container p-3">
    <div class="card admin-card shadow-lg mx-auto">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock-fill text-dark" style="font-size: 3rem;"></i>
                <h3 class="fw-bold mt-2">ADMIN PANEL</h3>
                <p class="text-muted small">Please enter your credentials</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger py-2 small text-center"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">ADMIN EMAIL</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@procar.com" required autofocus>
                </div>
                
                <div class="mb-4">
                    <label class="form-label small fw-bold">PASSWORD</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-admin w-100 py-2 fw-bold">
                    LOGIN TO SYSTEM
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
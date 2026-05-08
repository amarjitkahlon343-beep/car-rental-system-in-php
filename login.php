<?php
session_start();
require_once 'connect.php';

// FIX: Define these variables so the HTML doesn't throw a "Warning"
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';

// If already logged in as a user, you can optionally auto-redirect:
// if ($isLoggedIn && $userRole === 'user') { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Check if this is a USER login
            if (strtolower($user['role']) === 'user') {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['role'] = 'user';

                header("Location: Admin_dashboard.php");
                exit();
            } else {
                $error = "Access Denied: Admins must use the Staff Portal.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - PRO-CAR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .login-card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .admin-link { font-size: 0.8rem; text-decoration: none; color: #6c757d; transition: 0.3s; }
        .admin-link:hover { color: #0d6efd; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card login-card mx-auto shadow-sm" style="max-width: 400px;">
        <div class="card-body p-4">
            
            <?php if ($isLoggedIn): ?>
                <div class="alert alert-info text-center small mb-4">
                    Logged in as <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong><br>
                    <a href="<?= ($userRole === 'admin') ? 'Admin_dashboard.php' : 'index.php' ?>" class="fw-bold text-decoration-none">
                        Go to Dashboard <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>

            <h4 class="text-center fw-bold mb-4">Customer Login</h4>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger py-2 small text-center"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">EMAIL ADDRESS</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">PASSWORD</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">SIGN IN</button>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <p class="text-muted small mb-3">Don't have an account yet?</p>
                <div class="d-grid gap-2 mb-3">
                    <a href="user_register.php" class="btn btn-outline-primary btn-sm rounded-pill py-2">
                        Register as Customer
                    </a>
                    <a href="admin_register.php" class="btn btn-outline-dark btn-sm rounded-pill py-2">
        <i class="bi bi-person-badge"></i> Register as Admin/Staff
    </a>
                </div>
                
                <a href="admin_login.php" class="admin-link">
                    <i class="bi bi-shield-lock"></i> Admin/Staff Login Panel
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
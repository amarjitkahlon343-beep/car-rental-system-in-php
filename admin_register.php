<?<?php
include('connect.php');

if (isset($_POST['admin_register'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $security_token = $_POST['security_token'];

    // CHANGE THIS: Only people who know this secret key can register as admin
    $correct_token = "ADMIN123";

    if ($security_token !== $correct_token) {
        echo "<script>alert('Invalid Security Token! Access Denied.');</script>";
    } else {
        // Check if Admin email already exists
        $stmt = $conn->prepare("SELECT email FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            echo "<script>alert('This admin email is already registered!');</script>";
        } else {
            $sql = "INSERT INTO admins (fullname, email, password, phone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $fullname, $email, $password, $phone);

            if ($stmt->execute()) {
                echo "<script>alert('Admin Account created successfully!'); window.location='admin_login.php';</script>";
            } else {
                echo "<script>alert('Error: Could not register admin.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #212529; color: white; }
        .card { border-radius: 15px; background-color: #fff; color: #333; }
        .btn-dark { background-color: #212529; border: none; }
        .btn-dark:hover { background-color: #000; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="card mx-auto shadow-lg" style="max-width: 500px;">
        <div class="card-body p-4 p-md-5">
            <h3 class="text-center fw-bold mb-2">Admin Portal</h3>
            <p class="text-center text-muted small mb-4">Internal Staff Registration Only</p>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label small fw-bold">FULL NAME</label>
                    <input type="text" name="fullname" class="form-control" placeholder="Admin Name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">WORK EMAIL</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@company.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">PHONE</label>
                    <input type="tel" name="phone" class="form-control" placeholder="Official Number" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">SECURITY TOKEN</label>
                    <input type="password" name="security_token" class="form-control" placeholder="Enter System Secret Key" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">PASSWORD</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" name="admin_register" class="btn btn-dark w-100 py-2 fw-bold shadow-sm">
                    INITIALIZE ADMIN ACCOUNT
                </button>
            </form>
            
            <p class="text-center mt-4 mb-0 small text-muted">
                Back to <a href="admin_login.php" class="text-dark fw-bold">Public Login</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
<?php
include('connect.php');

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dob = $_POST['dob'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $village = $_POST['village'];
    $role = 'user';

    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        echo "<script>alert('This email is already registered!');</script>";
    } else {
        $sql = "INSERT INTO users (fullname, email, password, role, dob, phone, address, state, district, village) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $fullname, $email, $password, $role, $dob, $phone, $address, $state, $district, $village);

        if ($stmt->execute()) {
            echo "<script>alert('Account created! Please login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error: Check your database fields.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .card { border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .form-label { font-size: 0.8rem; font-weight: 700; color: #444; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card mx-auto" style="max-width: 700px;">
            <div class="card-body p-4 p-md-5">
                <h3 class="text-center fw-bold mb-4 text-primary">User Sign Up</h3>
                <form method="POST" action="">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">FULL NAME</label>
                            <input type="text" name="fullname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">DATE OF BIRTH</label>
                            <input type="date" name="dob" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">EMAIL ADDRESS</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PHONE NUMBER</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">COMPLETE ADDRESS</label>
                        <textarea name="address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><label class="form-label">STATE</label><input type="text" name="state" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">DISTRICT</label><input type="text" name="district" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">VILLAGE/CITY</label><input type="text" name="village" class="form-control" required></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">PASSWORD</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100 py-3 fw-bold">CREATE ACCOUNT</button>
                </form>
                <div class="text-center mt-4">
                    <p class="small text-muted">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Login</a></p>
                    <hr>
                    <a href="admin_register.php" class="small text-secondary text-decoration-none">Are you Staff? Register Here</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
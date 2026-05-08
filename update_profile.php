<?php
session_start();
require_once 'connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'];

// 2. HANDLE FORM SUBMISSION (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $village = $_POST['village'];
    $new_password = $_POST['new_password'];

    // Update All Profile Info
    $sql = "UPDATE users SET fullname=?, phone=?, dob=?, address=?, state=?, district=?, village=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $fullname, $phone, $dob, $address, $state, $district, $village, $user_id);

    if ($stmt->execute()) {
        $_SESSION['fullname'] = $fullname; // Sync session

        // Update Password only if not empty
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pwd_stmt->bind_param("si", $hashed_password, $user_id);
            $pwd_stmt->execute();
        }
        header("Location: update_profile.php?msg=success");
        exit();
    }
}

// 3. FETCH DATA FOR DISPLAY
$fetch_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$fetch_stmt->bind_param("i", $user_id);
$fetch_stmt->execute();
$user_data = $fetch_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 15px; border: none; }
        .form-label { color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            Profile updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm py-4">
                <div class="card-body">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_data['fullname']) ?>&background=random&size=128" class="rounded-circle mb-3 shadow-sm" width="120">
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user_data['fullname']) ?></h4>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?= strtoupper($user_data['role']) ?></span>
                    <hr class="my-4">
                    <div class="text-start small text-muted">
                        <p class="mb-1"><i class="bi bi-envelope me-2"></i><?= $user_data['email'] ?></p>
                        <p class="mb-0"><i class="bi bi-geo-alt me-2"></i><?= $user_data['district'] ?>, <?= $user_data['state'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">Account Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form action="update_profile.php" method="POST">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user_data['fullname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= $user_data['dob'] ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user_data['email']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user_data['phone']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Complete Address</label>
                            <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($user_data['address']) ?></textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">State</label>
                                <select name="state" class="form-select" required>
                                    <option value="<?= $user_data['state'] ?>"><?= $user_data['state'] ?: 'Select State' ?></option>
                                    <option value="Punjab">Punjab</option>
                                    <option value="Maharashtra">Maharashtra</option>
                                    <option value="Delhi">Delhi</option>
                                    </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">District</label>
                                <input type="text" name="district" class="form-control" value="<?= htmlspecialchars($user_data['district']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Village/City</label>
                                <input type="text" name="village" class="form-control" value="<?= htmlspecialchars($user_data['village']) ?>" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Change Password</h6>
                            <label class="form-label small">New Password (Leave blank to keep current)</label>
                            <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
require_once 'connect.php';

// Security Check: Only Admins
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: index.php");
    exit();
}

// 1. Get User Data
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT fullname, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die("User not found.");
    }
}

// 2. Handle Form Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name = $_POST['fullname'];
    $new_email = $_POST['email'];
    $new_role = $_POST['role'];
    $user_id = intval($_POST['user_id']);

    $update = $conn->prepare("UPDATE users SET fullname = ?, email = ?, role = ? WHERE id = ?");
    $update->bind_param("sssi", $new_name, $new_email, $new_role, $user_id);

    if ($update->execute()) {
        header("Location: manage_users.php?msg=updated");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-4">Edit User Details</h3>
                        
                        <form action="edit_user.php" method="POST">
                            <input type="hidden" name="user_id" value="<?= $id ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">User Role</label>
                                <select name="role" class="form-select">
                                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                                <a href="manage_users.php" class="btn btn-light border px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
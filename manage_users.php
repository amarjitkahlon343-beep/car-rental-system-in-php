<?php
session_start();
require_once 'connect.php';

// 1. Security Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: Admin_dashboard.php");
    exit();
}

// Determine the logged-in ID (Check both common session keys)
$currentAdminId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;

// 2. Handle User Deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Prevent admin from deleting themselves
    if ($delete_id != $currentAdminId) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
    header("Location: manage_users.php");
    exit();
}

// 3. Handle Role Update
if (isset($_GET['toggle_role'])) {
    $uid = intval($_GET['toggle_role']);
    $new_role = ($_GET['current'] == 'admin') ? 'user' : 'admin';
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $uid);
    $stmt->execute();
    header("Location: manage_users.php");
    exit();
}

// 4. Fetch Users
$result = $conn->query("SELECT id, fullname, email, role, created_at FROM users ORDER BY role ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | PRO-CAR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>User Management</h2>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end">
            <div class="input-group w-75 shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="userSearch" class="form-control border-start-0 ps-0" placeholder="Search name or email...">
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($user['fullname']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] == 'admin' ? 'bg-warning text-dark' : 'bg-info' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                
                             <td class="text-end pe-4">
    <div class="btn-group shadow-sm">
        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-light border">
            <i class="bi bi-pencil-square text-primary"></i> Edit
        </a>

        <a href="manage_users.php?delete_id=<?= $user['id'] ?>" 
           class="btn btn-sm btn-light border text-danger" 
           onclick="return confirm('Permanently delete this user?')">
            <i class="bi bi-trash"></i> Delete
        </a>
    </div>
    
    <div style="font-size: 10px; color: red;">
        Row ID: <?= $user['id'] ?> | Your ID: <?= $_SESSION['user_id'] ?? 'Not Set' ?>
    </div>
</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('userSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#userTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>
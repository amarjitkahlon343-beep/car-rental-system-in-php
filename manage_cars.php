<?php
session_start();
require_once 'connect.php';

// 1. Security Check: Only Admin
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: Admin_dashboard.php");
    exit();
}

// 2. Handle Stock Update (Quick Edit)
if (isset($_POST['update_stock'])) {
    $car_id = intval($_POST['car_id']);
    $new_stock = intval($_POST['new_stock']);
    
    $stmt = $conn->prepare("UPDATE cars SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_stock, $car_id);
    $stmt->execute();
    
    // Updated redirect name
    header("Location: manage_cars.php?msg=Stock Updated");
    exit();
}

// 3. Handle Car Deletion
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    
    // Fetch image name to delete file from server
    $img_res = $conn->query("SELECT image FROM cars WHERE id = $del_id");
    $img_data = $img_res->fetch_assoc();
    if($img_data && file_exists("images/".$img_data['image'])) {
        unlink("images/".$img_data['image']);
    }

    $conn->query("DELETE FROM cars WHERE id = $del_id");
    
    // Updated redirect name
    header("Location: manage_cars.php?msg=Car Removed");
    exit();
}

// 4. Fetch All Cars
$result = $conn->query("SELECT * FROM cars ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Cars | PRO-CAR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .car-img-sm { width: 80px; height: 50px; object-fit: cover; border-radius: 5px; }
        .stock-input { width: 80px; }
        .table-card { border-radius: 15px; overflow: hidden; border: none; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="bi bi-car-front-fill me-2"></i>Manage Cars</h2>
            <p class="text-muted">Update stock levels and manage your vehicle fleet</p>
        </div>
        <div class="d-flex gap-2">
            <a href="admin_cars.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i>Add New Car
            </a>
            <a href="Admin_dashboard.php" class="btn btn-outline-dark rounded-pill px-4">Dashboard</a>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="carSearch" class="form-control border-start-0" placeholder="Filter by name, model, fuel type...">
            </div>
        </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="carTable">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Vehicle</th>
                        <th>Specs</th>
                        <th>Price</th>
                        <th>Current Stock</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($car = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <img src="images/<?= htmlspecialchars($car['image']) ?>" class="car-img-sm me-3 shadow-sm" alt="car">
                                <div class="fw-bold"><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= $car['fuel_type'] ?></span>
                            <span class="badge bg-light text-dark border"><?= $car['transmission'] ?></span>
                        </td>
                        <td><span class="text-success fw-bold">₹<?= number_format($car['price_per_day']) ?></span></td>
                        <td>
                            <form action="manage_cars.php" method="POST" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                <input type="number" name="new_stock" class="form-control form-control-sm stock-input" value="<?= $car['stock'] ?>" min="0">
                                <button type="submit" name="update_stock" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <?php if($car['stock'] <= 0): ?>
                                    <span class="badge bg-danger">Sold Out</span>
                                <?php endif; ?>
                            </form>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="manage_cars.php?delete_id=<?= $car['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this car?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search Filter
document.getElementById('carSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#carTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
require_once 'connect.php';

// 1. Security Check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: Admin_dashboard.php");
    exit();
}

// 2. Fetch Existing Data
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM cars WHERE id = $id");
    $car = $result->fetch_assoc();

    if (!$car) {
        header("Location: manage_cars.php");
        exit();
    }
}

// 3. Handle Update Submission
if (isset($_POST['update_car'])) {
    $id = intval($_POST['id']);
    $brand = $conn->real_escape_string($_POST['brand']);
    $model = $conn->real_escape_string($_POST['model']);
    $year = $_POST['make_year'];
    $price = $_POST['price_per_day'];
    $fuel = $_POST['fuel_type'];
    $trans = $_POST['transmission'];
    $stock = $_POST['stock'];

    // Handle Image Upload (Optional)
    $image_name = $car['image']; // Default to old image
    if (!empty($_FILES['image']['name'])) {
        $target = "images/" . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_name = $_FILES['image']['name'];
        }
    }

    $sql = "UPDATE cars SET 
            brand='$brand', model='$model', make_year='$year', 
            price_per_day='$price', fuel_type='$fuel', 
            transmission='$trans', stock='$stock', image='$image_name' 
            WHERE id=$id";

    if ($conn->query($sql)) {
        echo "<script>alert('Car updated successfully!'); window.location='manage_cars.php';</script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Car | PRO-CAR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0">Edit Vehicle: <?= $car['brand'] ?> <?= $car['model'] ?></h5>
                </div>
                <div class="card-body p-4">
                    <form action="edit_car.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $car['id'] ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" class="form-control" value="<?= $car['brand'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control" value="<?= $car['model'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year</label>
                                <input type="number" name="make_year" class="form-control" value="<?= $car['make_year'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price Per Day (₹)</label>
                                <input type="number" name="price_per_day" class="form-control" value="<?= $car['price_per_day'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock</label>
                                <input type="number" name="stock" class="form-control" value="<?= $car['stock'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fuel Type</label>
                                <select name="fuel_type" class="form-select">
                                    <option value="Petrol" <?= $car['fuel_type'] == 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                                    <option value="Diesel" <?= $car['fuel_type'] == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                    <option value="Electric" <?= $car['fuel_type'] == 'Electric' ? 'selected' : '' ?>>Electric</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Transmission</label>
                                <select name="transmission" class="form-select">
                                    <option value="Manual" <?= $car['transmission'] == 'Manual' ? 'selected' : '' ?>>Manual</option>
                                    <option value="Automatic" <?= $car['transmission'] == 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <label class="form-label">Change Image (Leave blank to keep current)</label>
                                <input type="file" name="image" class="form-control">
                                <div class="mt-2">
                                    <small class="text-muted">Current: <?= $car['image'] ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 d-flex gap-2">
                            <button type="submit" name="update_car" class="btn btn-primary px-5">Save Changes</button>
                            <a href="manage_cars.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
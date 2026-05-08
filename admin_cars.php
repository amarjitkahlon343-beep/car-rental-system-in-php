<?php
session_start();
include('connect.php');

// Redirect to Admin_dashboard.php if NOT an admin
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: Admin_dashboard.php");
    exit();
}

if (isset($_POST['submit'])) {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $fuel = $_POST['fuel_type'];
    $category = $_POST['category'];
    $transmission = $_POST['transmission'];
    $stock = $_POST['stock'];

    $target_dir = "images/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["car_image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["car_image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO cars (brand, model, make_year, price_per_day, image, status, fuel_type, category, transmission, stock) 
                VALUES ('$brand', '$model', '$year', '$price', '$file_name', 'available', '$fuel', '$category', '$transmission', '$stock')";

        if ($conn->query($sql)) {
            // Updated redirect after success
            echo "<script>alert('Car Added Successfully!'); window.location='admin_cars.php';</script>";
        } else {
            echo "Database Error: " . $conn->error;
        }
    } else {
        echo "Upload Error: Could not move file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Fleet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow mx-auto mb-5" style="max-width: 750px; border-radius: 15px;">
        <div class="card-header bg-primary text-white py-3 text-center">
            <h5 class="mb-0"><i class="bi bi-car-front-fill me-2"></i>Add New Vehicle to Fleet</h5>
        </div>
        <div class="card-body p-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Brand</label>
                        <input type="text" name="brand" class="form-control" placeholder="e.g. BMW" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Model</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g. M4" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Transmission</label>
                        <select name="transmission" class="form-select" required>
                            <option value="">Select Transmission</option>
                            <option value="Manual">Manual</option>
                            <option value="Automatic">Automatic</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fuel Type</label>
                        <select name="fuel_type" class="form-select" required>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Sports">Sports</option>
                            <option value="Luxury">Luxury</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Model Year</label>
                        <input type="number" name="year" class="form-control" placeholder="2024" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Price / Day (₹)</label>
                        <input type="number" name="price" class="form-control" placeholder="3000" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" min="1" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Car Photo</label>
                    <input type="file" name="car_image" class="form-control" accept="image/*" required>
                </div>

                <button type="submit" name="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload & Save Car
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow border-0" style="border-radius: 15px;">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Current Fleet Inventory</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Car Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM cars ORDER BY id DESC");
                    while ($row = $res->fetch_assoc()):
                        ?>
                    <tr>
                        <td><img src="images/<?= $row['image'] ?>" class="rounded" width="70"></td>
                        <td><strong><?= $row['brand'] ?> <?= $row['model'] ?></strong></td>
                        <td><?= $row['category'] ?></td>
                        <td>₹<?= number_format($row['price_per_day']) ?></td>
                        <td>
                            <span class="badge <?= $row['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                <?= $row['stock'] ?> Left
                            </span>
                        </td>
                        <td>
                            <a href="edit_car.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-pencil-square"></i></a>
                            <a href="delete_car.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this car?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
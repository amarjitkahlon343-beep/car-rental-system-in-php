<?php
session_start();
include('connect.php');

// 1. Logic for Search and Filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : 'All';

$query = "SELECT * FROM cars WHERE 1=1";
if (!empty($search)) {
    $query .= " AND (model LIKE '%$search%' OR brand LIKE '%$search%')";
}
if ($type != 'All') {
    $query .= " AND category = '$type'";
}
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Premium Car Rentals | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --primary-blue: #3b82f6; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .hero-section { background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%); padding: 100px 0 60px 0; color: white; border-bottom-left-radius: 50px; border-bottom-right-radius: 50px; margin-bottom: 40px; }
        .booking-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .filter-pill { border-radius: 50px; padding: 8px 20px; border: 1px solid #e2e8f0; background: white; color: #64748b; text-decoration: none; transition: 0.3s; margin-right: 10px; }
        .filter-pill.active, .filter-pill:hover { background: var(--primary-blue); color: white; border-color: var(--primary-blue); }
        .car-card { border: none; border-radius: 20px; overflow: hidden; transition: 0.3s; background: white; height: 100%; }
        .car-card:hover { transform: translateY(-10px); box-shadow: 0 20px 25px rgba(0,0,0,0.1); }
        .car-img { height: 200px; object-fit: cover; width: 100%; background: #f1f5f9; }
        .price-tag { font-size: 1.4rem; font-weight: 800; color: var(--primary-blue); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-lg py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="index.php">
            <span class="text-primary">PRO</span>-CAR
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto align-items-center">
                
                <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard_overview.php"><i class="bi bi-speedometer2"></i> Overview</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminFleet" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-car-front"></i> Fleet
                        </a>
                        <ul class="dropdown-menu shadow">
                            <li><a class="dropdown-item" href="admin_cars.php">Add New Car</a></li>
                            <li><a class="dropdown-item" href="manage_cars.php">Manage Inventory</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="my_bookings.php"><i class="bi bi-calendar-check"></i> Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="bi bi-people"></i> Customers</a></li>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'user'): ?>
                    <!--<li class="nav-item"><a class="nav-link" href="Admin_dashboard.php">Browse Cars</a></li>-->
                    <li class="nav-item"><a class="nav-link" href="my_bookings.php"><i class="bi bi-journal-check"></i> My Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="update_profile.php"><i class="bi bi-person-badge"></i> My Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="help_support.php"><i class="bi bi-question-circle"></i> Support</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-1 border border-white border-opacity-25 me-2">
                        <span class="badge <?= strtolower($_SESSION['role']) == 'admin' ? 'bg-warning text-dark' : 'bg-info text-white' ?> me-2 small">
                            <?= ucfirst($_SESSION['role']) ?>
                        </span>
                        <span class="text-white small fw-medium"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </div>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>

                <?php else: ?>
              <div class="dropdown">
    <button class="btn btn-outline-primary btn-sm rounded-pill px-4 dropdown-toggle" type="button" data-bs-toggle="dropdown">
        Register
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow">
        <li><a class="dropdown-item fw-bold" href="user_register.php">User Registration</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item small text-muted" href="admin_register.php">Staff/Admin Registration</a></li>
    </ul>
</div>

<div class="dropdown ms-2">
    <button class="btn btn-primary btn-sm rounded-pill px-4 dropdown-toggle" type="button" data-bs-toggle="dropdown">
        Login
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow">
        <li><a class="dropdown-item fw-bold" href="login.php">User Login</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item small text-muted" href="admin_login.php">Admin Portal</a></li>
    </ul>
</div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start">
                <h1 class="display-3 fw-bold">Drive Your Dream</h1>
                <p class="lead opacity-75">Premium vehicles for every journey.</p>
            </div>
            <div class="col-lg-5 offset-lg-1">
                <div class="booking-card">
                    <form action="Admin_dashboard.php" method="GET">
                        <div class="mb-3"><input type="text" name="search" class="form-control py-2" placeholder="Search brand or model..." value="<?= $search ?>"></div>
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">Find Available Cars</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Categories</h2>
        <div class="d-flex justify-content-center mt-3">
            <?php
            $cats = ['All', 'SUV', 'Sedan', 'Luxury', 'Sports'];
            // Fix: Check if 'type' exists in URL to avoid another warning
            $current_type = $_GET['type'] ?? 'All';

            foreach ($cats as $c): ?>
                <a href="Admin_dashboard.php?type=<?= $c ?>" class="filter-pill <?= $current_type == $c ? 'active' : '' ?>"><?= $c ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card car-card shadow-sm">
                    <div class="position-relative">
                        <img src="images/<?= htmlspecialchars($row['image']) ?>" class="car-img w-100" style="height:200px; object-fit:cover;">
                       <span class="badge bg-primary position-absolute top-0 end-0 m-2 shadow-sm">
    <?php
    $stock_count = $row['stock'] ?? 0; // Get number from database
    if ($stock_count > 0) {
        echo "Stock: " . htmlspecialchars($stock_count);
    } else {
        echo "Out of Stock";
    }
    ?>
</span>
                    </div>
                    
                    <div class="card-body p-4">
                        <h5 class="fw-bold"><?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($row['fuel_type']) ?> | <?= htmlspecialchars($row['transmission']) ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="price-tag fw-bold text-primary">₹<?= number_format($row['price_per_day']) ?></span>
                                <small class="text-muted">/day</small>
                            </div>
                            <a href="booking.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm px-4">Book</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
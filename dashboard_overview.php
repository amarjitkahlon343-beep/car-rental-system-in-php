<?php
session_start();
include('connect.php');

// Security Check
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'];
$is_admin = isset($_SESSION['admin_id']);

// --- FETCH STATS ---
// 1. Total Bookings
$booking_count_query = $is_admin ? "SELECT COUNT(*) as total FROM bookings" : "SELECT COUNT(*) as total FROM bookings WHERE user_id = $user_id";
$total_bookings = $conn->query($booking_count_query)->fetch_assoc()['total'];

// 2. Active Rentals (Confirmed)
$active_query = $is_admin ? "SELECT COUNT(*) as total FROM bookings WHERE status='Confirmed'" : "SELECT COUNT(*) as total FROM bookings WHERE user_id = $user_id AND status='Confirmed'";
$active_rentals = $conn->query($active_query)->fetch_assoc()['total'];

// 3. Total Spending / Revenue
$money_query = $is_admin ? "SELECT SUM(total_price) as total FROM bookings WHERE status='Confirmed'" : "SELECT SUM(total_price) as total FROM bookings WHERE user_id = $user_id AND status='Confirmed'";
$total_money = $conn->query($money_query)->fetch_assoc()['total'] ?? 0;

// 4. Available Cars (For Admin) or Cars Owned (For User)
$car_query = "SELECT COUNT(*) as total FROM cars WHERE stock > 0";
$available_cars = $conn->query($car_query)->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Overview | PRO-CAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 250px; }
        body { background-color: #f8f9fa; }
        .stat-card { border: none; border-radius: 15px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Welcome back, ADMIN </h2>
            <p class="text-muted">Here is what's happening with your rentals today.</p>
        </div>
        <div class="col-auto">
            <a href="Admin_dashboard.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i><?= $is_admin ? 'Manage Fleet' : 'Book a Car' ?>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary-subtle text-primary me-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Bookings</small>
                        <h4 class="fw-bold mb-0"><?= $total_bookings ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-success-subtle text-success me-3">
                        <i class="bi bi-car-front"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Active Rentals</small>
                        <h4 class="fw-bold mb-0"><?= $active_rentals ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-warning-subtle text-warning-emphasis me-3">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block"><?= $is_admin ? 'Total Revenue' : 'Total amount' ?></small>
                        <h4 class="fw-bold mb-0">₹<?= number_format($total_money) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-info-subtle text-info me-3">
                        <i class="bi bi-tags"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Available Fleet</small>
                        <h4 class="fw-bold mb-0"><?= $available_cars ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Quick Navigation</h5>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <a href="profile.php" class="text-decoration-none p-4 d-block bg-white border rounded-4 text-center hover-bg-light">
                            <i class="bi bi-clock-history fs-2 text-primary d-block mb-2"></i>
                            <span class="text-dark fw-semibold">History</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="Admin_dashboard.php" class="text-decoration-none p-4 d-block bg-white border rounded-4 text-center">
                            <i class="bi bi-grid fs-2 text-success d-block mb-2"></i>
                            <span class="text-dark fw-semibold">Fleet</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="logout.php" class="text-decoration-none p-4 d-block bg-white border rounded-4 text-center">
                            <i class="bi bi-box-arrow-right fs-2 text-danger d-block mb-2"></i>
                            <span class="text-dark fw-semibold">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-3">System Status</h5>
                <ul class="list-unstyled">
                    <li class="d-flex align-items-center mb-3">
                        <div class="p-2 bg-success rounded-circle me-3"></div>
                        <div>
                            <p class="mb-0 small fw-bold">Database Connection</p>
                            <small class="text-success">Active & Secure</small>
                        </div>
                    </li>
                   
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
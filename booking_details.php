<?php
session_start();
require_once 'connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// 2. Fetch Booking and Car details together
$sql = "SELECT bookings.*, cars.brand, cars.model, cars.image, cars.fuel_type, cars.transmission 
        FROM bookings 
        JOIN cars ON bookings.car_id = cars.id 
        WHERE bookings.id = ? AND bookings.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// 3. If booking doesn't exist or doesn't belong to this user
if (!$data) {
    header("Location: profile.php?msg=Booking not found.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Receipt | #<?= $data['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .receipt-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .status-header { border-radius: 20px 20px 0 0; background: #1e293b; color: white; padding: 20px; }
        .car-img { width: 100%; max-width: 300px; border-radius: 12px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4 d-flex justify-content-between align-items-center no-print">
                <a href="profile.php" class="btn btn-light rounded-pill"><i class="bi bi-arrow-left me-2"></i>Back to History</a>
                <button onclick="window.print()" class="btn btn-dark rounded-pill px-4"><i class="bi bi-printer me-2"></i>Print Receipt</button>
            </div>

            <div class="card receipt-card overflow-hidden">
                <div class="status-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold">Booking Summary</h4>
                        <small class="opacity-75">Reference: #BK-<?= str_pad($data['id'], 5, '0', STR_PAD_LEFT) ?></small>
                    </div>
                    <span class="badge rounded-pill bg-warning text-dark px-3 py-2">
                        <?= strtoupper($data['status']) ?>
                    </span>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="row mb-5 align-items-center">
                        <div class="col-md-5 text-center mb-4 mb-md-0">
                            <img src="images/<?= htmlspecialchars($data['image']) ?>" class="car-img shadow-sm" alt="Vehicle">
                        </div>
                        <div class="col-md-7">
                            <h3 class="fw-bold mb-1"><?= $data['brand'] ?> <?= $data['model'] ?></h3>
                            <p class="text-muted mb-3"><?= $data['fuel_type'] ?> • <?= $data['transmission'] ?> • All Inclusive</p>
                            
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-uppercase text-muted d-block small fw-bold">Pick-up</small>
                                    <span class="fw-bold"><?= date('D, M d, Y', strtotime($data['pickup_date'])) ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-uppercase text-muted d-block small fw-bold">Return</small>
                                    <span class="fw-bold"><?= date('D, M d, Y', strtotime($data['return_date'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold text-uppercase small text-primary mb-3">Customer Information</h6>
                            <p class="mb-1 fw-bold"><?= htmlspecialchars($data['first_name'] . " " . $data['last_name']) ?></p>
                            <p class="mb-1 text-muted"><?= htmlspecialchars($data['phone']) ?></p>
                            <p class="mb-0 text-muted"><?= htmlspecialchars($data['address1']) ?>, <?= htmlspecialchars($data['city']) ?></p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold text-uppercase small text-primary mb-3">Payment Details</h6>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Method:</span>
                                <span class="fw-bold"><?= $data['payment_method'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Status:</span>
                                <span class="badge bg-light text-dark border"><?= $data['payment_status'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                <span class="h5 fw-bold">Total Paid:</span>
                                <span class="h4 fw-bold text-success">₹<?= number_format($data['total_price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light p-4 text-center border-0">
                    <p class="small text-muted mb-0">Thank you for choosing <strong>PRO-CAR Rental Services</strong>. Please bring a valid ID and driving license at the time of pickup.</p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
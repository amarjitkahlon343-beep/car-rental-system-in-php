<?php
session_start();
include('connect.php');

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch combined booking and car data
$sql = "SELECT bookings.*, cars.brand, cars.model 
        FROM bookings 
        JOIN cars ON bookings.car_id = cars.id 
        WHERE bookings.user_id = ? 
        ORDER BY bookings.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Rental History | PRO-CAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .table-card { border-radius: 15px; border: none; }
        .badge-fixed { min-width: 100px; }
    </style>
</head>
<body>

<div class="container py-5">
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-info-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">My Rental History</h2>
            <p class="text-muted">View and manage your past and upcoming car reservations.</p>
        </div>
        <a href="Admin_dashboard.php" class="btn btn-outline-primary rounded-pill px-4">
            <i class="bi bi-house-door me-2"></i>New Booking
        </a>
    </div>

    <div class="card shadow-sm table-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="ps-4 py-3">Vehicle Details</th>
                        <th class="py-3">Rental Period</th>
                        <th class="py-3 text-center">Total Price</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-3 p-2 me-3 d-none d-md-block">
                                        <i class="bi bi-car-front text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['brand'] . " " . $row['model']); ?></h6>
                                        <small class="text-muted">Ref ID: #BK-<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="small fw-semibold"><i class="bi bi-calendar-event me-2"></i><?= date('M d, Y', strtotime($row['pickup_date'])); ?></span>
                                    <span class="small text-muted ps-4">to <?= date('M d, Y', strtotime($row['return_date'])); ?></span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="fw-bold text-dark">₹<?= number_format($row['total_price'], 2); ?></span>
                            </td>

                            <td class="text-center">
                                <?php
                                $status = strtolower($row['status']);
                                $badge_class = 'bg-secondary';
                                if ($status == 'confirmed')
                                    $badge_class = 'bg-success-subtle text-success border border-success';
                                if ($status == 'pending')
                                    $badge_class = 'bg-warning-subtle text-warning-emphasis border border-warning';
                                if ($status == 'cancelled')
                                    $badge_class = 'bg-danger-subtle text-danger border border-danger';
                                ?>
                                <span class="badge rounded-pill px-3 py-2 badge-fixed <?= $badge_class; ?>">
                                    <?= ucfirst($status); ?>
                                </span>
                            </td>

                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
    <li>
        <a class="dropdown-item" href="booking_details.php?id=<?= $row['id']; ?>">
            <i class="bi bi-eye me-2"></i>View Details
        </a>
    </li>

    <?php if ($status != 'cancelled'): ?>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-danger" href="cancel_booking.php?id=<?= $row['id']; ?>" 
               onclick="return confirm('Are you sure you want to cancel this booking?')">
                <i class="bi bi-x-circle me-2"></i>Cancel Booking
            </a>
        </li>
    <?php else: ?>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-danger" href="delete_booking.php?id=<?= $row['id']; ?>" 
               onclick="return confirm('Remove this record from your history permanently?')">
                <i class="bi bi-trash me-2"></i>Delete Record
            </a>
        </li>
    <?php endif; ?>
</ul>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-3"></i>
                                No booking history found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
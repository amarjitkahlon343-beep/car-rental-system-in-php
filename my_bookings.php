<?php
session_start();
require_once 'connect.php';

$user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;

// Fetch the user's bookings joined with car details
$query = "SELECT b.*, c.brand, c.model FROM bookings b 
          JOIN cars c ON b.car_id = c.id 
          WHERE b.user_id = ? ORDER BY b.id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
        .btn-action { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h2 class="mb-0 fw-bold text-dark">My Reservations</h2>
            <div>
                <button onclick="window.print()" class="btn btn-outline-dark me-2">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button onclick="generatePDF()" class="btn btn-danger">
                    <i class="bi bi-file-pdf"></i> PDF
                </button>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success shadow-sm mb-4 no-print alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i> Booking Successful!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-info shadow-sm mb-4 no-print alert-dismissible fade show">
                <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm" id="booking-content">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary">RESERVATION SUMMARY</h5>
                <small class="text-muted">ID: #USER-<?= $user_id ?></small>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-bordered mb-0">
                    <thead class="table-light text-uppercase small fw-bold">
                        <tr>
                            <th>Car Details</th>
                            <th>Dates</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings->num_rows > 0): ?>
                            <?php while ($row = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['brand']) ?> <?= htmlspecialchars($row['model']) ?></div>
                                    <small class="text-muted text-uppercase"><?= $row['payment_method'] ?></small>
                                </td>
                                <td>
                                    <div class="small">Pickup: <strong><?= date('d M Y', strtotime($row['pickup_date'])) ?></strong></div>
                                    <div class="small">Return: <strong><?= date('d M Y', strtotime($row['return_date'])) ?></strong></div>
                                </td>
                                <td class="fw-bold text-dark">₹ <?= number_format($row['total_price'], 2) ?></td>
                                <td>
                                    <?php
                                    $status = $row['status'] ?? 'Confirmed';
                                    $badgeClass = 'bg-success'; // Default
                                    if ($status == 'Cancelled')
                                        $badgeClass = 'bg-danger';
                                    if ($status == 'Returned')
                                        $badgeClass = 'bg-primary';
                                    if ($status == 'Pending')
                                        $badgeClass = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= strtoupper($status) ?></span>
                                </td>
                                <td class="no-print">
                                    <?php if ($status != 'Cancelled' && $status != 'Returned'): ?>
                                        <div class="btn-group">
                                            <a href="cancel_booking.php?id=<?= $row['id'] ?>&action=cancel" 
                                               class="btn btn-outline-danger btn-action" 
                                               onclick="return confirm('Cancel this booking?')">
                                               Cancel
                                            </a>
                                            <a href="cancel_booking.php?id=<?= $row['id'] ?>&action=return" 
                                               class="btn btn-outline-primary btn-action" 
                                               onclick="return confirm('Is the car returned?')">
                                               Return Car
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">No actions available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No booking history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-center py-3">
                <small class="text-muted">Generated on: <?= date('d M Y, H:i') ?> | PRO-CAR Rental Services</small>
            </div>
        </div>
        
        <div class="mt-4 no-print">
            <a href="index.php" class="btn btn-link px-0 text-decoration-none">
                <i class="bi bi-arrow-left"></i> Back to Fleet
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('booking-content');
            const opt = {
                margin:       0.5,
                filename:     'Booking_Summary_<?= date('Ymd') ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
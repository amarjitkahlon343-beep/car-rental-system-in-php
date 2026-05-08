<?php
session_start();
require_once 'connect.php';

// 1. Session and Security Check
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;

// 2. Fetch Car Data based on URL ID
$car_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$car = null;

if ($car_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
}

if (!$car) {
    header("Location: Admin_dashboard.php"); // Updated from index.php
    exit();
}

// Set default pricing for initial load (2 days)
$initial_days = 2;
$initial_total = ($car['price_per_day'] * $initial_days);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking | <?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --navy: #1e293b; --yellow: #facc15; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #fff; overflow-x: hidden; }
        
        .navbar-custom { background-color: var(--navy); padding: 15px 0; }
        .booking-sidebar { background-color: var(--navy); color: white; padding: 40px; min-height: 100vh; }
        .sidebar-title { border-bottom: 3px solid var(--yellow); display: inline-block; padding-bottom: 5px; margin-bottom: 25px; text-transform: uppercase; font-weight: bold; }
        .car-preview { border-radius: 8px; margin-bottom: 25px; width: 100%; border: 1px solid #334155; object-fit: contain; background: #fff; padding: 10px; }
        
        .form-section-title { border-bottom: 3px solid var(--yellow); display: inline-block; padding-bottom: 5px; margin-bottom: 30px; font-weight: 700; color: var(--navy); }
        .form-label { font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .form-control { border-radius: 4px; border: 1px solid #e2e8f0; padding: 12px; margin-bottom: 20px; background-color: #f8fafc; }
        .form-control:focus { border-color: var(--yellow); box-shadow: none; background-color: #fff; }
        
        .btn-confirm { background-color: var(--navy); color: white; font-weight: bold; padding: 15px 40px; border-radius: 4px; border: none; transition: 0.3s; width: 100%; }
        .btn-confirm:hover { background-color: #0f172a; color: var(--yellow); }

        #scanner_container { transition: all 0.5s ease; border: 2px dashed #e2e8f0; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom shadow-sm">
    <div class="container">
        <a class="navbar-brand text-white fw-bold fs-3" href="index.php">PRO-CAR Rental</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 p-lg-5 p-4">
            <h4 class="form-section-title">YOUR DETAILS</h4>
            
            <form action="process_booking.php" method="POST" id="bookingForm">
                <input type="hidden" name="user_id" value="<?= $current_user_id ?>">
                <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                <input type="hidden" name="total_price" id="total_price_input" value="<?= $initial_total ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required placeholder="First Name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required placeholder="Last Name">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address1" class="form-control" required placeholder="Street address">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" required placeholder="City">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" required placeholder="Phone Number">
                    </div>

                    <div class="col-md-6 mt-3">
                        <label class="form-label">Pick-up Date</label>
                        <input type="date" name="pickup_date" id="pickup_date" class="form-control" 
                               min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="form-label">Return Date</label>
                        <input type="date" name="return_date" id="return_date" class="form-control" 
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+2 days')) ?>" required>
                    </div>
                </div>

                <h4 class="form-section-title mt-5">PAYMENT METHOD</h4>
                <div class="card p-4 border-0 bg-light shadow-sm">
                    <div class="mb-3">
                        <label class="form-label">Select Payment Type</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="Cash">Cash on Pickup</option>
                            <option value="Card">Credit / Debit Card</option>
                            <option value="UPI">UPI / QR Scanner</option>
                        </select>
                    </div>

                    <div id="scanner_container" style="display: none;" class="text-center p-3 bg-white mt-2">
                        <h6 class="fw-bold">Scan to Pay Now</h6>
                        <img id="qr_code" src="" alt="QR Scanner" class="img-fluid mb-2" style="max-width: 180px;">
                        <p class="small text-muted mb-0">Scan with GPay, PhonePe, or Paytm</p>
                        <p class="fw-bold text-primary mt-1">Amount: ₹<span id="qr_amount_display">0</span></p>
                    </div>

                    <div id="card_details" style="display: none;" class="mt-2">
                        <div class="row">
                            <div class="col-md-12 mb-2"><input type="text" class="form-control" placeholder="Card Number"></div>
                            <div class="col-6"><input type="text" class="form-control" placeholder="MM/YY"></div>
                            <div class="col-6"><input type="password" class="form-control" placeholder="CVV"></div>
                        </div>
                    </div>
                </div>

                <h4 class="form-section-title mt-5">ADD EXTRAS</h4>
                <div class="card p-3 border-0 bg-light">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="insurance" id="extra_insurance">
                        <label class="form-check-label fw-bold" for="extra_insurance">Premium Insurance (₹500 / day)</label>
                        <small class="d-block text-muted">Full coverage for total peace of mind.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-confirm mt-5">CONFIRM MY RESERVATION</button>
            </form>
        </div>

        <div class="col-lg-4 booking-sidebar">
            <h4 class="sidebar-title">BOOKING DETAILS</h4>
            <div class="text-center">
                <img src="images/<?= htmlspecialchars($car['image']) ?>" class="car-preview shadow-sm" alt="Car">
            </div>
            <h5 class="fw-bold mb-1 text-uppercase letter-spacing-1"><?= htmlspecialchars($car['brand']) ?></h5>
            <h4 class="mb-4 text-warning"><?= htmlspecialchars($car['model']) ?></h4>
            
            <div class="booking-summary mt-4">
                <div class="d-flex justify-content-between border-bottom border-secondary py-3">
                    <span class="text-secondary">Pick-up:</span>
                    <span id="display_pickup" class="fw-bold"><?= date('M d, Y') ?></span>
                </div>
                <div class="d-flex justify-content-between border-bottom border-secondary py-3">
                    <span class="text-secondary">Return:</span>
                    <span id="display_return" class="fw-bold"><?= date('M d, Y', strtotime('+2 days')) ?></span>
                </div>
                <div class="d-flex justify-content-between py-3">
                    <span class="text-secondary">Duration:</span>
                    <span class="fw-bold"><span id="display_days">2</span> Day(s)</span>
                </div>
            </div>
            
            <div class="mt-5 p-4 bg-dark rounded-3 border border-secondary">
                <small class="text-secondary d-block mb-1">Total Price</small>
                <h2 class="text-warning fw-bold mb-0">₹ <span id="display_total"><?= number_format($initial_total) ?></span></h2>
            </div>
        </div>
    </div>
</div>

<script>
const dailyRate = <?= intval($car['price_per_day']) ?>;
const upiID = "yourname@bank"; // REPLACE THIS WITH YOUR UPI ID

function updateSummary() {
    const pickupVal = document.getElementById('pickup_date').value;
    const returnVal = document.getElementById('return_date').value;
    if(!pickupVal || !returnVal) return;

    const pickupDate = new Date(pickupVal);
    const returnDate = new Date(returnVal);
    const insurance = document.getElementById('extra_insurance').checked;
    
    let days = Math.ceil((returnDate - pickupDate) / (1000 * 3600 * 24));
    if (days < 1) days = 1;

    let total = (dailyRate * days) + (insurance ? (500 * days) : 0);

    // Update Sidebar
    document.getElementById('display_pickup').innerText = pickupDate.toLocaleDateString();
    document.getElementById('display_return').innerText = returnDate.toLocaleDateString();
    document.getElementById('display_days').innerText = days;
    document.getElementById('display_total').innerText = total.toLocaleString();
    
    // Update Hidden Input and QR Data
    document.getElementById('total_price_input').value = total;
    document.getElementById('qr_amount_display').innerText = total.toLocaleString();
    
    if(document.getElementById('payment_method').value === 'UPI') {
        const qrContent = `upi://pay?pa=${upiID}&pn=ProCarRental&am=${total}&cu=INR`;
        document.getElementById('qr_code').src = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(qrContent)}`;
    }
}

document.getElementById('payment_method').addEventListener('change', function() {
    document.getElementById('scanner_container').style.display = (this.value === 'UPI') ? 'block' : 'none';
    document.getElementById('card_details').style.display = (this.value === 'Card') ? 'block' : 'none';
    updateSummary();
});

document.getElementById('pickup_date').addEventListener('change', updateSummary);
document.getElementById('return_date').addEventListener('change', updateSummary);
document.getElementById('extra_insurance').addEventListener('change', updateSummary);
window.onload = updateSummary;
</script>

</body>
</html>
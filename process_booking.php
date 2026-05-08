<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize data
    $user_id = intval($_POST['user_id']);
    $car_id = intval($_POST['car_id']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $address1 = $conn->real_escape_string($_POST['address1']);
    $city = $conn->real_escape_string($_POST['city']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $pickup = $_POST['pickup_date'];
    $return = $_POST['return_date'];
    $total = floatval($_POST['total_price']);
    $payment_method = $_POST['payment_method'];

    // Logic: If Cash, status is Pending. If Card/UPI, consider it Paid for now.
    $payment_status = ($payment_method == 'Cash') ? 'Pending' : 'Paid';

    $conn->begin_transaction();

    try {
        // 1. Check stock
        $check_stock = $conn->prepare("SELECT stock FROM cars WHERE id = ? FOR UPDATE");
        $check_stock->bind_param("i", $car_id);
        $check_stock->execute();
        $res = $check_stock->get_result();
        $car = $res->fetch_assoc();

        if (!$car || $car['stock'] <= 0) {
            throw new Exception("Sorry, this car is no longer available!");
        }

        // 2. Insert the booking
        $sql = "INSERT INTO bookings (user_id, car_id, first_name, last_name, address1, city, phone, pickup_date, return_date, total_price, payment_method, payment_status, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssssdss", $user_id, $car_id, $first_name, $last_name, $address1, $city, $phone, $pickup, $return, $total, $payment_method, $payment_status);

        // Execute the insertion
        $booking_done = $stmt->execute();

        // 3. Decrease stock
        $update_stock = $conn->prepare("UPDATE cars SET stock = stock - 1 WHERE id = ?");
        $update_stock->bind_param("i", $car_id);
        $update_stock->execute();

        // 4. Commit and Redirect
        if ($booking_done) {
            $conn->commit();
            // This is where we send them to profile.php
            header("Location: profile.php?msg=Booking Confirmed Successfully!");
            exit();
        } else {
            throw new Exception("Execution failed.");
        }

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "'); window.location='Admin_dashboard.php';</script>";
    }
} else {
    header("Location: Admin_dashboard.php");
    exit();
}
?>
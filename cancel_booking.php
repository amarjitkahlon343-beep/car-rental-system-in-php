<?php
session_start();
require_once 'connect.php';

// 1. Security Check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // 2. Fetch the booking to make sure it belongs to this user and get the car_id
    $stmt = $conn->prepare("SELECT car_id, status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    // 3. Only allow cancellation if the booking exists and isn't already cancelled
    if ($booking && $booking['status'] !== 'Cancelled') {
        $car_id = $booking['car_id'];

        // Start Transaction
        $conn->begin_transaction();

        try {
            // A. Update booking status
            $update_booking = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
            $update_booking->bind_param("i", $booking_id);
            $update_booking->execute();

            // B. Restore car stock (+1)
            $update_stock = $conn->prepare("UPDATE cars SET stock = stock + 1 WHERE id = ?");
            $update_stock->bind_param("i", $car_id);
            $update_stock->execute();

            $conn->commit();
            header("Location: profile.php?msg=Booking Cancelled Successfully");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Error processing cancellation.'); window.location='profile.php';</script>";
        }
    } else {
        header("Location: profile.php");
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}
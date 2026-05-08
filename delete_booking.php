<?php
session_start();
require_once 'connect.php';

// Security Check: User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// CASE 1: Delete a SINGLE cancelled booking
if (isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);

    // Security: Only delete if it belongs to the user AND is 'Cancelled'
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ? AND status = 'Cancelled'");
    $stmt->bind_param("ii", $booking_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $msg = "Record deleted successfully.";
    } else {
        $msg = "Error: Record not found or not cancelled.";
    }
}
// CASE 2: Delete ALL cancelled bookings (Clear All)
else if (isset($_GET['action']) && $_GET['action'] == 'clear_all') {
    $stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ? AND status = 'Cancelled'");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $count = $stmt->affected_rows;
        $msg = ($count > 0) ? "$count cancelled records cleared." : "No cancelled records to clear.";
    } else {
        $msg = "Error clearing records.";
    }
}

// Redirect back to profile with the result message
header("Location: profile.php?msg=" . urlencode($msg));
exit();
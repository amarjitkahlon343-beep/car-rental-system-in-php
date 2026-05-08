<?php
include('connect.php');
// If not logged in OR not an admin, redirect to home
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 1. Get the image filename first so we can delete the file from the folder
    $query = "SELECT image FROM cars WHERE id = $id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = "images/" . $row['image'];

        // 2. Delete the physical image file from the /images/ folder
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // 3. Delete the record from the database
        $sql = "DELETE FROM cars WHERE id = $id";

        if ($conn->query($sql)) {
            echo "<script>alert('Car deleted successfully!'); window.location='admin_cars.php';</script>";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Car not found.";
    }
} else {
    header("Location: admin_cars.php");
}
?>
<?php
session_start();
include('includes/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username    = $_SESSION['user_name'];
    $temple      = $_POST['temple'] ?? '';
    $pass_id     = $_POST['pass_id'] ?? '';
    $full_name   = $_POST['full_name'] ?? '';
    $age         = intval($_POST['age'] ?? 0);
    $mobile      = $_POST['mobile'] ?? '';
    $aadhaar     = $_POST['aadhaar'] ?? '';
    $pass_count  = intval($_POST['pass_count'] ?? 1);
    $booking_date= $_POST['booking_date'] ?? '';
    $time_slot   = $_POST['time_slot'] ?? '';

    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO bookings 
        (username, temple, pass_id, full_name, age, mobile, aadhaar, pass_count, booking_date, time_slot) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if(!$stmt){
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    // Bind parameters: s = string, i = integer
    $stmt->bind_param(
        "ssssiissss", 
        $username, 
        $temple, 
        $pass_id, 
        $full_name, 
        $age, 
        $mobile, 
        $aadhaar, 
        $pass_count, 
        $booking_date, 
        $time_slot
    );

    // Execute and respond
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
}
?>

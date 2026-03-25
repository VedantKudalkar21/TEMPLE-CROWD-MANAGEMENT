<?php
header("Content-Type: application/json");
include 'includes/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO donations 
(full_name, address, city, state, zipcode, country, email, mobile_no, aadhaar_no, donation_amount)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sssssssssd",
    $data['fullName'],
    $data['address'],
    $data['city'],
    $data['state'],
    $data['zipcode'],
    $data['country'],
    $data['email'],
    $data['mobileNo'],
    $data['aadhaarNo'],
    $data['donationAmount']
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

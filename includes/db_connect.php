<?php
$servername = "localhost";
$username = "root"; // default in XAMPP
$password = ""; // leave blank
$dbname = "temple_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

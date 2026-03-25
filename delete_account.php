<?php
session_start();
include('includes/db_connect.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['delete'])){
    $password = $_POST['password'];

    // Fetch user details
    $sql = "SELECT * FROM users WHERE id = '$user_id'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Verify password
        if(password_verify($password, $hashedPassword)){
            // Delete user's bookings first
            $conn->query("DELETE FROM bookings WHERE user_id = '$user_id'");

            // Then delete user account
            $deleteUser = "DELETE FROM users WHERE id = '$user_id'";
            if($conn->query($deleteUser)){
                session_destroy();
                echo "<script>alert('Your account has been deleted successfully!'); window.location='register.php';</script>";
            } else {
                echo "<script>alert('Error deleting account. Please try again.'); window.location='dashboard.php';</script>";
            }
        } else {
            echo "<script>alert('Incorrect password. Please try again.'); window.location='dashboard.php';</script>";
        }
    }
}
?>

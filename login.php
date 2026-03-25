<?php
include('includes/db_connect.php');
session_start();

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name']; 
            $_SESSION['user_email'] = $row['email']; // ✅ Add this line

            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid password!');</script>";
        }
    } else {
        echo "<script>alert('No user found with this email!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Temple Crowd Management</title>

<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <div class="decorative-pattern"></div>
    <div class="form-card">
        <h2>🛕 Temple Crowd Management</h2>
        <p class="subtitle">Login to Your Account</p>

        <?php
        if (isset($error)) {
            echo "<p class='error-message'>$error</p>";
        }
        ?>

        <form method="POST" autocomplete="off">
            <label>Email</label>
            <input type="email" name="email" placeholder="example@email.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter Password" required>

            <button type="submit" name="login">Login</button>

            <p class="bottom-text">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </form>
    </div>
</div>
</body>
</html>
<?php
session_start();
include('../includes/db_connect.php'); // ✅ Correct Path

if (isset($_POST['admin_login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ✅ Admin Table
    $sql = "SELECT * FROM admin WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ✅ If password not hashed
        if ($password == $row['password']) {

            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['name']; // ✅ Correct session variable
            $_SESSION['admin_email'] = $row['email'];

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "❌ Incorrect Password!";
        }
    } else {
        $error = "❌ Admin Account Not Found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - Temple Management</title>

<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;700&display=swap" rel="stylesheet">

<style>
<?php echo file_get_contents("../assets/style.css"); ?> 
</style>

</head>
<body>

<div class="container">
    <div class="decorative-pattern"></div>

    <div class="form-card">

        <h2>🛕 Temple Admin Login</h2>
        <p class="subtitle">Access Control Panel</p>

        <?php if(isset($error)){ ?>
            <div class="error-message"><p><?php echo $error; ?></p></div>
        <?php } ?>

        <form method="POST" autocomplete="off">
            
            <label>Email</label>
            <input type="email" name="email" placeholder="admin@email.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter Admin Password" required>

            <button type="submit" name="admin_login">Login</button>

            <p class="bottom-text" style="margin-top:10px;">
                ← Back to User Login? <a href="../login.php">Click Here</a>
            </p>

        </form>

    </div>
</div>

</body>
</html>

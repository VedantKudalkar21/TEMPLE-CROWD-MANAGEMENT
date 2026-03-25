<?php
include('includes/db_connect.php');

if (isset($_POST['register'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $phone    = trim($_POST['phone']);

    // SERVER-SIDE PASSWORD VALIDATION
    $passwordPattern = "/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($passwordPattern, $password)) {
        echo "<script>alert('Password must be at least 8 characters long, contain one uppercase letter, one number, and one special character.'); window.history.back();</script>";
        exit();
    }

    // Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Use prepared statements to prevent SQL injection
    $sql = "INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $phone);

    if ($stmt->execute()) {
        echo "<script>alert('Registration Successful! You can now login.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Temple Crowd Management</title>

<!-- Indian Font -->
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;700&display=swap" rel="stylesheet">

<!-- CSS -->
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <div class="decorative-pattern"></div>
    <div class="form-card">
        <h2>🛕 Temple Crowd Management</h2>
        <p class="subtitle">Register Your Account</p>

        <form method="POST" autocomplete="off" onsubmit="return validatePassword();">
            <label>Name</label>
            <input type="text" name="name" placeholder="Your Full Name" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="example@email.com" required>

            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Enter Password" required>
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
            </div>

            <!-- Password Strength Indicator -->
            <div class="password-strength"><span></span></div>
            <p class="password-status" id="passwordStatus">Enter a secure password</p>

            <p class="password-note">
                Password must be at least 8 characters long. It should include at least one uppercase letter, one number, and one special symbol.
            </p>

            <label>Phone</label>
            <input type="text" name="phone" placeholder="Optional">

            <button type="submit" name="register">Register</button>

            <p class="bottom-text">
                Already registered? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>
</div>

<!-- CLIENT-SIDE VALIDATION & STRENGTH BAR -->
<script>
function validatePassword() {
    const password = document.getElementById('password').value;
    const pattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!pattern.test(password)) {
        alert("Password must be at least 8 characters long, include one uppercase letter, one number, and one special symbol.");
        return false;
    }
    return true;
}

// Password strength indicator logic
const passwordInput = document.getElementById('password');
const strengthBar = document.querySelector('.password-strength span');
const passwordStatus = document.getElementById('passwordStatus');

passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    let strength = 0;

    if (val.match(/[A-Z]/)) strength++;
    if (val.match(/[0-9]/)) strength++;
    if (val.match(/[@$!%*?&]/)) strength++;
    if (val.length >= 8) strength++;

    const bar = document.querySelector('.password-strength');
    bar.className = 'password-strength'; // reset

    if (strength === 0) {
        strengthBar.style.width = "0";
        passwordStatus.textContent = "Enter a secure password";
        passwordStatus.style.color = "#555";
    } else if (strength <= 1) {
        bar.classList.add('weak');
        passwordStatus.textContent = "Weak Password";
        passwordStatus.style.color = "#e74c3c";
    } else if (strength === 2 || strength === 3) {
        bar.classList.add('medium');
        passwordStatus.textContent = "Medium Strength";
        passwordStatus.style.color = "#f1c40f";
    } else {
        bar.classList.add('strong');
        passwordStatus.textContent = "Strong Password";
        passwordStatus.style.color = "#2ecc71";
    }
});

// Toggle password visibility
function togglePassword() {
    const passwordField = document.getElementById('password');
    if (passwordField.type === "password") {
        passwordField.type = "text";
    } else {
        passwordField.type = "password";
    }
}
</script>
</body>
</html>

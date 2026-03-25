<?php
include('includes/db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'unknown@example.com'; // Use session email if available

// --- NEW: Fetch Temples from Database ---
$temples_from_db = []; // Initialize an empty array
$sql_fetch_temples = "SELECT temple_name FROM temples ORDER BY temple_name ASC";
$result_fetch_temples = mysqli_query($conn, $sql_fetch_temples);

if ($result_fetch_temples) {
    while ($row = mysqli_fetch_assoc($result_fetch_temples)) {
        $temples_from_db[] = $row['temple_name'];
    }
} else {
    error_log("Error fetching temples for complaint form: " . mysqli_error($conn));
    // Optional: Set a flag or message to inform the user if temples can't be loaded
}
// --- END: Fetch Temples ---

// Handle complaint submission
if (isset($_POST['submit'])) {
    $temple = $_POST['temple'];
    $complaint = $_POST['complaint'];

    // --- Security Enhancement: Use Prepared Statements ---
    $sql = "INSERT INTO complaints (user_id, name, email, temple, complaint_text, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind parameters (i=integer, s=string)
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $user_name, $user_email, $temple, $complaint);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Complaint registered successfully!'); window.location='dashboard.php';</script>";
            exit(); // Exit after successful submission and redirect
        } else {
            // Provide a user-friendly error, log the detailed error
            echo "<script>alert('Error registering complaint. Please try again.');</script>";
            error_log("Error executing complaint insert: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        // Provide a user-friendly error, log the detailed error
        echo "<script>alert('Database error. Please try again later.');</script>";
        error_log("Error preparing complaint insert: " . mysqli_error($conn));
    }
    // --- End Prepared Statements ---
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Complaint - Mandir Mitra</title> <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    /* Using your provided styles - Minimal adjustments for consistency */
    :root {
      --primary-color: #ff9933;
      --secondary-color: #d35400;
      --text-color: #5d4037;
      --border-color: #e0b080;
      --bg-gradient-start: #ffe5b4;
      --bg-gradient-end: #fff1e6;
      --card-bg: #fff8e1;
    }
    body {
      font-family: 'Baloo 2', cursive;
      background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
      color: var(--text-color);
      display: flex; /* Added */
      justify-content: center; /* Added */
      align-items: center; /* Added */
      min-height: 100vh; /* Added */
      padding: 20px; /* Added */
      margin: 0; /* Added */
    }
    .form-card {
      background: #fff;
      max-width: 500px; /* Adjusted width */
      width: 100%; /* Responsive width */
      /* margin: 80px auto; Removed margin */
      padding: 35px 40px; /* Adjusted padding */
      border-radius: 15px;
      box-shadow: 0 6px 25px rgba(0,0,0,0.1); /* Adjusted shadow */
      text-align: center; /* Center content */
    }
    h2 {
      color: var(--secondary-color); /* Use theme color */
      margin-top: 0;
      margin-bottom: 10px;
    }
    p.welcome-text { /* Added class for welcome message */
        font-size: 1.1em;
        margin-bottom: 25px;
        color: var(--text-light);
    }
    p.welcome-text strong {
        color: var(--text-color);
    }
    label {
        display: block; /* Make labels block level */
        text-align: left; /* Align labels left */
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 1.05em;
        color: var(--text-color);
    }
    select, textarea {
      width: 100%;
      padding: 12px 15px; /* Adjusted padding */
      margin-bottom: 20px; /* Increased margin */
      border: 1px solid var(--border-color); /* Use theme color */
      border-radius: 8px;
      font-family: inherit; /* Inherit font */
      font-size: 1em; /* Adjust font size */
      background-color: #fff; /* Ensure background is white */
    }
    textarea {
        resize: vertical; /* Allow vertical resize */
        min-height: 120px; /* Set min height */
    }
    button {
      margin-top: 15px;
      padding: 12px 25px; /* Adjusted padding */
      background-color: var(--primary-color); /* Use theme color */
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-family: inherit; /* Inherit font */
      font-size: 1.1em; /* Adjust font size */
      font-weight: 700;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: var(--secondary-color); /* Use theme color */
    }
    /* Simple header */
    .simple-header {
        position: absolute;
        top: 20px;
        left: 20px;
        font-size: 1.5em;
        font-weight: 700;
        color: var(--secondary-color);
        text-decoration: none;
    }
     .simple-header a:hover {
        color: var(--primary-color);
    }

</style>
</head>
<body>

<div class="simple-header">
    <a href="dashboard.php" style="text-decoration:none; color: inherit;">🛕 Mandir Mitra</a>
</div>

<div class="form-card">
    <h2>🚨 Register a Complaint</h2>
    <p class="welcome-text">Submit your feedback or report an issue, <strong><?php echo htmlspecialchars($user_name); ?></strong>.</p>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"> <label for="temple">Select Temple</label>
        <select id="temple" name="temple" required>
            <option value="">-- Select Temple --</option>
            <?php foreach ($temples_from_db as $templeName): ?>
                <option value="<?php echo htmlspecialchars($templeName); ?>">
                    <?php echo htmlspecialchars($templeName); ?> Temple
                </option>
            <?php endforeach; ?>
            <?php if (empty($temples_from_db)): ?>
                <option value="" disabled>Could not load temples</option>
            <?php endif; ?>
            </select>

        <label for="complaint">Your Complaint</label>
        <textarea id="complaint" name="complaint" rows="5" placeholder="Please describe the issue in detail..." required></textarea>

        <button type="submit" name="submit">Submit Complaint</button>
    </form>
</div>
</body>
</html>
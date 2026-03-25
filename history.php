<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user_name'];

// Fetch all bookings for the logged-in user
$result = $conn->query("SELECT * FROM bookings WHERE username='$user' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking History | Mandir Mitra</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: "Baloo 2", cursive;
        background: linear-gradient(135deg, #fff3e0, #ffe0b2);
        margin: 0;
        padding: 30px;
    }
    h2 {
        text-align: center;
        color: #ff9933;
        font-size: 28px;
        margin-bottom: 30px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff8e1;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px 15px;
        text-align: center;
        border-bottom: 1px solid #ffd8a8;
        font-weight: 500;
    }
    th {
        background-color: #ffb74d;
        color: white;
        font-size: 16px;
    }
    tr:last-child td {
        border-bottom: none;
    }
    tr:hover {
        background-color: #ffe8c6;
    }
    .back-btn {
        display: block;
        width: max-content;
        margin: 20px auto;
        padding: 10px 20px;
        background-color: #ff9933;
        color: white;
        text-decoration: none;
        font-weight: 600;
        border-radius: 10px;
        transition: background-color 0.3s ease;
    }
    .back-btn:hover {
        background-color: #e68a00;
    }
</style>
</head>
<body>

<h2>📜 Your Booking History</h2>

<?php if($result->num_rows > 0): ?>
<table>
    <tr>
        <th>Pass ID</th>
        <th>Temple</th>
        <th>Pilgrim Name</th>
        <th>Age</th>
        <th>Mobile</th>
        <th>Date</th>
        <th>Time Slot</th>
        <th>Pass Count</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['pass_id']); ?></td>
        <td><?php echo htmlspecialchars($row['temple']); ?></td>
        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
        <td><?php echo htmlspecialchars($row['age']); ?></td>
        <td><?php echo htmlspecialchars($row['mobile']); ?></td>
        <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
        <td><?php echo htmlspecialchars($row['time_slot']); ?></td>
        <td><?php echo htmlspecialchars($row['pass_count']); ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center; color:#6d4c41; font-size:18px;">You have no bookings yet. Start booking your darshan now! 🛕</p>
<?php endif; ?>

<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

</body>
</html>

<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}
$user = ucfirst($_SESSION['user_name']);

// Handle account deletion
if (isset($_POST['delete_account'])) {
    $email = $_SESSION['user_email'];
    $delete_sql = "DELETE FROM users WHERE email='$email'";
    if ($conn->query($delete_sql) === TRUE) {
        session_destroy();
        echo "<script>alert('Account deleted successfully.'); window.location='register.php';</script>";
    } else {
        echo "<script>alert('Error deleting account.');</script>";
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// --- NEW: Fetch Temples from Database ---
$temples_from_db = []; // Initialize an empty array
$sql_fetch_temples = "SELECT temple_name FROM temples ORDER BY temple_name ASC"; // Your temples table and column name
$result_fetch_temples = mysqli_query($conn, $sql_fetch_temples);

if ($result_fetch_temples) {
    while ($row = mysqli_fetch_assoc($result_fetch_temples)) {
        $temples_from_db[] = $row['temple_name']; // Add temple name to the array
    }
} else {
    // Optional: Log an error if the query fails, but don't stop the page
    error_log("Error fetching temples from database: " . mysqli_error($conn));
}
// --- END: Fetch Temples ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Temple Dashboard | Mandir Mitra</title>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: "Baloo 2", cursive;
      background: linear-gradient(135deg, #fff3e0, #ffe0b2);
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    header {
      width: 100%;
      background: #ff9933;
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 18px 50px;
      font-size: 20px;
      font-weight: 700;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
      box-sizing: border-box;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 20px;
      font-size: 16px;
    }

    .user-buttons form {
      display: inline-block;
    }

    .user-buttons button {
      background: #fff;
      border: none;
      padding: 8px 14px;
      border-radius: 8px;
      cursor: pointer;
      font-family: inherit;
      font-weight: 600;
      transition: 0.3s;
    }

    .logout-btn {
      color: #9c27b0;
    }

    .delete-btn {
      color: #c62828;
    }

    .user-buttons button:hover {
      background: #ffe0b2;
      transform: scale(1.05);
    }

    .dashboard-container {
      width: 95%;
      max-width: 1350px;
      margin: auto;
      padding: 40px 0 60px;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-sizing: border-box;
    }

    /* Temple Selector */
    .temple-select {
      margin: 30px 0 20px;
      text-align: center;
    }

    .temple-select label {
      font-weight: 600;
      color: #4a2c0a;
      font-size: 18px;
    }

    select {
      margin-left: 10px;
      padding: 10px 15px;
      border-radius: 10px;
      border: 2px solid #ff9933;
      background: #fff8e1;
      font-family: inherit;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      transition: 0.3s;
    }

    select:hover {
      background: #ffecd1;
    }

    .temple-greeting {
      font-size: 23px;
      font-weight: 600;
      color: #5d4037;
      margin-bottom: 35px;
      transition: opacity 0.5s ease;
    }

    /* Cards Grid */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 35px;
      width: 100%;
      justify-content: center;
    }

    .card {
      background: #fff8e1;
      padding: 40px 30px;
      border-radius: 22px;
      text-align: center;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .card h3 {
      color: #4a2c0a;
      font-size: 20px;
      margin-bottom: 12px;
    }

    .card p {
      font-size: 16px;
      color: #6d4c41;
      margin-bottom: 18px;
    }

    .card button {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      background-color: #7b1fa2;
      color: white;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .card button:hover {
      background-color: #4a148c;
    }

    /* Colorful backgrounds */
    .yellow { background-color: #fff3b0; }
    .green { background-color: #d6f8d6; }
    .pink { background-color: #ffe0e9; }
    .lavender { background-color: #f3e8ff; }
    .gold { background-color: #fff5cc; }

    footer {
      margin-top: auto;
      padding: 15px;
      background-color: #ff9933;
      color: #fff;
      width: 100%;
      text-align: center;
      font-weight: 500;
      font-size: 14px;
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
        padding: 15px;
      }
      .user-info {
        flex-direction: column;
        gap: 8px;
      }
      .cards {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      .card {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>

  <header>
    <div class="header-left">
      <span>🛕 Mandir Mitra</span>
    </div>

    <div class="user-info">
      <span>👤 Logged in as: <?php echo $user; ?></span>
      <div class="user-buttons">
        <form method="POST"><button class="logout-btn" name="logout">Logout</button></form>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account permanently?');">
          <button class="delete-btn" name="delete_account">Delete</button>
        </form>
      </div>
    </div>
  </header>

  <div class="dashboard-container">
    <div class="temple-select">
      <label for="temple">Select Temple:</label>
      <select id="temple" name="temple">
        <option value="">-- Choose Temple --</option>
        <?php foreach ($temples_from_db as $templeName): ?>
            <option value="<?php echo htmlspecialchars($templeName); ?>">
                <?php echo htmlspecialchars($templeName); ?> Temple 
            </option>
        <?php endforeach; ?>
        <?php if (empty($temples_from_db)): ?>
            <option value="" disabled>No temples available</option>
        <?php endif; ?>
         </select>
  </div>

    <div class="temple-greeting" id="templeGreeting">Please select a temple to begin your Darshan 🌼</div>

    <div class="cards">
      <div class="card yellow">
        <h3>📜 Digital Pass Booking</h3>
        <p>Book your Darshan entry slot easily.</p>
        <button id="bookNowBtn">Book Now</button>
      </div>

      <div class="card green">
        <h3>👥 Real-time Crowd Prediction</h3>
        <p>Check live status to avoid heavy rush.</p>
        <button id="liveStatusBtn">Check Live Status</button>
      </div>

      <div class="card pink">
        <h3>🕒 Temple Timings & Waiting</h3>
        <p>Get up-to-date timings and queue info.</p>
        <button id="TimingBtn">Check Details</button>
      </div>

      <div class="card yellow">
        <h3>♿ PwD & Senior Citizen Services</h3>
        <p>Priority access and easy routes for all devotees.</p>
        <button id="priorityBtn">Priority Access</button>
      </div>

      <div class="card lavender">
        <h3>📺 Live Darshan & Aarti Broadcast</h3>
        <p>Experience the temple rituals live.</p>
        <button id="LiveBtn">Watch Live</button>
      </div>

      <div class="card gold">
        <h3>💰 Sewa & Donations</h3>
        <p>Contribute to temple activities and charity.</p>
        <button id="DonateBtn">Donate Now</button>
      </div>

      <div class="card green">
        <h3>🍛Prasadam / Food Token</h3>
        <p>Receive a token for complimentary Prasadam.</p>
        <button id="foodtokenBtn">Food Tokken</button>
      </div>

      <div class="card pink">
        <h3>📜History & Significance</h3>
        <p>Learn about the temple's rich spiritual past.</p>
        <button id="HistoryBtn">History & Significance</button>
      </div>

      <div class="card gold">
        <h3>🌏360° Temple View</h3>
        <p>Take a virtual tour of the premises.</p>
        <button id="view360Btn">360° Temple View</button>
      </div>

      <div class="card pink">
        <h3>🚦Traffic & Mobility Management</h3>
        <p>Parking and shuttle service info.</p>
        <button id="TrafficBtn">Traffic & Mobility Management</button>
      </div>

      <div class="card gold">
        <h3>🕰️Your Booking History</h3>
        <p>See your past bookings info.</p>
        <button id="yourHistory">Your Booking History</button>
      </div>

      <div class="card pink">
        <h3>🚨Register a Complaint</h3>
        <p>Report lost items or general issues.</p>
        <button id="registerComplainBtn">Register a Complaint</button>
      </div>
    </div>
  </div>

  <footer>© 2025 Mandir Mitra | Designed with devotion 🪔</footer>

  <script>
    const templeSelect = document.getElementById('temple');
    const templeGreeting = document.getElementById('templeGreeting');

    templeSelect.addEventListener('change', () => {
      const selectedTemple = templeSelect.value;
      if (selectedTemple) {
        templeGreeting.style.opacity = '0';
        setTimeout(() => {
          templeGreeting.textContent = `Welcome to ${selectedTemple} Temple 🛕`;
          templeGreeting.style.opacity = '1';
        }, 300);
      } else {
        templeGreeting.textContent = 'Please select a temple to begin your Darshan 🌼';
      }
    });
  
    document.getElementById("bookNowBtn").addEventListener("click", () => {
        const selectedTemple = document.getElementById('temple').value;
        if (!selectedTemple) {
            alert('Please select a temple first!');
            return;
        }
        // Pass temple via URL parameter
        window.location.href = "booking.php?temple=" + encodeURIComponent(selectedTemple);
    });

    document.getElementById("liveStatusBtn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "realcrowd_prediction.php?temple=" + encodeURIComponent(selectedTemple);
    });
 
    document.getElementById("yourHistory").addEventListener("click", () => {
    window.location.href = "history.php?page=history";
    });

    document.getElementById("registerComplainBtn").addEventListener("click", () => {
    window.location.href = "register_complain.php?page=register_complain";
    });

    document.getElementById("foodtokenBtn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "food_token.php?temple=" + encodeURIComponent(selectedTemple);
    });

    document.getElementById("priorityBtn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "pwd_service.php?temple=" + encodeURIComponent(selectedTemple);
    });
    
    document.getElementById("view360Btn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "temple_360view.php?temple=" + encodeURIComponent(selectedTemple);
    });

    document.getElementById("LiveBtn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "live_broadcast.php?temple=" + encodeURIComponent(selectedTemple);
    });

    document.getElementById("DonateBtn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "donation.php?temple=" + encodeURIComponent(selectedTemple);
    });

    document.getElementById("TrafficBtn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "traffic_management.php?temple=" + encodeURIComponent(selectedTemple);
    });

    document.getElementById("TimingBtn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "temple_timings.php?temple=" + encodeURIComponent(selectedTemple);
    });

    document.getElementById("HistoryBtn").addEventListener("click", () => {
      const selectedTemple = templeSelect.value;
      if (!selectedTemple) {
        alert('Please select a temple first!');
        return;
      }
      window.location.href = "hist&sign.php?temple=" + encodeURIComponent(selectedTemple);
    });
  </script>
</body>
</html>

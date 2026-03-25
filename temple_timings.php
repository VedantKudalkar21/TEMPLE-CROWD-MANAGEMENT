<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}
$user = ucfirst($_SESSION['user_name']);

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// --- Temple Data Array ---
$temple_data = [
    "Somnath" => [
        "name" => "Somnath Temple",
        "timings" => [
            "Darshan" => "6:00 AM - 10:00 PM"
        ],
        "aarti" => [
            "Morning Aarti" => "7:00 AM",
            "Noon Aarti" => "12:00 PM",
            "Evening Aarti" => "7:00 PM"
        ],
        "notes" => [
            "Mobile phones, cameras, and all electronic devices are strictly not allowed inside the temple. Free locker facilities are available.",
            "'Jay Somnath' Light and Sound Show: 8:00 PM - 9:00 PM (Daily, except in monsoon)."
        ],
        "open_intervals" => [["06:00", "22:00"]]
    ],
    "Dwarka" => [
        "name" => "Dwarkadhish Temple (Jagat Mandir)",
        "timings" => [
            "Morning Darshan" => "6:30 AM - 1:00 PM",
            "Temple Closed" => "1:00 PM - 5:00 PM",
            "Evening Darshan" => "5:00 PM - 9:30 PM"
        ],
        "aarti" => [
            "Mangla Aarti" => "6:30 AM",
            "Shayan Aarti" => "8:30 PM"
        ],
        "notes" => [
            "The temple closes in the afternoon. Please plan your visit accordingly.",
            "Modest attire is required. Photography is prohibited inside the main temple.",
            "56 steps (Chhappan Sidhi) lead to the temple's Swarga Dwar."
        ],
        "open_intervals" => [["06:30", "13:00"], ["17:00", "21:30"]]
    ],
    "Ambaji" => [
        "name" => "Ambaji Temple",
        "timings" => [
            "Morning Darshan" => "7:30 AM - 11:30 AM",
            "Afternoon Darshan" => "12:30 PM - 4:30 PM",
            "Evening Darshan" => "6:30 PM - 9:00 PM"
        ],
        "aarti" => [
            "Morning Aarti" => "7:30 AM",
            "Evening Aarti" => "6:30 PM"
        ],
        "notes" => [
            "The temple schedule changes frequently based on festivals and seasons. These are the general timings.",
            "The 'Gabbar Hill' (original seat of the Goddess) is nearby and can be reached by ropeway."
        ],
        "open_intervals" => [["07:30", "11:30"], ["12:30", "16:30"], ["18:30", "21:00"]]
    ],
    "Pavagadh" => [
        "name" => "Pavagadh Temple",
        "timings" => [
            "Temple Darshan" => "5:00 AM - 7:00 PM"
        ],
        "aarti" => [
            "Mangla Aarti" => "5:00 AM",
            "Evening Aarti" => "6:30 PM"
        ],
        "notes" => [
            "The temple is located on a hill. You must park at Manchi and take the 'Udankhatola' (Ropeway/Cable Car) to the top.",
            "Ropeway Timings: 6:00 AM - 6:00 PM (Timings may vary).",
            "After the ropeway, there are ~250 steps to the main temple. Doli (palanquin) services are available."
        ],
        "open_intervals" => [["05:00", "19:00"]]
    ]
];

// --- NEW: Get temple name from URL ---
$selected_temple_key = null;
$selected_temple_name = "Temple"; // Default title
if (isset($_GET['temple']) && !empty($_GET['temple'])) {
    $temple_key = $_GET['temple'];
    if (array_key_exists($temple_key, $temple_data)) {
        $selected_temple_key = $temple_key;
        $selected_temple_name = $temple_data[$selected_temple_key]['name'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Temple Timings | Mandir Mitra</title>
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
    }

    /* --- Header --- */
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
    .header-left { display: flex; align-items: center; gap: 8px; }
    .user-info { display: flex; align-items: center; gap: 20px; font-size: 16px; }
    .user-buttons form { display: inline-block; }
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
    .logout-btn { color: #9c27b0; }
    .user-buttons button:hover { background: #ffe0b2; transform: scale(1.05); }

    /* --- Main Content Container --- */
    .content-container {
      width: 90%;
      max-width: 1000px; /* Centered, slightly narrower */
      margin: 30px auto;
      flex-grow: 1;
      padding-bottom: 40px;
    }

    /* --- Title Container with Back Button --- */
    .title-container {
        position: relative;
        text-align: center;
        margin-bottom: 25px; 
    }
    .title-container h2 {
        font-size: 28px;
        font-weight: 700;
        color: #d35400;
        margin: 0; 
        display: inline-block;
        padding: 0 160px;
    }
    .content-back-btn {
        position: absolute; 
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        text-decoration: none;
        color: #d35400; 
        font-size: 16px;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: 8px;
        background-color: #fff8e1; 
        border: 1px solid #ffcc80;
        transition: all 0.3s;
    }
    .content-back-btn:hover {
        background-color: #ffecd1; 
        transform: translateY(-50%) scale(1.05); 
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    /* --- REMOVED: Temple Selector Bar --- */
    
    /* --- MODIFIED: Info Container & Placeholder --- */
    .temple-info-container {
        display: none; /* Hidden by default, shown by JS */
    }
    .placeholder-message {
        display: none; /* Hidden by default */
        text-align: center;
        font-size: 20px;
        font-weight: 600;
        color: #8c1d37; /* Error color */
        padding: 40px;
        background: #ffe0e9;
        border: 1px solid #8c1d37;
        border-radius: 15px;
    }
    
    /* --- Info Cards Grid --- */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr; /* Two equal columns */
      gap: 25px;
    }

    .info-card {
      background: #fff8e1;
      padding: 25px 30px;
      border-radius: 20px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
    }
    /* Make some cards span full width */
    .info-card.full-width {
        grid-column: 1 / -1;
    }
    
    .info-card h3 {
      color: #4a2c0a;
      font-size: 20px;
      margin-top: 0;
      margin-bottom: 15px;
      border-bottom: 2px solid #ffcc80;
      padding-bottom: 8px;
    }
    .info-card p {
      font-size: 16px;
      color: #6d4c41;
      line-height: 1.6;
      margin: 0;
    }
    
    /* --- Live Status & Waiting Time Cards --- */
    .status-card {
        text-align: center;
    }
    #live-status-text {
        font-size: 28px;
        font-weight: 700;
        margin: 10px 0 0 0;
        padding: 10px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    .status-open {
        background-color: #d6f8d6;
        color: #1e4620;
    }
    .status-closed {
        background-color: #ffe0e9;
        color: #8c1d37;
    }
    
    #waiting-time-text {
        font-size: 36px;
        font-weight: 700;
        color: #d35400;
        margin: 10px 0 0 0;
    }
    #waiting-time-subtext {
        font-size: 16px;
        color: #6d4c41;
        font-weight: 500;
        margin-top: 5px;
    }

    /* --- Timing & Info Lists --- */
    .timing-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .timing-list li {
        display: flex;
        justify-content: space-between;
        font-size: 16px;
        padding: 10px 0;
        border-bottom: 1px dashed #e0b080;
    }
    .timing-list li:last-child {
        border-bottom: none;
    }
    .timing-list .item-name {
        font-weight: 600;
        color: #4a2c0a;
    }
    .timing-list .item-time {
        font-weight: 500;
        color: #d35400;
    }
    
    .notes-list {
        list-style-type: '🛕';
        padding-left: 25px;
        margin: 0;
        font-size: 15px;
        color: #6d4c41;
        line-height: 1.6;
    }
    .notes-list li {
        padding-left: 10px;
        margin-bottom: 10px;
    }

    @media (max-width: 900px) {
        .title-container h2 { padding: 0 140px; }
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
        padding: 15px;
      }
      .user-info { flex-direction: column; gap: 8px; }
      
      .title-container { text-align: left; margin-bottom: 20px; }
      .title-container h2 {
          font-size: 22px;
          margin-top: 15px; 
          padding: 0; 
          display: block; 
      }
      .content-back-btn {
          position: static; 
          transform: none;
          display: inline-block; 
          margin-bottom: 10px; 
      }
      .info-grid {
          grid-template-columns: 1fr; /* Stack all cards on mobile */
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
      </div>
    </div>
  </header>

  <div class="content-container">
    
    <div class="title-container">
        <a href="dashboard.php" class="content-back-btn">← Back to Dashboard</a>
        <h2>🕒 <?php echo htmlspecialchars($selected_temple_name); ?> Timings</h2>
    </div>
    
    <div class="placeholder-message" id="placeholder-message">
        <p>No temple selected. Please go back to the dashboard and choose a temple.</p>
    </div>

    <div class="temple-info-container" id="temple-info-container">
        <div class="info-grid">
            
            <div class="info-card status-card">
                <h3>Live Status</h3>
                <p id="live-status-text">--</p>
                <p id="live-status-subtext" style="color: #6d4c41; margin-top: 5px;">(Updates automatically)</p>
            </div>
            
            <div class="info-card status-card">
                <h3>Est. Waiting Time</h3>
                <p id="waiting-time-text">--</p>
                <p id="waiting-time-subtext">(Simulated live data)</p>
            </div>
            
            <div class="info-card full-width">
                <h3>Darshan Timings</h3>
                <ul class="timing-list" id="darshan-timings-list">
                    </ul>
            </div>
            
            <div class="info-card">
                <h3>Aarti Timings</h3>
                <ul class="timing-list" id="aarti-timings-list">
                    </ul>
            </div>
            
            <div class="info-card">
                <h3>💡 Useful Information</h3>
                <ul class="notes-list" id="notes-list">
                    </ul>
            </div>
            
        </div>
    </div>
    
  </div>

  <script>
    // --- Pass PHP data to JavaScript ---
    const templeData = <?php echo json_encode($temple_data); ?>;
    // --- Get the currently selected temple key from PHP ---
    const currentTempleKey = <?php echo json_encode($selected_temple_key); ?>;
    
    // --- Global variables for intervals ---
    let waitingTimeInterval;
    let liveStatusInterval;

    // --- Get DOM Elements ---
    const placeholderEl = document.getElementById('placeholder-message');
    const containerEl = document.getElementById('temple-info-container');
    
    const liveStatusTextEl = document.getElementById('live-status-text');
    const waitingTimeTextEl = document.getElementById('waiting-time-text');
    const darshanListEl = document.getElementById('darshan-timings-list');
    const aartiListEl = document.getElementById('aarti-timings-list');
    const notesListEl = document.getElementById('notes-list');

    // --- Helper function to get random int ---
    function getRandomInt(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
    
    // --- Function to update waiting time ---
    function updateWaitingTime() {
        const baseTime = getRandomInt(20, 50);
        const minTime = Math.max(5, baseTime - 5); // Ensure min is not too low
        const maxTime = baseTime + 5;
        waitingTimeTextEl.textContent = `${minTime}-${maxTime} mins`;
    }
    
    // --- Function to check and update live status ---
    function updateLiveStatus(intervals) {
        const now = new Date();
        const currentTime = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);
        
        let isOpen = false;
        
        for (const interval of intervals) {
            const [open, close] = interval;
            if (currentTime >= open && currentTime < close) {
                isOpen = true;
                break;
            }
        }
        
        if (isOpen) {
            liveStatusTextEl.textContent = "Open for Darshan";
            liveStatusTextEl.className = "status-open";
        } else {
            liveStatusTextEl.textContent = "Closed for Darshan";
            liveStatusTextEl.className = "status-closed";
        }
    }
    
    // --- Main function to update all info ---
    function displayTempleInfo(templeKey) {
        // Clear any existing intervals to prevent memory leaks
        clearInterval(waitingTimeInterval);
        clearInterval(liveStatusInterval);
        
        // Check if the templeKey is valid
        if (!templeKey || !templeData[templeKey]) {
            placeholderEl.style.display = 'block';
            containerEl.style.display = 'none';
            return;
        }
        
        // Show the info container
        placeholderEl.style.display = 'none';
        containerEl.style.display = 'block';
        
        // Get the data for the selected temple
        const data = templeData[templeKey];
        
        // 1. Populate Darshan Timings
        darshanListEl.innerHTML = ""; // Clear old data
        for (const [name, time] of Object.entries(data.timings)) {
            darshanListEl.innerHTML += `
                <li>
                    <span class="item-name">${name}</span>
                    <span class="item-time">${time}</span>
                </li>
            `;
        }
        
        // 2. Populate Aarti Timings
        aartiListEl.innerHTML = ""; // Clear old data
        for (const [name, time] of Object.entries(data.aarti)) {
            aartiListEl.innerHTML += `
                <li>
                    <span class="item-name">${name}</span>
                    <span class="item-time">${time}</span>
                </li>
            `;
        }
        
        // 3. Populate Notes
        notesListEl.innerHTML = ""; // Clear old data
        for (const note of data.notes) {
            notesListEl.innerHTML += `<li>${note}</li>`;
        }
        
        // 4. Start Live Status
        updateLiveStatus(data.open_intervals); // Run once immediately
        liveStatusInterval = setInterval(() => updateLiveStatus(data.open_intervals), 30000); // Update every 30 seconds
        
        // 5. Start Live Waiting Time
        updateWaitingTime(); // Run once immediately
        waitingTimeInterval = setInterval(updateWaitingTime, 4000); // Update every 4 seconds
    }
    
    // --- Event Listeners ---
    
    // 1. REMOVED: Select element event listener
    
    // 2. MODIFIED: Check for URL parameter on page load
    document.addEventListener('DOMContentLoaded', () => {
        // 'currentTempleKey' comes directly from PHP
        displayTempleInfo(currentTempleKey);
    });

  </script>

</body>
</html>
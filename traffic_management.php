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

// --- NEW: Enhanced & Re-written Mobility Data ---
$mobility_data = [
    "Somnath" => [
        "parking" => "Official temple parking is at Parking Lots A & B (near Somnath Chowpatty). It can fill up during peak hours (Aarti times). Be prepared for a short 5-10 minute walk.",
        "shuttle" => "Free electric shuttle (e-rickshaw) service runs continuously between the main parking lots and the temple's 'Yatri Plaza' (Gate 1). Service is frequent, running every 10-15 minutes.",
        "pwd"     => "Wheelchair access and special assistance are available at Gate 1. Please contact the temple trust office on arrival for priority Darshan, support, and access to accessible restrooms.",
        "map_link"=> "https://www.google.com/maps/search/parking+near+somnath+temple"
    ],
    "Dwarka" => [
        "parking" => "Parking is extremely limited near the temple. It is strongly advised to use the Dwarka Municipal Parking Garage (a 10-15 min walk) and hire a local auto-rickshaw to the temple gate.",
        "shuttle" => "Local auto-rickshaws and e-rickshaws are the primary transport from parking areas and the bus stand. They provide last-mile connectivity directly to the 'Swarg Dwar' entrance.",
        "pwd"     => "Ramp access is available at the main 'Swarg Dwar' entrance. Temple volunteers (sevaks) are present inside to assist PwD and senior devotees with darshan.",
        "map_link"=> "https://www.google.com/maps/search/parking+near+dwarkadhish+temple+dwarka"
    ],
    "Ambaji" => [
        "parking" => "Follow signs for the large, designated 'Yatri Parking' lots located on the town's outskirts. State transport buses and private jeeps connect these lots to the temple complex.",
        "shuttle" => "A regular bus service runs from the main Ambaji Bus Stand to the temple's main circle. Shared jeeps are also a popular and quick option for a nominal fee.",
        "pwd"     => "Priority queues and complimentary wheelchair facilities are available. Please contact the Shri Arasuri Ambaji Mata Devasthan Trust office near Gate 3 for assistance.",
        "map_link"=> "https://www.google.com/maps/search/parking+near+ambaji+temple"
    ],
    "Pavagadh" => [
        "parking" => "All private vehicles *must* be parked at the base station in Manchi. It is not possible to drive any further up the hill to the temple.",
        "shuttle" => "From Manchi, the 'Udankhatola' (Cable Car / Ropeway) is the main transport to the temple peak. Expect queues on weekends. Shuttle buses connect the Pavagadh bus stand to Manchi.",
        "pwd"     => "The cable car is wheelchair accessible. However, please note there is a final set of ~250 stairs to the main shrine. 'Doli' (palanquin) services are available for hire at the ropeway exit.",
        "map_link"=> "https://www.google.com/maps/search/parking+at+manchi+pavagadh"
    ],
    "default" => [
        "parking" => "Please follow local signage for designated devotee parking. Availability can be limited during festivals and peak hours.",
        "shuttle" => "Check with local transport authorities or auto-rickshaw stands for shuttle services and timings to the temple complex.",
        "pwd"     => "Most temples offer priority services. We recommend contacting the temple trust office upon arrival for the most accurate information on accessible routes.",
        "map_link"=> "https://www.google.com/maps"
    ]
];

// Get the selected temple and its data
$selected_temple_key = "default";
$selected_temple_name = "Mandir Mitra";

if (isset($_GET['temple']) && !empty($_GET['temple'])) {
    $selected_temple_key_raw = $_GET['temple'];
    $selected_temple_name = htmlspecialchars($selected_temple_key_raw) . " Temple";
    
    // Check if we have specific data for this temple
    if (array_key_exists($selected_temple_key_raw, $mobility_data)) {
        $selected_temple_key = $selected_temple_key_raw;
    }
}

// Assign the correct info strings
$parking_info = $mobility_data[$selected_temple_key]['parking'];
$shuttle_info = $mobility_data[$selected_temple_key]['shuttle'];
$pwd_info = $mobility_data[$selected_temple_key]['pwd'];
$parking_map_link = $mobility_data[$selected_temple_key]['map_link'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Traffic & Mobility | Mandir Mitra</title>
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

    /* --- Header (Back Button REMOVED from here) --- */
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
      gap: 8px; /* Reset gap */
    }
    .header-left span {
        font-weight: 700;
        font-size: 20px;
    }
    /* --- End Header --- */

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
    .traffic-container {
      width: 90%;
      max-width: 1200px;
      margin: 30px auto;
      flex-grow: 1;
      padding-bottom: 40px; /* Add padding at bottom */
    }

    /* --- NEW: Title Container with Back Button --- */
    .title-container {
        position: relative; /* Parent for absolute positioning */
        text-align: center; /* Center the h2 */
        margin-bottom: 25px; 
    }

    .title-container h2 {
        font-size: 28px;
        font-weight: 700;
        color: #d35400;
        margin: 0; 
        display: inline-block; /* Keeps it centered */
        padding: 0 160px; /* Add padding so title doesn't overlap button */
    }
    
    .content-back-btn {
        position: absolute; /* Position relative to .title-container */
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        
        text-decoration: none;
        color: #d35400; /* Match title color */
        font-size: 16px;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: 8px;
        background-color: #fff8e1; /* Match card color */
        border: 1px solid #ffcc80;
        transition: all 0.3s;
    }
    .content-back-btn:hover {
        background-color: #ffecd1; /* Light hover */
        transform: translateY(-50%) scale(1.05); /* Keep transform */
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    /* --- End Title Container --- */


    /* --- Info Cards Grid --- */
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 25px;
    }

    .info-card {
      background: #fff8e1;
      padding: 25px 30px;
      border-radius: 20px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
    }
    
    .info-card h3 {
      color: #4a2c0a;
      font-size: 20px;
      margin-top: 0;
      margin-bottom: 12px;
      border-bottom: 2px solid #ffcc80;
      padding-bottom: 8px;
    }

    .info-card p {
      font-size: 16px;
      color: #6d4c41;
      line-height: 1.6;
      margin-bottom: 10px;
      flex-grow: 1; /* Makes p tags fill space */
    }
    
    .info-card p strong {
        color: #d35400;
        font-weight: 700;
        display: block;
        margin-bottom: 2px;
    }

    /* --- Real-time Traffic Card --- */
    #traffic-card {
      text-align: center;
      transition: background-color 0.5s ease;
    }
    
    #traffic-status {
      font-size: 36px;
      font-weight: 700;
      margin: 10px 0;
      padding: 10px;
      border-radius: 10px;
      transition: all 0.5s ease;
    }

    #traffic-text {
      font-size: 18px;
      font-weight: 600;
      margin-top: 10px;
    }

    /* --- Status Color Classes --- */
    .status-low { background-color: #d6f8d6; }
    .status-low #traffic-status { color: #1e4620; }
    .status-moderate { background-color: #fff3b0; }
    .status-moderate #traffic-status { color: #5c500b; }
    .status-high { background-color: #ffe0e9; }
    .status-high #traffic-status { color: #8c1d37; }

    /* --- Parking Availability Card Styles --- */
    .parking-check-form {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }
    .parking-check-form input[type="date"] {
      flex: 3;
      padding: 8px 10px;
      border: 1px solid #e0b080;
      border-radius: 8px;
      font-family: "Baloo 2", cursive;
      font-size: 14px;
    }
    .parking-check-form button {
      flex: 2;
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      background-color: #7b1fa2;
      color: white;
      font-family: "Baloo 2", cursive;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: 0.3s;
    }
    .parking-check-form button:hover {
      background-color: #4a148c;
    }
    #parking-availability-result {
      text-align: center;
      font-size: 18px;
      font-weight: 600;
      color: #d35400;
    }
    #parking-availability-result span {
        font-size: 2.5em;
        font-weight: 700;
        color: #4a2c0a;
        display: block;
        line-height: 1.2;
    }

    /* --- Map Button Style --- */
    .btn-map {
      display: block;
      padding: 12px 20px;
      border-radius: 10px;
      background-color: #ff9933;
      color: #fff;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
      text-align: center;
      margin-top: auto; /* Pushes button to bottom */
    }
    .btn-map:hover {
      background-color: #e67e22;
      transform: translateY(-2px);
    }

    @media (max-width: 900px) {
        .title-container h2 {
            padding: 0 140px; /* Less padding on tablet */
        }
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
        padding: 15px;
      }
      .user-info { flex-direction: column; gap: 8px; }
      
      /* --- Responsive Title/Button --- */
      .title-container {
            text-align: left; /* Align left */
            margin-bottom: 20px;
        }
      .title-container h2 {
          font-size: 22px;
          margin-top: 15px; /* Add space below button */
          padding: 0; /* Remove padding */
          display: block; /* Stack it */
      }
      .content-back-btn {
          position: static; /* Un-do absolute positioning */
          transform: none;
          display: inline-block; /* Make it an inline-block */
          margin-bottom: 10px; /* Space it out */
      }
      /* --- End Responsive Title --- */
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

  <div class="traffic-container">
    
    <div class="title-container">
        <a href="dashboard.php" class="content-back-btn">← Back to Dashboard</a>
        <h2>🚦 Traffic & Mobility for <?php echo $selected_temple_name; ?></h2>
    </div>

    <div class="info-grid">
      <div class="info-card" id="traffic-card">
        <h3>CURRENT TRAFFIC</h3>
        <p>Live status for roads near the temple.</p>
        <div id="traffic-status">--</div>
        <p id="traffic-text">Loading status...</p>
      </div>
      
      <div class="info-card">
        <h3>🅿️ Live Parking Availability</h3>
        <p>Check (simulated) available parking slots for a future date.</p>
        <div class="parking-check-form">
            <input type="date" id="parking-date-picker">
            <button id="check-parking-btn">Check</button>
        </div>
        <div id="parking-availability-result">
            Select a date to check
        </div>
      </div>

      <div class="info-card">
        <h3>🗺️ Find Nearest Parking</h3>
        <p>Open Google Maps to find parking lots and routes near the temple.</p>
        <a href="<?php echo $parking_map_link; ?>" target="_blank" class="btn-map">
          Open Map
        </a>
      </div>

      <div class="info-card">
        <h3>📝 Parking Guidelines</h3>
        <p><?php echo $parking_info; ?></p>
      </div>

      <div class="info-card">
        <h3>🚌 Shuttle Services</h3>
        <p><?php echo $shuttle_info; ?></p>
      </div>
      
      <div class="info-card">
        <h3>♿ Priority Access</h3>
        <p><?php echo $pwd_info; ?></p>
      </div>
      
      <div class="info-card">
        <h3>💡 Travel Advisory</h3>
        <p><strong>Comfortable Footwear:</strong> You may need to walk a significant distance from designated parking. We recommend wearing comfortable shoes or sandals.</p>
        <p><strong>Stay Hydrated:</strong> Carry a water bottle, especially during peak afternoon hours and summer months to avoid dehydration.</p>
        <p><strong>Be Aware:</strong> In areas of high congestion, please be mindful of your personal belongings and surroundings.</p>
      </div>

    </div>
    
  </div>

  <script>
    // --- A demo list of Indian holidays for 2025 ---
    // Format: "YYYY-MM-DD,HolidayName"
    const indianHolidays = [
        "2025-01-14,Makar Sankranti",
        "2025-01-26,Republic Day",
        "2025-03-14,Holi",
        "2025-03-31,Eid-ul-Fitr",
        "2025-04-06,Ram Navami",
        "2025-08-15,Independence Day",
        "2025-08-19,Raksha Bandhan",
        "2025-08-26,Janmashtami",
        "2025-10-02,Gandhi Jayanti",
        "2025-10-21,Dussehra",
        "2025-11-09,Diwali", // Note: This is an example, Diwali dates vary.
        "2025-12-25,Christmas"
    ];

    // --- Your getSlotAvailability function ---
    function getSlotAvailability(dateString) {
        const max = 200; // Max parking slots
        const date = new Date(dateString);
        const day = date.getDay(); // 0 = Sunday, 6 = Saturday
        let baseBooked = 0;
        
        // Check for weekend
        if (day === 0 || day === 6) {
            // Higher base booking on weekends (80-179)
            baseBooked = Math.floor(Math.random() * 100) + 80; 
        } else {
            // Lower base booking on weekdays (20-79)
            baseBooked = Math.floor(Math.random() * 60) + 20; 
        }
        
        // Check for holiday
        const isHoliday = indianHolidays.map(h => h.split(',')[0]).includes(dateString);
        if (isHoliday) {
            // Add 50 booked slots on holidays, but don't exceed max
             baseBooked = Math.min(max, baseBooked + 50);
        }
        
        // Ensure booked never exceeds max
        const booked = Math.min(max, baseBooked);
        return max - booked; // Return available slots
    }


    document.addEventListener('DOMContentLoaded', () => {
        // --- Traffic Status Logic (Unchanged) ---
        const trafficCard = document.getElementById('traffic-card');
        const trafficStatusEl = document.getElementById('traffic-status');
        const trafficTextEl = document.getElementById('traffic-text');

        function getTrafficStatus() { 
            const traffic = Math.random();
            if (traffic < 0.3) return { status: 'LOW', className: 'status-low', text: 'Free Flow' };
            if (traffic < 0.7) return { status: 'MODERATE', className: 'status-moderate', text: 'Moderate Congestion' };
            return { status: 'HIGH', className: 'status-high', text: 'Heavy Congestion' };
        }

        function updateTrafficUI() {
            const status = getTrafficStatus();
            trafficStatusEl.textContent = status.status;
            trafficTextEl.textContent = status.text;
            trafficCard.classList.remove('status-low', 'status-moderate', 'status-high');
            trafficCard.classList.add(status.className);
        }
        updateTrafficUI();
        setInterval(updateTrafficUI, 5000); // Refresh every 5 seconds

        // --- Parking Availability Logic ---
        const datePicker = document.getElementById('parking-date-picker');
        const checkBtn = document.getElementById('check-parking-btn');
        const resultEl = document.getElementById('parking-availability-result');

        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        datePicker.setAttribute('min', today);
        datePicker.value = today; // Set default value to today

        checkBtn.addEventListener('click', () => {
            const selectedDate = datePicker.value;
            if (!selectedDate) {
                resultEl.innerHTML = "Please select a date first.";
                return;
            }

            const availableSlots = getSlotAvailability(selectedDate);

            // Format the result nicely
            resultEl.innerHTML = `<span>${availableSlots}</span> Slots Available`;
        });

        // Trigger click on load to show today's availability
        checkBtn.click();
    });
  </script>

</body>
</html>
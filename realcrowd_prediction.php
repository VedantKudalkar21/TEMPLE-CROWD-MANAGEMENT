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

// Get the selected temple
$selected_temple_name = "Mandir Mitra";
if (isset($_GET['temple']) && !empty($_GET['temple'])) {
    $selected_temple_name = htmlspecialchars($_GET['temple']) . " Temple";
}

// --- NEW: Pass Server Date Info to JavaScript ---
// Get current day of the week (0=Sunday, 1=Monday... 6=Saturday)
$current_day_of_week = date('w'); 
// Get current month (0=Jan, 1=Feb... 11=Dec)
$current_month = date('n') - 1; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crowd Prediction | Mandir Mitra</title>
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
      max-width: 1200px;
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
      flex-grow: 1;
    }
    .info-card .card-content {
      text-align: center;
      flex-grow: 1;
    }

    /* --- Hero "Live Crowd" Card --- */
    #live-crowd-card {
        grid-column: 1 / -1; /* Make this card span full width */
        flex-direction: row;
        align-items: center;
        gap: 25px;
        transition: background-color 0.5s ease;
    }
    .live-crowd-icon {
        font-size: 60px;
    }
    .live-crowd-details {
        flex-grow: 1;
        text-align: left;
    }
    #live-crowd-count {
      font-size: 52px;
      font-weight: 700;
      line-height: 1.1;
      transition: color 0.5s ease;
    }
    #live-crowd-text {
      font-size: 20px;
      font-weight: 600;
      margin: 0;
      color: #6d4c41;
    }

    /* --- Status Color Classes --- */
    .status-low { background-color: #d6f8d6; }
    .status-low #live-crowd-count { color: #1e4620; }
    .status-moderate { background-color: #fff3b0; }
    .status-moderate #live-crowd-count { color: #5c500b; }
    .status-high { background-color: #ffe0e9; }
    .status-high #live-crowd-count { color: #8c1d37; }
    .status-very-high { background-color: #f3e8ff; } /* Light Purple */
    .status-very-high #live-crowd-count { color: #4a148c; }

    /* --- NEW: Est. Travel Time Card --- */
    .travel-time-content {
        padding-top: 20px; /* Add space from title */
    }
    .travel-time-content .big-number {
        font-size: 42px;
        font-weight: 700;
        color: #d35400;
        line-height: 1.2;
    }
    .travel-time-content .big-number span {
        font-size: 0.6em;
        font-weight: 600;
        color: #6d4c41;
    }
    .travel-time-content .sub-text {
        font-size: 16px;
        font-weight: 500;
        color: #6d4c41;
        margin-top: 5px;
    }
    
    /* --- Best Time Card --- */
    .best-time-display {
        font-size: 30px;
        font-weight: 700;
        color: #d35400;
        line-height: 1.2;
    }

    /* --- NEW: Shared List Styles (Forecast, Climate, Monthly) --- */
    .data-list {
        list-style: none;
        padding: 0;
        margin: 0 0 15px 0;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .data-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 5px;
        background-color: rgba(0,0,0,0.02);
    }
    .data-list li:first-child {
        background-color: #ffecd1; /* Highlight today/current */
    }
    .data-list .day-name, .data-list .month-name {
        font-weight: 700;
        color: #4a2c0a;
    }
    .data-list .day-status, .data-list .climate-status, .data-list .index-status {
        font-weight: 600;
    }
    /* Status colors for forecast */
    .status-text-low { color: #1e4620; }
    .status-text-moderate { color: #5c500b; }
    .status-text-high { color: #8c1d37; }
    
    /* Specific list styles */
    #forecast-list { max-height: 60px; /* Height of one item */ }
    #forecast-list.expanded { max-height: 500px; }
    
    #climate-list { max-height: 60px; /* Height of one item */ }
    #climate-list.expanded { max-height: 500px; }
    
    #monthly-index-list {
        height: 220px; /* Fixed height for all 12 */
        overflow-y: auto;
        border: 1px solid #ffecd1;
        padding: 5px;
        margin: 0;
    }
    #monthly-index-list .index-bar {
        width: 100px;
        height: 10px;
        background-color: #e0b080;
        border-radius: 5px;
        overflow: hidden;
    }
    #monthly-index-list .index-bar-fill {
        height: 100%;
        background-color: #d35400;
        border-radius: 5px;
    }
    /* --- End NEW List Styles --- */

    .btn-toggle-forecast {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        background-color: #7b1fa2;
        color: white;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 100%;
        margin-top: auto; /* Push to bottom */
    }
    .btn-toggle-forecast:hover {
        background-color: #4a148c;
    }
    
    /* --- Factors List --- */
    .factors-list {
        list-style: none;
        padding: 0;
        margin: 0;
        height: 220px; /* Fixed height */
        overflow-y: auto; /* Scrollable */
        background: rgba(0,0,0,0.02);
        border-radius: 8px;
        border: 1px solid #ffecd1;
    }
    .factors-list li {
        padding: 8px 12px;
        border-bottom: 1px solid #ffecd1;
    }
    .factors-list li:last-child {
        border-bottom: none;
    }
    .factors-list .holiday-date {
        font-weight: 600;
        color: #d35400;
        display: inline-block;
        width: 110px;
    }
    .factors-list .holiday-name {
        font-weight: 500;
        color: #4a2c0a;
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
      
      #live-crowd-card {
          flex-direction: column;
          align-items: center;
          text-align: center;
      }
      .live-crowd-details {
          text-align: center;
      }
      .live-crowd-icon {
          font-size: 40px;
      }
      #live-crowd-count {
          font-size: 40px;
      }
      #live-crowd-text {
          font-size: 18px;
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
        <h2>👥 Crowd Prediction for <?php echo $selected_temple_name; ?></h2>
    </div>

    <div class="info-grid">
      
      <div class="info-card" id="live-crowd-card">
        <div class="live-crowd-icon">
            <span id="live-crowd-emoji">📊</span>
        </div>
        <div class="live-crowd-details">
            <div id="live-crowd-count">--</div>
            <p id="live-crowd-text">Calculating current crowd...</p>
        </div>
      </div>

      <div class="info-card">
        <h3>🚗 Est. Travel Time</h3>
        <div class="card-content travel-time-content">
            <div class="big-number">
                <span id="travel-time">--</span> <span>mins</span>
            </div>
            <p class="sub-text">
                (Approx. <span id="travel-dist">--</span> km from your location)
            </p>
        </div>
      </div>

      <div class="info-card">
        <h3>🌟 Best Time to Visit (Today)</h3>
        <div class="card-content">
            <p class="best-time-display" id="best-time">
                --
            </p>
            <p style="margin-top: 10px; color: #6d4c41;">(Estimated least crowded period)</p>
        </div>
      </div>

      <div class="info-card">
        <h3>📅 Daily Crowd Forecast</h3>
        <ul class="data-list" id="forecast-list">
            </ul>
        <button class="btn-toggle-forecast" id="toggle-forecast-btn">View 7-Day Forecast</button>
      </div>
      
      <div class="info-card">
        <h3>☀️ Climate Forecast</h3>
        <ul class="data-list" id="climate-list">
            </ul>
        <button class="btn-toggle-forecast" id="toggle-climate-btn">View 7-Day Forecast</button>
      </div>
      
      <div class="info-card">
        <h3>🗓️ Monthly Crowd Index</h3>
        <p style="flex-grow: 0; margin-bottom: 5px;">(Est. crowd levels by month)</p>
        <ul class="data-list" id="monthly-index-list">
            </ul>
      </div>

      <div class="info-card">
        <h3>📈 Influencing Factors</h3>
        <p style="flex-grow: 0; margin-bottom: 5px;">Upcoming holidays that may increase crowd levels.</p>
        <ul class="factors-list" id="factors-list">
            </ul>
      </div>

    </div>
    
  </div>

  <script>
    // --- Data Passed from PHP ---
    const CURRENT_DAY = <?php echo $current_day_of_week; ?>;
    const CURRENT_MONTH = <?php echo $current_month; ?>;

    // --- Data Arrays ---
    const DAYS_OF_WEEK = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const MONTHS_OF_YEAR = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
    const FORECAST_LEVELS = [
        { text: "High Crowd", class: "status-text-high" },    // Sun
        { text: "Low Crowd", class: "status-text-low" },      // Mon
        { text: "Low Crowd", class: "status-text-low" },      // Tue
        { text: "Moderate Crowd", class: "status-text-moderate" }, // Wed
        { text: "Low Crowd", class: "status-text-low" },      // Thu
        { text: "Moderate Crowd", class: "status-text-moderate" }, // Fri
        { text: "High Crowd", class: "status-text-high" }     // Sat
    ];
    
    // --- NEW Climate Data ---
    const CLIMATE_DATA = [
        { icon: "☀️", text: "Sunny & Clear", temp: "31°/24°" }, // Sun
        { icon: "☀️", text: "Sunny", temp: "30°/23°" }, // Mon
        { icon: "🌤️", text: "Partly Cloudy", temp: "30°/24°" }, // Tue
        { icon: "☁️", text: "Cloudy", temp: "29°/23°" }, // Wed
        { icon: "🌤️", text: "Partly Cloudy", temp: "31°/24°" }, // Thu
        { icon: "☀️", text: "Sunny", temp: "32°/25°" }, // Fri
        { icon: "☀️", text: "Sunny & Warm", temp: "32°/24°" }  // Sat
    ];

    const BEST_TIMES = ["Early Morning (6 AM - 9 AM)", "Afternoon (1 PM - 4 PM)", "Evening (7 PM - 9 PM)", "Morning (9 AM - 11 AM)"];
    
    // --- NEW Monthly Data with random crowds ---
    const MONTHLY_CROWD_DATA = [
        { month: "Jan", index: 7.5 },
        { month: "Feb", index: 8.0 },
        { month: "Mar", index: 8.5 },
        { month: "Apr", index: 9.0 },
        { month: "May", index: 6.0 },
        { month: "Jun", index: 5.5 },
        { month: "Jul", index: 7.0 },
        { month: "Aug", index: 8.8 },
        { month: "Sep", index: 8.2 },
        { month: "Oct", index: 9.5 },
        { month: "Nov", index: 9.2 },
        { month: "Dec", index: 8.0 }
    ];

    const INDIAN_HOLIDAYS = [
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
        "2025-11-09,Diwali",
        "2025-12-25,Christmas"
    ];

    // --- Helper function to get random int ---
    function getRandomInt(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    // --- 1. Update Live Crowd ---
    function updateLiveCrowd() {
        const crowdCard = document.getElementById('live-crowd-card');
        const crowdCountEl = document.getElementById('live-crowd-count');
        const crowdTextEl = document.getElementById('live-crowd-text');
        const crowdEmojiEl = document.getElementById('live-crowd-emoji');

        const crowd = getRandomInt(50, 5000);
        let status, className, emoji;

        if (crowd < 1000) {
            status = "Low Crowd";
            className = "status-low";
            emoji = "🟢";
        } else if (crowd < 3000) {
            status = "Moderate Crowd";
            className = "status-moderate";
            emoji = "🟡";
        } else if (crowd < 4500) {
            status = "High Crowd";
            className = "status-high";
            emoji = "🔴";
        } else {
            status = "Very High Crowd";
            className = "status-very-high";
            emoji = "🟣";
        }
        
        crowdCountEl.textContent = `${crowd} Devotees`;
        crowdTextEl.textContent = `Current status is ${status}`;
        crowdEmojiEl.textContent = emoji;
        
        crowdCard.className = 'info-card'; // Reset classes
        crowdCard.classList.add(className);
    }

    // --- 2. Update Travel Time ---
    function updateTravelTime() {
        document.getElementById('travel-time').textContent = getRandomInt(15, 90);
        document.getElementById('travel-dist').textContent = getRandomInt(5, 35);
    }

    // --- 3. Update Best Time to Visit ---
    function updateBestTime() {
        const bestTime = BEST_TIMES[getRandomInt(0, BEST_TIMES.length - 1)];
        document.getElementById('best-time').textContent = bestTime;
    }

    // --- 4. NEW: Populate Monthly Crowd Index List ---
    function populateMonthlyIndex() {
        const listEl = document.getElementById('monthly-index-list');
        listEl.innerHTML = ""; // Clear list

        MONTHLY_CROWD_DATA.forEach((data, index) => {
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="month-name" style="width: 40px;">${data.month}</span>
                <span class="index-bar">
                    <div class="index-bar-fill" style="width: ${data.index * 10}%;"></div>
                </span>
                <span class="index-status" style="width: 40px; text-align: right;">${data.index.toFixed(1)}</span>
            `;
            if (index === CURRENT_MONTH) {
                li.style.backgroundColor = "#ffecd1"; // Highlight current month
            }
            listEl.appendChild(li);
        });
    }

    // --- 5. Generate Daily Crowd Forecast ---
    function generateDailyForecast() {
        const listEl = document.getElementById('forecast-list');
        listEl.innerHTML = ""; // Clear list

        for (let i = 0; i < 7; i++) {
            const dayIndex = (CURRENT_DAY + i) % 7;
            const dayName = (i === 0) ? "Today" : DAYS_OF_WEEK[dayIndex];
            const forecast = FORECAST_LEVELS[dayIndex];
            
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="day-name">${dayName}</span>
                <span class="day-status ${forecast.class}">${forecast.text}</span>
            `;
            listEl.appendChild(li);
        }
    }

    // --- 6. Toggle 7-Day Crowd Forecast ---
    function setupForecastToggle() {
        const btn = document.getElementById('toggle-forecast-btn');
        const list = document.getElementById('forecast-list');
        
        btn.addEventListener('click', () => {
            const isExpanded = list.classList.toggle('expanded');
            btn.textContent = isExpanded ? "Hide Forecast" : "View 7-Day Forecast";
        });
    }
    
    // --- 7. NEW: Generate Daily Climate Forecast ---
    function generateClimateForecast() {
        const listEl = document.getElementById('climate-list');
        listEl.innerHTML = ""; // Clear list

        for (let i = 0; i < 7; i++) {
            const dayIndex = (CURRENT_DAY + i) % 7;
            const dayName = (i === 0) ? "Today" : DAYS_OF_WEEK[dayIndex];
            const climate = CLIMATE_DATA[dayIndex]; // Get simulated climate data
            
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="day-name">${dayName}</span>
                <span class="climate-status" style="color: #6d4c41;">
                    ${climate.icon} ${climate.text}
                </span>
                <span class="day-status" style="color: #d35400;">${climate.temp}</span>
            `;
            listEl.appendChild(li);
        }
    }
    
    // --- 8. NEW: Toggle 7-Day Climate Forecast ---
    function setupClimateToggle() {
        const btn = document.getElementById('toggle-climate-btn');
        const list = document.getElementById('climate-list');
        
        btn.addEventListener('click', () => {
            const isExpanded = list.classList.toggle('expanded');
            btn.textContent = isExpanded ? "Hide Forecast" : "View 7-Day Forecast";
        });
    }

    // --- 9. Populate Holiday Factors ---
    function populateHolidays() {
        const listEl = document.getElementById('factors-list');
        listEl.innerHTML = ""; // Clear list
        
        // Get today's date in YYYY-MM-DD format
        const today = new Date();
        today.setHours(0,0,0,0); // Normalize to start of day

        // --- UPDATED: Use your future dates ---
        const holidaysToDisplay = INDIAN_HOLIDAYS.filter(holiday => {
             const [dateStr] = holiday.split(',');
             const holidayDate = new Date(dateStr);
             return holidayDate >= today; // Only show upcoming
        });

        holidaysToDisplay.forEach(holiday => {
            const [dateStr, name] = holiday.split(',');
            const holidayDate = new Date(dateStr);
            
            const li = document.createElement('li');
            
            // Format date as "14 Jan 2025"
            const displayDate = holidayDate.toLocaleDateString('en-GB', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric' 
            });

            li.innerHTML = `
                <span class="holiday-date">${displayDate}</span>
                <span class="holiday-name">${name}</span>
            `;
            listEl.appendChild(li);
        });

        // Add a message if no holidays are upcoming
        if (holidaysToDisplay.length === 0) {
             const li = document.createElement('li');
             li.innerHTML = `<span class="holiday-name">No major holidays upcoming.</span>`;
             listEl.appendChild(li);
        }
    }

    // --- Run all functions on page load ---
    document.addEventListener('DOMContentLoaded', () => {
        // Initial calls
        updateLiveCrowd();
        updateTravelTime();
        updateBestTime();
        populateMonthlyIndex(); // <-- Changed from updateMonthlyIndex
        generateDailyForecast();
        setupForecastToggle();
        generateClimateForecast(); // <-- New
        setupClimateToggle();      // <-- New
        populateHolidays();

        // Set intervals
        setInterval(updateLiveCrowd, 3000); // 3 seconds
        setInterval(updateTravelTime, 7000); // 7 seconds
    });

  </script>

</body>
</html>
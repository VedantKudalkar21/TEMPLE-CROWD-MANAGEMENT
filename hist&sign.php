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

// --- History Data Array ---
$history_data = [
    "Somnath" => [
        "name" => "Somnath Temple",
        "image_src" => "images/somnath_temple.jpg",
        "significance" => "The first among the twelve <strong>Jyotirlinga</strong> shrines of Lord Shiva. It is a revered pilgrimage site, known as 'the Shrine Eternal,' symbolizing the indestructible spirit of resilience and faith.",
        "history" => "The temple's history is turbulent. It is believed to have been built first in gold by Somraja (the Moon God), then in silver by Ravana, in wood by Lord Krishna, and in stone by Bhimdev. It was famously plundered by <strong>Mahmud of Ghazni in 1024</strong>. Despite repeated destruction, it was rebuilt each time. The present-day temple was reconstructed through the efforts of <strong>Sardar Vallabhbhai Patel</strong> and completed in 1951."
    ],
    "Dwarka" => [
        "name" => "Dwarkadhish Temple (Jagat Mandir)",
        "image_src" => "images/dwarka_temple.jpg",
        "significance" => "One of the four principal pilgrimage sites (<strong>Char Dham</strong>) for Hindus. It is dedicated to <strong>Lord Krishna</strong>, who is worshipped here as 'Dwarkadhish' or the 'King of Dwarka.' The city is believed to be the ancient kingdom of Krishna.",
        "history" => "The current temple structure, also known as Jagat Mandir, is believed to be around 2,200-2,500 years old. It is a 5-storied edifice built over 72 pillars. The temple was expanded in the 15th-16th century and has two main entrances: 'Swarga Dwar' (Gate of Heaven) for entry and 'Moksha Dwar' (Gate of Salvation) for exit."
    ],
    "Ambaji" => [
        "name" => "Ambaji Temple",
        "image_src" => "images/ambaji_temple.jpg",
        "significance" => "Ambaji is one of the <strong>51 Shakti Peethas</strong>, where the <strong>heart of Sati</strong> is believed to have fallen. Uniquely, there is no idol in the sanctum. Instead, a holy <strong>'Vishwa Yantra'</strong> is worshipped. Worshippers can only see the Yantra through a small slit, as direct viewing is prohibited.",
        "history" => "The temple is an ancient and revered site. It is believed that Lord Krishna had his *mundan* (first tonsure) ceremony here. The present temple, constructed from white marble, is a major pilgrimage destination, especially during the Bhadarvi Poonam fair."
    ],
    "Pavagadh" => [
        "name" => "Shree Mahakali Mataji Temple, Pavagadh",
        "image_src" => "images/pavagadh_temple.jpg",
        "significance" => "Like Ambaji, Pavagadh is also one of the <strong>51 Shakti Peethas</strong>. It is believed that the <strong>right toe of the goddess Sati</strong> fell here. The temple is located atop the Pavagadh Hill and is a potent site of worship for Mahakali Mata.",
        "history" => "The temple complex is part of the <strong>Champaner-Pavagadh Archaeological Park</strong>, a UNESCO World Heritage Site. The site shows a blend of Hindu and Islamic architecture, reflecting its history, especially its proximity to the 15th-century capital of Gujarat, Champaner."
    ]
];

// --- Get Temple from URL ---
$selected_temple_key = null;
$show_error = false;
$page_title = "History & Significance"; // Default

if (isset($_GET['temple']) && !empty($_GET['temple'])) {
    $temple_key = $_GET['temple'];
    if (array_key_exists($temple_key, $history_data)) {
        $selected_temple_key = $temple_key;
        $page_title = $history_data[$selected_temple_key]['name']; // Set page title
    } else {
        $show_error = true;
    }
} else {
    $show_error = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>History & Significance | Mandir Mitra</title>
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
      max-width: 1000px;
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
    
    /* --- Content CSS --- */
    .history-content {
        background: #fff8e1;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    .history-layout {
        display: flex;
        gap: 25px;
    }
    .history-image {
        flex: 1;
        min-width: 300px;
    }
    .history-image img {
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .history-text {
        flex: 2;
    }
    
    /* --- MODIFIED: Larger Font Sizes --- */
    .history-text h3 {
        font-size: 26px; /* Was 24px */
        color: #d35400;
        margin-top: 0;
        margin-bottom: 15px;
    }
    .history-text h4 {
        font-size: 20px; /* Was 18px */
        color: #4a2c0a;
        margin-bottom: 5px;
        border-bottom: 2px solid #ffcc80;
        padding-bottom: 5px;
    }
    .history-text p {
        font-size: 17px; /* Was 16px */
        color: #6d4c41;
        line-height: 1.8; /* Was 1.7 */
        margin-bottom: 15px;
    }
    /* --- End Modifications --- */
    
    /* --- NEW: Placeholder Message --- */
    .placeholder-message {
        display: block; /* Shown by default if error is true */
        text-align: center;
        font-size: 20px;
        font-weight: 600;
        color: #8c1d37; /* Error color */
        padding: 40px;
        background: #ffe0e9;
        border: 1px solid #8c1d37;
        border-radius: 15px;
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
      .history-layout {
          flex-direction: column; /* Stack image and text */
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
        <h2>📜 <?php echo htmlspecialchars($page_title); ?></h2>
    </div>
    
    <?php if ($show_error): ?>
        
        <div class="placeholder-message">
            <p>Could not find temple information. Please go back to the dashboard and select a temple.</p>
        </div>

    <?php else: ?>

        <?php $temple = $history_data[$selected_temple_key]; ?>
        <div class="history-content">
            <div class="history-layout">
                <div class="history-image">
                    <img src="<?php echo $temple['image_src']; ?>" alt="Image of <?php echo $temple['name']; ?>">
                </div>
                <div class="history-text">
                    <h3><?php echo $temple['name']; ?></h3>
                    
                    <h4>Significance</h4>
                    <p><?php echo $temple['significance']; ?></p>
                    
                    <h4>History</h4>
                    <p><?php echo $temple['history']; ?></p>
                </div>
            </div>
        </div>

    <?php endif; ?>
    
  </div>

</body>
</html>
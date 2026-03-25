<?php
session_start();
include('includes/db_connect.php'); // if needed for session/user validation

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$temple = isset($_GET['temple']) ? $_GET['temple'] : "";
$embedUrl = "";

switch ($temple) {
    case "Somnath":
        $embedUrl = "https://www.youtube.com/embed/UYt408sCVtk"; // update with actual channel or embed ID
        break;
    case "Dwarka":
        $embedUrl = "https://www.youtube.com/embed/osFC5D2BFho"; // update
        break;
    case "Ambaji":
        $embedUrl = "https://www.youtube.com/embed/z9IwkjND_fQ"; // update
        break;
    case "Pavagadh":
        $embedUrl = "https://www.youtube.com/embed/9ytdRHGBQOM"; // update
        break;
    default:
        $embedUrl = "";
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Darshan – <?php echo htmlspecialchars($temple ?: "Temple"); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Baloo 2", cursive;
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        header {
            background: #ff9933;
            color: #fff;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
        }
        .container {
            flex:1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .video-wrapper {
            width: 100%;
            max-width: 900px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        iframe {
            width: 100%;
            height: 500px;
            border: 0;
        }
        .back-btn {
            text-align: center;
            margin: 20px 0;
        }
        .back-btn button {
            background: #7b1fa2;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .back-btn button:hover {
            background: #4a148c;
        }
        .message {
            text-align: center;
            font-size: 18px;
            color: #6d4c41;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<header>
    🛕 Mandir Mitra · Live Darshan – <?php echo htmlspecialchars($temple ?: "Temple"); ?>
</header>

<div class="container">
    <?php if ($embedUrl): ?>
        <div class="video-wrapper">
            <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" allowfullscreen></iframe>
        </div>
    <?php else: ?>
        <p class="message">Please select a temple from the dashboard to view live darshan.</p>
    <?php endif; ?>
</div>

<div class="back-btn">
    <button onclick="window.location.href='dashboard.php'">← Back to Dashboard</button>
</div>

</body>
</html>

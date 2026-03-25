<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$temple = isset($_GET['temple']) ? $_GET['temple'] : "";
?>

<!DOCTYPE html>
<html>
<head>
<title>360° Temple Virtual View</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: "Baloo 2", cursive;
    background: #fff8e1;
    margin: 0;
    padding: 20px;
    text-align: center;
}
.container {
    background:white;
    padding:25px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    max-width:900px;
    margin:auto;
}
iframe {
    width:100%;
    height:500px;
    border:0;
    border-radius:15px;
}
.back-btn {
    margin-top:20px;
    background:#ff9933;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
    transition:0.3s;
}
.back-btn:hover { background:#e67e00; }
</style>
</head>
<body>

<div class="container">
    <h2>🌏 360° Virtual View - <?php echo htmlspecialchars($temple); ?> Temple</h2>
    <p>Move your finger / mouse to look around the temple.</p>

    <?php
        if ($temple == "Somnath") {
            echo '<iframe src="https://www.google.com/maps/embed?pb=!4v1700000000!6m8!1m7!1sCAoSLEFGMVFpcE44eEpYSU90VTF3LUdNM0c5UWxTRWFBcEhZS3NYWmtPN3M0Zzg2!2m2!1d20.888!2d70.401!3f120!4f0!5f0.782" allowfullscreen></iframe>';
        } elseif ($temple == "Dwarka") {
            echo '<iframe src="https://www.google.com/maps/embed?pb=!4v1761405781885!6m8!1m7!1sCAoSF0NJSE0wb2dLRUlDQWdJQ0VsTHlNaEFF!2m2!1d22.23769206287698!2d68.96731946910081!3f80!4f20!5f0.7820865974627469"  allowfullscreen></iframe>';
        } elseif ($temple == "Ambaji") {
            echo '<iframe src="https://www.google.com/maps/embed?pb=!4v1761406327383!6m8!1m7!1sCAoSHENJQUJJaER0X2E2S2pVQ3hiOGhJWUFkaFFDRDM.!2m2!1d24.33585834348996!2d72.85024874037083!3f120!4f20!5f0.7820865974627469" allowfullscreen></iframe>';
        } elseif ($temple == "Pavagadh") {
            echo '<iframe src="https://www.google.com/maps/embed?pb=!4v1761406013040!6m8!1m7!1sCAoSF0NJSE0wb2dLRUlDQWdJRE90cG4tMndF!2m2!1d23.71101196538481!2d72.38317609497771!3f126.66071564343999!4f3.2129807790619225!5f0.7528147041304039" allowfullscreen></iframe>';
        } else {
            echo "<p style='color:red;font-weight:600;'>No temple selected! Please go back and select a temple.</p>";
        }
    ?>

    <button class="back-btn" onclick="window.history.back()">← Back</button>
</div>

</body>
</html>

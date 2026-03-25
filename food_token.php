<?php
session_start();
include('includes/db_connect.php'); // Make sure this file exists

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$user_name = ucfirst($_SESSION['user_name']);
$token = '';
$message = '';
$temple_name = isset($_GET['temple']) ? $_GET['temple'] : '';

// Create prasadam_tokens table if it doesn't exist
$tableCheck = $conn->query("SHOW TABLES LIKE 'prasadam_tokens'");
if ($tableCheck->num_rows == 0) {
    $createTableSQL = "CREATE TABLE prasadam_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_name VARCHAR(100) NOT NULL,
        token VARCHAR(20) NOT NULL,
        temple_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($createTableSQL);
}

if (isset($_POST['generate_token'])) {
    $token = 'MM' . rand(100000, 999999);

    $stmt = $conn->prepare("INSERT INTO prasadam_tokens (user_name, token, temple_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user_name, $token, $temple_name);
    $stmt->execute();

    $message = "Your Prasadam token has been generated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prasadam / Food Token Generator</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700&display=swap">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
body {
    font-family: "Baloo 2", cursive;
    background: linear-gradient(135deg, #fff3e0, #ffe0b2);
    margin: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
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
}

header .user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

header .user-info button {
    padding: 6px 18px;
    border: none;
    border-radius: 6px;
    background-color: #7b1fa2;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

header .user-info button:hover {
    background-color: #4a148c;
}

.dashboard-container {
    width: 95%;
    max-width: 700px;
    margin: 40px auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.card {
    background: #fff8e1;
    padding: 40px 30px;
    border-radius: 22px;
    text-align: center;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    width: 100%;
}

.card h3 {
    color: #4a2c0a;
    font-size: 24px;
    margin-bottom: 15px;
}

.card p {
    font-size: 16px;
    color: #6d4c41;
    margin-bottom: 25px;
}

.card form {
    margin: 0;
    display: inline-block;
}

.card button {
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    background-color: #7b1fa2;
    color: white;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    margin: 5px;
    transition: background-color 0.3s;
}

.card button:hover {
    background-color: #5a1879;
}

/* --- New Token Box Styles --- */
.token-box {
    width: 100%;
    max-width: 400px; /* Narrower for a ticket feel */
    padding: 0; /* Remove old padding */
    background: #ffffff;
    border-radius: 12px;
    font-size: 1em; /* Reset font size for internal control */
    font-weight: 500; /* Use new font-weight */
    color: #333;
    margin: 25px auto; /* Center it */
    text-align: left; /* Align text left */
    border: 1px solid #ddd;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    overflow: hidden; /* Ensures border-radius clips children */
}

.token-box h4 {
    background: #FF9933; /* Saffron header */
    color: #fff;
    padding: 15px 20px;
    margin: 0;
    font-size: 1.25em;
    font-weight: 700;
    text-align: center;
}

.token-details {
    padding: 20px 25px;
    border-bottom: 1px dashed #ccc;
}

.token-info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 1.1em;
}
.token-info-row:last-child {
    margin-bottom: 0;
}

.token-info-row .label {
    font-weight: 600;
    color: #555;
}

.token-info-row .value {
    font-weight: 500;
    color: #000;
}

.token-code-wrapper {
    padding: 20px 25px;
    text-align: center;
    background: #fdfaf5;
}

.token-code-label {
    font-size: 0.9em;
    color: #777;
    margin-bottom: 8px;
    font-weight: 600;
}

.token-code {
    font-size: 2.3em;
    font-weight: 700;
    color: #7b1fa2; /* Use theme purple */
    letter-spacing: 2px;
    padding: 10px;
    background: #fff;
    border: 2px dashed #e0c6f5;
    border-radius: 8px;
}

.token-footer {
    padding: 12px 20px;
    background: #f9f9f9;
    text-align: center;
    font-size: 0.9em;
    color: #888;
    font-weight: 500;
}
/* --- End of New Token Box Styles --- */


.message {
    margin: 15px 0;
    color: #388E3C;
    font-weight: 600;
}

/* --- New Button & Link Styles --- */
.action-buttons-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.back-link {
    display: inline-block;
    margin-top: 25px;
    font-size: 1.05em;
    color: #7b1fa2;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
}

.back-link:hover {
    color: #4a148c;
}
/* --- End of New Button & Link Styles --- */


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
</style>
</head>
<body>

<header>
    <div>Prasadam / Food Token Generator</div>
    <div class="user-info">
        <span><?php echo $user_name; ?></span>
        <form action="dashboard.php" method="GET" style="margin:0;"><button type="submit">Back to Dashboard</button></form>
    </div>
</header>

<div class="dashboard-container">
    <div class="card">
        <h3>Generate Your Prasadam Token</h3>
        <p>Temple: <strong><?php echo htmlspecialchars($temple_name); ?></strong></p>

        <?php if($message != ''): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if($token != ''): ?>
            <div class="token-box" id="tokenBox">
                <h4>Prasadam E-Token</h4>
                <div class="token-details">
                    <div class="token-info-row">
                        <span class="label">Temple:</span>
                        <span class="value"><?php echo htmlspecialchars($temple_name); ?></span>
                    </div>
                    <div class="token-info-row">
                        <span class="label">User:</span>
                        <span class="value"><?php echo $user_name; ?></span>
                    </div>
                </div>
                <div class="token-code-wrapper">
                    <div class="token-code-label">Your Token Code</div>
                    <div class="token-code"><?php echo $token; ?></div>
                </div>
                <div class="token-footer">Valid for: <?php echo date("d M Y"); ?></div>
            </div>
        <?php endif; ?>

        <div class="action-buttons-container">
            <?php if($token != ''): ?>
                <button onclick="downloadPDF()">Download PDF</button>
            <?php endif; ?>

            <form method="POST">
                <button type="submit" name="generate_token">
                    <?php echo ($token != '') ? 'Generate New Token' : 'Generate Token'; ?>
                </button>
            </form>
        </div>

        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        
    </div>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Temple Crowd Management System
</footer>

<script>
function downloadPDF() {
    const tokenBox = document.getElementById('tokenBox');
    // Using a higher scale improves the quality of the image on the PDF
    html2canvas(tokenBox, { scale: 3 }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;
        
        // Calculate dimensions
        const pdf = new jsPDF('p', 'pt', 'a4'); // A4 size in points
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        
        const canvasWidth = canvas.width;
        const canvasHeight = canvas.height;
        const canvasAspectRatio = canvasWidth / canvasHeight;

        // Fit to width, maintaining aspect ratio
        let imgWidth = pdfWidth - 80; // A4 width minus 40pt margin on each side
        let imgHeight = imgWidth / canvasAspectRatio;

        // If it's too tall, fit to height instead
        if (imgHeight > pdfHeight - 80) {
            imgHeight = pdfHeight - 80; // A4 height minus 40pt margin
            imgWidth = imgHeight * canvasAspectRatio;
        }

        // Center the image
        const x = (pdfWidth - imgWidth) / 2;
        const y = 40; // 40pt margin from top

        pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
        pdf.save('Prasadam_Token_<?php echo $token; ?>.pdf');
    });
}
</script>

</body>
</html>
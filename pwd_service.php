<?php
session_start();
include('includes/db_connect.php'); // Make sure this file exists

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$form_filler_name = ""; // Variable to hold the name from the form

if (isset($_POST['submit'])) {
    $full_name = $_POST['full_name'];
    $age = $_POST['age'];
    $phone = $_POST['phone'];
    $aadhar = $_POST['aadhar'];

    // Store the submitted name for use in the pass
    $form_filler_name = $full_name;

    $target_dir = "uploads/";
    if (!file_exists($target_dir)) mkdir($target_dir);
    $file_name = time() . "_" . basename($_FILES["certificate"]["name"]);
    $target_file = $target_dir . $file_name;
    
    // Check if file was uploaded
    if (move_uploaded_file($_FILES["certificate"]["tmp_name"], $target_file)) {
        // Create table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS pwd_service (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            age INT NOT NULL,
            phone VARCHAR(15) NOT NULL,
            aadhar VARCHAR(12) NOT NULL,
            certificate VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO pwd_service(full_name, age, phone, aadhar, certificate) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $full_name, $age, $phone, $aadhar, $file_name);

        if ($stmt->execute()) {
            $message = "✅ Request Submitted Successfully!";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "❌ Error uploading file.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>PWD Support Service</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Added PDF generation libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
body { background: #fff7e6; font-family: 'Baloo 2', cursive; }
.card { background:white; border-radius:22px; box-shadow:0 10px 25px rgba(0,0,0,0.1); }
/* Pass styles */
.pass-box {
    width: 100%;
    max-width: 400px;
    padding: 0;
    background: #ffffff;
    border-radius: 12px;
    font-size: 1em;
    font-weight: 500;
    color: #333;
    margin: 25px auto;
    text-align: left;
    border: 1px solid #ddd;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}
.pass-header {
    color: #fff;
    padding: 15px 20px;
    margin: 0;
    font-size: 1.25em;
    font-weight: 700;
    text-align: center;
}
.pass-details {
    padding: 20px 25px;
    border-bottom: 1px dashed #ccc;
}
.pass-info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 1.1em;
}
.pass-info-row .label { font-weight: 600; color: #555; }
.pass-info-row .value { font-weight: 500; color: #000; }
.pass-code-wrapper {
    padding: 20px 25px;
    text-align: center;
    background: #fdfaf5;
}
.pass-code-label {
    font-size: 0.9em;
    color: #777;
    margin-bottom: 8px;
    font-weight: 600;
}
.pass-code {
    font-size: 2em;
    font-weight: 700;
    letter-spacing: 2px;
    padding: 10px;
    background: #fff;
    border-radius: 8px;
}
</style>
</head>
<body class="p-6">

<div class="max-w-2xl mx-auto card p-8">
    <h2 class="text-3xl font-bold text-center text-yellow-700">🕊 PwD & Senior Citizen Support</h2>

    <?php if ($message) echo "<p class'text-center mt-3 font-semibold text-green-600'>$message</p>"; ?>

    <!-- Hide form if already submitted -->
    <?php if (!$message) { ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-4 mt-6">
        <input type="text" name="full_name" placeholder="Full Name" class="w-full p-3 border rounded-lg shadow-sm" required>
        <input type="number" name="age" placeholder="Age" class="w-full p-3 border rounded-lg shadow-sm" required>
        <input type="text" name="phone" placeholder="Mobile Number" class="w-full p-3 border rounded-lg shadow-sm" required>
        <input type="text" name="aadhar" placeholder="Aadhar Number (12 digits)" maxlength="12" class="w-full p-3 border rounded-lg shadow-sm" required>
        
        <label class="font-semibold block pt-2">Upload Disability/Senior Citizen Certificate</label>
        <input type="file" name="certificate" class="w-full border p-2 rounded-lg" required>

        <button type="submit" name="submit" class="w-full bg-yellow-600 text-white py-3 rounded-lg hover:bg-yellow-700 font-bold text-lg shadow-lg">
            Submit Request
        </button>
    </form>
    <?php } ?>
</div>

<!-- ✅ SHOW SUVIDHA ONLY AFTER FORM SUBMISSION -->
<?php if ($message) { ?>
    <!-- Pass the form filler's name to JavaScript -->
    <script>
        const formFillerName = "<?php echo htmlspecialchars($form_filler_name, ENT_QUOTES); ?>";
        let currentPassId = ""; // To store the generated pass IDs
        let currentRampCode = "";
    </script>

    <!-- Main Suvidha Options -->
    <div id="suvidhaContainer" class="max-w-2xl mx-auto mt-8 card p-6 text-center space-y-4">
        <h3 class="text-xl font-bold text-yellow-700">Available Suvidha</h3>

        <button onclick="generatePriorityPass()" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 font-semibold text-lg">
            🎫 Priority Darshan Pass
        </button>

        <button onclick="showModal('Wheelchair Request', '✅ Wheelchair request accepted! Please collect it at Gate No. 1.')" 
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-semibold text-lg">
            ♿ Request Wheelchair
        </button>

        <button onclick="generateRampPass()" class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 font-semibold text-lg">
            🛗 Ramp Accessibility Slip
        </button>

        <button onclick="showModal('Volunteer Assistance', '🧍 Volunteer assigned! Please wait near Main Gate.')" 
                class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 font-semibold text-lg">
            🙋 Request Volunteer Assistance
        </button>

        <button onclick="window.location.href='dashboard.php';" 
                class="w-full bg-gray-700 text-white py-2 rounded-lg hover:bg-black mt-2 font-semibold text-lg">
            ← Back to Dashboard
        </button>
    </div>

    <!-- Hidden Priority Pass UI -->
    <div id="priorityPassUI" class="hidden max-w-2xl mx-auto mt-8 card p-6 text-center">
        <div class="pass-box" id="priorityPassCard">
            <h4 class="pass-header bg-green-600">✅ Priority Darshan Pass</h4>
            <div class="pass-details">
                <div class="pass-info-row">
                    <span class="label">Name:</span>
                    <span id="passName" class="value"></span>
                </div>
                <div class="pass-info-row">
                    <span class="label">Queue:</span>
                    <span class="value">Skipped (PwD)</span>
                </div>
                <div class="pass-info-row">
                    <span class="label">Entry:</span>
                    <span class="value">Immediate</span>
                </div>
            </div>
            <div class="pass-code-wrapper">
                <div class="pass-code-label">Pass ID</div>
                <div id="passId" class="pass-code text-green-700 border-2 border-dashed border-green-200"></div>
            </div>
        </div>
        <button onclick="downloadPriorityPassPDF()" class="w-full sm:w-auto bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 font-semibold mt-4">Download PDF</button>
        <button onclick="showSuvidha()" class="w-full sm:w-auto bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 font-semibold mt-4 ml-2">← Back to Suvidha</button>
    </div>

    <!-- Hidden Ramp Slip UI -->
    <div id="rampSlipUI" class="hidden max-w-2xl mx-auto mt-8 card p-6 text-center">
        <div class="pass-box" id="rampSlipCard">
            <h4 class="pass-header bg-purple-600">🛗 Ramp Access Slip</h4>
            <div class="pass-details">
                <div class="pass-info-row">
                    <span class="label">Name:</span>
                    <span id="rampName" class="value"></span>
                </div>
                <div class="pass-info-row">
                    <span class="label">Access:</span>
                    <span class="value">All Ramps</span>
                </div>
            </div>
            <div class="pass-code-wrapper">
                <div class="pass-code-label">Slip Code</div>
                <div id="rampCode" class="pass-code text-purple-700 border-2 border-dashed border-purple-200"></div>
            </div>
        </div>
        <button onclick="downloadRampSlipPDF()" class="w-full sm:w-auto bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 font-semibold mt-4">Download PDF</button>
        <button onclick="showSuvidha()" class="w-full sm:w-auto bg-gray-600 text-white py-2 px-6 rounded-lg hover:bg-gray-700 font-semibold mt-4 ml-2">← Back to Suvidha</button>
    </div>

<?php } ?>

<!-- Hidden Modal UI -->
<div id="customModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm text-center">
        <h3 id="modalTitle" class="text-xl font-bold text-yellow-700 mb-4"></h3>
        <p id="modalMessage" class="text-gray-700 mb-6"></p>
        <button onclick="closeModal()" class="w-full bg-yellow-600 text-white py-2 rounded-lg hover:bg-yellow-700 font-semibold">
            OK
        </button>
    </div>
</div>

<script>
// --- Modal Functions ---
const modal = document.getElementById('customModal');
function showModal(title, message) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    modal.classList.remove('hidden');
}
function closeModal() {
    modal.classList.add('hidden');
}

// --- Navigation Functions ---
function showSuvidha() {
    document.getElementById('suvidhaContainer').classList.remove('hidden');
    document.getElementById('priorityPassUI').classList.add('hidden');
    document.getElementById('rampSlipUI').classList.add('hidden');
}

// --- Pass/Slip Generation ---
function generatePriorityPass() {
    currentPassId = "PD" + Math.floor(Math.random() * 900000 + 100000);
    
    document.getElementById('passName').textContent = formFillerName;
    document.getElementById('passId').textContent = currentPassId;
    
    document.getElementById('suvidhaContainer').classList.add('hidden');
    document.getElementById('priorityPassUI').classList.remove('hidden');
}

function generateRampPass() {
    currentRampCode = "RA" + Math.floor(Math.random() * 90000 + 10000);
    
    document.getElementById('rampName').textContent = formFillerName;
    document.getElementById('rampCode').textContent = currentRampCode;

    document.getElementById('suvidhaContainer').classList.add('hidden');
    document.getElementById('rampSlipUI').classList.remove('hidden');
}

// --- PDF Download Functions ---
function downloadPriorityPassPDF() {
    const passCard = document.getElementById('priorityPassCard');
    html2canvas(passCard, { scale: 3 }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'pt', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        
        const canvasWidth = canvas.width;
        const canvasHeight = canvas.height;
        const canvasAspectRatio = canvasWidth / canvasHeight;

        let imgWidth = pdfWidth - 80;
        let imgHeight = imgWidth / canvasAspectRatio;
        
        const x = (pdfWidth - imgWidth) / 2;
        const y = 40;

        pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
        pdf.save(`Priority_Pass_${currentPassId}.pdf`);
    });
}

function downloadRampSlipPDF() {
    const slipCard = document.getElementById('rampSlipCard');
    html2canvas(slipCard, { scale: 3 }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'pt', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        
        const canvasWidth = canvas.width;
        const canvasHeight = canvas.height;
        const canvasAspectRatio = canvasWidth / canvasHeight;

        let imgWidth = pdfWidth - 80;
        let imgHeight = imgWidth / canvasAspectRatio;

        const x = (pdfWidth - imgWidth) / 2;
        const y = 40;

        pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
        pdf.save(`Ramp_Slip_${currentRampCode}.pdf`);
    });
}
</script>

</body>
</html>
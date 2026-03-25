<?php
session_start();
include('includes/db_connect.php'); // Assuming $conn is established here

$receiptGenerated = false;
$full_name = $donation_amount = $txn = $date = $temple_name = ""; // Initialize variables

// --- NEW: Get Temple Name from URL (on initial page load) ---
$temple_name_from_url = "Unknown Temple"; // Default
if(isset($_GET['temple']) && !empty($_GET['temple'])) {
    // Sanitize for displaying in HTML
    $temple_name_from_url = htmlspecialchars($_GET['temple']); 
}

if(isset($_POST['pay'])){
    // --- Security Enhancement: Sanitize and prepare inputs ---
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zipcode = trim($_POST['zipcode']);
    $country = trim($_POST['country']);
    $email = trim($_POST['email']);
    $mobile_no = trim($_POST['mobile_no']);
    $aadhaar_no = trim($_POST['aadhaar_no']);
    $donation_amount = trim($_POST['donation_amount']);
    $payment_type = trim($_POST['payment_type']);
    $temple_name = trim($_POST['temple_name']); // --- NEW: Get Temple from hidden form field

    // --- Use Prepared Statements to prevent SQL Injection ---
    // --- ASSUMPTION: You have added 'payment_type' and 'temple_name' columns to your 'donations' table ---
    $sql = "INSERT INTO donations(full_name, address, city, state, zipcode, country, email, mobile_no, aadhaar_no, donation_amount, payment_type, temple_name) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    // --- UPDATED: 'sssssssssds' becomes 'sssssssssdss' (added 's' for temple_name) ---
    mysqli_stmt_bind_param($stmt, "sssssssssdss", 
        $full_name, $address, $city, $state, $zipcode, $country, 
        $email, $mobile_no, $aadhaar_no, $donation_amount, $payment_type, $temple_name
    );
    
    // Execute the statement
    if(mysqli_stmt_execute($stmt)) {
        // --- Logic Enhancement: Generate these *only* on success ---
        $receiptGenerated = true;
        $txn = "TXN" . rand(100000, 999999);
        $date = date("d M Y, h:i A");
        // Note: $full_name, $donation_amount, and $temple_name are already set from the POST data
    } else {
        // Handle error if needed
        echo "Error: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Temple Donation</title>

<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* --- Indian Touch Theme (Enhanced) --- */
:root {
  --primary-color: #ff9933; /* Saffron/Orange */
  --secondary-color: #d35400; /* Deep Orange */
  --text-color: #5d4037; /* Brown */
  --border-color: #e0b080; /* Light Brown */
  --bg-gradient-start: #ffe5b4; /* Light Apricot */
  --bg-gradient-end: #fff1e6; /* Very Light Peach */
  --card-bg: #fff3e0; /* Creamy */
  --receipt-bg: #fffdf6; /* Off-white */
}

/* --- Layout & "Fit on Screen" Fix --- */
* { box-sizing: border-box; }
body {
  font-family: "Baloo 2", cursive;
  margin: 0;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
  padding: 40px 20px;
}
.container { 
  position: relative; 
  width: 100%; 
  max-width: 700px; /* --- WIDER CONTAINER for 2 columns --- */
}
.decorative-pattern {
  position: absolute; top: -45px; right: -45px; width: 110px; height: 110px;
  background: radial-gradient(circle, var(--primary-color) 20%, transparent 70%);
  border-radius: 50%; opacity: 0.3; z-index: 0;
}
.form-card {
  background: var(--card-bg); 
  border-radius: 20px; 
  padding: 40px 30px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
  position: relative; 
  z-index: 1;
}

/* --- Typography --- */
h2 { 
  text-align: center; 
  color: var(--secondary-color); 
  margin-top: 0;
  margin-bottom: 5px; 
}
.subtitle { 
  text-align: center; 
  font-weight: 600; 
  color: #e67e22; 
  margin-bottom: 25px; 
}

/* --- NEW: Form Grid for Side-by-Side --- */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr; /* Two equal columns */
  gap: 0 20px; /* 0 vertical gap, 20px horizontal */
}
/* On mobile, stack columns */
@media (max-width: 600px) {
  .form-grid { grid-template-columns: 1fr; }
}

/* --- Form Inputs & Labels --- */
label { 
  font-weight: 600; 
  color: var(--text-color); 
  display: block; 
  margin-bottom: 5px; 
  font-size: 15px;
}
input {
  width: 100%; 
  padding: 12px 15px;
  margin-bottom: 15px; 
  border-radius: 10px;
  border: 1px solid var(--border-color); 
  font-size: 14px; 
  font-family: "Baloo 2", cursive;
  transition: all 0.2s ease-in-out;
}
input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 8px rgba(255, 153, 51, 0.5);
}

/* --- NEW: Payment Type Selector --- */
.payment-options {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}
.payment-options input[type="radio"] {
  display: none; /* Hide original radio button */
}
.payment-options label {
  flex: 1;
  padding: 12px;
  border: 2px solid var(--border-color);
  border-radius: 10px;
  text-align: center;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  background: var(--receipt-bg);
}
/* Style for the selected option */
.payment-options input[type="radio"]:checked + label {
  background: var(--primary-color);
  color: #fff;
  border-color: var(--secondary-color);
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* --- NEW: UPI & Card Forms --- */
.payment-method-box {
  display: none; /* Hide both by default */
  background: var(--receipt-bg);
  border: 1px dashed var(--border-color);
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
}
.upi-info {
  text-align: center;
  font-weight: 600;
  color: var(--text-color);
  line-height: 1.4;
}
.upi-info span {
  font-weight: 700;
  color: #000;
  display: block;
  font-size: 1.1em;
  word-break: break-all;
}

/* NEW: Grid for Expiry & CVV */
.card-details-grid {
  display: grid;
  grid-template-columns: 2fr 1fr; /* Expiry is wider than CVV */
  gap: 15px;
}
/* --- --- */

button {
  width: 100%; 
  padding: 14px; 
  border: none; 
  border-radius: 10px;
  background-color: var(--primary-color); 
  color: #fff; 
  font-size: 17px;
  cursor: pointer;
  font-weight: 700; 
  transition: all 0.3s;
  font-family: "Baloo 2", cursive;
}
button:hover { 
  background-color: #e67e22; 
  transform: translateY(-2px); 
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* --- Receipt Card Styles (Unchanged) --- */
.receipt-box {
  background: var(--receipt-bg);
  border: 2px solid #f3c892;
  border-radius: 16px;
  padding: 25px;
  color: var(--text-color);
  margin-bottom: 20px;
}
.receipt-box h3 {
  text-align: center; color: var(--secondary-color); margin-top: 0; margin-bottom: 15px;
}
.receipt-box p { margin: 8px 0; font-size: 15px; line-height: 1.5; }
.receipt-box b { color: #000; min-width: 120px; display: inline-block; }
.button-group {
  display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
}
.button-group a { text-decoration: none; }
.button-group button { width: 100%; }
.button-group .btn-secondary {
  background-color: #f3c892; color: var(--text-color);
}
.button-group .btn-secondary:hover {
  background-color: var(--border-color); transform: translateY(-2px);
}
</style>
</head>
<body>

<main class="container">
  <div class="decorative-pattern"></div>
  <div class="form-card">

  <?php if(!$receiptGenerated){ ?>

    <h2>🙏 Donation for <?php echo $temple_name_from_url; ?></h2>
    <p class="subtitle">Your offering is sacred</p>

    <form method="POST">
    
      <input type="hidden" name="temple_name" value="<?php echo $temple_name_from_url; ?>">

      <div class="form-grid">
        <div>
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" required>
        </div>
        <div>
          <label for="aadhaar_no">Aadhaar Number</label>
          <input type="text" id="aadhaar_no" name="aadhaar_no" pattern="\d{12}" title="Must be 12 digits" required>
        </div>
        <div>
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div>
          <label for="mobile_no">Mobile Number</label>
          <input type="text" id="mobile_no" name="mobile_no" pattern="\d{10}" title="Must be 10 digits" required>
        </div>
        <div>
          <label for="address">Address</label>
          <input type="text" id="address" name="address" required>
        </div>
        <div>
          <label for="city">City</label>
          <input type="text" id="city" name="city" required>
        </div>
        <div>
          <label for="state">State</label>
          <input type="text" id="state" name="state" required>
        </div>
        <div>
          <label for="zipcode">Zipcode</label>
          <input type="text" id="zipcode" name="zipcode" pattern="\d{6}" title="Must be 6 digits" required>
        </div>
        <div>
          <label for="country">Country</label>
          <input type="text" id="country" name="country" value="India" required>
        </div>
        <div>
          <label for="donation_amount">Donation Amount (₹)</label>
          <input type="number" id="donation_amount" name="donation_amount" min="1" required>
        </div>
      </div> <hr style="border:0; border-top:1px solid var(--border-color); margin: 25px 0;">

      <label>Payment Method</label>
      <div class="payment-options">
        <input type="radio" id="pay_upi" name="payment_type" value="UPI" checked>
        <label for="pay_upi">UPI</label>
        
        <input type="radio" id="pay_card" name="payment_type" value="Card">
        <label for="pay_card">Credit/Debit Card</label>
      </div>

      <div id="upiBox" class="payment-method-box upi-info">
        Please pay to the UPI ID below:
        <span>kudalkarsanjay46@okicici ✅</span>
      </div>

      <div id="cardBox" class="payment-method-box">
        <label for="card_name">Name on Card</label>
        <input type="text" id="card_name" name="card_name">
        
        <label for="card_number">Card Number</label>
        <input type="text" id="card_number" name="card_number" pattern="\d{16}" title="Must be 16 digits">

        <div class="card-details-grid">
          <div>
            <label for="card_expiry">Expiry (MM/YY)</label>
            <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" pattern="\d{2}/\d{2}">
          </div>
          <div>
            <label for="card_cvv">CVV</label>
            <input type="text" id="card_cvv" name="card_cvv" pattern="\d{3,4}">
          </div>
        </div>
        <small style="text-align:center; display:block; margin-top:10px; color:var(--text-color);">
          🔒 Your card details are not stored and are sent directly to our secure payment gateway.
        </small>
      </div>

      <button type="submit" name="pay">Record My Donation</button>

    </form>

  <?php } else { ?>

    <h2>✅ Donation Successful</h2>
    <p class="subtitle">Download your receipt</p>

    <div id="receiptDiv" class="receipt-box">
      <h3>Donation Receipt</h3>
      <p><b>Temple:</b> <?php echo htmlspecialchars($temple_name); ?></p>
      <p><b>Name:</b> <?php echo htmlspecialchars($full_name); ?></p>
      <p><b>Amount:</b> ₹<?php echo htmlspecialchars($donation_amount); ?></p>
      <p><b>Transaction ID:</b> <?php echo htmlspecialchars($txn); ?></p>
      <p><b>Date:</b> <?php echo htmlspecialchars($date); ?></p>
      <p style="text-align:center; margin-top:15px; font-size: 16px;">
        <i>Thank you for your divine contribution 🙏</i>
      </p>
    </div>

    <div class="button-group">
      <button id="downloadBtn">Download Receipt</button>
      <a href="dashboard.php"><button class="btn-secondary">Back to Dashboard</button></a>
    </div>

  <?php } ?>

  </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script>
<?php if(!$receiptGenerated){ // Only run this JS on the form page ?>
  
  // --- NEW: JavaScript to toggle payment forms ---
  const upiRadio = document.getElementById('pay_upi');
  const cardRadio = document.getElementById('pay_card');
  const upiBox = document.getElementById('upiBox');
  const cardBox = document.getElementById('cardBox');

  function togglePaymentForm() {
    if (cardRadio.checked) {
      upiBox.style.display = 'none';
      cardBox.style.display = 'block';
    } else {
      upiBox.style.display = 'block';
      cardBox.style.display = 'none';
    }
  }

  // Add event listeners
  upiRadio.addEventListener('change', togglePaymentForm);
  cardRadio.addEventListener('change', togglePaymentForm);

  // Run on page load to set the default state (UPI)
  togglePaymentForm();

<?php } else { // This is the receipt page JS ?>
  
document.getElementById("downloadBtn").addEventListener("click", function(){
    const receiptElement = document.getElementById("receiptDiv");
    const options = {
        margin:       1,
        filename:     'Donation_Receipt_<?php echo $txn; ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'in', format: 'a5', orientation: 'portrait' }
    };
    html2pdf().from(receiptElement).set(options).save();
});

<?php } ?>
</script>

</body>
</html>
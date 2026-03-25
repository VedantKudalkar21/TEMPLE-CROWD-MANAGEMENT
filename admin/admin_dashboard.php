<?php
session_start();
// --- UPDATED PATH ---
include('../includes/db_connect.php'); // ✅ Correct Path

// --- Security Check: Use admin_name from your login session ---
if (!isset($_SESSION['admin_name'])) {
    header("Location: admin_login.php");
    exit();
}
$admin_user = ucfirst($_SESSION['admin_name']);

// --- Handle Logout ---
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// --- Handle Complaint Resolution ---
$resolve_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resolve_complaint'])) {
    $complaint_id_to_resolve = intval($_POST['id']); // Assuming 'id' is your complaint ID column

    $sql_resolve = "UPDATE complaints SET status = 'Resolved', resolved_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'Pending'";
    if ($stmt_resolve = mysqli_prepare($conn, $sql_resolve)) {
        mysqli_stmt_bind_param($stmt_resolve, "i", $complaint_id_to_resolve);
        if (mysqli_stmt_execute($stmt_resolve)) {
            if (mysqli_stmt_affected_rows($stmt_resolve) > 0) {
                $resolve_message = '<div class="alert success">Complaint ID ' . $complaint_id_to_resolve . ' resolved.</div>';
            } else {
                $resolve_message = '<div class="alert error">Complaint already resolved or not found (ID: ' . $complaint_id_to_resolve . ').</div>';
            }
        } else { $resolve_message = '<div class="alert error">Error resolving complaint.</div>'; }
        mysqli_stmt_close($stmt_resolve);
    } else { $resolve_message = '<div class="alert error">DB error preparing resolve.</div>'; }
}

// --- Handle Add Temple ---
$add_temple_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_temple'])) {
    $temple_name = trim($_POST['temple_name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);

    if (empty($temple_name)) {
        $add_temple_message = '<div class="alert error">Temple Name is required.</div>';
    } else {
        $sql_add_temple = "INSERT INTO temples (temple_name, location, description, image_url) VALUES (?, ?, ?, ?)";
        if ($stmt_add = mysqli_prepare($conn, $sql_add_temple)) {
            mysqli_stmt_bind_param($stmt_add, "ssss", $temple_name, $location, $description, $image_url);
            if (mysqli_stmt_execute($stmt_add)) {
                $add_temple_message = '<div class="alert success">Temple "' . htmlspecialchars($temple_name) . '" added! Refresh to see in filters.</div>';
            } else {
                 if (mysqli_errno($conn) == 1062) { $add_temple_message = '<div class="alert error">Temple name already exists.</div>'; }
                 else { $add_temple_message = '<div class="alert error">Error adding temple: ' . mysqli_error($conn) . '</div>'; }
            }
            mysqli_stmt_close($stmt_add);
        } else { $add_temple_message = '<div class="alert error">DB error preparing add temple.</div>'; }
    }
}

// --- Fetch Temple List ---
$temples = [];
$sql_temples = "SELECT temple_id, temple_name FROM temples ORDER BY temple_name ASC";
$result_temples = mysqli_query($conn, $sql_temples);
if ($result_temples) { while ($row = mysqli_fetch_assoc($result_temples)) { $temples[] = $row; } }

// --- Get selected temple for filtering complaints ---
$selected_temple_filter = $_GET['temple'] ?? 'All';

// --- Fetch Pending Complaints (Filtered) ---
$complaints = [];
// Assuming your complaints table has columns: id, user_id, name, email, temple, complaint_text, created_at, status
$sql_complaints = "SELECT id, user_id, name, email, temple, complaint_text, created_at FROM complaints WHERE status = 'Pending'";
if ($selected_temple_filter != 'All') { $sql_complaints .= " AND temple = ?"; }
$sql_complaints .= " ORDER BY created_at DESC";

if ($stmt_complaints = mysqli_prepare($conn, $sql_complaints)) {
    if ($selected_temple_filter != 'All') { mysqli_stmt_bind_param($stmt_complaints, "s", $selected_temple_filter); }
    if (mysqli_stmt_execute($stmt_complaints)) {
        $result_complaints = mysqli_stmt_get_result($stmt_complaints);
        while ($row = mysqli_fetch_assoc($result_complaints)) { $complaints[] = $row; }
    } else { error_log("Error executing complaints query: " . mysqli_stmt_error($stmt_complaints)); }
    mysqli_stmt_close($stmt_complaints);
} else { error_log("Error preparing complaints query: " . mysqli_error($conn)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | Mandir Mitra</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* --- Basic Reset & Body --- */
    :root {
      --primary-color: #ff9933; /* Saffron/Orange */
      --secondary-color: #d35400; /* Deep Orange */
      --text-color: #5d4037; /* Brown */
      --text-light: #6d4c41; /* Lighter Brown */
      --border-color: #e0b080; /* Light Brown */
      --bg-color: #fdf6e7; /* Even Lighter Peach/Cream */
      --card-bg: #ffffff; /* White cards */
      --card-bg-alt: #fff8e1; /* Creamy card alternate */
      --success-bg: #dff0d8; /* Light Green */
      --success-text: #3c763d; /* Dark Green */
      --error-bg: #f2dede; /* Light Red */
      --error-text: #a94442; /* Dark Red */
      --info-bg: #e7f3fe; /* Lighter Blue */
      --info-text: #31708f; /* Dark Blue */
      --card-shadow: 0 6px 18px rgba(0, 0, 0, 0.09); /* Slightly more pronounced shadow */
      --border-radius: 12px; /* Consistent border radius */
      --blue-button: #2980b9;
      --blue-button-hover: #216794;
      --red-button: #c0392b;
      --red-button-hover: #a03024;
      --purple-button: #8e44ad;
      --purple-button-hover: #70308c;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: "Baloo 2", cursive;
      background-color: var(--bg-color);
      color: var(--text-color);
      line-height: 1.6;
      font-size: 16px;
    }

    /* --- Header --- */
    header {
      background: var(--primary-color); color: #fff; display: flex;
      justify-content: space-between; align-items: center; padding: 15px 40px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15); position: sticky; top: 0; z-index: 100; /* Sticky header */
    }
    .header-left span { font-size: 24px; font-weight: 700; }
    .user-info { display: flex; align-items: center; gap: 20px; font-size: 17px; }
    .user-buttons form { display: inline-block; }
    .user-buttons button {
      background: #fff; border: none; padding: 9px 15px; border-radius: var(--border-radius);
      cursor: pointer; font-family: inherit; font-weight: 600; transition: 0.3s;
      color: var(--secondary-color); font-size: 15px;
    }
    .user-buttons button:hover { background: #ffe0b2; transform: scale(1.05); }

    /* --- Temple Filter (MOVED) --- */
    .temple-filter-wrapper {
        background-color: var(--card-bg-alt); /* Creamy background */
        padding: 15px 40px; /* Match header padding */
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border-bottom: 1px solid #f3d9b1;
    }
    .temple-filter { display: flex; align-items: center; gap: 10px; max-width: 1400px; margin: 0 auto; width: 95%;}
    .temple-filter label { font-weight: 600; font-size: 17px; margin-right: 5px; color: #4a2c0a; }
    .temple-filter select {
        padding: 9px 14px; border-radius: 8px; border: 1px solid var(--border-color);
        font-family: inherit; font-size: 16px; margin-right: 10px;
        background-color: white; min-width: 200px;
    }
     .temple-filter small { font-size: 14px; color: var(--text-light); margin-left: auto; /* Push to right */ }

    /* --- Main Content --- */
    .dashboard-container { width: 95%; max-width: 1400px; margin: 30px auto 40px auto; } /* Adjusted top margin */
    h2 {
      font-size: 30px; font-weight: 700; color: var(--secondary-color);
      margin-bottom: 30px; border-bottom: 3px solid var(--primary-color);
      padding-bottom: 15px; text-align: center;
    }

    /* --- Grid Layout --- */
    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 30px; margin-bottom: 40px; }

    /* --- Cards --- */
    .info-card {
      background: var(--card-bg); padding: 25px 30px; border-radius: var(--border-radius);
      box-shadow: var(--card-shadow); display: flex; flex-direction: column;
      border: 1px solid #f3d9b1;
    }
    .info-card h3 {
      color: #4a2c0a; font-size: 22px; margin-top: 0; margin-bottom: 18px;
      border-bottom: 2px solid #ffcc80; padding-bottom: 10px;
      display: flex; align-items: center; gap: 10px;
    }
    .info-card p, .info-card div, .info-card ul { margin-bottom: 12px; font-size: 17px; }
    .info-card small { font-size: 14px; color: var(--text-light); display: block; margin-top: 15px; text-align: center; } /* Increased top margin */

    /* --- Top Metrics Row --- */
    .metrics-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 40px; }
    .metric-card { padding: 25px; border-radius: var(--border-radius); color: #fff; box-shadow: var(--card-shadow); text-align: center; }
    .metric-card-red { background: linear-gradient(135deg, #e74c3c, var(--error-text)); }
    .metric-card-blue { background: linear-gradient(135deg, #3498db, var(--info-text)); }
    .metric-card-green { background: linear-gradient(135deg, #2ecc71, var(--success-text)); }
    .metric-value { font-size: 56px; font-weight: 700; line-height: 1.1; display: block; }
    .metric-label { font-size: 17px; font-weight: 500; opacity: 0.9; display: block; margin-top: 5px; }
    .metric-sublabel { font-size: 14px; font-weight: 400; opacity: 0.8; display: block; margin-top: 2px; }

    /* --- Section Styling --- */
    .dashboard-section {
        background: var(--card-bg); padding: 30px; border-radius: var(--border-radius);
        box-shadow: var(--card-shadow); margin-bottom: 40px; border: 1px solid #f3d9b1;
    }
    .dashboard-section h3 {
        color: #4a2c0a; font-size: 24px; margin-top: 0; margin-bottom: 20px;
        border-bottom: 2px solid #ffcc80; padding-bottom: 10px;
        display: flex; align-items: center; gap: 10px;
    }

    /* --- IoT and Traffic Status Boxes --- */
    .status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .status-box { padding: 15px 20px; border-radius: 10px; border: 1px solid; transition: background-color 0.5s, border-color 0.5s; } /* Added transition */
    .status-box-green { background-color: var(--success-bg); border-color: #b2dba1; color: var(--success-text); }
    .status-box-red { background-color: var(--error-bg); border-color: #e4b9b9; color: var(--error-text); }
    .status-box-yellow { background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b; }
    .status-label { font-size: 17px; font-weight: 600; display: block; margin-bottom: 5px; opacity: 0.9; }
    .status-value { font-size: 19px; font-weight: 700; display: block; }
    .status-details { font-size: 15px; font-weight: 500; opacity: 0.8; margin-top: 3px; }

    /* --- Action Buttons --- */
    .action-button {
        display: block; width: 100%; text-align: center; padding: 14px 20px;
        border: none; border-radius: var(--border-radius); color: white;
        font-family: inherit; font-size: 17px; font-weight: 600; cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        margin-top: 20px; /* Increased margin */
    }
    .action-button:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .action-button-purple { background-color: var(--purple-button); }
    .action-button-purple:hover { background-color: var(--purple-button-hover); }
    .action-button-danger { background-color: var(--red-button); }
    .action-button-danger:hover { background-color: var(--red-button-hover); }
    /* REMOVED blue button styles */
    .placeholder-text { /* Style for text replacing buttons */
        text-align: center;
        color: var(--text-light);
        font-style: italic;
        margin-top: 25px; /* Adjust as needed */
        font-size: 15px;
        padding: 10px;
        background-color: rgba(0,0,0,0.02);
        border-radius: 8px;
    }


    /* --- Staff Deployment --- */
    .staff-deployment { display: flex; justify-content: space-around; text-align: center; margin: 25px 0; padding: 20px 0; border-top: 1px dashed var(--border-color); border-bottom: 1px dashed var(--border-color); gap: 20px; flex-wrap: wrap; }
    .staff-zone { flex: 1; min-width: 120px; }
    .staff-zone .value { font-size: 38px; font-weight: 700; color: var(--purple-button); line-height: 1; }
    .staff-zone .label { font-size: 16px; font-weight: 600; color: var(--text-color); display: block; margin-top: 2px; }

    /* --- Active Incidents --- */
    #incidentList { list-style: none; padding: 0; max-height: 200px; overflow-y: auto; margin-bottom: 15px;}
    #incidentList li { margin-bottom: 12px; }
    .incident-item { display: flex; justify-content: space-between; align-items: center; color: var(--error-text); font-weight: 500; font-size: 16px; padding: 10px 15px; background: var(--error-bg); border-radius: 8px; border: 1px solid #f5c6cb; transition: opacity 0.3s ease; /* Added transition */ }
    .incident-item .details { display: flex; align-items: center; gap: 10px; }
    .incident-item .time-ago { font-size: 13px; color: #a04000; }
    .resolve-incident-btn { background: none; border: none; color: var(--error-text); cursor: pointer; font-size: 14px; font-weight: bold; text-decoration: underline; padding: 0; }
    .resolved-text { color: var(--success-text); font-weight: bold; font-size: 15px; }

    /* --- Complaints & Add Temple --- */
    .complaints-table-wrapper { overflow-x: auto; margin-top: 15px; }
    .complaints-table { width: 100%; border-collapse: collapse; }
    .complaints-table th, .complaints-table td { border: 1px solid var(--border-color); padding: 12px 14px; text-align: left; vertical-align: top; font-size: 15px; }
    .complaints-table th { background-color: #ffecd1; font-weight: 600; font-size: 16px; }
    .complaints-table td { background-color: #fff; }
    .complaints-table .message-col { max-width: 320px; min-width: 220px; word-wrap: break-word; }
    .complaints-table .action-col form { display: inline; }
    .resolve-button { background-color: var(--secondary-color); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: background-color 0.2s; }
    .resolve-button:hover { background-color: #a04000; }

    .add-temple-form label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 17px; }
    .add-temple-form input[type="text"], .add-temple-form textarea { width: 100%; padding: 12px; margin-bottom: 18px; border-radius: 8px; border: 1px solid var(--border-color); font-family: inherit; font-size: 16px; }
    .add-temple-form textarea { resize: vertical; min-height: 90px; }
    .add-temple-form button { background-color: var(--primary-color); color: white; border: none; padding: 14px 22px; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 17px; transition: background-color 0.3s; }
    .add-temple-form button:hover { background-color: var(--secondary-color); }

    /* --- Alert Messages --- */
    .alert { padding: 14px 20px; border-radius: 8px; margin: 0 0 25px 0; font-weight: 600; font-size: 16px; border: 1px solid transparent; }
    .alert.success { background-color: var(--success-bg); color: var(--success-text); border-color: #c3e6cb; }
    .alert.error { background-color: var(--error-bg); color: var(--error-text); border-color: #f5c6cb; }

    /* --- NEW Popup Message --- */
    .popup-message {
        position: fixed; top: 80px; left: 50%; transform: translateX(-50%);
        background-color: var(--success-bg); color: var(--success-text);
        padding: 15px 25px; border-radius: 8px; border: 1px solid #c3e6cb;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); font-size: 16px; font-weight: 600;
        z-index: 1001; opacity: 0; transition: opacity 0.5s ease, top 0.5s ease;
    }
    .popup-message.show { top: 100px; opacity: 1; }

    /* Responsive adjustments */
    @media (max-width: 992px) { .metrics-row { grid-template-columns: 1fr; } .status-grid { grid-template-columns: 1fr; } }
    @media (max-width: 768px) {
        header { flex-direction: column; gap: 10px; padding: 15px; text-align: center; }
        .user-info { flex-direction: column; gap: 8px; }
        .temple-filter-wrapper { padding: 15px 20px; }
        .temple-filter { flex-direction: column; gap: 8px; align-items: stretch; }
        .temple-filter select { width: 100%; margin-right: 0; }
        .temple-filter small { margin-left: 0; text-align: center; }
        .staff-deployment { gap: 15px; }
        .staff-zone { flex-basis: calc(50% - 10px); }
        h2 { font-size: 26px; }
        .info-card h3 { font-size: 20px; }
        .metric-value { font-size: 48px; }
        .metric-label { font-size: 16px; }
        .staff-zone .value { font-size: 30px; }
        .status-label, .surveillance-item .label, .traffic-parking-item .label { font-size: 16px; }
        .status-value, .surveillance-item .details, .traffic-parking-item .details { font-size: 16px; }
    }
</style>
</head>
<body>

<div id="popup-message" class="popup-message"></div>

<header>
    <div class="header-left">
      <span>🔑 Admin Panel | Mandir Mitra</span>
    </div>
    <div class="user-info">
      <span>👤 <?php echo $admin_user; ?></span>
      <div class="user-buttons">
        <form method="POST"><button name="logout">Logout</button></form>
      </div>
    </div>
</header>

<div class="temple-filter-wrapper">
    <div class="temple-filter">
        <label for="temple_select">View Complaints For:</label>
        <select id="temple_select" name="temple">
            <option value="All" <?php echo ($selected_temple_filter == 'All') ? 'selected' : ''; ?>>All Temples</option>
            <?php foreach ($temples as $temple): ?>
                <option value="<?php echo htmlspecialchars($temple['temple_name']); ?>" <?php echo ($selected_temple_filter == $temple['temple_name']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($temple['temple_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small>Select temple to filter complaints list below</small>
    </div>
</div>

<main class="dashboard-container">
    <h2>Real-time IoT Monitoring & AI Forecast 👋</h2>

    <?php echo $resolve_message; ?>
    <?php echo $add_temple_message; ?>

    <div class="metrics-row">
        <div class="metric-card metric-card-red">
            <span class="metric-value" id="peak-crowd">--</span>
            <span class="metric-label">Predicted 24hr Peak Crowd</span>
            <span class="metric-sublabel">Forecast by AI Model</span>
        </div>
        <div class="metric-card metric-card-blue">
             <span class="metric-value" id="staff-rec">--</span>
            <span class="metric-label">Current Staff Required</span>
             <span class="metric-sublabel">Based on Crowd Load</span>
        </div>
         <div class="metric-card metric-card-green">
             <span class="metric-value" id="queue-throughput">--</span>
            <span class="metric-label">Average Queue Throughput</span>
             <span class="metric-sublabel">Pilgrims per minute</span>
        </div>
    </div>

    <section class="dashboard-section">
        <h3>📹 IoT Surveillance & Density Status (CCTV/Sensors)</h3>
        <div class="status-grid">
            <div id="main-hall-density-box" class="status-box status-box-green">
                <span class="status-label">Main Hall Density (Zone C)</span>
                <span id="main-hall-density-status" class="status-value">--</span>
                <span id="main-hall-density-details" class="status-details">(Calculating...)</span>
            </div>
             <div id="entry-gate-anomaly-box" class="status-box status-box-green">
                <span class="status-label">Entry Gate Anomaly Detection</span>
                <span id="entry-gate-anomaly-status" class="status-value">NORMAL</span>
                 <span id="entry-gate-anomaly-details" class="status-details">(No anomalies detected)</span>
            </div>
        </div>
        <p class="placeholder-text">Live camera feed integration planned for future update.</p>
        <small>(Simulated Sensor Data - Updates approx. every 10 secs)</small>
    </section>

    <section class="dashboard-section">
        <h3>🧑‍🤝‍🧑 Manager: Staff Deployment Management</h3>
         <div class="staff-deployment">
             <div class="staff-zone">
                <div id="staff-zone-a" class="value">--</div>
                <div class="label">Zone A (Gate)</div>
            </div>
             <div class="staff-zone">
                <div id="staff-zone-b" class="value">--</div>
                <div class="label">Zone B (Queue)</div>
            </div>
             <div class="staff-zone">
                <div id="staff-zone-c" class="value">--</div>
                <div class="label">Zone C (Hall)</div>
            </div>
             <div class="staff-zone">
                <div id="staff-zone-d" class="value">--</div>
                <div class="label">Medical Post 1</div>
            </div>
        </div>
        <button class="action-button action-button-purple" id="dispatch-staff-btn">Auto-Dispatch Staff to Critical Zones</button>
     </section>


    <div class="dashboard-grid">
        <div class="info-card">
            <h3>🚨 Active Incidents (Real-time Alerts)</h3>
            <ul id="incidentList">
                <li>Loading incidents...</li>
            </ul>
            <button class="action-button action-button-danger" onclick="resolveAllIncidents()">Resolve All Incidents</button>
        </div>

         <div class="info-card">
            <h3>🚦 Traffic & Parking Control</h3>
             <div class="traffic-parking-status">
                <div class="traffic-parking-item">
                     <div class="label">Approach Road Traffic Flow</div>
                    <div id="traffic-flow-box" class="status-box status-box-green" style="margin-bottom: 10px;">
                        <span id="traffic-flow-status" class="status-value">--</span>
                    </div>
                </div>
                <div class="traffic-parking-item">
                    <div class="label">Parking Lot Occupancy</div>
                     <div id="parking-occupancy-box" class="status-box status-box-green">
                        <span id="parking-occupancy-status" class="status-value">--</span>
                         <span id="parking-occupancy-details" class="status-details">(Calculating...)</span>
                    </div>
                </div>
            </div>
             <p class="placeholder-text">Traffic signal API integration under development.</p>
             <small>(Simulated Live Data)</small>
        </div>
    </div> <section class="dashboard-section" style="margin-bottom: 30px;">
        <h3>📝 Pending User Complaints (<?php echo htmlspecialchars($selected_temple_filter); ?>)</h3>
        <?php if (empty($complaints)): ?>
            <p style="text-align: center; font-weight: 500;">No pending complaints found<?php echo ($selected_temple_filter != 'All' ? ' for ' . htmlspecialchars($selected_temple_filter) : ''); ?>.</p>
        <?php else: ?>
            <div class="complaints-table-wrapper">
                <table class="complaints-table">
                    <thead>
                        <tr>
                            <th>ID</th> <th>User ID</th> <th>Name</th> <th>Email</th>
                            <th>Temple</th> <th class="message-col">Complaint Text</th>
                            <th>Received</th> <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?php echo $complaint['id']; ?></td>
                            <td><?php echo htmlspecialchars($complaint['user_id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($complaint['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($complaint['email']); ?></td>
                            <td><?php echo htmlspecialchars($complaint['temple']); ?></td>
                            <td class="message-col"><?php echo nl2br(htmlspecialchars($complaint['complaint_text'])); ?></td>
                            <td><?php echo date("d M Y, h:i A", strtotime($complaint['created_at'])); ?></td>
                            <td class="action-col">
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <input type="hidden" name="id" value="<?php echo $complaint['id']; ?>">
                                    <button type="submit" name="resolve_complaint" class="resolve-button">Resolve</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="dashboard-section">
        <h3>➕ Add New Temple</h3>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="add-temple-form">
            <div> <label for="temple_name">Temple Name*</label> <input type="text" id="temple_name" name="temple_name" required> </div>
            <div> <label for="location">Location (City, State)</label> <input type="text" id="location" name="location"> </div>
            <div> <label for="description">Description (Optional)</label> <textarea id="description" name="description"></textarea> </div>
            <div> <label for="image_url">Image URL (Optional)</label> <input type="text" id="image_url" name="image_url"> </div>
            <button type="submit" name="add_temple">Add Temple</button>
        </form>
    </section>

</main>

<script>
    // --- Helper function to get random int ---
    function getRandomInt(min, max) { min = Math.ceil(min); max = Math.floor(max); return Math.floor(Math.random() * (max - min + 1)) + min; }
    // --- Helper function for random float ---
    function getRandomFloat(min, max, decimals) { const str = (Math.random() * (max - min) + min).toFixed(decimals); return parseFloat(str); }

    // --- Simulated Active Incidents ---
    let activeIncidents = [];
    let incidentCounter = 0;

    function addRandomIncident() {
        const incidentTypes = ["Medical Emergency", "Lost Child", "Security Alert", " overcrowding"];
        const zones = ["Entry Queue", "Main Hall", "Prasadam Counter", "Parking Lot B", "Exit Gate"];
        if (Math.random() < 0.15) {
             const newIncident = { id: incidentCounter++, type: incidentTypes[getRandomInt(0, incidentTypes.length - 1)], zone: zones[getRandomInt(0, zones.length - 1)], time: new Date() };
             activeIncidents.push(newIncident);
             if (activeIncidents.length > 5) { activeIncidents.shift(); }
             renderIncidentList();
        }
    }

    function resolveIncident(id) {
        activeIncidents = activeIncidents.filter(inc => inc.id !== id);
        const itemToRemove = document.querySelector(`.incident-item[data-incident-id='${id}']`);
        if (itemToRemove) {
             itemToRemove.style.opacity = '0';
             setTimeout(() => { itemToRemove.remove(); checkEmptyIncidents(); }, 300);
        } else { // Fallback if item not found
             renderIncidentList();
        }
    }

    function resolveAllIncidents() { activeIncidents = []; renderIncidentList(); }

     function checkEmptyIncidents() {
         const list = document.getElementById('incidentList');
         if (list && activeIncidents.length === 0 && list.children.length === 0) { // Check if visually empty too
             list.innerHTML = `<li style="color: var(--success-text); font-weight: 600;">✅ No active high-priority incidents.</li>`;
         }
     }

    function renderIncidentList() {
        const list = document.getElementById('incidentList');
        if (!list) return;
        if (activeIncidents.length === 0) {
            list.innerHTML = `<li style="color: var(--success-text); font-weight: 600;">✅ No active high-priority incidents.</li>`;
        } else {
            list.innerHTML = activeIncidents.map(inc => {
                const minutesAgo = Math.max(0, Math.floor((new Date() - inc.time) / 60000));
                return `<li class="incident-item" data-incident-id="${inc.id}">
                    <span>🚨 ${inc.type} in ${inc.zone}</span>
                    <span class="details">
                         <span class="time-ago">${minutesAgo} min ago</span>
                         <button class="resolve-incident-btn" data-incident-id="${inc.id}" onclick="resolveIncident(${inc.id})">Resolve</button>
                    </span>
                 </li>`;
            }).join('');
        }
    }

    // --- Update Random Metrics ---
    function updateMetrics() {
        // Top Row
        const peakCrowdEl = document.getElementById('peak-crowd');
        const staffRecEl = document.getElementById('staff-rec');
        const queueEl = document.getElementById('queue-throughput');
        // Staff Zones
        const staffZoneA = document.getElementById('staff-zone-a');
        const staffZoneB = document.getElementById('staff-zone-b');
        const staffZoneC = document.getElementById('staff-zone-c');
        const staffZoneD = document.getElementById('staff-zone-d');
        // Surveillance
        const hallDensityBox = document.getElementById('main-hall-density-box');
        const hallDensityVal = document.getElementById('main-hall-density-status');
        const hallDensityDet = document.getElementById('main-hall-density-details');
        const anomalyBox = document.getElementById('entry-gate-anomaly-box');
        const anomalyVal = document.getElementById('entry-gate-anomaly-status');
        const anomalyDet = document.getElementById('entry-gate-anomaly-details');
        // Traffic & Parking
        const trafficFlowBox = document.getElementById('traffic-flow-box');
        const trafficFlowVal = document.getElementById('traffic-flow-status');
        const parkingOccBox = document.getElementById('parking-occupancy-box');
        const parkingOccVal = document.getElementById('parking-occupancy-status');
        const parkingOccDet = document.getElementById('parking-occupancy-details');

        // Update Top Row Metrics
        if(peakCrowdEl) peakCrowdEl.textContent = getRandomInt(4000, 15000);
        if(staffRecEl) staffRecEl.textContent = getRandomInt(50, 200);
        if(queueEl) queueEl.textContent = getRandomInt(5, 40); // Pilgrims per minute

        // Update Staff Zones (Deployed / Required format)
        if(staffZoneA) staffZoneA.textContent = `${getRandomInt(4, 10)}/${getRandomInt(5, 12)}`;
        if(staffZoneB) staffZoneB.textContent = `${getRandomInt(8, 20)}/${getRandomInt(10, 25)}`;
        if(staffZoneC) staffZoneC.textContent = `${getRandomInt(10, 25)}/${getRandomInt(12, 30)}`;
        if(staffZoneD) staffZoneD.textContent = `${getRandomInt(2, 5)}/${getRandomInt(2, 6)}`;

        // Update Surveillance (Simulating ~10 sec update)
        if (Math.random() < 0.5) {
            if (hallDensityVal && hallDensityDet && hallDensityBox) {
                 const density = getRandomFloat(0.1, 1.5, 1);
                 let densityStatus = "LOW"; let boxClass = "status-box-green";
                 if (density > 0.8) { densityStatus = "HIGH"; boxClass = "status-box-red"; }
                 else if (density > 0.4) { densityStatus = "MODERATE"; boxClass = "status-box-yellow"; }
                 hallDensityVal.textContent = densityStatus;
                 hallDensityDet.textContent = `(${density} people/m²)`;
                 hallDensityBox.className = `status-box ${boxClass}`;
            }
            if (anomalyVal && anomalyDet && anomalyBox) {
                if (Math.random() < 0.1) {
                    anomalyVal.textContent = "ANOMALY";
                    anomalyDet.textContent = `(Unusual crowd flow detected)`;
                    anomalyBox.className = 'status-box status-box-red';
                } else {
                    anomalyVal.textContent = "NORMAL";
                    anomalyDet.textContent = `(No anomalies detected)`;
                     anomalyBox.className = 'status-box status-box-green';
                }
            }
        }

        // Update Traffic & Parking
         if (trafficFlowVal && trafficFlowBox) {
             const flow = Math.random();
             let flowStatus = "Free Flow"; let boxClass = "status-box-green";
             if (flow > 0.7) { flowStatus = "Heavy Congestion"; boxClass = "status-box-red"; }
             else if (flow > 0.3) { flowStatus = "Moderate Congestion"; boxClass = "status-box-yellow"; }
             trafficFlowVal.textContent = flowStatus;
             trafficFlowBox.className = `status-box ${boxClass}`;
         }
         if(parkingOccVal && parkingOccDet && parkingOccBox) {
             const maxSlots = 200; const occupied = getRandomInt(10, maxSlots);
             const percentage = ((occupied / maxSlots) * 100).toFixed(0);
             parkingOccVal.textContent = `${occupied} / ${maxSlots}`;
             parkingOccDet.textContent = `(${percentage}% full)`;
             let boxClass = "status-box-green";
             if (percentage > 85) { boxClass = "status-box-red"; }
             else if (percentage > 60) { boxClass = "status-box-yellow"; }
              parkingOccBox.className = `status-box ${boxClass}`;
         }
    }

    // --- Temple Filter Change Listener ---
    const templeSelect = document.getElementById('temple_select');
    if (templeSelect) {
        templeSelect.addEventListener('change', (e) => {
            const selectedTemple = e.target.value;
            window.location.href = `admin_dashboard.php?temple=${encodeURIComponent(selectedTemple)}`;
        });
    }

    // --- NEW: Staff Dispatch Popup ---
    const dispatchBtn = document.getElementById('dispatch-staff-btn');
    const popupMsgEl = document.getElementById('popup-message');
    let popupTimeout; // To manage hiding the popup

    function showPopupMessage(message) {
        if (!popupMsgEl) return;
        popupMsgEl.textContent = message;
        popupMsgEl.classList.add('show');

        // Clear previous timeout if exists
        clearTimeout(popupTimeout);

        // Hide popup after 3 seconds
        popupTimeout = setTimeout(() => {
            popupMsgEl.classList.remove('show');
        }, 3000);
    }

    if (dispatchBtn) {
        dispatchBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent potential form submission if it were in a form
            // Simulate dispatch action
            showPopupMessage('✅ Staff auto-dispatch initiated based on current conditions!');
            // You could potentially trigger an updateMetrics() call here too
            updateMetrics(); // Refresh metrics after dispatch
        });
    }

    // --- Run on page load ---
    document.addEventListener('DOMContentLoaded', () => {
        updateMetrics(); // Initial call
        renderIncidentList(); // Initial render
        setInterval(updateMetrics, 5000); // Update metrics every 5 seconds
        setInterval(addRandomIncident, 7000); // Chance to add incident every 7 seconds
    });

</script>

</body>
</html>
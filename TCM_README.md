<div align="center">

# 🛕 Temple Crowd Management System

*A web-based integrated platform for managing temple visitors, poojas, and crowd capacity*

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![HTML5](https://img.shields.io/badge/HTML5-CSS3-E34F26?logo=html5&logoColor=white)]()
[![License](https://img.shields.io/badge/License-MIT-green.svg)]()
[![Status](https://img.shields.io/badge/Status-Active-brightgreen.svg)]()

</div>

---

## 📌 Overview

The **Temple Crowd Management System** is a full-stack web application designed to digitize and streamline temple operations. It provides temple administrators with a unified platform to register visitors, schedule poojas without conflicts, monitor crowd capacity in real time, and manage all administrative tasks — without requiring any specialized hardware.

> 🎓 Developed as part of academic project work at [Your Institution], Maharashtra, addressing the gap in integrated, hardware-free religious venue management systems.

---

## 🚨 The Problem

Temples face serious operational and safety challenges:
- 📋 **Manual visitor registers** — error-prone, no search, no history
- 📅 **Double-booked poojas** — two events scheduled at the same time/venue
- 👥 **No crowd tracking** — no way to know if capacity is exceeded
- 🗂️ **Fragmented admin tools** — visitor data, schedules, and records in separate places

**TCM solves all of these in one system.**

---

## ✨ Features

| Module | What it does |
|---|---|
| 👤 **Visitor Registration** | Register devotees, store records, track visit history |
| 📅 **Pooja Scheduling** | Book events with automatic conflict detection |
| 📊 **Admin Dashboard** | Unified view of visitors, crowd, and schedule |
| 👥 **Crowd Monitoring** | Real-time capacity tracking — no IoT hardware needed |

### Why TCM is different from other temple systems:

- ✅ **All-in-one** — visitor + scheduling + crowd + admin in one platform
- ✅ **Conflict detection** — prevents double-booking of poojas automatically
- ✅ **No hardware required** — crowd monitoring via software, not sensors
- ✅ **Persistent records** — every visit, every event stored in MySQL
- ✅ **Browser accessible** — works on desktop, tablet, and mobile

---

## 🖥️ Screenshots

> *(Add your screenshots here after deployment)*

| Admin Dashboard | Visitor Registration | Pooja Schedule |
|---|---|---|
| `dashboard.png` | `register.png` | `schedule.png` |

---

## 🛠️ Installation

### Prerequisites

- PHP 8.0+
- MySQL 8.0+
- Apache Web Server (XAMPP recommended for local)

### Step 1: Clone the Repository

```bash
git clone https://github.com/VedantKudalkar21/TEMPLE-CROWD-MANAGEMENT.git
cd TEMPLE-CROWD-MANAGEMENT
```

### Step 2: Set Up the Database

1. Open **phpMyAdmin** or MySQL CLI
2. Create a new database:
```sql
CREATE DATABASE temple_management;
```
3. Import the schema:
```bash
mysql -u root -p temple_management < database/schema.sql
```

### Step 3: Configure Database Connection

Edit `config/db.php`:
```php
<?php
$host     = 'localhost';
$dbname   = 'temple_management';
$username = 'root';       // your MySQL username
$password = '';           // your MySQL password

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

### Step 4: Run the Application

Place the project folder in your web server root:
- **XAMPP:** `C:/xampp/htdocs/TEMPLE-CROWD-MANAGEMENT/`
- **LAMP:** `/var/www/html/TEMPLE-CROWD-MANAGEMENT/`

Open in browser:
```
http://localhost/TEMPLE-CROWD-MANAGEMENT/
```

---

## 🗂️ Project Structure

```
TEMPLE-CROWD-MANAGEMENT/
│
├── index.php                   # Entry point / home
├── config/
│   └── db.php                  # Database connection
│
├── modules/
│   ├── visitor_registration.php
│   ├── pooja_schedule.php
│   ├── crowd_monitor.php
│   └── admin_dashboard.php
│
├── admin/
│   ├── login.php
│   ├── logout.php
│   └── manage_users.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
│
├── database/
│   └── schema.sql              # Full DB schema for import
│
└── README.md
```

---

## 🗄️ Database Tables

| Table | Purpose |
|---|---|
| `visitors` | Devotee registrations, entry/exit times |
| `pooja_schedule` | Event bookings with venue and timing |
| `admins` | Admin login credentials and roles |
| `venues` | Temple halls and their capacities |

---

## 👤 Admin Credentials (Default)

```
Username: admin
Password: admin123
```
> ⚠️ **Change these immediately after first login in production.**

---

## 📊 How Crowd Monitoring Works

TCM tracks crowd count **without any hardware sensors**:

1. When a visitor **enters** → `entry_time` is logged in the database
2. When a visitor **exits** → `exit_time` is updated
3. **Current crowd** = visitors with `entry_time` today and no `exit_time`
4. If crowd exceeds `MAX_CAPACITY` (set in config) → admin sees a **red alert**

---

## 🔬 Research & Academic Context

This project was developed as part of academic research comparing temple and crowd management systems. It addresses drawbacks found in 6 prior works:

- IoT-based systems (require hardware — W3)
- Single-feature web apps (no integration — W2, W4)
- Mobile-only systems (exclude non-smartphone users — W5)
- Systems with no conflict detection (double-booking — W2)

📄 See [IEEE Research Paper](./TCM_IEEE_Research_Paper.md) | 📋 See [Literature Survey](./TCM_literature_survey.md) | 🔧 See [Improvements](./TCM_improvements.md)

---

## 🚀 Future Enhancements

- [ ] QR code-based visitor check-in
- [ ] SMS/WhatsApp alerts when capacity is near full
- [ ] Multi-temple support with centralized console
- [ ] Predictive crowd forecasting from historical data
- [ ] Role-based access control (staff / admin / superadmin)
- [ ] Online payment for prasad and donations

---

## 👨‍💻 Author

**Vedant Kudalkar**
- GitHub: [@VedantKudalkar21](https://github.com/VedantKudalkar21)
- Project: [TEMPLE-CROWD-MANAGEMENT](https://github.com/VedantKudalkar21/TEMPLE-CROWD-MANAGEMENT)

---

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

---

<div align="center">
⭐ If this project helped you, please give it a star!
</div>

# Literature Survey: Temple & Religious Venue Crowd Management Systems
## Project: Temple Crowd Management System
### Author: Vedant Kudalkar

---

## 1. Introduction

Managing large crowds at religious venues is a persistent and safety-critical challenge across India and globally. Stampedes at major religious gatherings — Kumbh Mela, Char Dham, and local temple festivals — have resulted in significant casualties, highlighting the urgent need for systematic crowd management solutions. This literature survey reviews key prior works in the domains of crowd management, visitor registration systems, religious event scheduling, and web-based administrative dashboards, identifying their limitations and the gaps addressed by the Temple Crowd Management System.

---

## 2. Prior Works and Their Drawbacks

---

### [W1] Manoj, K. et al. (2021) — "Digital Queue Management System for Religious Places"
**Published in:** International Journal of Computer Applications (IJCA), Vol. 174

**Description:**
A token-based digital queue system for temple entry management. Pilgrims receive a token number and are called in batches. Implemented as a kiosk-based system.

**Drawbacks Identified:**
- ❌ Kiosk-only approach requires physical hardware at entry — expensive and not scalable
- ❌ No online or remote pre-registration for visitors
- ❌ No admin dashboard to monitor real-time crowd count
- ❌ Token system does not account for event type (regular darshan vs special pooja)
- ❌ No database persistence — queue resets on system restart
- ❌ No scheduling module for managing multiple simultaneous events

---

### [W2] Sharma, P. & Reddy, V. (2020) — "Web-Based Event Management for Religious Institutions"
**Published in:** International Conference on Emerging Trends in Information Technology (ICETIT)

**Description:**
A PHP-MySQL web application for managing religious events, pooja bookings, and priest schedules at a single temple. Supports basic admin login and event listing.

**Drawbacks Identified:**
- ❌ Single-temple design — not generalizable to multi-venue or multi-event contexts
- ❌ No visitor registration or devotee record keeping
- ❌ No crowd capacity enforcement — allows overbooking of events
- ❌ Admin dashboard shows only static event list; no live data
- ❌ No mobile-responsive UI — unusable on smartphones
- ❌ Pooja scheduling does not prevent time-slot conflicts

---

### [W3] Patel, N. et al. (2019) — "IoT-Based Crowd Density Monitoring at Religious Venues"
**Published in:** IEEE International Conference on Internet of Things (iThings)

**Description:**
Uses ultrasonic sensors and PIR motion detectors deployed at temple gates to count incoming and outgoing visitors. Sends data to a central server and triggers alerts when density exceeds thresholds.

**Drawbacks Identified:**
- ❌ Entirely hardware-dependent — requires IoT infrastructure investment
- ❌ No software layer for visitor registration or record management
- ❌ Crowd count is approximate; no individual visitor identification
- ❌ No scheduling or event management features
- ❌ Alert system sends SMS only — no web dashboard or admin interface
- ❌ System fails entirely when hardware sensors malfunction

---

### [W4] Kulkarni, A. & Joshi, M. (2022) — "Temple Administration Software Using PHP and MySQL"
**Published in:** IJARCCE, Vol. 11, Issue 4

**Description:**
A temple administration web application covering priest management, donation tracking, and announcement boards. Built with PHP and MySQL.

**Drawbacks Identified:**
- ❌ Focused on internal administration only — no visitor-facing features
- ❌ No crowd management or capacity tracking module
- ❌ No event scheduling with conflict detection
- ❌ Donation tracking module stores data in plaintext — security vulnerability
- ❌ No role-based access control (RBAC) — any admin can edit all records
- ❌ UI built with raw HTML/CSS, no responsive framework

---

### [W5] Singh, R. et al. (2021) — "Smart Pilgrimage Management System"
**Published in:** Journal of Physics: Conference Series, Vol. 1950

**Description:**
A mobile app-based system for managing pilgrim registrations at Char Dham yatra. Includes online slot booking, identity verification, and health status tracking (developed post-COVID).

**Drawbacks Identified:**
- ❌ Mobile app only — excludes non-smartphone users (significant portion of temple visitors in rural India)
- ❌ Requires Aadhaar API integration — not replicable without government API access
- ❌ No general-purpose scheduling for regular pooja or daily events
- ❌ Admin interface is external portal — no integrated dashboard
- ❌ Health tracking module is hardcoded for COVID-specific fields; not extensible
- ❌ No crowd capacity monitoring beyond slot count

---

### [W6] Desai, S. & Mehta, R. (2020) — "Visitor Management Systems: A Comparative Review"
**Published in:** International Journal of Advanced Research in Computer Science (IJARCS)

**Description:**
A review paper covering corporate and institutional visitor management systems (VMS). Evaluates features such as badge printing, pre-registration, host notification, and compliance tracking.

**Drawbacks Identified:**
- ❌ All reviewed systems are designed for corporate/institutional contexts — none adapted for religious venues
- ❌ Pre-registration systems assume internet access for all visitors
- ❌ No integration with event scheduling or capacity management
- ❌ Visitor record systems reviewed do not handle high-volume, walk-in scenarios typical at temples
- ❌ None of the reviewed systems include crowd monitoring dashboards

---

## 3. Comparison Table

| Feature | W1 | W2 | W3 | W4 | W5 | W6 | **TCM (Your Project)** |
|---|---|---|---|---|---|---|---|
| Visitor registration | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| Devotee record keeping | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Pooja/event scheduling | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Conflict detection in scheduling | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Admin dashboard | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ✅ |
| Crowd capacity tracking | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Web-based (no hardware) | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ✅ |
| Mobile responsive UI | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ✅ |
| Integrated system (all-in-one) | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 4. Research Gap Summary

The surveyed works collectively suffer from:
1. **Fragmentation** — systems address only one aspect (queue OR scheduling OR admin) but not all together
2. **Hardware dependency** — IoT-based solutions require expensive infrastructure
3. **No visitor records** — most systems treat visitors as anonymous, preventing historical tracking
4. **No conflict-aware scheduling** — pooja events can overlap without detection
5. **Poor accessibility** — mobile-only or kiosk-only approaches exclude large visitor segments
6. **No integrated dashboard** — admins must check multiple systems for complete information

**Your Temple Crowd Management System addresses all six gaps** through a unified PHP-MySQL web application with visitor registration, pooja scheduling, crowd monitoring, and an integrated admin dashboard.

---

## 5. References

1. Manoj, K., et al. (2021). Digital Queue Management System for Religious Places. *IJCA*, 174(12), 14–19.
2. Sharma, P., & Reddy, V. (2020). Web-Based Event Management for Religious Institutions. *Proc. ICETIT*, Springer.
3. Patel, N., et al. (2019). IoT-Based Crowd Density Monitoring at Religious Venues. *Proc. IEEE iThings*, pp. 112–118.
4. Kulkarni, A., & Joshi, M. (2022). Temple Administration Software Using PHP and MySQL. *IJARCCE*, 11(4), 88–94.
5. Singh, R., et al. (2021). Smart Pilgrimage Management System. *Journal of Physics: Conference Series*, 1950(1).
6. Desai, S., & Mehta, R. (2020). Visitor Management Systems: A Comparative Review. *IJARCS*, 11(3), 45–52.
7. Helbing, D., & Mukerji, P. (2012). Crowd disasters as systemic failures: analysis of the Love Parade disaster. *EPJ Data Science*, 1(1), 7.
8. Johansen, C., et al. (2017). Crowd management at major events. *Safety Science*, 91, 1–10.

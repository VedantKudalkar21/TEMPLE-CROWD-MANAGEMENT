<?php
session_start();
include('includes/db_connect.php'); // Make sure this file exists

$selectedTemple = isset($_GET['temple']) ? $_GET['temple'] : '';

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}
$user = ucfirst($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Digital Darshan Pass | Mandir Mitra</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <style>
    body {
      font-family: 'Baloo 2', cursive;
      background: linear-gradient(135deg, #fff3e0, #ffe0b2);
      min-height: 100vh;
      padding: 30px 10px;
    }
    .page {
      animation: fadeIn 0.4s ease-in-out;
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(15px);}
      to {opacity: 1; transform: translateY(0);}
    }
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 8px;
    }
    .calendar-day {
      text-align: center;
      padding: 10px;
      border-radius: 12px;
      cursor: pointer;
      background-color: #fffaf0;
      border: 1px solid #ffd8a8;
      transition: all 0.2s ease;
    }
    .calendar-day:hover {
      transform: scale(1.05);
      background-color: #ffe8c6;
    }
    .calendar-day.selected {
      background-color: #ffb74d;
      color: white;
      font-weight: 700;
    }
  </style>
</head>
<body>

<div id="page3_booking" class="page flex-col w-full max-w-4xl mx-auto p-8 bg-white rounded-3xl shadow-2xl">
  
  <div class="flex justify-between items-center mb-6">
    <button onclick="window.location.href='dashboard.php';" class="bg-orange-500 text-black py-2 px-6 rounded-full font-semibold hover:bg-black-600 transition-colors">← Back to Dashboard</button>
    <div class="text-right text-gray-700 font-semibold">
      👤 Logged in as: <span class="text-orange-700"><?php echo $user; ?></span>
    </div>
  </div>

  <div id="calendarAndSuggestions" class="w-full">
      <h2 class="text-3xl font-extrabold text-center mb-4 text-orange-700">🛕 Digital Darshan Pass Booking</h2>
      <div class="p-4 bg-orange-50 border-l-4 border-orange-400 rounded-lg mb-6 shadow-sm">
          <p class="font-semibold text-orange-700">⏱ Estimated Total Visit Time: <span class="text-orange-900">~ 1 hour 30 minutes</span></p>
          <p class="text-sm text-orange-600">Includes queue time, darshan, and temple routes.</p>
      </div>
      
      <div id="bestDaySuggestion" class="bg-green-100 p-4 rounded-xl mb-6 shadow-md">
          <p class="text-lg font-bold text-green-700 mb-1">✨ AI Suggestion for You:</p>
          <p id="suggestedDay" class="font-extrabold text-green-800"></p>
          <p id="suggestedReason" class="text-sm text-green-600"></p>
      </div>

      <div id="calendarView" class="w-full p-6 bg-white rounded-xl shadow-lg border border-gray-100">
          <div class="flex items-center justify-between mb-4">
              <button id="prevMonth" class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 transform hover:scale-105 transition-transform font-bold"><</button>
              <h3 id="currentMonth" class="text-2xl font-extrabold text-gray-800"></h3>
              <button id="nextMonth" class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 transform hover:scale-105 transition-transform font-bold">></button>
          </div>
          <div class="calendar-grid font-bold mb-2 text-gray-500">
              <div class="text-center">Sun</div>
              <div class="text-center">Mon</div>
              <div class="text-center">Tue</div>
              <div class="text-center">Wed</div>
              <div class="text-center">Thu</div>
              <div class="text-center">Fri</div>
              <div class="text-center">Sat</div>
          </div>
          <div id="calendarDates" class="calendar-grid"></div>
      </div>
  </div>

  <div id="bookingForm" class="hidden flex-col space-y-4 w-full max-w-xl mx-auto mt-6 p-6 bg-white rounded-3xl shadow-2xl border border-orange-100">
      <button id="backToCalendar" class="bg-gray-700 text-white py-2 px-6 rounded-full font-semibold hover:bg-gray-800 transition-colors mb-4">← Change Date/Time</button>
      <h3 class="text-xl font-extrabold text-center text-orange-700">🪔 Pilgrim Details</h3>

      <label class="block">
          <span class="text-gray-700 font-semibold">Full Name</span>
          <input type="text" id="fullName" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 p-2.5">
      </label>

      <div class="grid grid-cols-2 gap-4">
          <label class="block">
              <span class="text-gray-700 font-semibold">Age</span>
              <input type="number" id="age" min="1" max="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 p-2.5">
          </label>
          <label class="block">
              <span class="text-gray-700 font-semibold">Mobile Number</span>
              <input type="tel" id="mobileNumber" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 p-2.5">
          </label>
      </div>

      <label class="block">
          <span class="text-gray-700 font-semibold">Aadhaar Number</span>
          <input type="text" id="aadhaarNumber" maxlength="12" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 p-2.5">
      </label>

      <label class="block">
          <span class="text-gray-700 font-semibold">Number of Passes (Max 6)</span>
          <input type="number" id="passCount" min="1" max="6" value="1" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 p-2.5">
      </label>

      <h3 class="text-xl font-extrabold mt-6 text-gray-800">Selected Date & Time Slot</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3 bg-orange-50 rounded-lg">
          <p id="selectedDateText" class="text-gray-800 font-bold self-center"></p>
          <label class="block">
              <span class="text-gray-700 font-semibold">Select Time Slot</span>
              <select id="timeSelect" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 p-2.5"></select>
          </label>
      </div>

      <button id="submitBooking" class="w-full bg-green-600 text-white py-3 rounded-xl font-extrabold hover:bg-green-700 transition-colors shadow-lg">Generate Digital Pass</button>
  </div>

  <div id="digitalPass" class="hidden flex-col items-center p-8 bg-white rounded-3xl shadow-2xl space-y-6 mt-6 w-full max-w-xl mx-auto border-4 border-green-400">
      <h3 class="text-3xl font-extrabold text-green-600 animate-pulse">✅ PASS GENERATED!</h3>
      <div class="text-center space-y-2 bg-gray-50 p-6 rounded-xl w-full border border-gray-200">
          <p class="text-xl font-extrabold text-orange-700" id="passId"></p>
          <p class="text-gray-800"><strong>Pilgrim:</strong> <span id="passName" class="font-semibold"></span> (<span id="passAge"></span>)</p>
          <p class="text-gray-800"><strong>Tickets:</strong> <span id="passCountText"></span></p>
          <p class="text-gray-800"><strong>Date:</strong> <span id="passDate"></span></p>
          <p><strong>Selected Temple:</strong> <?php echo htmlspecialchars($selectedTemple); ?></p>
          <p class="text-2xl font-extrabold text-red-600 mt-3">ENTRY TIME: <span id="passTime"></span></p>
          <div class="mt-4 p-3 bg-orange-100 rounded-lg">
              <p class="font-extrabold text-orange-700">Digital Queue Status:</p>
              <p class="text-3xl font-extrabold text-orange-800"><span id="personsAhead">--</span> Persons Ahead</p>
          </div>
          <img id="qrCode" class="mt-4 mx-auto w-40 h-40 border-4 border-gray-200 rounded-lg" src="" alt="QR Code">
      </div>
      
      <div class="flex flex-col sm:flex-row gap-4 justify-center w-full mt-4">
          <button onclick="window.location.href='dashboard.php';" class="w-full sm:w-auto bg-orange-500 text-white py-3 px-8 rounded-xl font-semibold hover:bg-orange-600 transition-colors shadow-lg">Return to Dashboard</button>
          <button id="downloadPass" class="w-full sm:w-auto bg-blue-600 text-white py-3 px-8 rounded-xl font-semibold hover:bg-blue-700 transition-colors shadow-lg">Download PDF</button>
      </div>
  </div>

</div>

<script>
// ----- JS Starts -----
const timeSlots = ["08:00 AM - 09:00 AM","09:00 AM - 10:00 AM","10:00 AM - 11:00 AM","11:00 AM - 12:00 PM","12:00 PM - 01:00 PM","01:00 PM - 02:00 PM"];
let selectedDate = null;
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

const indianHolidays = ["2025-10-31,Diwali","2025-11-14,Children's Day"];

function showMessage(msg){ alert(msg); }

function getSlotAvailability(dateString){
  const max=200;
  const day=new Date(dateString).getDay();
  let base=Math.floor(Math.random()*(day===0||day===6?100:60))+20;
  return Math.max(0,max-base);
}

function findBestBookingDay(){
  const today=new Date(); let best=null,max=-1;
  for(let i=1;i<=14;i++){
    const d=new Date(today); d.setDate(today.getDate()+i);
    const ds=d.toISOString().split('T')[0];
    const a=getSlotAvailability(ds);
    if(a>max){max=a;best=d;}
  }
  if(!best) return {day:'N/A',reason:'No slots'};
  return {
    day:best.toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'}),
    reason:'Lowest expected crowd — ideal for darshan.'
  };
}

function renderCalendar(m,y){
  const mn=["January","February","March","April","May","June","July","August","September","October","November","December"];
  document.getElementById('currentMonth').textContent=`${mn[m]} ${y}`;
  const c=document.getElementById('calendarDates');
  c.innerHTML='';
  const today=new Date();
  const first=new Date(y,m,1).getDay();
  const total=new Date(y,m+1,0).getDate();
  for(let i=0;i<first;i++) c.innerHTML+='<div></div>';
  for(let d=1;d<=total;d++){
    const date=new Date(y,m,d);
    const dateStr=date.toISOString().split('T')[0];
    const avail=getSlotAvailability(dateStr);
    const div=document.createElement('div');
    div.className='calendar-day';
    div.innerHTML=`<div>${d}</div><div class="text-xs">${avail>0?'Avail: '+avail:'Booked'}</div>`;
    if(date<today) div.classList.add('bg-gray-200','text-gray-400');
    else if(avail===0) div.classList.add('bg-red-100','text-red-600');
    else div.addEventListener('click',()=>{document.querySelectorAll('.calendar-day').forEach(d=>d.classList.remove('selected'));div.classList.add('selected');selectedDate=date;showBookingForm();});
    c.appendChild(div);
  }
}

function showBookingCalendar(){
  document.getElementById('calendarAndSuggestions').classList.remove('hidden');
  document.getElementById('bookingForm').classList.add('hidden');
  document.getElementById('digitalPass').classList.add('hidden');
  const s=findBestBookingDay();
  document.getElementById('suggestedDay').textContent='Recommended: '+s.day;
  document.getElementById('suggestedReason').textContent=s.reason;
  renderCalendar(currentMonth,currentYear);
}

function showBookingForm(){
  document.getElementById('calendarAndSuggestions').classList.add('hidden');
  document.getElementById('bookingForm').classList.remove('hidden');
  document.getElementById('selectedDateText').textContent='Selected: '+selectedDate.toLocaleDateString();
  const t=document.getElementById('timeSelect');
  t.innerHTML=timeSlots.map(s=>`<option>${s}</option>`).join('');
}

function simulateDigitalQueue(){
  const h=new Date().getHours(); return Math.floor(Math.random()*((h>=7&&h<10)?150:(h>=17&&h<20)?120:50))+5;
}

// ----- New Function to Download PDF -----
function downloadPassPDF() {
    const passElement = document.getElementById('digitalPass');
    // Get the pass ID from the text content for the filename
    const passIdText = document.getElementById('passId').textContent.split(': ')[1] || 'DigitalPass';
    
    // Use scale: 3 for better quality in the PDF
    html2canvas(passElement, { scale: 3 }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const { jsPDF } = window.jspdf;
        
        const pdf = new jsPDF('p', 'pt', 'a4'); // A4 size in points
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        
        const canvasWidth = canvas.width;
        const canvasHeight = canvas.height;
        const canvasAspectRatio = canvasWidth / canvasHeight;

        // Calculate image dimensions to fit in PDF with margins
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
        pdf.save(`Digital_Pass_${passIdText}.pdf`); // Save with dynamic name
    });
}
// ----- End of New Function -----

function generatePass(){
    const n = document.getElementById('fullName').value.trim();
    const a = document.getElementById('age').value;
    const m = document.getElementById('mobileNumber').value;
    const ad = document.getElementById('aadhaarNumber').value;
    const c = document.getElementById('passCount').value;
    const t = document.getElementById('timeSelect').value;
    
    // Get the temple from URL param or fallback
    const urlParams = new URLSearchParams(window.location.search);
    const selectedTemple = urlParams.get('temple') || document.getElementById('temple')?.value || '';

    if(!n || !a || !m || !ad || !c || !t || !selectedTemple){
        alert('Please fill all fields and select a temple.');
        return;
    }

    if(ad.length !== 12 || isNaN(ad)){
        alert('Aadhaar must be 12 digits');
        return;
    }

    const id = 'MM' + Math.floor(100000 + Math.random()*900000);

    // Show pass
    document.getElementById('bookingForm').classList.add('hidden');
    document.getElementById('digitalPass').classList.remove('hidden');
    document.getElementById('passId').textContent = 'Pass ID: ' + id;
    document.getElementById('passName').textContent = n;
    document.getElementById('passAge').textContent = a + ' yrs';
    document.getElementById('passCountText').textContent = c;
    document.getElementById('passDate').textContent = selectedDate.toLocaleDateString();
    document.getElementById('passTime').textContent = t;
    document.getElementById('personsAhead').textContent = simulateDigitalQueue();
    document.getElementById('qrCode').src = `https://placehold.co/150x150/FF9933/FFF?text=${id}`;

    // Save to database
    fetch('save_booking.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `pass_id=${id}&full_name=${encodeURIComponent(n)}&age=${a}&mobile=${encodeURIComponent(m)}&aadhaar=${ad}&pass_count=${c}&booking_date=${selectedDate.toISOString().split('T')[0]}&time_slot=${encodeURIComponent(t)}&temple=${encodeURIComponent(selectedTemple)}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.status !== 'success') alert('Booking failed: ' + data.message);
    })
    .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded',()=>{
  showBookingCalendar();
  document.getElementById('prevMonth').onclick=()=>{currentMonth--;if(currentMonth<0){currentMonth=11;currentYear--;}renderCalendar(currentMonth,currentYear);};
  document.getElementById('nextMonth').onclick=()=>{currentMonth++;if(currentMonth>11){currentMonth=0;currentYear++;}renderCalendar(currentMonth,currentYear);};
  document.getElementById('backToCalendar').onclick=showBookingCalendar;
  document.getElementById('submitBooking').onclick=generatePass;
  
  // Attach click event to the new download button
  document.getElementById('downloadPass').onclick = downloadPassPDF;
});
</script>

</body>
</html>
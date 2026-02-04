<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION["user"])) header("Location: login_register.php");
if (!isset($_GET['car_id'])) die("Brak ID samochodu.");

$car_id = (int)$_GET['car_id'];
$carStmt = $pdo->prepare("SELECT * FROM cars WHERE id=?");
$carStmt->execute([$car_id]);
$car = $carStmt->fetch(PDO::FETCH_ASSOC);
if (!$car) die("Nie znaleziono samochodu.");

$bookedStmt = $pdo->prepare("SELECT start_date, end_date FROM bookings WHERE car_id=?");
$bookedStmt->execute([$car_id]);
$booked = $bookedStmt->fetchAll(PDO::FETCH_ASSOC);

$extras = $pdo->query("SELECT * FROM extras")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rezerwacja - <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></title>
<link rel="stylesheet" href="assets/style.css">
<style>
.booking-container {
  display: flex;
  flex-direction:column;
  max-width: 500px;
  margin: auto;
  gap: 20px;
}
.car-summary { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
.car-summary img { width: 150px; height: 100px; object-fit: cover; border-radius: 12px; }
.calendar-box, .pickup-return-box, .extras-box {
  border: 1px solid #ccc;
  padding: 15px;
  border-radius: 12px;
  background: #111;
  color: #FFD700;
}
.calendar-box h3, .pickup-return-box h4, .extras-box h4 { margin-bottom: 10px; }
#calendar { width: 100%; border-collapse: collapse; margin-top: 10px; }
#calendar th, #calendar td { width: 14.28%; text-align: center; padding: 5px; border-radius: 6px; }
#calendar td { cursor: pointer; }
#calendar td.available { background: #111; color: #FFD700; }
#calendar td.booked { background: #555; color: #ccc; cursor: not-allowed; }
#calendar td.selected { background: #FFD700; color: #111; }
#calendar td.inRange { background: #FFD70088; color: #111; }
#calendarNav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
</style>
</head>
<body>

<header>
  <h1>Wypożyczalnia Samochodów</h1>
  <nav>
    <a href="index.php" class="btn small">Strona główna</a>
    <a href="my_bookings.php" class="btn small">Moje rezerwacje</a>
    <span>Witaj, <?= htmlspecialchars($_SESSION["user"]["first_name"]) ?>!</span>
    <a href="logout.php" class="btn small">Wyloguj</a>
  </nav>
</header>

<main class="book-page">
  <div class="car-summary">
    <img src="assets/<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand']) ?>">
    <div>
      <h2><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></h2>
      <p><strong>Cena za dobę:</strong> <span id="dailyPrice"><?= $car['daily_price'] ?></span> zł</p>
    </div>
  </div>

  <div class="booking-container">
    <form id="bookingForm" action="save_booking.php" method="POST">
      <input type="hidden" name="car_id" value="<?= $car_id ?>">
      <input type="hidden" name="start_date" id="form_start_date">
      <input type="hidden" name="end_date" id="form_end_date">

      <div class="calendar-box">
        <h3>Wybierz termin rezerwacji</h3>
        <div id="calendarNav">
          <button type="button" id="prevMonth">◀</button>
          <span id="monthYear"></span>
          <button type="button" id="nextMonth">▶</button>
        </div>
        <table id="calendar">
          <thead>
            <tr><th>Nd</th><th>Pn</th><th>Wt</th><th>Śr</th><th>Cz</th><th>Pt</th><th>Sb</th></tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <div class="pickup-return-box">
        <h4>Odbiór i zwrot auta</h4>
        <label>Miejsce odbioru:
          <select name="pickup_location" required>
            <option value="">Wybierz lokalizację</option>
            <option value="Warszawa - Lotnisko">Warszawa - Lotnisko</option>
            <option value="Warszawa - Centrum">Warszawa - Centrum</option>
            <option value="Kraków - Lotnisko">Kraków - Lotnisko</option>
            <option value="Kraków - Centrum">Kraków - Centrum</option>
          </select>
        </label>
        <label>Miejsce zwrotu:
          <select name="return_location" required>
            <option value="">Wybierz lokalizację</option>
            <option value="Warszawa - Lotnisko">Warszawa - Lotnisko</option>
            <option value="Warszawa - Centrum">Warszawa - Centrum</option>
            <option value="Kraków - Lotnisko">Kraków - Lotnisko</option>
            <option value="Kraków - Centrum">Kraków - Centrum</option>
          </select>
        </label>
      </div>

      <div class="extras-box">
        <h4>Usługi dodatkowe</h4>
        <?php foreach ($extras as $e): ?>
          <div>
            <input type="checkbox" name="extras[]" value="<?= $e['id'] ?>" data-price="<?= $e['price'] ?>" id="extra<?= $e['id'] ?>">
            <label for="extra<?= $e['id'] ?>"><?= $e['name'] ?> (+<?= $e['price'] ?> zł)</label>
          </div>
        <?php endforeach; ?>
      </div>

      <p><strong>Całkowity koszt: </strong><span id="totalPrice"><?= $car['daily_price'] ?></span> zł</p>
      <button type="submit" class="btn">Zarezerwuj</button>
    </form>
  </div>
</main>

<footer>
  <p>© <?= date("Y") ?> Wypożyczalnia Samochodów</p>
</footer>

<script>
const booked = <?= json_encode($booked) ?>;

function formatDateLocal(d) {
  const y = d.getFullYear();
  const m = String(d.getMonth()+1).padStart(2,'0');
  const day = String(d.getDate()).padStart(2,'0');
  return `${y}-${m}-${day}`;
}

let bookedDates = [];
booked.forEach(b => {
  let d = new Date(b.start_date);
  const end = new Date(b.end_date);
  while(d <= end){
    bookedDates.push(formatDateLocal(d));
    d.setDate(d.getDate()+1);
  }
});

const dailyPrice = parseInt(document.getElementById("dailyPrice").innerText);
const extrasCheckboxes = document.querySelectorAll('input[name="extras[]"]');
let startDate = null, endDate = null;

const calendarBody = document.querySelector("#calendar tbody");
const monthYear = document.getElementById("monthYear");
let currentDate = new Date();

function renderCalendar(date){
  calendarBody.innerHTML = "";
  const year = date.getFullYear();
  const month = date.getMonth();
  monthYear.innerText = date.toLocaleString('pl-PL',{month:'long',year:'numeric'});

  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month+1, 0);
  let startDay = firstDay.getDay();
  if(startDay===0) startDay=0; // niedziela na 0
  let row = document.createElement("tr");

  for(let i=0;i<startDay;i++) row.appendChild(document.createElement("td"));

  for(let day=1; day<=lastDay.getDate(); day++){
    if(row.children.length===7){calendarBody.appendChild(row); row=document.createElement("tr");}
    const cell = document.createElement("td");
    const cellDate = new Date(year, month, day);
    const cellStr = formatDateLocal(cellDate);
    cell.innerText = day;

    if(bookedDates.includes(cellStr) || cellDate < new Date(new Date().setHours(0,0,0,0))) cell.classList.add("booked");
    else {
      cell.classList.add("available");
      cell.addEventListener("click", ()=>selectDate(cellDate));
    }

    if(startDate && cellStr===formatDateLocal(startDate)) cell.classList.add("selected");
    if(endDate && cellStr===formatDateLocal(endDate)) cell.classList.add("selected");
    if(startDate && endDate && cellDate>startDate && cellDate<endDate) cell.classList.add("inRange");

    row.appendChild(cell);
  }

  while(row.children.length<7) row.appendChild(document.createElement("td"));
  calendarBody.appendChild(row);
}

function selectDate(date){
  if(!startDate || (startDate && endDate)){ startDate=date; endDate=null; }
  else if(date>=startDate) endDate=date;
  else{ startDate=date; endDate=null; }

  document.getElementById("form_start_date").value = startDate? formatDateLocal(startDate):"";
  document.getElementById("form_end_date").value = endDate? formatDateLocal(endDate):"";

  renderCalendar(currentDate);
  if(startDate && endDate) calcTotal();
}

document.getElementById("prevMonth").addEventListener("click", ()=>{
  currentDate.setMonth(currentDate.getMonth()-1); renderCalendar(currentDate);
});
document.getElementById("nextMonth").addEventListener("click", ()=>{
  currentDate.setMonth(currentDate.getMonth()+1); renderCalendar(currentDate);
});

function calcTotal(){
  if(!startDate || !endDate) return;
  const days = Math.ceil((endDate-startDate)/(1000*60*60*24))+1;
  let total = days*dailyPrice;
  extrasCheckboxes.forEach(cb=>{ if(cb.checked) total += parseInt(cb.dataset.price); });
  document.getElementById("totalPrice").innerText = total;
}

extrasCheckboxes.forEach(cb=>cb.addEventListener("change", calcTotal));

renderCalendar(currentDate);
</script>
</body>
</html>

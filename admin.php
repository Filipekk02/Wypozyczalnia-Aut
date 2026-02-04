<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION["user"]) || !$_SESSION["user"]["is_admin"]) {
    header("Location: index.php");
    exit;
}

// --- Potwierdzanie / odrzucanie rezerwacji ---
if (isset($_GET["confirm"])) {
    $id = (int)$_GET["confirm"];
    $stmt = $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

if (isset($_GET["reject"])) {
    $id = (int)$_GET["reject"];
    $stmt = $pdo->prepare("UPDATE bookings SET status='rejected' WHERE id=?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// --- Usuwanie auta ---
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id=?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// --- Dodawanie auta ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_car"])) {
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $engine = $_POST["engine"];
    $fuel = $_POST["fuel"];
    $power = (int)$_POST["power"];
    $gearbox = $_POST["gearbox"];
    $year = (int)$_POST["year"];
    $description = $_POST["description"];
    $image = "";

    if (!empty($_FILES["image"]["name"])) {
        $targetDir = "assets/";
        $image = basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir.$image);
    }

    $stmt = $pdo->prepare("INSERT INTO cars (brand, model, engine, fuel, power, daily_price, gearbox, year, image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$brand, $model, $engine, $fuel, $power, 200, $gearbox, $year, $image, $description]);
    header("Location: admin.php");
    exit;
}

// --- Pobranie aut i usług ---
$cars = $pdo->query("SELECT * FROM cars ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$extrasAll = $pdo->query("SELECT * FROM extras")->fetchAll(PDO::FETCH_ASSOC);

// --- Pobranie rezerwacji z dodatkami ---
$bookings = $pdo->query("
    SELECT b.*, u.first_name, u.last_name, c.brand, c.model
    FROM bookings b
    JOIN users u ON b.user_id=u.id
    JOIN cars c ON b.car_id=c.id
    ORDER BY b.start_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Funkcja do dekodowania dodatków
function getExtrasNames($extrasJson, $allExtras) {
    if (!$extrasJson) return '-';
    $ids = json_decode($extrasJson, true);
    if (!$ids) return '-';
    $names = [];
    foreach ($allExtras as $e) {
        if (in_array($e['id'], $ids)) $names[] = $e['name'];
    }
    return implode(', ', $names);
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel administratora</title>
<link rel="stylesheet" href="../wypozyczalnia/assets/style.css">
<style>
.modal {
    display: none;
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
    z-index: 999;
}
.modal-content {
    background: #111;
    color: #FFD700;
    padding: 20px;
    border-radius: 12px;
    max-width: 500px;
    width: 100%;
}
.modal-content h3 { margin-top:0; }
.modal-close { float:right; cursor:pointer; }
#modalActions a { margin-right: 10px; }
.status-oczekujaca { color: orange; font-weight: bold; }
.status-potwierdzona { color: green; font-weight: bold; }
.status-odrzucona { color: red; font-weight: bold; }
</style>
</head>
<body>
<header>
  <h1>Panel Administratora</h1>
  <nav>
    <a href="index.php" class="btn small">Strona główna</a>
    <a href="logout.php" class="btn small">Wyloguj</a>
  </nav>
</header>
<main class="admin-page">

<!-- Rezerwacje -->
<section>
<h2>Aktualne rezerwacje</h2>
<table class="admin-table">
<tr>
<th>Użytkownik</th>
<th>Auto</th>
<th>Od</th>
<th>Do</th>
<th>Dodatki</th>
<th>Status</th>
<th>Akcje</th>
</tr>
<?php foreach ($bookings as $b): ?>
<tr>
<td><?= htmlspecialchars($b["first_name"]." ".$b["last_name"]) ?></td>
<td><?= htmlspecialchars($b["brand"]." ".$b["model"]) ?></td>
<td><?= $b["start_date"] ?></td>
<td><?= $b["end_date"] ?></td>
<td><?= htmlspecialchars(getExtrasNames($b["extras"], $extrasAll)) ?></td>
<td><?= htmlspecialchars(
    $b["status"] === 'pending' ? 'oczekująca' : ($b["status"] === 'confirmed' ? 'potwierdzona' : 'odrzucona')
) ?></td>
<td>
    <button class="btn small" onclick='openModal(<?= json_encode($b) ?>)'>🔍 Podgląd</button>
</td>
</tr>
<?php endforeach; ?>
</table>
</section>

<!-- Dodawanie auta -->
<section class="add-car-section">
<h2>Dodaj nowe auto</h2>
<form method="POST" enctype="multipart/form-data" class="add-car-form">
  <div class="form-row">
    <input type="text" name="brand" placeholder="Marka" required>
    <input type="text" name="model" placeholder="Model" required>
  </div>
  <div class="form-row">
    <input type="text" name="engine" placeholder="Silnik" required>
    <input type="text" name="fuel" placeholder="Paliwo" required>
    <input type="number" name="power" placeholder="Moc (KM)" required>
  </div>
  <div class="form-row">
    <input type="text" name="gearbox" placeholder="Skrzynia biegów" required>
    <input type="number" name="year" placeholder="Rok produkcji" required>
  </div>
  <textarea name="description" placeholder="Opis auta"></textarea>
  <input type="file" name="image" accept="image/*" required>
  <button type="submit" name="add_car" class="btn">Dodaj auto</button>
</form>
</section>

<!-- Zarządzanie flotą -->
<section>
<h2>Zarządzanie flotą</h2>
<table class="admin-table">
<tr>
<th>Zdjęcie</th>
<th>Auto</th>
<th>Silnik</th>
<th>Paliwo</th>
<th>Moc</th>
<th>Skrzynia</th>
<th>Rok</th>
<th>Akcje</th>
</tr>
<?php foreach ($cars as $car): ?>
<tr>
<td><img src="assets/<?= htmlspecialchars($car['image']) ?>" width="80"></td>
<td><?= htmlspecialchars($car['brand'] . " " . $car['model']) ?></td>
<td><?= htmlspecialchars($car['engine']) ?></td>
<td><?= htmlspecialchars($car['fuel']) ?></td>
<td><?= htmlspecialchars($car['power']) ?> KM</td>
<td><?= htmlspecialchars($car['gearbox']) ?></td>
<td><?= htmlspecialchars($car['year']) ?></td>
<td><a href="admin.php?delete=<?= $car['id'] ?>" class="btn small delete" onclick="return confirm('Czy na pewno chcesz usunąć to auto?')">Usuń</a></td>
</tr>
<?php endforeach; ?>
</table>
</section>

</main>
<footer>
<p>© <?= date("Y") ?> Wypożyczalnia Samochodów</p>
</footer>

<!-- Modal podglądu rezerwacji -->
<div id="bookingModal" class="modal">
  <div class="modal-content">
    <span class="modal-close" onclick="closeModal()">✖</span>
    <h3>Podgląd rezerwacji</h3>
    <p><strong>Użytkownik:</strong> <span id="modalUser"></span></p>
    <p><strong>Auto:</strong> <span id="modalCar"></span></p>
    <p><strong>Okres:</strong> <span id="modalPeriod"></span></p>
    <p><strong>Dodatki:</strong> <span id="modalExtras"></span></p>
    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
    <div id="modalActions"></div>
  </div>
</div>

<script>
function getExtrasNames(extrasJson){
    if(!extrasJson) return '-';
    try {
        const arr = JSON.parse(extrasJson);
        return arr.length ? arr.join(', ') : '-';
    } catch(e){ return '-'; }
}

function openModal(data){
    document.getElementById('modalUser').innerText = data.first_name + ' ' + data.last_name;
    document.getElementById('modalCar').innerText = data.brand + ' ' + data.model;
    document.getElementById('modalPeriod').innerText = data.start_date + ' → ' + data.end_date;
    document.getElementById('modalExtras').innerText = getExtrasNames(data.extras);

    const status = data.status; // pending / confirmed / rejected
    const statusPolish = (status === 'pending') ? 'oczekująca' : (status === 'confirmed') ? 'potwierdzona' : 'odrzucona';
    document.getElementById('modalStatus').innerText = statusPolish;
    document.getElementById('modalStatus').className = 'status-' + statusPolish.replace('ć','c');

    let actionsHtml = '';
    if(status === 'pending'){
        actionsHtml += `<a href="admin.php?confirm=${data.id}" class="btn">✅ Potwierdź</a>`;
        actionsHtml += `<a href="admin.php?reject=${data.id}" class="btn">❌ Odrzuć</a>`;
    }
    document.getElementById('modalActions').innerHTML = actionsHtml;

    document.getElementById('bookingModal').style.display = 'flex';
}

function closeModal(){
    document.getElementById('bookingModal').style.display = 'none';
}
</script>
</body>
</html>

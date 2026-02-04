<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION["user"])) {
    header("Location: login_register.php");
    exit;
}

$user_id = $_SESSION["user"]["id"];

$bookings = $pdo->prepare("
    SELECT b.*, c.brand, c.model
    FROM bookings b
    JOIN cars c ON b.car_id=c.id
    WHERE b.user_id=?
    ORDER BY b.start_date DESC
");
$bookings->execute([$user_id]);
$bookings = $bookings->fetchAll(PDO::FETCH_ASSOC);

// Pobranie wszystkich dodatków do dekodowania
$allExtras = $pdo->query("SELECT * FROM extras")->fetchAll(PDO::FETCH_ASSOC);

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
<title>Moje rezerwacje</title>
<link rel="stylesheet" href="assets/style.css">
<style>
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 8px; text-align: center; border: 1px solid #ccc; }
.status-pending { color: #FFD700; font-weight: bold; }
.status-confirmed { color: #0f0; font-weight: bold; }
.status-rejected { color: #f00; font-weight: bold; }
</style>
</head>
<body>
<header>
  <h1>Moje rezerwacje</h1>
  <nav>
    <a href="index.php" class="btn small">Strona główna</a>
    <a href="logout.php" class="btn small">Wyloguj</a>
  </nav>
</header>
<main>
<table>
<tr>
  <th>Auto</th>
  <th>Od</th>
  <th>Do</th>
  <th>Dodatki</th>
  <th>Odbiór</th>
  <th>Zwrot</th>
  <th>Status</th>
</tr>
<?php foreach ($bookings as $b): ?>
<tr>
  <td><?= htmlspecialchars($b['brand'].' '.$b['model']) ?></td>
  <td><?= $b['start_date'] ?></td>
  <td><?= $b['end_date'] ?></td>
  <td><?= htmlspecialchars(getExtrasNames($b['extras'], $allExtras)) ?></td>
  <td><?= htmlspecialchars($b['pickup_location']) ?></td>
  <td><?= htmlspecialchars($b['return_location']) ?></td>
  <td class="status-<?= $b['status'] ?>"><?= $b['status'] === 'pending' ? 'oczekująca' : ($b['status'] === 'confirmed' ? 'potwierdzona' : 'odrzucona') ?></td>
</tr>
<?php endforeach; ?>
</table>
</main>
<footer>
<p>© <?= date("Y") ?> Wypożyczalnia Samochodów</p>
</footer>
</body>
</html>

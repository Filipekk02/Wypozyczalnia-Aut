<?php
session_start();
require_once "db_connect.php";

if (!isset($_GET['id'])) die("Brak ID samochodu.");
$car_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM cars WHERE id=?");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$car) die("Nie znaleziono samochodu.");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($car['brand'].' '.$car['model']) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
  <h1>Wypożyczalnia Samochodów</h1>
  <nav>
    <a href="index.php" class="btn small">Strona główna</a>
    <?php if (isset($_SESSION["user"])): ?>
      <span>Witaj, <?= htmlspecialchars($_SESSION["user"]["first_name"]) ?>!</span>
      <?php if ($_SESSION["user"]["is_admin"]): ?>
        <a href="admin.php" class="btn small">Panel admina</a>
      <?php endif; ?>
      <a href="logout.php" class="btn small">Wyloguj</a>
    <?php else: ?>
      <a href="login_register.php" class="btn small">Zaloguj / Rejestracja</a>
    <?php endif; ?>
  </nav>
</header>


<main class="car-page">
  <div class="car-details">
    <img src="assets/<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand']) ?>">
    <div class="car-info">
      <h2><?= htmlspecialchars($car['brand'].' '.$car['model']) ?></h2>
      <p><strong>Silnik:</strong> <?= htmlspecialchars($car['engine']) ?></p>
      <p><strong>Paliwo:</strong> <?= htmlspecialchars($car['fuel']) ?></p>
      <p><strong>Moc:</strong> <?= htmlspecialchars($car['power']) ?> KM</p>
      <p><strong>Skrzynia:</strong> <?= htmlspecialchars($car['gearbox']) ?></p>
      <p><strong>Rok produkcji:</strong> <?= htmlspecialchars($car['year']) ?></p>
      <p><strong>Opis:</strong> <?= htmlspecialchars($car['description'] ?? 'Brak dodatkowych informacji') ?></p>
      <button class="btn" onclick="bookCar(<?= $car_id ?>)">Rezerwuj</button>
    </div>
  </div>
</main>

<footer>
  <p>© <?= date("Y") ?> Wypożyczalnia Samochodów</p>
</footer>

<script src="assets/script.js"></script>
<script>
const isLoggedIn = <?= isset($_SESSION["user"]) ? "true" : "false" ?>;
</script>
</body>
</html>

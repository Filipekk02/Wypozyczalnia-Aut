<?php
session_start();
require_once "db_connect.php";

// Pobranie wszystkich aut z bazy
$cars = $pdo->query("SELECT * FROM cars")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wypożyczalnia Samochodów</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header>
  <h1>Wypożyczalnia Samochodów</h1>
  <nav>
    <?php if (isset($_SESSION["user"])): ?>
      <span>Witaj, <?= htmlspecialchars($_SESSION["user"]["first_name"]) ?>!</span>
      <?php if ($_SESSION["user"]["is_admin"]): ?>
        <a href="admin.php" class="btn small">Panel admina</a>
      <?php endif; ?>
      <a href="logout.php" class="btn small">Wyloguj</a>
    <?php else: ?>
      <a href="login_register.php" class="btn small">Zaloguj / Rejestracja</a>
    <?php endif; ?>
    <a href="my_bookings.php" class="btn small">Moje rezerwacje</a>
  </nav>
</header>

<main>
  <h2>Dostępna flota</h2>
  <div class="cars-container">
    <?php foreach ($cars as $car): ?>
      <div class="car-card" onclick="window.location.href='car.php?id=<?= $car['id'] ?>'">
        <img src="assets/<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?>">
        <h3><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></h3>
        <p class="price">Cena za dobę: <?= $car['daily_price'] ?> zł</p>
      </div>
    <?php endforeach; ?>
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

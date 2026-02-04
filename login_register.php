<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logowanie / Rejestracja</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
  <h1>Wypożyczalnia Samochodów</h1>
  <nav>
    <a href="index.php" class="btn small">Strona główna</a>
  </nav>
</header>

<main class="login-register-page">
  <div class="login-register-container">
    <div class="login-section">
      <h2>Zaloguj się</h2>
      <input type="email" id="loginEmail" placeholder="Email" required>
      <input type="password" id="loginPassword" placeholder="Hasło" required>
      <button onclick="login()" class="btn">Zaloguj</button>
    </div>

    <div class="register-section">
      <h2>Rejestracja</h2>
      <input type="text" id="firstName" placeholder="Imię" required>
      <input type="text" id="lastName" placeholder="Nazwisko" required>
      <input type="email" id="email" placeholder="Email" required>
      <input type="password" id="password" placeholder="Hasło" required>
      <input type="password" id="confirm" placeholder="Powtórz hasło" required>
      <button onclick="register()" class="btn">Zarejestruj się</button>
    </div>
  </div>
</main>

<footer>
  <p>© <?= date("Y") ?> Wypożyczalnia Samochodów</p>
</footer>

<script src="assets/script.js"></script>
<script>
const isLoggedIn = false;
</script>
</body>
</html>

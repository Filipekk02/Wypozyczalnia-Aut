<?php
session_start();
require_once "db_connect.php";

header("Content-Type: application/json");

$action = $_POST["action"] ?? "";

// === REJESTRACJA ===
if ($action === "register") {
    $first = trim($_POST["first"]);
    $last = trim($_POST["last"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];

    // Weryfikacja email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit(json_encode(["status" => "error", "msg" => "Nieprawidłowy format email!"]));
    }

    // Sprawdzenie czy hasła się zgadzają
    if ($password !== $confirm)
        exit(json_encode(["status" => "error", "msg" => "Hasła nie są takie same!"]));

    // Wymagania dla hasła: min. 6 znaków, wielka litera, cyfra, znak specjalny
    if (!preg_match("/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{6,}$/", $password))
        exit(json_encode(["status" => "error", "msg" => "Hasło musi mieć dużą literę, cyfrę i znak specjalny!"]));

    // Sprawdzenie czy email jest już zajęty
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->fetch())
        exit(json_encode(["status" => "error", "msg" => "Email jest już zajęty!"]));

    // Dodanie użytkownika do bazy
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$first, $last, $email, $hash]);

    exit(json_encode(["status" => "ok", "msg" => "Konto utworzone!"]));
}

// === LOGOWANIE ===
if ($action === "login") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user;
        exit(json_encode(["status" => "ok"]));
    }

    exit(json_encode(["status" => "error", "msg" => "Błędny email lub hasło!"]));
}

exit(json_encode(["status" => "error", "msg" => "Nieprawidłowe żądanie."]));
?>

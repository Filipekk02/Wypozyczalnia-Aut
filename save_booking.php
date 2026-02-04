<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION["user"])) header("Location: login_register.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user"]["id"];
    $car_id = (int)$_POST["car_id"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    $pickup_location = $_POST["pickup_location"];
    $return_location = $_POST["return_location"];
    $extras = isset($_POST["extras"]) ? json_encode($_POST["extras"]) : null;

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, extras, pickup_location, return_location) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $car_id, $start_date, $end_date, $extras, $pickup_location, $return_location]);

    header("Location: my_bookings.php");
    exit;
}
?>

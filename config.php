<?php
$host     = "localhost";
$dbname   = "ecommerce";   // ← apna DB naam yahan likhein
$username = "root";         // ← XAMPP default
$password = "";             // ← XAMPP default (blank)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => "DB Connection Failed: " . $e->getMessage()]));
}
?>
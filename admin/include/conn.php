<?php
// ══════════════════════════════
//  Database Connection
// ══════════════════════════════
$mysqli = new mysqli("localhost", "root", "", "ecommerce");

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

$conn = $mysqli; // ← ye ek line add karo
?>

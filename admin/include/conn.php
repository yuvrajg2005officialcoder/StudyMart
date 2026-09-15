<?php
// ══════════════════════════
//  Database Connection
// ══════════════════════════
$host     = getenv('DB_HOST') ?: 'localhost';
$dbname   = getenv('DB_NAME') ?: 'ecommerce';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$port     = getenv('DB_PORT') ?: '4000';

$mysqli = mysqli_init();
$mysqli->ssl_set(NULL, NULL, '/etc/ssl/certs/ca-certificates.crt', NULL, NULL);
$mysqli->real_connect($host, $username, $password, $dbname, (int)$port, NULL, MYSQLI_CLIENT_SSL);

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

$conn = $mysqli;
?>

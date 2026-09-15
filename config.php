<?php

$envFile = __DIR__ . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');

        $key = trim($key);
        $value = trim($value);

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}



$host     = getenv('DB_HOST');
$dbname   = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$port     = getenv('DB_PORT') ?: '4000';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";

    $pdo = new PDO($dsn, $username, $password, $options);

    // Enable TLS for TiDB Cloud
    $pdo->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");

} catch (PDOException $e) {
    die(json_encode([
        "error" => "DB Connection Failed: " . $e->getMessage()
    ]));
}
?>
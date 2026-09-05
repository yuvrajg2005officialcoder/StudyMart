<?php
session_start();

// DB connection (apna credentials yahan dalein)
$host = 'localhost';
$db   = 'ecommerce';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    // Basic validation
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty");
        exit;
    }

    // DB se user fetch karo
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Login success
        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_name']  = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];

        // Remember me cookie (30 din)
        if ($remember) {
            setcookie('admin_email', $email, time() + (30 * 24 * 3600), '/');
        }

        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=invalid");
        exit;
    }
}

header("Location: login.php");
exit;
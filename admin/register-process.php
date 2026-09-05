<?php
session_start();

// DB connection
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
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        header("Location: login.php?tab=register&error=empty");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?tab=register&error=invalid_email");
        exit;
    }

    if (strlen($password) < 8) {
        header("Location: login.php?tab=register&error=short_pass");
        exit;
    }

    if ($password !== $confirm) {
        header("Location: login.php?tab=register&error=pass_mismatch");
        exit;
    }

    // Check email already exist hai kya
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        header("Location: login.php?tab=register&error=email_exists");
        exit;
    }

    // Password hash karke save karo
    $hashedPass = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $hashedPass]);

    // Auto login after register
    $newId = $pdo->lastInsertId();
    $_SESSION['admin_id']    = $newId;
    $_SESSION['admin_name']  = $name;
    $_SESSION['admin_email'] = $email;

    header("Location: index.php");
    exit;
}

header("Location: login.php");
exit;
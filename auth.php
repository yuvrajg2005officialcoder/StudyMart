<?php
session_start();
include 'admin/include/conn.php';

// ── LOGIN ─────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Vendor table se email dhundo
    $stmt = $mysqli->prepare("SELECT * FROM vendor WHERE vendor_email = ? AND status = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $vendor = $result->fetch_assoc();

    if ($vendor && password_verify($password, $vendor['password'])) {
        // Session set karo
        $_SESSION['vendor_id']    = $vendor['vendor_id'];
        $_SESSION['vendor_name']  = $vendor['vendor_name'];
        $_SESSION['vendor_email'] = $vendor['vendor_email'];

        echo json_encode([
            'success'  => true,
            'message'  => 'Login ho gaya! 👋',
            'redirect' => 'index.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ya password galat hai!']);
    }
    $stmt->close();
    exit;
}

// ── REGISTER ──────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $first_name  = trim($_POST['first_name']);
    $last_name   = trim($_POST['last_name']);
    $vendor_name = $first_name . ' ' . $last_name;
    $email       = trim($_POST['email']);
    $mobile_no   = trim($_POST['mobile_no']);
    $college     = trim($_POST['college']);
    $state       = trim($_POST['state']);
    $city        = trim($_POST['city']);
    $area        = trim($_POST['area']);
    $address     = trim($_POST['address']);
    $password    = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // College ko address ke saath jod do (vendor table me alag college column nahi hai)
    if ($college !== '') {
        $address = $address !== '' ? $address . ' | College: ' . $college : 'College: ' . $college;
    }

    // Required fields check
    if ($first_name === '' || $last_name === '' || $email === '' || $mobile_no === '' || trim($_POST['password']) === '') {
        echo json_encode(['success' => false, 'message' => 'Saare required fields fill karo!']);
        exit;
    }

    // Email check karo
    $check = $mysqli->prepare("SELECT vendor_id FROM vendor WHERE vendor_email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Yeh email already registered hai!']);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO vendor 
            (vendor_name, vendor_email, mobile_no, address, state, city, area, password, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
        $stmt->bind_param("ssssssss", $vendor_name, $email, $mobile_no, $address, $state, $city, $area, $password);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Account ban gaya! Ab login karo 🎉']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $mysqli->error]);
        }
        $stmt->close();
    }
    $check->close();
    exit;
}
?>
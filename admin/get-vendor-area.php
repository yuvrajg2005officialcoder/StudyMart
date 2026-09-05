<?php
session_start();
if (!isset($_SESSION['admin_id'])) { echo json_encode([]); exit; }
require_once 'include/conn.php';

$id = intval($_GET['vendor_id'] ?? 0);
if (!$id) { echo json_encode([]); exit; }

$res = $conn->query("SELECT vendor_area FROM vendor WHERE vendor_id = $id LIMIT 1");
$row = $res ? $res->fetch_assoc() : [];
echo json_encode($row ?: new stdClass());
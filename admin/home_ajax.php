<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


header('Content-Type: application/json');
include 'include/conn.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$upload_dir = __DIR__ . '/assets/img/home/';
$upload_url = 'assets/img/home/';

if (!is_dir($upload_dir))
    mkdir($upload_dir, 0777, true);

function send($ok, $data = [], $msg = '')
{
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

switch ($action) {

    // ── LIST ─────────────────────────────────────────────
    case 'list':
        $rows = [];
        $res = $mysqli->query("SELECT * FROM home_section ORDER BY created_at DESC");
        while ($r = $res->fetch_assoc()) {
            $r['image_url'] = (!empty($r['image']) && file_exists($upload_dir . $r['image']))
                ? $upload_url . $r['image'] : '';
            $rows[] = $r;
        }
        send(true, $rows);
        break;

    // ── GET SINGLE (for edit/view modal) ────────────────
    case 'get':
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        $stmt = $mysqli->prepare("SELECT * FROM home_section WHERE home_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row)
            send(false, [], 'Record not found.');
        $row['image_url'] = (!empty($row['image']) && file_exists($upload_dir . $row['image']))
            ? $upload_url . $row['image'] : '';
        send(true, $row);
        break;

    // ── ADD ──────────────────────────────────────────────
    case 'add':
        $f = $_POST;
        if (empty(trim($f['title'] ?? '')))
            send(false, [], 'Title is required.');

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed))
                send(false, [], 'Only JPG, PNG, WEBP, GIF allowed.');
            $new = time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new)) {
                $image = $new;
            } else {
                send(false, [], 'Image upload failed.');
            }
        }

        $status = intval($f['status'] ?? 0);
        $stmt = $mysqli->prepare("INSERT INTO home_section
        (title,paragraph,button_text,button_link,products,students,rating,image,status,created_at)
        VALUES (?,?,?,?,?,?,?,?,?,NOW())");
        $stmt->bind_param(
            "ssssssssi",
            $f['title'],
            $f['paragraph'],
            $f['button_text'],
            $f['button_link'],
            $f['products'],
            $f['students'],
            $f['rating'],
            $image,
            $status
        );
        $ok = $stmt->execute();
        $stmt->close();
        $ok ? send(true, [], 'Added successfully!') : send(false, [], 'Insert failed.');
        break;

    // ── EDIT ─────────────────────────────────────────────
    case 'edit':
        $f  = $_POST;
        $id = intval($f['home_id'] ?? 0);
        if (empty(trim($f['title'] ?? '')))
            send(false, [], 'Title is required.');

        $image = $f['old_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed))
                send(false, [], 'Only JPG, PNG, WEBP, GIF allowed.');
            $new = time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new)) {
                if (!empty($image) && file_exists($upload_dir . $image)) unlink($upload_dir . $image);
                $image = $new;
            } else {
                send(false, [], 'Image upload failed.');
            }
        }

        $status = intval($f['status'] ?? 0);
        $stmt = $mysqli->prepare("UPDATE home_section SET
        title=?, paragraph=?, button_text=?, button_link=?,
        products=?, students=?, rating=?, image=?, status=? WHERE home_id=?");
        $stmt->bind_param(
            "ssssssssii",
            $f['title'],
            $f['paragraph'],
            $f['button_text'],
            $f['button_link'],
            $f['products'],
            $f['students'],
            $f['rating'],
            $image,
            $status,
            $id
        );
        
        $ok = $stmt->execute();
        $stmt->close();
        $ok ? send(true, [], 'Updated successfully!') : send(false, [], 'Update failed.');
        break;

    // ── DELETE ───────────────────────────────────────────
    case 'delete':
        $id = intval($_POST['home_id'] ?? 0);
        $stmt = $mysqli->prepare("SELECT image FROM home_section WHERE home_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row && !empty($row['image']) && file_exists($upload_dir . $row['image'])) {
            unlink($upload_dir . $row['image']);
        }
        $del = $mysqli->prepare("DELETE FROM home_section WHERE home_id=?");
        $del->bind_param("i", $id);
        $ok = $del->execute();
        $del->close();
        $ok ? send(true, [], 'Deleted successfully!') : send(false, [], 'Delete failed.');
        break;

    default:
        send(false, [], 'Invalid action.');
}
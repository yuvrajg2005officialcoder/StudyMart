<?php
session_start();
header('Content-Type: application/json');
include 'admin/include/conn.php'; // same connection file used in checkout.php ($mysqli)

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'error' => 'Cart is empty or invalid data']);
    exit;
}

$payMethod = $data['pay_method'] ?? 'upi';                 // upi | card | netbanking | cod
$isCod     = ($payMethod === 'cod');
$paymentStatus = $isCod ? 'Unpaid' : 'Paid';
$paymentMode   = $isCod ? 'Cash' : 'Online';

try {
    $mysqli->begin_transaction();

    $res = $mysqli->query("SELECT MAX(order_id) AS maxid FROM customer_order_items FOR UPDATE");
    $row = $res->fetch_assoc();
    $orderId = (int)($row['maxid'] ?? 0) + 1;
    $ordId   = 'ORD-' . str_pad($orderId, 4, '0', STR_PAD_LEFT);

    $stmtItem = $mysqli->prepare("INSERT INTO customer_order_items
        (order_id, product_id, ord_id, order_price, order_quantity, order_status, order_slug, unit)
        VALUES (?,?,?,?,?,?,?,?)");

    $stmtProduct = $mysqli->prepare("SELECT vendor_id FROM product WHERE product_id = ?");
    $stmtVendor  = $mysqli->prepare("SELECT area FROM vendor WHERE vendor_id = ?");

    // ── order_item column ab table mein nahi hai, isliye query/columns se hata diya ──
    $stmtVendorOrder = $mysqli->prepare("INSERT INTO vendor_orders_new
        (vendor_id, ordid, vendor_area, order_quantity, order_date, total, payment_status, payment_mode, vendor_order_status)
        VALUES (?,?,?,?, NOW(), ?, ?, ?, 'Pending')");

    foreach ($data['items'] as $item) {
        $product_id = isset($item['id']) ? intval($item['id']) : 0;
        $price      = floatval($item['price'] ?? 0);
        $qty        = intval($item['qty'] ?? 1);
        $slug       = $item['slug'] ?? '';
        $unit       = $item['unit'] ?? '';
        $status     = 'Pending';

        if ($product_id <= 0 || $qty <= 0) {
            throw new Exception("Invalid item data (product_id: $product_id, qty: $qty)");
        }

        $stmtItem->bind_param(
            'iisdisss',
            $orderId, $product_id, $ordId, $price, $qty, $status, $slug, $unit
        );
        $stmtItem->execute();

        $stmtProduct->bind_param('i', $product_id);
        $stmtProduct->execute();
        $prodRow = $stmtProduct->get_result()->fetch_assoc();

        if ($prodRow && !empty($prodRow['vendor_id'])) {
            $vendorId  = (int)$prodRow['vendor_id'];
            $lineTotal = $price * $qty;

            $stmtVendor->bind_param('i', $vendorId);
            $stmtVendor->execute();
            $vendorRow  = $stmtVendor->get_result()->fetch_assoc();
            $vendorArea = $vendorRow['area'] ?? '';

            $stmtVendorOrder->bind_param(
                'issidss',
                $vendorId, $ordId, $vendorArea, $qty, $lineTotal, $paymentStatus, $paymentMode
            );
            $stmtVendorOrder->execute();
        }
    }

    $stmtItem->close();
    $stmtProduct->close();
    $stmtVendor->close();
    $stmtVendorOrder->close();

    $mysqli->commit();

    echo json_encode([
        'success'  => true,
        'order_id' => $orderId,
        'ord_id'   => $ordId
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode([
        'success' => false,
        'error'   => 'Order save nahi hua: ' . $e->getMessage()
    ]);
}
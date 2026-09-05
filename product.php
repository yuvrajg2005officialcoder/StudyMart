<?php
// product.php -> product_detail.php redirect
// Ye file purane "product.php?id=13" links ko sahi page par bhej deti hai.
 
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
 
if ($id) {
    header("Location: product_detail.php?id=" . $id);
} else {
    header("Location: shop.php");
}
exit;
 
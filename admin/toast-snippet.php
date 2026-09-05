<?php
// ==================================================
// Yeh code view-subcategory.php ke bilkul upar
// include 'includes/db.php'; ke baad paste karo
// ==================================================

$toast = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted')  $toast = '<div class="alert-success-custom mb-3"><i class="la la-check-circle" style="font-size:18px;"></i> Sub Category deleted successfully!</div>';
    if ($_GET['msg'] == 'notfound') $toast = '<div class="alert-danger-custom mb-3"><i class="la la-times-circle" style="font-size:18px;"></i> Sub Category not found!</div>';
    if ($_GET['msg'] == 'error')    $toast = '<div class="alert-danger-custom mb-3"><i class="la la-times-circle" style="font-size:18px;"></i> Something went wrong. Please try again.</div>';
    if ($_GET['msg'] == 'invalid')  $toast = '<div class="alert-danger-custom mb-3"><i class="la la-times-circle" style="font-size:18px;"></i> Invalid request!</div>';
}
?>

<!-- Phir content area mein, table card ke upar yeh echo karo: -->
<?= $toast ?>
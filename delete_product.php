<?php
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

$product = find_by_id('assets', (int)$_GET['id']);
if (!$product) {
    $session->msg("d", "Missing Product id.");
    redirect('product.php');
}

// Perform the delete action
$delete_id = delete_by_id('assets', (int)$product['id']);
if ($delete_id) {
    // Log the delete action
    $current_user = current_user();
    $user_id = $current_user['id'];
    $username = $current_user['username'];
    $action = "Deleted product ID: " . $product['id'] . " (" . $product['assetname'] . ")";
    add_log($user_id, $username, $action);

    $session->msg("s", "Product deleted.");
    redirect('product.php');
} else {
    $session->msg("d", "Product deletion failed.");
    redirect('product.php');
}
?>

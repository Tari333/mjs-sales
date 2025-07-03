<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

header('Content-Type: application/json');

if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    echo json_encode(['error' => 'Product ID required']);
    exit;
}

$productId = $_POST['product_id'];

try {
    // Get product info
    $query = "SELECT gambar FROM produk WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $productId);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    // Check order details
    $query = "SELECT COUNT(*) as count FROM detail_pesanan WHERE produk_id = :produk_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':produk_id', $productId);
    $stmt->execute();
    $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check stock logs
    $query = "SELECT COUNT(*) as count FROM log_stok WHERE produk_id = :produk_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':produk_id', $productId);
    $stmt->execute();
    $stockLogs = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if has image
    $hasImage = !empty($product['gambar']) && file_exists(SITE_ROOT . '/../assets/uploads/products/' . $product['gambar']);

    // Prepare response
    $response = [
        'order_details' => (int)$orderDetails['count'],
        'stock_logs' => (int)$stockLogs['count'],
        'has_image' => $hasImage,
        'has_relations' => ($orderDetails['count'] > 0 || $stockLogs['count'] > 0)
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
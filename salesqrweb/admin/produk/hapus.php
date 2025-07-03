<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'ID produk tidak ditemukan.';
    redirect('admin/produk/');
}

$productId = $_GET['id'];

try {
    // Begin transaction
    $db->beginTransaction();
    
    // Check if product exists
    $query = "SELECT * FROM produk WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $productId);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['error'] = 'Produk tidak ditemukan.';
        redirect('admin/produk/');
    }

    // Get related data counts for logging
    $query = "SELECT COUNT(*) as order_count FROM detail_pesanan WHERE produk_id = :produk_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':produk_id', $productId);
    $stmt->execute();
    $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(*) as stock_log_count FROM log_stok WHERE produk_id = :produk_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':produk_id', $productId);
    $stmt->execute();
    $stockLogs = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete product image if exists
    if ($product['gambar'] && file_exists(SITE_ROOT . '/../assets/uploads/products/' . $product['gambar'])) {
        unlink(SITE_ROOT . '/../assets/uploads/products/' . $product['gambar']);
    }

    // Delete product (CASCADE will handle related data)
    $query = "DELETE FROM produk WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $productId);
    
    if ($stmt->execute()) {
        // Commit transaction
        $db->commit();
        
        // Create success message with details
        $deletedItems = [];
        if ($orderDetails['order_count'] > 0) {
            $deletedItems[] = $orderDetails['order_count'] . ' detail pesanan';
        }
        if ($stockLogs['stock_log_count'] > 0) {
            $deletedItems[] = $stockLogs['stock_log_count'] . ' log stok';
        }
        
        $message = 'Produk "' . htmlspecialchars($product['nama_produk']) . '" berhasil dihapus!';
        if (!empty($deletedItems)) {
            $message .= ' Juga terhapus: ' . implode(', ', $deletedItems) . '.';
        }
        
        $_SESSION['success'] = $message;
    } else {
        // Rollback transaction
        $db->rollback();
        $_SESSION['error'] = 'Gagal menghapus produk. Silakan coba lagi.';
    }

} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
}

redirect('admin/produk/');
?>
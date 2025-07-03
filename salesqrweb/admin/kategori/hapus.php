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
    $_SESSION['error'] = 'ID kategori tidak ditemukan.';
    redirect('admin/kategori/');
}

$categoryId = $_GET['id'];

try {
    // Begin transaction
    $db->beginTransaction();
    
    // Check if category exists
    $query = "SELECT * FROM kategori WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $categoryId);
    $stmt->execute();
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        $_SESSION['error'] = 'Kategori tidak ditemukan.';
        redirect('admin/kategori/');
    }

    // Get count of products in this category
    $query = "SELECT COUNT(*) as product_count FROM produk WHERE kategori_id = :kategori_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kategori_id', $categoryId);
    $stmt->execute();
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete category
    $query = "DELETE FROM kategori WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $categoryId);
    
    if ($stmt->execute()) {
        // Update products that were in this category to NULL
        if ($productCount['product_count'] > 0) {
            $query = "UPDATE produk SET kategori_id = NULL WHERE kategori_id = :kategori_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':kategori_id', $categoryId);
            $stmt->execute();
        }
        
        // Commit transaction
        $db->commit();
        
        // Create success message with details
        $message = 'Kategori "' . htmlspecialchars($category['nama_kategori']) . '" berhasil dihapus!';
        if ($productCount['product_count'] > 0) {
            $message .= ' ' . $productCount['product_count'] . ' produk yang terkait sekarang menjadi tanpa kategori.';
        }
        
        $_SESSION['success'] = $message;
    } else {
        // Rollback transaction
        $db->rollback();
        $_SESSION['error'] = 'Gagal menghapus kategori. Silakan coba lagi.';
    }

} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
}

redirect('admin/kategori/');
?>
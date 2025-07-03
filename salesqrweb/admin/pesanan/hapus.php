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
    $_SESSION['error'] = 'Invalid request';
    redirect('admin/pesanan/');
}

$orderId = $_GET['id'];

// Check if order exists
$query = "SELECT * FROM pesanan WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $orderId);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error'] = 'Pesanan tidak ditemukan';
    redirect('admin/pesanan/');
}

// Start transaction
$db->beginTransaction();

try {
    // Get order items to restore stock
    $query = "SELECT produk_id, jumlah FROM detail_pesanan WHERE pesanan_id = :pesanan_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pesanan_id', $orderId);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Restore stock for each product
    foreach ($items as $item) {
        $query = "UPDATE produk SET stok = stok + :jumlah WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':jumlah', $item['jumlah']);
        $stmt->bindParam(':id', $item['produk_id']);
        $stmt->execute();
        
        // Log stock change
        $query = "INSERT INTO log_stok (produk_id, jenis_transaksi, jumlah, stok_sebelum, stok_sesudah, keterangan, admin_id) 
                  SELECT :produk_id, 'masuk', :jumlah, stok, stok + :jumlah, 'Pembatalan pesanan #{$order['nomor_pesanan']}', :admin_id 
                  FROM produk WHERE id = :produk_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':produk_id', $item['produk_id']);
        $stmt->bindParam(':jumlah', $item['jumlah']);
        $stmt->bindParam(':admin_id', $_SESSION['admin_id']);
        $stmt->execute();
    }
    
    // Delete payment proof if exists
    $query = "SELECT nama_file FROM bukti_transfer WHERE pesanan_id = :pesanan_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pesanan_id', $orderId);
    $stmt->execute();
    $paymentProof = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paymentProof && file_exists('assets/uploads/bukti-transfer/' . $paymentProof['nama_file'])) {
        unlink('assets/uploads/bukti-transfer/' . $paymentProof['nama_file']);
    }
    
    // Delete QR code if exists
    if ($order['qr_code'] && file_exists('assets/uploads/qr-codes/' . $order['qr_code'])) {
        unlink('assets/uploads/qr-codes/' . $order['qr_code']);
    }
    
    // Delete order items
    $query = "DELETE FROM detail_pesanan WHERE pesanan_id = :pesanan_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pesanan_id', $orderId);
    $stmt->execute();
    
    // Delete payment proof record
    $query = "DELETE FROM bukti_transfer WHERE pesanan_id = :pesanan_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pesanan_id', $orderId);
    $stmt->execute();
    
    // Delete QR code record
    $query = "DELETE FROM qr_codes WHERE pesanan_id = :pesanan_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pesanan_id', $orderId);
    $stmt->execute();
    
    // Delete order
    $query = "DELETE FROM pesanan WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $orderId);
    $stmt->execute();
    
    $db->commit();
    
    $_SESSION['success'] = [
        'title' => 'Berhasil!',
        'message' => 'Pesanan #' . $order['nomor_pesanan'] . ' berhasil dihapus'
    ];
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = [
        'title' => 'Gagal!',
        'message' => 'Gagal menghapus pesanan: ' . $e->getMessage()
    ];
}

redirect('admin/pesanan/');
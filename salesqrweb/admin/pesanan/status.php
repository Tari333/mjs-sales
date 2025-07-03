<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id']) || !isset($_POST['status'])) {
    $_SESSION['error'] = 'Invalid request';
    redirect('admin/pesanan/');
}

$orderId = $_POST['order_id'];
$newStatus = $_POST['status'];

// Validate status
$allowedStatuses = ['pending', 'dikonfirmasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
if (!in_array($newStatus, $allowedStatuses)) {
    $_SESSION['error'] = 'Status tidak valid';
    redirect('admin/pesanan/detail.php?id=' . $orderId);
}

// Get current order status
$query = "SELECT status_pesanan, nomor_pesanan FROM pesanan WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $orderId);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error'] = 'Pesanan tidak ditemukan';
    redirect('admin/pesanan/');
}

// Check if status is actually changing
if ($order['status_pesanan'] === $newStatus) {
    $_SESSION['info'] = 'Status pesanan tidak berubah';
    redirect('admin/pesanan/detail.php?id=' . $orderId);
}

// Special validation for canceled orders
if ($newStatus === 'dibatalkan') {
    // Check if payment has been verified
    $query = "SELECT status_pembayaran FROM pesanan WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $orderId);
    $stmt->execute();
    $paymentStatus = $stmt->fetchColumn();
    
    if ($paymentStatus === 'lunas') {
        $_SESSION['error'] = 'Tidak dapat membatalkan pesanan yang sudah dibayar';
        redirect('admin/pesanan/detail.php?id=' . $orderId);
    }
}

// Start transaction
$db->beginTransaction();

try {
    // Update order status
    $query = "UPDATE pesanan SET status_pesanan = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $newStatus);
    $stmt->bindParam(':id', $orderId);
    $stmt->execute();

    // If order is being canceled, restore product stock
    if ($newStatus === 'dibatalkan') {
        // Get order items
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
        
        // Also update payment status to failed if not already
        $query = "UPDATE pesanan SET status_pembayaran = 'gagal' WHERE id = :id AND status_pembayaran != 'lunas'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();
    }
    
    $db->commit();
    
    $_SESSION['success'] = [
        'title' => 'Berhasil!',
        'message' => 'Status pesanan #' . $order['nomor_pesanan'] . ' berhasil diperbarui menjadi ' . $newStatus
    ];
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = [
        'title' => 'Gagal!',
        'message' => 'Gagal memperbarui status pesanan: ' . $e->getMessage()
    ];
}

redirect('admin/pesanan/detail.php?id=' . $orderId);
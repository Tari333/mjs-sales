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
    redirect('admin/pembayaran/');
}

$orderId = $_GET['id'];
$proofId = isset($_GET['bukti_id']) ? $_GET['bukti_id'] : null;

// Start transaction
$db->beginTransaction();

try {
    // First get order number for success message
    $query = "SELECT nomor_pesanan FROM pesanan WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $orderId);
    $stmt->execute();
    $orderNumber = $stmt->fetchColumn();
    
    if (!$orderNumber) {
        throw new Exception('Order not found');
    }
    
    // Check if bukti_transfer exists for this order
    $query = "SELECT id FROM bukti_transfer WHERE pesanan_id = :pesanan_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pesanan_id', $orderId);
    $stmt->execute();
    $existingProof = $stmt->fetchColumn();
    
    if (!$existingProof) {
        // Create bukti_transfer entry with 'send thru whatsapp'
        $query = "INSERT INTO bukti_transfer (pesanan_id, nama_file, path_file, tipe_file, status_verifikasi, tanggal_upload) 
                  VALUES (:pesanan_id, 'send thru whatsapp', 'whatsapp', 'whatsapp', 'menunggu', NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':pesanan_id', $orderId);
        $stmt->execute();
        $proofId = $db->lastInsertId();
    } else {
        $proofId = $existingProof;
    }
    
    // Reject payment
    $query = "UPDATE pesanan
              SET status_pembayaran = 'gagal',
                  status_pesanan = 'dibatalkan'
              WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $orderId);
    $stmt->execute();
    
    // Update payment proof status
    $query = "UPDATE bukti_transfer 
              SET status_verifikasi = 'ditolak', 
                  verified_by = :verified_by, 
                  verified_at = NOW() 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':verified_by', $_SESSION['admin_id']);
    $stmt->bindParam(':id', $proofId);
    $stmt->execute();
    
    $db->commit();
    
    $_SESSION['success'] = [
        'title' => 'Berhasil!',
        'message' => 'Pembayaran untuk pesanan #' . $orderNumber . ' berhasil ditolak'
    ];
    
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = [
        'title' => 'Gagal!',
        'message' => 'Gagal menolak pembayaran: ' . $e->getMessage()
    ];
}

redirect('admin/pembayaran/');
?>
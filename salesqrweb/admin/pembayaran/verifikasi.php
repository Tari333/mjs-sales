<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['bukti_id']) || empty($_GET['bukti_id'])) {
    $_SESSION['error'] = 'Invalid request';
    redirect('admin/pembayaran/');
}

$orderId = $_GET['id'];
$proofId = $_GET['bukti_id'];

// Start transaction
$db->beginTransaction();

try {
    // First get order number for success message
    $query = "SELECT nomor_pesanan FROM pesanan WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $orderId);
    $stmt->execute();
    $orderNumber = $stmt->fetchColumn();

    // Verify payment
    $query = "UPDATE pesanan 
              SET status_pembayaran = 'lunas', 
                  status_pesanan = 'dikonfirmasi',
                  tanggal_bayar = NOW() 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $orderId);
    $stmt->execute();

    // Update payment proof status
    $query = "UPDATE bukti_transfer 
              SET status_verifikasi = 'diterima', 
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
        'message' => 'Pembayaran untuk pesanan #' . $orderNumber . ' berhasil diverifikasi'
    ];
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = [
        'title' => 'Gagal!',
        'message' => 'Gagal memverifikasi pembayaran: ' . $e->getMessage()
    ];
}

redirect('admin/pembayaran/');
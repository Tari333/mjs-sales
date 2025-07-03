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
    $_SESSION['error'] = 'ID pengguna tidak ditemukan.';
    redirect('admin/user/');
}

$userId = $_GET['id'];

// Prevent deleting the current logged in user
if ($userId == $_SESSION['user_id']) {
    $_SESSION['error'] = 'Anda tidak dapat menghapus akun yang sedang digunakan!';
    redirect('admin/user/');
}

try {
    // Begin transaction
    $db->beginTransaction();
    
    // Get user details for message
    $query = "SELECT username FROM admin WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'Pengguna tidak ditemukan.';
        redirect('admin/user/');
    }

    // Delete user
    $query = "DELETE FROM admin WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    
    if ($stmt->execute()) {
        // Commit transaction
        $db->commit();
        $_SESSION['success'] = 'Pengguna "' . htmlspecialchars($user['username']) . '" berhasil dihapus!';
    } else {
        // Rollback transaction
        $db->rollback();
        $_SESSION['error'] = 'Gagal menghapus pengguna. Silakan coba lagi.';
    }

} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
}

redirect('admin/user/');
?>
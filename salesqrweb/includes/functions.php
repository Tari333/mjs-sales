<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// Basic utility functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('admin/login.php');
    }
}

function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
}

// File upload handling
function handleFileUpload($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    $fileName = basename($file['name']);
    $targetPath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

    // Check if file is allowed
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large'];
    }

    // Generate unique filename
    $newFileName = uniqid() . '.' . $fileType;
    $newTargetPath = $targetDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $newTargetPath)) {
        return ['success' => true, 'filename' => $newFileName, 'path' => $newTargetPath];
    } else {
        return ['success' => false, 'error' => 'Error uploading file'];
    }
}

// QR Code generation
function generateQRCode($data, $filename) {
    require_once __DIR__ . '/../libs/phpqrcode/qrlib.php';
    
    $path = '../' . QR_CODE_PATH . $filename;
    QRcode::png($data, $path, QR_ECLEVEL_L, QR_CODE_SIZE);
    
    return $path;
}
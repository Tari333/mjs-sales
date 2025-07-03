<?php
// Application Configuration
define('APP_NAME', 'PT. Mega Jaya Sakti');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/mjs-catalog/mjs-sales/salesqrweb/');
define ('SITE_ROOT', realpath(dirname(__FILE__)));

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Upload Configuration
define('UPLOAD_PATH', 'assets/uploads/');
define('BUKTI_TF', '../assets/uploads/bukti-transfer/');
define('ABSOLUTE', 'C:/xampp/htdocs/mjs-catalog/mjs-sales/salesqrweb/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// QR Code Configuration
define('QR_CODE_PATH', 'assets/uploads/qr-codes/');
define('QR_CODE_SIZE', 10);

function asset_path($relativePath, $type = 'css') {
    $prefixes = ['', '../', '../../', '../../../'];
    foreach ($prefixes as $prefix) {
        $fullPath = $prefix . $relativePath;
        if (file_exists($fullPath)) {
            $href = $fullPath;
            if ($type === 'css') {
                echo '<link type="text/css" href="' . $href . '" rel="stylesheet" />' . PHP_EOL;
            } elseif ($type === 'js') {
                echo '<script type="text/javascript" src="' . $href . '"></script>' . PHP_EOL;
            }
            return;
        }
    }
    // Do nothing if not found
}

// // Helper Functions
// function formatRupiah($number) {
//     return 'Rp ' . number_format($number, 0, ',', '.');
// }

// function formatDate($date) {
//     return date('d/m/Y H:i', strtotime($date));
// }

// function generateOrderNumber() {
//     return 'ORD-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
// }

// function sanitizeInput($data) {
//     return htmlspecialchars(strip_tags(trim($data)));
// }

// function redirect($url) {
//     header("Location: " . BASE_URL . $url);
//     exit();
// }

// function isLoggedIn() {
//     return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
// }

// function requireLogin() {
//     if (!isLoggedIn()) {
//         redirect('admin/login.php');
//     }
// }
?>
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$succheaders = '';
$errheaders = '';

// Get current headers
$queryheaders = "SELECT logo_toko, nama_toko FROM pengaturan LIMIT 1";
$stmtheaders = $db->prepare($queryheaders);
$stmtheaders->execute();
$headers = $stmtheaders->fetch(PDO::FETCH_ASSOC);

$logoheaders = '';
$namatokoheaders = APP_NAME; // fallback to default app name

if ($headers) {
    $namatokoheaders = $headers['nama_toko'] ?: APP_NAME;
    if (!empty($headers['logo_toko'])) {
        $logoheaders = $headers['logo_toko'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . htmlspecialchars($namatokoheaders) : htmlspecialchars($namatokoheaders) ?></title>
    
    <link rel="icon" type="image/x-icon" href="~/asset/favicon.ico">

    <?php
    // Bootstrap & FontAwesome
    asset_path('vendor/fontawesome-free/css/all.min.css');
    asset_path('vendor/fontawesome-free/css/fontawesome.min.css');
    asset_path('vendor/fontawesome-free/css/solid.min.css');
    asset_path('vendor/bootstrap/dist/css/bootstrap.min.css');

    // Important Addition
    asset_path('vendor/sweetalert2/dist/sweetalert2.min.css');
    asset_path('vendor/datatables/media/css/jquery.dataTables.min.css');
    asset_path('vendor/select2/dist/css/select2.min.css');
    asset_path('vendor/select2/dist/css/select2-bootstrap-5-theme.min.css');

    // Custom Styling
    asset_path('assets/css/sass.css');
    asset_path('assets/css/layers.css');
    asset_path('assets/css/style.css');
    asset_path('assets/css/responsive.css');
    ?>

</head>
<body>
    <div class="main-wrapper">
        <!-- ----NAVBAR---- -->
        <?php if (!isset($hideNavbar) || !$hideNavbar): ?>
            <?php include __DIR__ . '/navbar.php'; ?>
        <?php endif; ?>
        <!-- ----NAVBAR END---- -->

        <!-- ----SIDEBAR---- -->
        <?php if (isset($showSidebar) && $showSidebar): ?>
            <?php include __DIR__ . '/sidebar.php'; ?>
        <?php endif; ?>
        <!-- ----SIDEBAR END---- -->

        <!-- ----CONTENT---- -->
        <?php if (isLoggedIn()): ?>
        <div class="content-wrapper">
        <?php else: ?>
        <div class="content-wrapper hide">
        <?php endif; ?>
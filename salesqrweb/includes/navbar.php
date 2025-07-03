<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$successpengaturan = '';
$errorpengaturan = '';

// Get current pengagturansss
$querypengaturansss = "SELECT logo_toko, nama_toko FROM pengaturan LIMIT 1";
$stmtpengaturansss = $db->prepare($querypengaturansss);
$stmtpengaturansss->execute();
$pengagturansss = $stmtpengaturansss->fetch(PDO::FETCH_ASSOC);

$logopengaturansss = '';
$namatokopengaturansss = APP_NAME; // fallback to default app name

if ($pengagturansss) {
    $namatokopengaturansss = $pengagturansss['nama_toko'] ?: APP_NAME;
    if (!empty($pengagturansss['logo_toko'])) {
        $logopengaturansss = $pengagturansss['logo_toko'];
    }
}
?>

<div class="header-container fixed-top">
    <header class="header navbar navbar-expand-sm expand-header">
        <div class="header-left d-flex">
            <div class="logo d-flex justify-content-around align-items-center">
                <?php if (!empty($logopengaturansss)): ?>
                    <img src="<?= $prefixlogo . $logopengaturansss ?>" alt="<?= htmlspecialchars($namatokopengaturansss) ?>" class="me-2" style="height: 32px; width: auto;">
                <?php else: ?>
                    <i class="fas fa-store me-2"></i>
                <?php endif; ?>
                <p><?= htmlspecialchars($namatokopengaturansss) ?></p> 
                <?php if (isLoggedIn()): ?>
                    <a href="#" class="sidebarCollapse text-white" id="toggleSidebar" data-placement="bottom">
                        <span class="fas fa-bars"></span>
                    </a>
                <?php else: ?>
                    <i class="fas fa-store me-2"></i>
                <?php endif; ?>
            </div>
            <ul class="navbar-item me-auto flex-row ml-3">
                <li class="nav-item user-profile-dropdown">
                    <a class="nav-link user" href="<?= BASE_URL ?>">
                        <i class="fas fa-home me-1"></i>&nbsp;
                        Beranda
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item user-profile-dropdown">
                        <a class="nav-link user" href="<?= BASE_URL ?>admin/produk/">
                            <i class="fas fa-boxes me-1"></i>&nbsp;
                            Produk
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item user-profile-dropdown">
                        <a class="nav-link user" href="<?= BASE_URL ?>user/produk.php">
                            <i class="fas fa-boxes me-1"></i>&nbsp;
                            Produk
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item user-profile-dropdown">
                    <a class="nav-link user" href="<?= BASE_URL ?>tentang.php">
                        <i class="fas fa-info me-1"></i>&nbsp;
                        Tentang
                    </a>
                </li>
                <li class="nav-item user-profile-dropdown">
                    <a class="nav-link user" href="<?= BASE_URL ?>kontak.php">
                        <i class="fas fa-phone me-1"></i>&nbsp;
                        Kontak
                    </a>
                </li>
            </ul>
        </div>
        <ul class="navbar-item flex-row ml-auto">
            <?php if (isLoggedIn()): ?>
                <li class="nav-item user-profile-dropdown">
                    <?php
                    // Start session if not already started
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    // In a real application, you would get these from your authentication system
                    $_SESSION['admin_username'] = $_SESSION['admin_username'] ?? 'John Doe';
                    $_SESSION['admin_nama'] = $_SESSION['admin_nama'] ?? 'John Doe';
                    
                    $fullName = $_SESSION['admin_nama'] ?? 'User';
                    $firstLetter = strtoupper(substr($fullName, 0, 1));
                    $user_Name = $_SESSION['admin_username'] ?? 'Administrator';
                    ?>
                    <a href="<?= BASE_URL ?>admin/profile.php" style="text-decoration: none; background-color: none;">
                        <div class="user-profile text-muted">
                            <div class="user-avatar">
                                <span class="avatar-letter"><?php echo $firstLetter; ?></span>
                            </div>
                            <div class="d-none d-md-block">
                                <div class="text-sm font-medium"><?php echo $fullName; ?></div>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="nav-item user-profile-dropdown">
                    <a class="nav-link user" href="<?= BASE_URL ?>admin/logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Logout
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item user-profile-dropdown">
                    <a class="nav-link user" href="<?= BASE_URL ?>login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>
                        Admin Login
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </header>
</div>
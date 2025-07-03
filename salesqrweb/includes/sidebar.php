<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
?>

<?php if (isLoggedIn()): ?>
<div class="left-menu">
<?php else: ?>
<div class="left-menu hide">
<?php endif; ?>
    <div class="menubar-content">
        <nav class="animated bounceInDown">
            <ul id="sidebar">
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' && 'http://localhost' . $_SERVER['REQUEST_URI'] == BASE_URL . 'admin/' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>admin/">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?= strpos($_SERVER['REQUEST_URI'], 'produk') !== false ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>admin/produk/">
                        <i class="fas fa-boxes"></i>
                        <span>Produk</span>
                    </a>
                </li>
                <li class="<?= strpos($_SERVER['REQUEST_URI'], 'pesanan') !== false ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>admin/pesanan/">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pesanan</span>
                    </a>
                </li>
                <li class="<?= strpos($_SERVER['REQUEST_URI'], 'pembayaran') !== false ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>admin/pembayaran/">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Pembayaran</span>
                    </a>
                </li>
                <li class="<?= strpos($_SERVER['REQUEST_URI'], 'user') !== false ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>admin/user/">
                        <i class="fas fa-users"></i>
                        <span>Admins</span>
                    </a>
                </li>
                <li class="<?= strpos($_SERVER['REQUEST_URI'], 'kategori') !== false ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>admin/kategori/">
                        <i class="fas fa-list-alt"></i>
                        <span>Kategori</span>
                    </a>
                </li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>admin/profile.php">
                        <i class="fas fa-user-cog"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>admin/settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan Website</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

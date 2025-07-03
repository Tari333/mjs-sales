<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$pageTitle = 'Dashboard Admin';
$showSidebar = true;

$prefixlogo = '../';

// Get stats
$query = "SELECT 
            (SELECT COUNT(*) FROM produk WHERE status = 'aktif') as total_produk,
            (SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'pending') as pesanan_pending,
            (SELECT COUNT(*) FROM pesanan WHERE status_pembayaran = 'menunggu_verifikasi') as pembayaran_pending,
            (SELECT SUM(total_harga) FROM pesanan WHERE status_pembayaran = 'lunas' AND DATE(tanggal_pesanan) = CURDATE()) as pendapatan_hari_ini,
            (SELECT COUNT(*) FROM pesanan WHERE DATE(tanggal_pesanan) = CURDATE()) as pesanan_hari_ini,
            (SELECT COUNT(*) FROM produk WHERE stok <= 5 AND status = 'aktif') as produk_stok_rendah";
            
$stmt = $db->prepare($query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent orders
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM detail_pesanan dp WHERE dp.pesanan_id = p.id) as jumlah_item
          FROM pesanan p 
          ORDER BY p.tanggal_pesanan DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top products
$query = "SELECT pr.nama_produk, pr.gambar, SUM(dp.jumlah) as total_terjual, pr.stok
          FROM detail_pesanan dp
          JOIN produk pr ON dp.produk_id = pr.id
          JOIN pesanan p ON dp.pesanan_id = p.id
          WHERE p.status_pembayaran = 'lunas'
          GROUP BY dp.produk_id
          ORDER BY total_terjual DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
/* Dashboard Cards */
.dashboard-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    height: 100%;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(45, 55, 72, 0.15);
}

.dashboard-card.bg-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%) !important;
}

.dashboard-card.bg-warning {
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%) !important;
}

.dashboard-card.bg-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%) !important;
}

.dashboard-card.bg-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
}

.dashboard-card.bg-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%) !important;
}

.dashboard-card.bg-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%) !important;
}

.stat-icon {
    opacity: 0.3;
    transition: all 0.3s ease;
}

.dashboard-card:hover .stat-icon {
    opacity: 0.5;
    transform: scale(1.1);
}

/* Welcome Section */
.welcome-header {
    background: linear-gradient(
        135deg,
        rgba(113, 158, 221, 0.9) 25%,
        rgba(113, 158, 221, 0.5) 100%
    );
    border-radius: 25px;
    padding: 30px 20px;
    margin-bottom: 30px;
    color: #ffffff;
    box-shadow: 0 15px 35px rgba(113, 158, 221, 0.2);
}

/* Buttons */
.btn {
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    box-shadow: 0 3px 12px rgba(113, 158, 221, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(113, 158, 221, 0.4);
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%);
}

.btn-outline-primary {
    border: 2px solid #719edd;
    color: #719edd;
    background: transparent;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(113, 158, 221, 0.3);
}

.btn-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
}

.btn-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
}

.btn-warning {
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
}

/* Cards */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}

.card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-bottom: 2px solid #e2e8f0;
    border-radius: 15px 15px 0 0 !important;
    padding: 20px;
}

/* Table */
.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    border-bottom: 2px solid #e2e8f0;
    color: #4a5568;
    font-weight: 600;
    padding: 15px;
}

.table td {
    padding: 15px;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: rgba(113, 158, 221, 0.05);
}

/* Badges */
.badge {
    border-radius: 20px;
    padding: 8px 12px;
    font-weight: 500;
    font-size: 0.75rem;
}

/* Quick Actions */
.quick-actions .card-body {
    padding: 20px;
}

.quick-actions .btn {
    padding: 12px 20px;
    margin-bottom: 10px;
}

/* Top Products */
.product-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}

.product-item:last-child {
    border-bottom: none;
}

.product-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 10px;
    margin-right: 15px;
}

.product-placeholder {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dashboard-card .card-body {
        padding: 15px;
    }
    
    .dashboard-card h2 {
        font-size: 1.5rem;
    }
    
    .stat-icon {
        font-size: 2rem !important;
    }
}
</style>

<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="welcome-header text-center mb-4">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard Admin
        </h2>
        <p class="mb-0 fs-6">Selamat datang kembali! Kelola toko Anda dengan mudah</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="dashboard-card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Produk</h6>
                            <h2 class="mb-0"><?= $stats['total_produk'] ?></h2>
                        </div>
                        <i class="fas fa-boxes fa-2x stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="dashboard-card bg-warning text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pesanan Pending</h6>
                            <h2 class="mb-0"><?= $stats['pesanan_pending'] ?></h2>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="dashboard-card bg-info text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pembayaran Pending</h6>
                            <h2 class="mb-0"><?= $stats['pembayaran_pending'] ?></h2>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="dashboard-card bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pendapatan Hari Ini</h6>
                            <h2 class="mb-0 small"><?= formatRupiah($stats['pendapatan_hari_ini'] ?? 0) ?></h2>
                        </div>
                        <i class="fas fa-chart-line fa-2x stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="dashboard-card bg-secondary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pesanan Hari Ini</h6>
                            <h2 class="mb-0"><?= $stats['pesanan_hari_ini'] ?></h2>
                        </div>
                        <i class="fas fa-calendar-day fa-2x stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="dashboard-card bg-danger text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Stok Rendah</h6>
                            <h2 class="mb-0"><?= $stats['produk_stok_rendah'] ?></h2>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-list-alt me-2"></i>
                        Pesanan Terbaru
                    </h5>
                    <a href="<?= BASE_URL ?>admin/pesanan/" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Nama Pembeli</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada pesanan</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong><?= $order['nomor_pesanan'] ?></strong>
                                                <br>
                                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($order['tanggal_pesanan'])) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($order['nama_pembeli']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark"><?= $order['jumlah_item'] ?> item</span>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?= formatRupiah($order['total_harga']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $order['status_pesanan'] === 'pending' ? 'warning' : 
                                                    ($order['status_pesanan'] === 'dikonfirmasi' ? 'info' : 
                                                    ($order['status_pesanan'] === 'selesai' ? 'success' : 'secondary')) 
                                                ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $order['status_pesanan'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>admin/pesanan/detail.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Top Products -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card quick-actions mb-4">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-bolt me-2"></i>
                        Aksi Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>admin/produk/tambah.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Tambah Produk Baru
                        </a>
                        <a href="<?= BASE_URL ?>admin/pembayaran/" class="btn btn-info">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Verifikasi Pembayaran
                        </a>
                        <a href="<?= BASE_URL ?>admin/pesanan/" class="btn btn-warning">
                            <i class="fas fa-list-ul me-2"></i>
                            Kelola Pesanan
                        </a>
                        <a href="<?= BASE_URL ?>admin/produk/" class="btn btn-secondary">
                            <i class="fas fa-boxes me-2"></i>
                            Kelola Produk
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-star me-2"></i>
                        Produk Terlaris
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($topProducts)): ?>
                        <div class="text-center py-3">
                            <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Belum ada data penjualan</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topProducts as $index => $product): ?>
                            <div class="product-item">
                                <div class="me-2">
                                    <span class="badge bg-primary"><?= $index + 1 ?></span>
                                </div>
                                <?php if ($product['gambar']): ?>
                                    <img src="<?= BASE_URL ?>assets/uploads/products/<?= $product['gambar'] ?>" 
                                         class="product-img" alt="<?= htmlspecialchars($product['nama_produk']) ?>">
                                <?php else: ?>
                                    <div class="product-placeholder">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars(strlen($product['nama_produk']) > 25 ? substr($product['nama_produk'], 0, 25) . '...' : $product['nama_produk']) ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-shopping-cart me-1"></i>
                                        <?= $product['total_terjual'] ?> terjual 
                                        | <i class="fas fa-cubes me-1"></i>
                                        Stok: <?= $product['stok'] ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Add smooth animations to dashboard cards
    $('.dashboard-card').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        $(this).delay(index * 100).animate({opacity: 1}, 600, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });

    // Initialize DataTables for the orders table if it has data
    if ($('.table tbody tr').length > 1) {
        $('.table').DataTable({
            pageLength: 5,
            searching: false,
            lengthChange: false,
            info: false,
            language: {
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });
    }

    // Add loading state to buttons
    $('.btn').on('click', function() {
        if ($(this).attr('href') && !$(this).attr('href').startsWith('#')) {
            const originalHtml = $(this).html();
            $(this).html('<i class="fas fa-spinner fa-spin me-1"></i> Loading...');
            
            setTimeout(() => {
                $(this).html(originalHtml);
            }, 2000);
        }
    });

    // Auto-refresh stats every 5 minutes
    setInterval(function() {
        // You can implement AJAX refresh here if needed
        console.log('Auto-refresh stats...');
    }, 300000);

    // Add tooltips to stat cards
    $('.dashboard-card').each(function() {
        const title = $(this).find('.card-title').text();
        const value = $(this).find('h2').text();
        $(this).attr('title', `${title}: ${value}`);
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
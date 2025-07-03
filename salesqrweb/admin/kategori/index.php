<?php 
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php'; 
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();
$pageTitle = 'Manajemen Kategori';
$showSidebar = true;
$prefixlogo = '../../';

// Get all categories
$query = "SELECT k.*, 
          (SELECT COUNT(*) FROM produk WHERE kategori_id = k.id AND status = 'aktif') as total_produk
          FROM kategori k
          ORDER BY k.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get category statistics
$queryStats = "SELECT 
               COUNT(*) as total_kategori,
               SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
               SUM(CASE WHEN status = 'nonaktif' THEN 1 ELSE 0 END) as nonaktif
               FROM kategori";
$stmtStats = $db->prepare($queryStats);
$stmtStats->execute();
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
.category-stats-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.category-stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}

.category-stats-card.bg-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%) !important;
}

.category-stats-card.bg-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
}

.category-stats-card.bg-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%) !important;
}

.category-header {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.9) 25%, rgba(113, 158, 221, 0.5) 100%);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 25px;
    color: #ffffff;
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.2);
}

.btn-gradient-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 3px 12px rgba(113, 158, 221, 0.3);
}

.btn-gradient-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(113, 158, 221, 0.4);
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%);
    color: white;
}

.card-modern {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
}

.card-modern:hover {
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}

.table-modern th {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border: none;
    color: #4a5568;
    font-weight: 600;
    padding: 15px;
}

.table-modern td {
    padding: 12px 15px;
    vertical-align: middle;
    border-color: #e2e8f0;
}

.table-modern tbody tr:hover {
    background-color: rgba(113, 158, 221, 0.05);
}

.btn-action {
    border-radius: 8px;
    padding: 6px 10px;
    margin: 0 2px;
    transition: all 0.3s ease;
}

.btn-action:hover {
    transform: translateY(-1px);
}

.status-badge {
    border-radius: 20px;
    padding: 6px 12px;
    font-weight: 500;
    font-size: 0.75rem;
}

.product-count-badge {
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
    color: white;
}
</style>

<div class="container-fluid">
    <!-- Category Header -->
    <div class="category-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-tags me-2"></i> Manajemen Kategori
        </h2>
        <p class="mb-3 fs-6">Kelola semua kategori produk toko Anda</p>
        <a href="tambah.php" class="btn btn-gradient-primary btn-lg">
            <i class="fas fa-plus me-2"></i> Tambah Kategori Baru
        </a>
    </div>

    <!-- Category Statistics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="category-stats-card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Kategori</h6>
                            <h3 class="mb-0"><?= $stats['total_kategori'] ?></h3>
                        </div>
                        <i class="fas fa-tag fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="category-stats-card bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Kategori Aktif</h6>
                            <h3 class="mb-0"><?= $stats['aktif'] ?></h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="category-stats-card bg-secondary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Kategori Nonaktif</h6>
                            <h3 class="mb-0"><?= $stats['nonaktif'] ?></h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card-modern p-3">
        <div class="card-header bg-light mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list me-2"></i> Daftar Kategori
                </h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kategori</th>
                            <th>Jumlah Produk</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Dibuat Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Belum ada kategori</p>
                                    <a href="tambah.php" class="btn btn-gradient-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Tambah Kategori Pertama
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $index => $category): ?>
                                <tr>
                                    <td class="fw-bold"><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($category['nama_kategori']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge product-count-badge">
                                            <?= $category['total_produk'] ?> Produk
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($category['deskripsi']): ?>
                                            <?= htmlspecialchars(substr($category['deskripsi'], 0, 50)) ?>...
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-white status-badge bg-<?= $category['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($category['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('d M Y', strtotime($category['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-primary btn-action" title="Edit Kategori">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger btn-action confirm-delete" 
                                                    title="Hapus Kategori" 
                                                    data-id="<?= $category['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($category['nama_kategori']) ?>"
                                                    data-products="<?= $category['total_produk'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#categoriesTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        language: {
            search: "Cari Kategori:",
            lengthMenu: "Tampilkan _MENU_ kategori per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ kategori",
            infoEmpty: "Tidak ada kategori",
            infoFiltered: "(difilter dari _MAX_ total kategori)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Belum ada kategori"
        }
    });

    // Delete confirmation
    $('.confirm-delete').on('click', function(e) {
        e.preventDefault();
        const categoryId = $(this).data('id');
        const categoryName = $(this).data('name');
        const productCount = $(this).data('products');
        
        let warningText = `Yakin ingin menghapus kategori "${categoryName}"?`;
        let warningHtml = `<div class="text-start mt-3">`;
        
        if (productCount > 0) {
            warningHtml += `<div class="alert alert-warning">`;
            warningHtml += `<h6 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan!</h6>`;
            warningHtml += `<p class="mb-2">Kategori ini memiliki <strong>${productCount} produk</strong> terkait.</p>`;
            warningHtml += `<p class="mb-0 text-danger"><strong>Semua produk dalam kategori ini akan menjadi tanpa kategori!</strong></p>`;
            warningHtml += `</div>`;
        } else {
            warningHtml += `<div class="alert alert-info">`;
            warningHtml += `<i class="fas fa-info-circle me-2"></i>Kategori ini tidak memiliki produk terkait.`;
            warningHtml += `</div>`;
        }
        
        warningHtml += `</div>`;
        
        Swal.fire({
            title: 'Konfirmasi Hapus Kategori',
            html: warningHtml,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#a0aec0',
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
            customClass: {
                popup: 'swal-wide'
            },
            didOpen: () => {
                document.querySelector('.swal2-popup').style.width = '500px';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `hapus.php?id=${categoryId}`;
            }
        });
    });

    // Animate cards on load
    $('.category-stats-card').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        $(this).delay(index * 100).animate({opacity: 1}, 500, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
});
</script>
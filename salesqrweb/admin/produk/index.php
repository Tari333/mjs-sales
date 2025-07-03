<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$pageTitle = 'Manajemen Produk';
$showSidebar = true;
$prefixlogo = '../../';

// Get all products with category info
$query = "SELECT p.*, k.nama_kategori,
          (SELECT COUNT(*) FROM detail_pesanan dp WHERE dp.produk_id = p.id) as total_terjual
          FROM produk p 
          LEFT JOIN kategori k ON p.kategori_id = k.id 
          ORDER BY p.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get product statistics
$queryStats = "SELECT 
    COUNT(*) as total_produk,
    SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as produk_aktif,
    SUM(CASE WHEN stok <= 5 AND status = 'aktif' THEN 1 ELSE 0 END) as stok_rendah,
    SUM(CASE WHEN status = 'nonaktif' THEN 1 ELSE 0 END) as produk_nonaktif
    FROM produk";
$stmtStats = $db->prepare($queryStats);
$stmtStats->execute();
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
.product-stats-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.product-stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}

.product-stats-card.bg-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%) !important;
}

.product-stats-card.bg-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
}

.product-stats-card.bg-warning {
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%) !important;
}

.product-stats-card.bg-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%) !important;
}

.product-header {
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

.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
}

.product-placeholder {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e2e8f0;
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

.stock-warning {
    color: #e53e3e;
    font-weight: 600;
}

.stock-good {
    color: #38a169;
    font-weight: 600;
}
</style>

<div class="container-fluid">
    <!-- Product Header -->
    <div class="product-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-boxes me-2"></i>
            Manajemen Produk
        </h2>
        <p class="mb-3 fs-6">Kelola semua produk toko Anda dengan mudah</p>
        <a href="tambah.php" class="btn btn-gradient-primary btn-lg">
            <i class="fas fa-plus me-2"></i>
            Tambah Produk Baru
        </a>
    </div>

    <!-- Product Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="product-stats-card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Produk</h6>
                            <h3 class="mb-0"><?= $stats['total_produk'] ?></h3>
                        </div>
                        <i class="fas fa-cube fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="product-stats-card bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Produk Aktif</h6>
                            <h3 class="mb-0"><?= $stats['produk_aktif'] ?></h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="product-stats-card bg-warning text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Stok Rendah</h6>
                            <h3 class="mb-0"><?= $stats['stok_rendah'] ?></h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="product-stats-card bg-secondary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Nonaktif</h6>
                            <h3 class="mb-0"><?= $stats['produk_nonaktif'] ?></h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card-modern p-3">
        <div class="card-header bg-light mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list me-2"></i>
                    Daftar Produk
                </h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover" id="productsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Gambar</th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Terjual</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Belum ada produk</p>
                                    <a href="tambah.php" class="btn btn-gradient-primary mt-2">
                                        <i class="fas fa-plus me-1"></i>
                                        Tambah Produk Pertama
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $index => $product): ?>
                                <tr class="text-center align-middle align-items-center">
                                    <td class="fw-bold"><?= $index + 1 ?></td>
                                    <td>
                                        <?php if ($product['gambar']): ?>
                                            <img src="<?= BASE_URL ?>assets/uploads/products/<?= $product['gambar'] ?>" 
                                                 class="product-image" alt="<?= htmlspecialchars($product['nama_produk']) ?>">
                                        <?php else: ?>
                                            <div class="product-placeholder">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded"><?= $product['kode_produk'] ?></code>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($product['nama_produk']) ?></strong>
                                        <?php if ($product['deskripsi']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($product['deskripsi'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['nama_kategori']): ?>
                                            <span class="badge bg-light text-dark"><?= $product['nama_kategori'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-primary"><?= formatRupiah($product['harga']) ?></td>
                                    <td>
                                        <span class="<?= $product['stok'] <= 5 ? 'stock-warning' : 'stock-good' ?>">
                                            <?= $product['stok'] ?>
                                            <?php if ($product['stok'] <= 5): ?>
                                                <i class="fas fa-exclamation-triangle ms-1"></i>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $product['total_terjual'] ?></span>
                                    </td>
                                    <td>
                                        <span class=" text-white status-badge bg-<?= $product['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($product['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?= $product['id'] ?>" 
                                               class="btn btn-sm btn-primary btn-action" 
                                               title="Edit Produk">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger btn-action confirm-delete" 
                                                    title="Hapus Produk"
                                                    data-id="<?= $product['id'] ?>"
                                                    data-name="<?= htmlspecialchars($product['nama_produk']) ?>">
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
    $('#productsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [1, 9] } // Disable sorting for image and action columns
        ],
        language: {
            search: "Cari Produk:",
            lengthMenu: "Tampilkan _MENU_ produk per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ produk",
            infoEmpty: "Tidak ada produk",
            infoFiltered: "(difilter dari _MAX_ total produk)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Belum ada produk"
        }
    });

    // Replace the existing delete confirmation script with this enhanced version
    $('.confirm-delete').on('click', function(e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        
        // Get related data counts via AJAX
        $.ajax({
            url: 'get_related_data.php',
            method: 'POST',
            data: { product_id: productId },
            dataType: 'json',
            success: function(response) {
                let warningText = `Yakin ingin menghapus produk "${productName}"?`;
                let warningHtml = `<div class="text-start mt-3">`;
                
                if (response.has_relations) {
                    warningHtml += `<div class="alert alert-warning">`;
                    warningHtml += `<h6 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan!</h6>`;
                    warningHtml += `<p class="mb-2">Menghapus produk ini akan juga menghapus:</p>`;
                    warningHtml += `<ul class="mb-2">`;
                    
                    if (response.order_details > 0) {
                        warningHtml += `<li><strong>${response.order_details}</strong> detail pesanan terkait</li>`;
                    }
                    if (response.stock_logs > 0) {
                        warningHtml += `<li><strong>${response.stock_logs}</strong> log riwayat stok</li>`;
                    }
                    if (response.has_image) {
                        warningHtml += `<li>File gambar produk</li>`;
                    }
                    
                    warningHtml += `</ul>`;
                    warningHtml += `<p class="mb-0 text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>`;
                    warningHtml += `</div>`;
                } else {
                    warningHtml += `<div class="alert alert-info">`;
                    warningHtml += `<i class="fas fa-info-circle me-2"></i>Produk ini tidak memiliki data terkait.`;
                    if (response.has_image) {
                        warningHtml += ` File gambar produk akan ikut terhapus.`;
                    }
                    warningHtml += `</div>`;
                }
                
                warningHtml += `</div>`;
                
                Swal.fire({
                    title: 'Konfirmasi Hapus Produk',
                    html: warningHtml,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#a0aec0',
                    confirmButtonText: '<i class="fas fa-trash me-2"></i>Ya, Hapus Semuanya!',
                    cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
                    customClass: {
                        popup: 'swal-wide'
                    },
                    didOpen: () => {
                        // Add custom styling
                        document.querySelector('.swal2-popup').style.width = '500px';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Menghapus Produk...',
                            html: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Redirect to delete
                        window.location.href = `hapus.php?id=${productId}`;
                    }
                });
            },
            error: function() {
                // Fallback to simple confirmation
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Yakin ingin menghapus produk "${productName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#a0aec0',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `hapus.php?id=${productId}`;
                    }
                });
            }
        });
    });
    
    // Filter functionality
    $('#filterBtn').on('click', function() {
        // You can implement advanced filtering here
        Swal.fire({
            title: 'Filter Produk',
            html: `
                <select id="statusFilter" class="form-select mb-2">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
                <select id="stockFilter" class="form-select">
                    <option value="">Semua Stok</option>
                    <option value="low">Stok Rendah (≤5)</option>
                    <option value="good">Stok Cukup (>5)</option>
                </select>
            `,
            showCancelButton: true,
            confirmButtonText: 'Terapkan Filter',
            cancelButtonText: 'Reset'
        }).then((result) => {
            if (result.isConfirmed) {
                // Apply filters to DataTable
                const statusFilter = $('#statusFilter').val();
                const stockFilter = $('#stockFilter').val();
                
                let table = $('#productsTable').DataTable();
                
                // Reset filters
                table.columns().search('').draw();
                
                // Apply status filter
                if (statusFilter) {
                    table.column(8).search(statusFilter).draw();
                }
            }
        });
    });

    // Animate cards on load
    $('.product-stats-card').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        $(this).delay(index * 100).animate({opacity: 1}, 500, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
});
</script>
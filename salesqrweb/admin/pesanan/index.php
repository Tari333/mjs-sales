<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$pageTitle = 'Manajemen Pesanan';
$showSidebar = true;
$prefixlogo = '../../';

// Get all orders with enhanced data
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM detail_pesanan dp WHERE dp.pesanan_id = p.id) as jumlah_item,
          (SELECT SUM(dp.jumlah) FROM detail_pesanan dp WHERE dp.pesanan_id = p.id) as total_qty
          FROM pesanan p 
          ORDER BY p.tanggal_pesanan DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order statistics
$queryStats = "SELECT 
    COUNT(*) as total_pesanan,
    SUM(CASE WHEN status_pesanan = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status_pesanan = 'dikonfirmasi' THEN 1 ELSE 0 END) as dikonfirmasi,
    SUM(CASE WHEN status_pesanan = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN status_pembayaran = 'menunggu_verifikasi' THEN 1 ELSE 0 END) as menunggu_verifikasi,
    SUM(CASE WHEN status_pembayaran = 'lunas' THEN 1 ELSE 0 END) as lunas,
    SUM(CASE WHEN status_pesanan = 'selesai' THEN total_harga ELSE 0 END) as total_revenue
    FROM pesanan";
$stmtStats = $db->prepare($queryStats);
$stmtStats->execute();
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
.order-stats-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.order-stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}

.order-stats-card.bg-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%) !important;
}

.order-stats-card.bg-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
}

.order-stats-card.bg-warning {
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%) !important;
}

.order-stats-card.bg-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%) !important;
}

.order-stats-card.bg-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%) !important;
}

.order-stats-card.bg-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%) !important;
}

.order-header {
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

.order-number {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #719edd;
    background: rgba(113, 158, 221, 0.1);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.85rem;
}

.revenue-highlight {
    background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    font-size: 1.1em;
}
</style>

<div class="container-fluid">
    <!-- Order Header -->
    <div class="order-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-shopping-cart me-2"></i>
            Manajemen Pesanan
        </h2>
        <p class="mb-0 fs-6">Kelola semua pesanan pelanggan dengan mudah</p>
    </div>

    <!-- Order Statistics -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="order-stats-card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Pesanan</h6>
                            <h3 class="mb-0"><?= $stats['total_pesanan'] ?></h3>
                        </div>
                        <i class="fas fa-shopping-bag fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="order-stats-card bg-warning text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pending</h6>
                            <h3 class="mb-0"><?= $stats['pending'] ?></h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="order-stats-card bg-info text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Dikonfirmasi</h6>
                            <h3 class="mb-0"><?= $stats['dikonfirmasi'] ?></h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="order-stats-card bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Selesai</h6>
                            <h3 class="mb-0"><?= $stats['selesai'] ?></h3>
                        </div>
                        <i class="fas fa-check-double fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="order-stats-card bg-danger text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Verifikasi</h6>
                            <h3 class="mb-0"><?= $stats['menunggu_verifikasi'] ?></h3>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="order-stats-card bg-secondary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Revenue</h6>
                            <h4 class="mb-0 revenue-highlight"><?= formatRupiah($stats['total_revenue']) ?></h4>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card-modern p-3">
        <div class="card-header bg-light mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list me-2"></i>
                    Daftar Pesanan
                </h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover" id="ordersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No. Pesanan</th>
                            <th>Pembeli</th>
                            <th>Total</th>
                            <th>Items</th>
                            <th>Status Pesanan</th>
                            <th>Status Pembayaran</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $index => $order): ?>
                            <tr class="text-center align-middle">
                                <td class="fw-bold"><?= $index + 1 ?></td>
                                <td>
                                    <span class="order-number"><?= $order['nomor_pesanan'] ?></span>
                                </td>
                                <td class="text-start">
                                    <div>
                                        <strong><?= htmlspecialchars($order['nama_pembeli']) ?></strong>
                                        <br><small class="text-muted">
                                            <i class="fas fa-phone fa-xs me-1"></i>
                                            <?= $order['no_hp'] ?>
                                        </small>
                                    </div>
                                </td>
                                <td class="fw-bold text-primary">
                                    <?= formatRupiah($order['total_harga']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= $order['jumlah_item'] ?> items
                                    </span>
                                    <br><small class="text-muted">
                                        <?= $order['total_qty'] ?> qty
                                    </small>
                                </td>
                                <td>
                                    <span class="status-badge bg-<?= 
                                        $order['status_pesanan'] === 'pending' ? 'warning' : 
                                        ($order['status_pesanan'] === 'dikonfirmasi' ? 'info' : 
                                        ($order['status_pesanan'] === 'diproses' ? 'primary' :
                                        ($order['status_pesanan'] === 'dikirim' ? 'dark' :
                                        ($order['status_pesanan'] === 'selesai' ? 'success' : 'secondary')))) 
                                    ?> text-white">
                                        <?= ucfirst(str_replace('_', ' ', $order['status_pesanan'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge bg-<?= 
                                        $order['status_pembayaran'] === 'belum_bayar' ? 'danger' : 
                                        ($order['status_pembayaran'] === 'menunggu_verifikasi' ? 'warning' : 
                                        ($order['status_pembayaran'] === 'lunas' ? 'success' : 'secondary')) 
                                    ?> text-white">
                                        <?= ucfirst(str_replace('_', ' ', $order['status_pembayaran'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= formatDate($order['tanggal_pesanan']) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="detail.php?id=<?= $order['id'] ?>" 
                                            class="btn btn-sm btn-primary btn-action" 
                                            title="Detail Pesanan">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger btn-action confirm-delete" 
                                                title="Hapus Pesanan"
                                                data-id="<?= $order['id'] ?>"
                                                data-name="<?= htmlspecialchars($order['nomor_pesanan']) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
    $('#ordersTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[7, 'desc']], // Order by date descending
        columnDefs: [
            { orderable: false, targets: [8] } // Disable sorting for action column
        ],
        language: {
            search: "Cari Pesanan:",
            lengthMenu: "Tampilkan _MENU_ pesanan per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pesanan",
            infoEmpty: "Tidak ada pesanan",
            infoFiltered: "(difilter dari _MAX_ total pesanan)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Belum ada pesanan"
        }
    });

    // Delete confirmation
    $('.confirm-delete').on('click', function(e) {
        e.preventDefault();
        const orderId = $(this).data('id');
        const orderNumber = $(this).data('name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Yakin ingin menghapus pesanan ${orderNumber}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#a0aec0',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `hapus.php?id=${orderId}`;
            }
        });
    });

    // Filter functionality
    $('#filterBtn').on('click', function() {
        Swal.fire({
            title: 'Filter Pesanan',
            html: `
                <div class="text-start">
                    <label class="form-label">Status Pesanan:</label>
                    <select id="statusPesananFilter" class="form-select mb-3">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="dikonfirmasi">Dikonfirmasi</option>
                        <option value="diproses">Diproses</option>
                        <option value="dikirim">Dikirim</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                    <label class="form-label">Status Pembayaran:</label>
                    <select id="statusPembayaranFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="belum_bayar">Belum Bayar</option>
                        <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                        <option value="lunas">Lunas</option>
                        <option value="gagal">Gagal</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Terapkan Filter',
            cancelButtonText: 'Reset',
            preConfirm: () => {
                return {
                    statusPesanan: $('#statusPesananFilter').val(),
                    statusPembayaran: $('#statusPembayaranFilter').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const filters = result.value;
                let table = $('#ordersTable').DataTable();
                
                // Reset filters
                table.columns().search('').draw();
                
                // Apply filters
                if (filters.statusPesanan) {
                    table.column(5).search(filters.statusPesanan).draw();
                }
                if (filters.statusPembayaran) {
                    table.column(6).search(filters.statusPembayaran).draw();
                }
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Reset all filters
                $('#ordersTable').DataTable().columns().search('').draw();
            }
        });
    });

    // Export functionality
    $('#exportBtn').on('click', function() {
        Swal.fire({
            title: 'Export Data',
            text: 'Fitur export akan segera tersedia',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    });

    // Animate cards on load
    $('.order-stats-card').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        $(this).delay(index * 100).animate({opacity: 1}, 500, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
});
</script>
<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$pageTitle = 'Verifikasi Pembayaran';
$showSidebar = true;
$prefixlogo = '../../';

// Get payment statistics
$queryStats = "SELECT 
    COUNT(*) as total_pembayaran,
    COUNT(CASE WHEN status_verifikasi = 'menunggu' THEN 1 END) as menunggu,
    COUNT(CASE WHEN status_verifikasi = 'diterima' THEN 1 END) as diterima,
    COUNT(CASE WHEN status_verifikasi = 'ditolak' THEN 1 END) as ditolak,
    SUM(CASE WHEN status_verifikasi = 'diterima' THEN p.total_harga ELSE 0 END) as total_verified
    FROM bukti_transfer bt
    JOIN pesanan p ON bt.pesanan_id = p.id";
$stmtStats = $db->prepare($queryStats);
$stmtStats->execute();
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

// Get pending payments
$query = "SELECT p.id, p.nomor_pesanan, p.nama_pembeli, p.total_harga, 
          bt.id as bukti_id, bt.nama_file, bt.status_verifikasi, bt.tanggal_upload,
          p.status_pesanan, p.status_pembayaran
          FROM pesanan p
          LEFT JOIN bukti_transfer bt ON p.id = bt.pesanan_id
          ORDER BY p.tanggal_pesanan DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
.payment-stats-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.payment-stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}

.payment-stats-card.bg-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%) !important;
}

.payment-stats-card.bg-warning {
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%) !important;
}

.payment-stats-card.bg-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
}

.payment-stats-card.bg-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%) !important;
}

.payment-stats-card.bg-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%) !important;
}

.payment-header {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.9) 25%, rgba(113, 158, 221, 0.5) 100%);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 25px;
    color: #ffffff;
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.2);
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

.card-modern .card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border: none;
    border-radius: 15px 15px 0 0;
    color: #4a5568;
    font-weight: 600;
    padding: 15px 20px;
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

.proof-image {
    max-width: 100px;
    max-height: 60px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
}

.proof-image:hover {
    transform: scale(1.8);
    z-index: 10;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-group-actions {
    display: flex;
    gap: 5px;
}

.btn-verify {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    border: none;
}

.btn-reject {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
    color: white;
    border: none;
}

.btn-detail {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: white;
    border: none;
}
</style>

<div class="container-fluid">
    <!-- Payment Header -->
    <div class="payment-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-credit-card me-2"></i>
            Verifikasi Pembayaran
        </h2>
        <p class="mb-0 fs-6">Kelola semua pembayaran yang memerlukan verifikasi</p>
    </div>

    <!-- Payment Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="payment-stats-card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Pembayaran</h6>
                            <h3 class="mb-0"><?= $stats['total_pembayaran'] ?></h3>
                        </div>
                        <i class="fas fa-file-invoice-dollar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="payment-stats-card bg-warning text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Menunggu</h6>
                            <h3 class="mb-0"><?= $stats['menunggu'] ?></h3>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="payment-stats-card bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Diterima</h6>
                            <h3 class="mb-0"><?= $stats['diterima'] ?></h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="payment-stats-card bg-danger text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Ditolak</h6>
                            <h3 class="mb-0"><?= $stats['ditolak'] ?></h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card-modern p-3">
        <div class="card-header bg-light mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list me-2"></i>
                    Daftar Pembayaran
                </h5>
                <div>
                    <button id="filterBtn" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-filter me-1"></i>
                        Filter
                    </button>
                    <button id="exportBtn" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-export me-1"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No. Pesanan</th>
                            <th>Pembeli</th>
                            <th>Total</th>
                            <th>Bukti Transfer</th>
                            <th>Status Verifikasi</th>
                            <th>Status Pesanan</th>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $index => $payment): ?>
                            <tr class="align-middle">
                                <td class="fw-bold"><?= $index + 1 ?></td>
                                <td>
                                    <span class="order-number"><?= $payment['nomor_pesanan'] ?></span>
                                </td>
                                <td><?= htmlspecialchars($payment['nama_pembeli']) ?></td>
                                <td class="fw-bold text-primary">
                                    <?= formatRupiah($payment['total_harga']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($payment['nama_file']) && file_exists(__DIR__ . '/../../assets/uploads/bukti-transfer/' . $payment['nama_file'])): ?>
                                        <a href="<?= BASE_URL ?>assets/uploads/bukti-transfer/<?= $payment['nama_file'] ?>" 
                                           target="_blank" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-title="Lihat Bukti Transfer">
                                            <img src="<?= BASE_URL ?>assets/uploads/bukti-transfer/<?= $payment['nama_file'] ?>" 
                                                 class="proof-image" 
                                                 alt="Bukti Transfer">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Tidak ada
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    // Set default status if empty
                                    $status = !empty($payment['status_verifikasi']) ? $payment['status_verifikasi'] : 'menunggu';
                                    ?>
                                    <span class="status-badge bg-<?= 
                                        $status === 'menunggu' ? 'warning' : 
                                        ($status === 'diterima' ? 'success' : 'danger') 
                                    ?> text-white">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge bg-<?= 
                                        $payment['status_pesanan'] === 'pending' ? 'warning' : 
                                        ($payment['status_pesanan'] === 'dikonfirmasi' ? 'info' : 
                                        ($payment['status_pesanan'] === 'selesai' ? 'success' : 
                                        ($payment['status_pesanan'] === 'dibatalkan' ? 'danger' : 'secondary'))) 
                                    ?>">
                                        <?= ucfirst(str_replace('_', ' ', $payment['status_pesanan'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= !empty($payment['tanggal_upload']) ? formatDate($payment['tanggal_upload']) : '-' ?></small>
                                </td>
                                <td>
                                    <div class="btn-group-actions">
                                        <?php if (!empty($payment['nama_file']) && file_exists(__DIR__ . '/../../assets/uploads/bukti-transfer/' . $payment['nama_file'])): ?>
                                            <a href="verifikasi.php?id=<?= $payment['id'] ?>&bukti_id=<?= $payment['bukti_id'] ?>" 
                                               class="btn btn-sm btn-verify btn-action" 
                                               title="Verifikasi"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="tolak.php?id=<?= $payment['id'] ?>&bukti_id=<?= $payment['bukti_id'] ?>" 
                                               class="btn btn-sm btn-reject btn-action" 
                                               title="Tolak"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-verify btn-action" disabled title="Bukti transfer belum tersedia">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-reject btn-action" disabled title="Bukti transfer belum tersedia">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>admin/pesanan/detail.php?id=<?= $payment['id'] ?>" 
                                           class="btn btn-sm btn-detail btn-action" 
                                           title="Detail Pesanan"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
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
    $('#paymentsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[7, 'desc']], // Order by upload date descending
        columnDefs: [
            { orderable: false, targets: [8] } // Disable sorting for action column
        ],
        language: {
            search: "Cari Pembayaran:",
            lengthMenu: "Tampilkan _MENU_ pembayaran per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pembayaran",
            infoEmpty: "Tidak ada pembayaran",
            infoFiltered: "(difilter dari _MAX_ total pembayaran)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Belum ada pembayaran"
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Filter functionality
    $('#filterBtn').on('click', function() {
        Swal.fire({
            title: 'Filter Pembayaran',
            html: `
                <div class="text-start">
                    <label class="form-label">Status Verifikasi:</label>
                    <select id="statusVerifikasiFilter" class="form-select mb-3">
                        <option value="">Semua Status</option>
                        <option value="menunggu">Menunggu</option>
                        <option value="diterima">Diterima</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                    <label class="form-label">Status Pesanan:</label>
                    <select id="statusPesananFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="dikonfirmasi">Dikonfirmasi</option>
                        <option value="diproses">Diproses</option>
                        <option value="dikirim">Dikirim</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Terapkan Filter',
            cancelButtonText: 'Reset',
            preConfirm: () => {
                return {
                    statusVerifikasi: $('#statusVerifikasiFilter').val(),
                    statusPesanan: $('#statusPesananFilter').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const filters = result.value;
                let table = $('#paymentsTable').DataTable();
                
                // Reset filters
                table.columns().search('').draw();
                
                // Apply filters
                if (filters.statusVerifikasi) {
                    table.column(5).search(filters.statusVerifikasi).draw();
                }
                if (filters.statusPesanan) {
                    table.column(6).search(filters.statusPesanan).draw();
                }
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Reset all filters
                $('#paymentsTable').DataTable().columns().search('').draw();
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
    $('.payment-stats-card').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        $(this).delay(index * 100).animate({opacity: 1}, 500, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });

    // Confirm before verify/reject
    $('.btn-verify').on('click', function(e) {
        if ($(this).is(':disabled')) return;
        e.preventDefault();
        const url = $(this).attr('href');
        Swal.fire({
            title: 'Verifikasi Pembayaran',
            text: 'Yakin ingin memverifikasi pembayaran ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#48bb78',
            cancelButtonColor: '#a0aec0',
            confirmButtonText: 'Ya, Verifikasi!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    $('.btn-reject').on('click', function(e) {
        if ($(this).is(':disabled')) return;
        e.preventDefault();
        const url = $(this).attr('href');
        Swal.fire({
            title: 'Tolak Pembayaran',
            text: 'Yakin ingin menolak pembayaran ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#a0aec0',
            confirmButtonText: 'Ya, Tolak!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>
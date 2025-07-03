<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('admin/pesanan/');
}

$orderId = $_GET['id'];

// Get order details
$query = "SELECT p.* FROM pesanan p WHERE p.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $orderId);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('admin/pesanan/');
}

// Get order items
$query = "SELECT dp.*, pr.nama_produk, pr.gambar 
          FROM detail_pesanan dp 
          JOIN produk pr ON dp.produk_id = pr.id 
          WHERE dp.pesanan_id = :pesanan_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':pesanan_id', $orderId);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment proof if exists
$query = "SELECT * FROM bukti_transfer WHERE pesanan_id = :pesanan_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':pesanan_id', $orderId);
$stmt->execute();
$paymentProof = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Detail Pesanan #' . $order['nomor_pesanan'];
$showSidebar = true;
$prefixlogo = '../../';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
.order-header {
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

.info-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(45, 55, 72, 0.05);
}

.info-label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 5px;
}

.info-value {
    color: #2d3748;
    font-size: 1.1em;
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

.btn-gradient-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 3px 12px rgba(72, 187, 120, 0.3);
}

.btn-gradient-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
    background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
    color: white;
}

.btn-gradient-danger {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 3px 12px rgba(229, 62, 62, 0.3);
}

.btn-gradient-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(229, 62, 62, 0.4);
    background: linear-gradient(135deg, #c53030 0%, #e53e3e 100%);
    color: white;
}

.product-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.product-item:hover {
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.1);
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    margin-right: 15px;
}

.product-placeholder {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e2e8f0;
    margin-right: 15px;
}

.status-badge {
    border-radius: 20px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.payment-proof-image {
    max-width: 100%;
    max-height: 200px;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.1);
}

.form-modern {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
}

.form-modern .form-select {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 15px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.form-modern .form-select:focus {
    border-color: #719edd;
    box-shadow: 0 0 0 0.2rem rgba(113, 158, 221, 0.25);
}

.total-section {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid #e2e8f0;
}

.qr-code-container {
    text-align: center;
    padding: 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.back-btn {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 3px 12px rgba(160, 174, 192, 0.3);
}

.back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(160, 174, 192, 0.4);
    background: linear-gradient(135deg, #718096 0%, #a0aec0 100%);
    color: white;
}
</style>

<div class="container-fluid">
    <!-- Order Header -->
    <div class="order-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-file-invoice me-2"></i>
            Detail Pesanan
        </h2>
        <p class="mb-3 fs-6">Pesanan #<?= $order['nomor_pesanan'] ?></p>
        <div class="d-flex justify-content-center gap-2">
            <span class="status-badge bg-<?= 
                $order['status_pesanan'] === 'pending' ? 'warning' : 
                ($order['status_pesanan'] === 'dikonfirmasi' ? 'info' : 
                ($order['status_pesanan'] === 'selesai' ? 'success' : 
                ($order['status_pesanan'] === 'dibatalkan' ? 'danger' : 'secondary'))) 
            ?>">
                <i class="fas fa-<?= 
                    $order['status_pesanan'] === 'pending' ? 'clock' : 
                    ($order['status_pesanan'] === 'dikonfirmasi' ? 'check' : 
                    ($order['status_pesanan'] === 'selesai' ? 'check-circle' : 
                    ($order['status_pesanan'] === 'dibatalkan' ? 'times' : 'question'))) 
                ?>"></i>
                <?= ucfirst(str_replace('_', ' ', $order['status_pesanan'])) ?>
            </span>
            <span class="status-badge bg-<?= 
                $order['status_pembayaran'] === 'belum_bayar' ? 'danger' : 
                ($order['status_pembayaran'] === 'menunggu_verifikasi' ? 'warning' : 
                ($order['status_pembayaran'] === 'lunas' ? 'success' : 'secondary')) 
            ?>">
                <i class="fas fa-<?= 
                    $order['status_pembayaran'] === 'belum_bayar' ? 'exclamation-triangle' : 
                    ($order['status_pembayaran'] === 'menunggu_verifikasi' ? 'clock' : 
                    ($order['status_pembayaran'] === 'lunas' ? 'check-circle' : 'question')) 
                ?>"></i>
                <?= ucfirst(str_replace('_', ' ', $order['status_pembayaran'])) ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Information -->
            <div class="card-modern mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Pesanan
                    </h5>
                </div>
                <div class="card-body p-2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="info-label">
                                    <i class="fas fa-user me-2"></i>
                                    Informasi Pembeli
                                </h6>
                                <div class="info-value">
                                    <p class="mb-2">
                                        <strong>Nama:</strong> <?= htmlspecialchars($order['nama_pembeli']) ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>No. HP:</strong> 
                                        <a href="https://wa.me/<?= $order['no_hp'] ?>" target="_blank" class="text-success">
                                            <i class="fab fa-whatsapp me-1"></i>
                                            <?= $order['no_hp'] ?>
                                        </a>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Alamat:</strong> <?= htmlspecialchars($order['alamat']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="info-label">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Detail Pesanan
                                </h6>
                                <div class="info-value">
                                    <p class="mb-2">
                                        <strong>No. Pesanan:</strong> 
                                        <code class="bg-light px-2 py-1 rounded"><?= $order['nomor_pesanan'] ?></code>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Tanggal Pesanan:</strong> <?= formatDate($order['tanggal_pesanan']) ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Total Harga:</strong> 
                                        <span class="text-primary fw-bold fs-5"><?= formatRupiah($order['total_harga']) ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card-modern mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>
                        Item Pesanan
                    </h5>
                </div>
                <div class="card-body p-2">
                    <?php foreach ($items as $item): ?>
                        <div class="product-item">
                            <?php if ($item['gambar']): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/products/<?= $item['gambar'] ?>" 
                                     alt="<?= htmlspecialchars($item['nama_produk']) ?>" 
                                     class="product-image">
                            <?php else: ?>
                                <div class="product-placeholder">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['nama_produk']) ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">Harga: </span>
                                        <span class="fw-bold"><?= formatRupiah($item['harga_satuan']) ?></span>
                                        <span class="text-muted mx-2">×</span>
                                        <span class="fw-bold"><?= $item['jumlah'] ?></span>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-primary fw-bold fs-5"><?= formatRupiah($item['subtotal']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="total-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-calculator me-2"></i>
                                Total Pesanan
                            </h5>
                            <h4 class="mb-0 text-primary fw-bold"><?= formatRupiah($order['total_harga']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 mt-4 text-center d-flex justify-content-center align-items-center">
                <a href="index.php" class="btn back-btn">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali ke Daftar Pesanan
                </a>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Payment Status -->
            <div class="card-modern mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Status Pembayaran
                    </h5>
                </div>
                <div class="card-body p-2">
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="status-badge bg-<?= 
                            $order['status_pembayaran'] === 'belum_bayar' ? 'danger' : 
                            ($order['status_pembayaran'] === 'menunggu_verifikasi' ? 'warning' : 
                            ($order['status_pembayaran'] === 'lunas' ? 'success' : 'secondary')) 
                        ?>">
                            <i class="fas fa-<?= 
                                $order['status_pembayaran'] === 'belum_bayar' ? 'exclamation-triangle' : 
                                ($order['status_pembayaran'] === 'menunggu_verifikasi' ? 'clock' : 
                                ($order['status_pembayaran'] === 'lunas' ? 'check-circle' : 'question')) 
                            ?>"></i>
                            <?= ucfirst(str_replace('_', ' ', $order['status_pembayaran'])) ?>
                        </span>
                    </div>
                    
                    <?php if ($order['status_pembayaran'] === 'lunas' && $order['tanggal_bayar']): ?>
                        <div class="mb-3">
                            <strong>Tanggal Bayar:</strong>
                            <p class="text-success fw-bold mb-0">
                                <i class="fas fa-calendar-check me-1"></i>
                                <?= formatDate($order['tanggal_bayar']) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($paymentProof): ?>
                        <div class="mb-3">
                            <strong>Bukti Transfer:</strong>
                            <div class="mt-2 text-center">
                                <a href="<?= BASE_URL ?>assets/uploads/bukti-transfer/<?= $paymentProof['nama_file'] ?>" 
                                   target="_blank" 
                                   class="d-block">
                                    <img src="<?= BASE_URL ?>assets/uploads/bukti-transfer/<?= $paymentProof['nama_file'] ?>" 
                                         class="payment-proof-image" 
                                         alt="Bukti Transfer">
                                </a>
                            </div>
                            <div class="mt-2">
                                <strong>Status Verifikasi:</strong>
                                <span class="status-badge bg-<?= 
                                    $paymentProof['status_verifikasi'] === 'menunggu' ? 'warning' : 
                                    ($paymentProof['status_verifikasi'] === 'diterima' ? 'success' : 'danger') 
                                ?>">
                                    <i class="fas fa-<?= 
                                        $paymentProof['status_verifikasi'] === 'menunggu' ? 'clock' : 
                                        ($paymentProof['status_verifikasi'] === 'diterima' ? 'check-circle' : 'times-circle') 
                                    ?>"></i>
                                    <?= ucfirst($paymentProof['status_verifikasi']) ?>
                                </span>
                            </div>
                            <?php if ($paymentProof['catatan_admin']): ?>
                                <div class="mt-2">
                                    <strong>Catatan Admin:</strong>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($paymentProof['catatan_admin']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['qr_code']): ?>
                        <div class="mb-3">
                            <strong>QR Code:</strong>
                            <div class="qr-code-container">
                                <img src="<?= BASE_URL ?>assets/uploads/qr-codes/<?= $order['qr_code'] ?>" 
                                     class="img-fluid" 
                                     style="max-height: 150px;" 
                                     alt="QR Code">
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['status_pembayaran'] === 'menunggu_verifikasi'): ?>
                        <div class="d-grid gap-2">
                            <button type="button" 
                                    class="btn btn-gradient-success" 
                                    onclick="verifyPayment(<?= $order['id'] ?>)">
                                <i class="fas fa-check me-2"></i>
                                Verifikasi Pembayaran
                            </button>
                            <button type="button" 
                                    class="btn btn-gradient-danger" 
                                    onclick="rejectPayment(<?= $order['id'] ?>)">
                                <i class="fas fa-times me-2"></i>
                                Tolak Pembayaran
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Update Status -->
            <div class="card-modern mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Update Status
                    </h5>
                </div>
                <div class="card-body p-2">
                    <form id="updateStatusForm" class="form-modern">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">
                                <i class="fas fa-cog me-2"></i>
                                Status Pesanan
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?= $order['status_pesanan'] === 'pending' ? 'selected' : '' ?>>
                                    <i class="fas fa-clock"></i> Pending
                                </option>
                                <option value="dikonfirmasi" <?= $order['status_pesanan'] === 'dikonfirmasi' ? 'selected' : '' ?>>
                                    <i class="fas fa-check"></i> Dikonfirmasi
                                </option>
                                <option value="diproses" <?= $order['status_pesanan'] === 'diproses' ? 'selected' : '' ?>>
                                    <i class="fas fa-cogs"></i> Diproses
                                </option>
                                <option value="dikirim" <?= $order['status_pesanan'] === 'dikirim' ? 'selected' : '' ?>>
                                    <i class="fas fa-shipping-fast"></i> Dikirim
                                </option>
                                <option value="selesai" <?= $order['status_pesanan'] === 'selesai' ? 'selected' : '' ?>>
                                    <i class="fas fa-check-circle"></i> Selesai
                                </option>
                                <option value="dibatalkan" <?= $order['status_pesanan'] === 'dibatalkan' ? 'selected' : '' ?>>
                                    <i class="fas fa-times"></i> Dibatalkan
                                </option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-gradient-primary w-100">
                            <i class="fas fa-save me-2"></i>
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include __DIR__ . '/../../includes/footer.php'; 
?>

<script>
$(document).ready(function() {
    // Update status form
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const currentStatus = '<?= $order['status_pesanan'] ?>';
        const newStatus = formData.get('status');
        
        if (currentStatus === newStatus) {
            Swal.fire({
                title: 'Info',
                text: 'Status pesanan tidak berubah!',
                icon: 'info',
                confirmButtonColor: '#719edd'
            });
            return;
        }
        
        Swal.fire({
            title: 'Konfirmasi Update',
            text: `Yakin ingin mengubah status pesanan menjadi "${newStatus}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#719edd',
            cancelButtonColor: '#a0aec0',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    html: 'Sedang mengupdate status pesanan',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form
                $.ajax({
                    url: 'status.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Status pesanan berhasil diperbarui',
                            icon: 'success',
                            confirmButtonColor: '#48bb78'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal mengupdate status pesanan',
                            icon: 'error',
                            confirmButtonColor: '#e53e3e'
                        });
                    }
                });
            }
        });
    });
    
    // Animate cards on load
    $('.card-modern').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        $(this).delay(index * 100).animate({opacity: 1}, 500, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
});

function verifyPayment(orderId) {
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
            window.location.href = `<?= BASE_URL ?>admin/pembayaran/verifikasi.php?id=${orderId}`;
        }
    });
}

function rejectPayment(orderId) {
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
            window.location.href = `<?= BASE_URL ?>admin/pembayaran/tolak.php?id=${orderId}`;
        }
    });
}
</script>
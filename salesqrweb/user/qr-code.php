<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('user/produk.php');
}

$orderId = $_GET['id'];

// Get order details
$query = "SELECT p.*, 
          (SELECT pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pesanan_id = p.id LIMIT 1) as nama_produk
          FROM pesanan p 
          WHERE p.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $orderId);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('user/produk.php');
}

$pageTitle = 'QR Code Pesanan #' . $order['nomor_pesanan'];
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
/* QR Code Page Styles - Matching konfirmasi-pembayaran.php design */
.qr-container {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    min-height: calc(100vh - 100px);
    padding: 20px;
}

.qr-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    background: white;
    max-width: none;
}

.qr-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(45, 55, 72, 0.2);
}

.card-header-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    padding: 25px 30px;
    border: none;
    position: relative;
    overflow: hidden;
}

.card-header-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.8s;
}

.qr-card:hover .card-header-primary::before {
    left: 100%;
}

.card-header-primary h4 {
    color: white;
    margin: 0;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.qr-icon {
    font-size: 1.5rem;
    animation: qrPulse 2s infinite;
}

@keyframes qrPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* QR Code Display Section */
.qr-display-section {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.05) 0%, rgba(113, 158, 221, 0.02) 100%);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    border: 1px solid rgba(113, 158, 221, 0.1);
    text-align: center;
    position: relative;
}

.qr-display-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border-radius: 3px 3px 0 0;
}

.qr-code-container {
    display: inline-block;
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(113, 158, 221, 0.2);
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.qr-code-container:hover {
    transform: scale(1.02);
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.3);
}

.qr-code-img {
    max-width: 200px;
    height: auto;
    border-radius: 8px;
    display: block;
    margin: 0 auto;
}

.qr-info {
    margin: 15px 0;
    font-size: 1.1rem;
}

.qr-info strong {
    color: #2d3748;
    font-weight: 600;
}

/* Alert Styling */
.alert {
    border: none;
    border-radius: 15px;
    padding: 20px 25px;
    font-weight: 500;
    border-left: 4px solid;
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
}

.alert::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.3), transparent);
    animation: alertShimmer 3s infinite;
}

@keyframes alertShimmer {
    0%, 100% { opacity: 0; }
    50% { opacity: 1; }
}

.alert-success {
    background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
    border-left-color: #38a169;
    color: #22543d;
}

.alert-warning {
    background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    border-left-color: #d69e2e;
    color: #744210;
}

/* Order Details Section */
.order-details-section {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.05) 0%, rgba(113, 158, 221, 0.02) 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    border: 1px solid rgba(113, 158, 221, 0.1);
    position: relative;
}

.order-details-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border-radius: 3px 3px 0 0;
}

/* Table Styling */
.table-custom {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: none;
}

.table-custom th {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    color: #2d3748;
    font-weight: 600;
    border: none;
    padding: 15px 20px;
    font-size: 0.9rem;
}

.table-custom td {
    border: none;
    padding: 15px 20px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.table-custom tr:last-child td {
    border-bottom: none;
}

.table-custom tr:hover {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.02) 0%, rgba(113, 158, 221, 0.01) 100%);
}

/* Status Badge */
.badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bg-success {
    background: linear-gradient(135deg, #48bb78 0%, #68d391 100%) !important;
    color: white;
    border: 1px solid rgba(72, 187, 120, 0.2);
}

.bg-warning {
    background: linear-gradient(135deg, #ed8936 0%, #f6ad55 100%) !important;
    color: white;
    border: 1px solid rgba(237, 137, 54, 0.2);
}

.bg-danger {
    background: linear-gradient(135deg, #e53e3e 0%, #fc8181 100%) !important;
    color: white;
    border: 1px solid rgba(229, 62, 62, 0.2);
}

/* Button Styling */
.btn {
    border-radius: 12px;
    font-weight: 600;
    padding: 12px 25px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    font-size: 0.95rem;
    position: relative;
    overflow: hidden;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 8px;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(113, 158, 221, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.4);
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #48bb78 0%, #68d391 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
    background: linear-gradient(135deg, #68d391 0%, #48bb78 100%);
    color: white;
}

.btn-outline-secondary {
    border: 2px solid #cbd5e0;
    color: #4a5568;
    background: transparent;
}

.btn-outline-secondary:hover {
    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 85, 104, 0.3);
}

/* Action Buttons Container */
.action-buttons {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    border: 1px solid rgba(113, 158, 221, 0.1);
}

.action-buttons .btn {
    min-width: 200px;
}

/* Total Amount Highlight */
.total-amount {
    font-size: 1.2rem;
    font-weight: bold;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.qr-card {
    animation: fadeInUp 0.6s ease;
}
</style>

<div class="qr-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="qr-card">
                    <div class="card-header-primary">
                        <h4 class="mb-0">
                            <i class="fas fa-qrcode me-3 qr-icon"></i>
                            QR Code Pesanan
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($order['qr_code']): ?>
                            <div class="qr-display-section">
                                <div class="qr-code-container">
                                    <img src="<?= BASE_URL ?>assets/uploads/qr-codes/<?= $order['qr_code'] ?>" alt="QR Code" class="qr-code-img">
                                </div>
                                <div class="qr-info">
                                    <strong>No. Pesanan:</strong> <?= $order['nomor_pesanan'] ?>
                                </div>
                                <div class="qr-info mb-3">
                                    <strong>Tanggal:</strong> <?= formatDate($order['tanggal_pesanan']) ?>
                                </div>
                                <a href="<?= BASE_URL ?>assets/uploads/qr-codes/<?= $order['qr_code'] ?>" download="QRCode_<?= $order['nomor_pesanan'] ?>.png" class="btn btn-primary">
                                    <i class="fas fa-download me-2"></i>
                                    Download QR Code
                                </a>
                            </div>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                QR Code ini merupakan bukti valid untuk pesanan Anda. Simpan atau download untuk keperluan verifikasi.
                            </div>
                        <?php else: ?>
                            <div class="qr-display-section">
                                <div style="padding: 40px; color: #718096;">
                                    <i class="fas fa-clock" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                                    <h5>QR Code Belum Tersedia</h5>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                QR Code belum tersedia. Silakan lakukan pembayaran terlebih dahulu dan tunggu verifikasi dari admin.
                            </div>
                        <?php endif; ?>
                        
                        <div class="order-details-section">
                            <div class="table-responsive">
                                <table class="table table-custom">
                                    <tbody>
                                        <tr>
                                            <th width="30%">No. Pesanan</th>
                                            <td><strong><?= $order['nomor_pesanan'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Produk</th>
                                            <td><?= $order['nama_produk'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Pembayaran</th>
                                            <td class="total-amount"><?= formatRupiah($order['total_harga']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status Pembayaran</th>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $order['status_pembayaran'] === 'belum_bayar' ? 'danger' : 
                                                    ($order['status_pembayaran'] === 'menunggu_verifikasi' ? 'warning' : 'success') 
                                                ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $order['status_pembayaran'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="konfirmasi-pembayaran.php?id=<?= $order['id'] ?>" class="btn btn-success">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Instruksi Pembayaran
                            </a>
                            <a href="produk.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali ke Daftar Produk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Add smooth scrolling
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 20
            }, 300);
        }
    });
    
    // Add loading state to buttons
    $('.btn').on('click', function(e) {
        if ($(this).attr('href') && ($(this).attr('href').startsWith('http') || $(this).attr('download'))) {
            return; // Don't add loading state for downloads or external links
        }
        
        const btn = $(this);
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
        btn.prop('disabled', true);
        
        setTimeout(function() {
            btn.html(originalText);
            btn.prop('disabled', false);
        }, 2000);
    });
    
    // Initialize tooltips if Bootstrap tooltips are available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
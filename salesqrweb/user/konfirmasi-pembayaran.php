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

// Get shop settings
$query = "SELECT * FROM pengaturan LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle upload bukti transfer
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_bukti'])) {
    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload($_FILES['bukti_transfer'], '../assets/uploads/bukti-transfer/');
        
        if ($uploadResult['success']) {
            // Save payment proof to database
            $query = "INSERT INTO bukti_transfer (pesanan_id, nama_file, path_file, tipe_file, ukuran_file) 
                      VALUES (:pesanan_id, :nama_file, :path_file, :tipe_file, :ukuran_file)";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':pesanan_id', $orderId);
            $stmt->bindParam(':nama_file', $uploadResult['filename']);
            $stmt->bindParam(':path_file', $uploadResult['path']);
            $stmt->bindParam(':tipe_file', $_FILES['bukti_transfer']['type']);
            $stmt->bindParam(':ukuran_file', $_FILES['bukti_transfer']['size']);
            
            if ($stmt->execute()) {
                // Update order payment status
                $query = "UPDATE pesanan SET status_pembayaran = 'menunggu_verifikasi' WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $orderId);
                $stmt->execute();
                
                $success = 'Bukti transfer berhasil diupload dan menunggu verifikasi admin';
                
                // Refresh order data to show updated status
                $query = "SELECT p.*, 
                          (SELECT pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pesanan_id = p.id LIMIT 1) as nama_produk
                          FROM pesanan p 
                          WHERE p.id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $orderId);
                $stmt->execute();
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Gagal menyimpan data bukti transfer';
            }
        } else {
            $error = $uploadResult['error'];
        }
    } else {
        $error = 'Silakan pilih file bukti transfer';
    }
}

// Check if bukti transfer already uploaded
$query = "SELECT * FROM bukti_transfer WHERE pesanan_id = :pesanan_id ORDER BY tanggal_upload DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':pesanan_id', $orderId);
$stmt->execute();
$bukti_transfer = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Konfirmasi Pembayaran';
$prefixlogo = '../';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
/* Confirmation Page Styles - Matching pesan.php design */
.confirmation-container {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    min-height: calc(100vh - 100px);
    padding: 20px;
}

.confirmation-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    background: white;
    max-width: none;
}

.confirmation-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(45, 55, 72, 0.2);
}

.card-header-success {
    background: linear-gradient(135deg, #48bb78 0%, #68d391 100%);
    padding: 25px 30px;
    border: none;
    position: relative;
    overflow: hidden;
}

.card-header-success::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.8s;
}

.confirmation-card:hover .card-header-success::before {
    left: 100%;
}

.card-header-success h4 {
    color: white;
    margin: 0;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.success-icon {
    font-size: 2rem;
    animation: successPulse 2s infinite;
}

@keyframes successPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
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

.alert-info {
    background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
    border-left-color: #3182ce;
    color: #2c5282;
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

.section-title {
    color: #2d3748;
    font-weight: 700;
    margin-bottom: 20px;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
}

.section-title i {
    margin-right: 10px;
    color: #719edd;
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
.status-badge {
    background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    color: #c2410c;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid rgba(194, 65, 12, 0.2);
    animation: statusPulse 3s infinite;
}

@keyframes statusPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Payment Instructions */
.payment-instructions {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    border: 1px solid rgba(3, 105, 161, 0.1);
    position: relative;
}

.payment-instructions::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
    border-radius: 3px 3px 0 0;
}

.payment-instructions ol {
    margin: 0;
    padding-left: 0;
    counter-reset: step-counter;
}

.payment-instructions li {
    counter-increment: step-counter;
    margin-bottom: 20px;
    padding-left: 0;
    position: relative;
    list-style: none;
}

.payment-instructions li::before {
    content: counter(step-counter);
    position: absolute;
    left: -40px;
    top: 0;
    background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
    box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
}

.payment-instructions li:last-child {
    margin-bottom: 0;
}

/* Bank Account Card */
.bank-account-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid rgba(113, 158, 221, 0.2);
    border-radius: 12px;
    padding: 20px;
    margin: 15px 0;
    position: relative;
    transition: all 0.3s ease;
}

.bank-account-card:hover {
    border-color: #719edd;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.15);
}

.bank-account-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border-radius: 4px 0 0 4px;
}

.bank-info {
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.bank-name {
    font-weight: 700;
    color: #2d3748;
    font-size: 1.2rem;
    margin-bottom: 8px;
}

.account-number {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #4a5568;
    font-size: 1.1rem;
    letter-spacing: 1px;
}

.account-name {
    color: #4a5568;
    font-weight: 500;
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

.btn-secondary {
    background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(113, 128, 150, 0.3);
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(113, 128, 150, 0.4);
    background: linear-gradient(135deg, #4a5568 0%, #718096 100%);
    color: white;
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
    margin: 8px;
    min-width: 200px;
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

.confirmation-card {
    animation: fadeInUp 0.6s ease;
}

/* Total Amount Highlight */
.total-amount {
    font-size: 1.3rem;
    font-weight: bold;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* WhatsApp Button Special Styling */
.btn-whatsapp {
    background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
}

.btn-whatsapp:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
    background: linear-gradient(135deg, #128c7e 0%, #25d366 100%);
    color: white;
}

.btn-whatsapp i {
    animation: whatsappBounce 2s infinite;
}

@keyframes whatsappBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Modal Styles */
.modal-content {
    border: none;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(45, 55, 72, 0.2);
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: white;
    border: none;
    padding: 25px 30px;
    position: relative;
}

.modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.8s;
}

.modal:hover .modal-header::before {
    left: 100%;
}

.modal-title {
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-close {
    filter: invert(1);
    opacity: 0.8;
}

.btn-close:hover {
    opacity: 1;
}

/* Upload Area Styles */
.upload-area {
    border: 3px dashed #cbd5e0;
    border-radius: 15px;
    padding: 40px 20px;
    text-align: center;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    position: relative;
    overflow: hidden;
}

.upload-area:hover {
    border-color: #719edd;
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.05) 0%, rgba(113, 158, 221, 0.02) 100%);
    transform: translateY(-2px);
}

.upload-area.dragover {
    border-color: #48bb78;
    background: linear-gradient(135deg, rgba(72, 187, 120, 0.05) 0%, rgba(72, 187, 120, 0.02) 100%);
}

.upload-icon {
    font-size: 3rem;
    color: #a0aec0;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.upload-area:hover .upload-icon {
    color: #719edd;
    transform: scale(1.1);
}

.upload-text {
    color: #4a5568;
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.upload-subtext {
    color: #a0aec0;
    font-size: 0.9rem;
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
    cursor: pointer;
    z-index: 10;
}

/* Image Preview Styles */
.image-preview {
    max-width: 100%;
    max-height: 300px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin: 20px 0;
    display: none;
    border: 3px solid #e2e8f0;
    transition: all 0.3s ease;
}

.image-preview:hover {
    transform: scale(1.02);
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
}

.preview-container {
    text-align: center;
    position: relative;
}

.remove-image {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #e53e3e;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(229, 62, 62, 0.3);
    transition: all 0.3s ease;
}

.remove-image:hover {
    background: #c53030;
    transform: scale(1.1);
}

/* Progress Bar */
.upload-progress {
    display: none;
    margin-top: 20px;
}

.progress {
    height: 8px;
    border-radius: 10px;
    background: #e2e8f0;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(135deg, #48bb78 0%, #68d391 100%);
    transition: width 0.3s ease;
    border-radius: 10px;
}

/* Upload Status */
.upload-status {
    margin-top: 15px;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 500;
    display: none;
}

.upload-status.success {
    background: linear-gradient(135deg, rgba(72, 187, 120, 0.1) 0%, rgba(104, 211, 145, 0.1) 100%);
    color: #2f855a;
    border: 1px solid rgba(72, 187, 120, 0.2);
}

.upload-status.error {
    background: linear-gradient(135deg, rgba(229, 62, 62, 0.1) 0%, rgba(245, 101, 101, 0.1) 100%);
    color: #c53030;
    border: 1px solid rgba(229, 62, 62, 0.2);
}

/* File Info */
.file-info {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 12px;
    padding: 15px 20px;
    margin-top: 15px;
    border: 1px solid rgba(3, 105, 161, 0.1);
    display: none;
}

.file-info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.file-info-item:last-child {
    margin-bottom: 0;
}

.file-info-label {
    font-weight: 600;
    color: #2d3748;
}

.file-info-value {
    color: #4a5568;
}

/* Existing Upload Display */
.existing-upload {
    background: linear-gradient(135deg, #f0fff4 0%, #e6fffa 100%);
    border: 2px solid rgba(72, 187, 120, 0.2);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}

.existing-upload h6 {
    color: #2f855a;
    margin-bottom: 15px;
    font-weight: 700;
}

.upload-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.upload-details {
    flex: 1;
}

.upload-date {
    color: #4a5568;
    font-size: 0.9rem;
}

.verification-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-menunggu {
    background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    color: #c2410c;
    border: 1px solid rgba(194, 65, 12, 0.2);
}

.status-diterima {
    background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
    color: #166534;
    border: 1px solid rgba(22, 101, 52, 0.2);
}

.status-ditolak {
    background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
    color: #991b1b;
    border: 1px solid rgba(153, 27, 27, 0.2);
}

.uploaded-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

.uploaded-image:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Animation */
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal.show .modal-dialog {
    animation: modalSlideIn 0.3s ease;
}
</style>

<div class="confirmation-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="confirmation-card">
                    <div class="card-header-success">
                        <h4 class="mb-0">
                            <i class="fas fa-check-circle me-3 success-icon"></i>
                            Pesanan Berhasil Dibuat
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Silakan lakukan pembayaran sesuai dengan instruksi di bawah ini.
                        </div>
                        
                        <div class="order-details-section">
                            <h5 class="section-title">
                                <i class="fas fa-receipt"></i>&nbsp;
                                Detail Pesanan
                            </h5>
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
                                            <th>Status</th>
                                            <td>
                                                <span class="status-badge">
                                                    Menunggu Pembayaran
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="payment-instructions">
                            <h5 class="section-title">
                                <i class="fas fa-credit-card"></i>&nbsp;
                                Instruksi Pembayaran
                            </h5>
                            <div style="margin-left: 40px;">
                                <ol>
                                    <li>Transfer tepat sejumlah <strong class="total-amount"><?= formatRupiah($order['total_harga']) ?></strong> ke rekening:
                                        <div class="bank-account-card">
                                            <div class="bank-name"><?= $settings['nama_bank'] ?></div>
                                            <div class="bank-info">No. Rekening: <span class="account-number"><?= $settings['no_rekening'] ?></span></div>
                                            <div class="bank-info">Atas Nama: <span class="account-name"><?= $settings['atas_nama'] ?></span></div>
                                        </div>
                                    </li>
                                    <li>Setelah transfer, konfirmasi pembayaran dengan mengirim bukti transfer melalui WhatsApp:
                                        <div class="mt-3">
                                            <a href="https://wa.me/<?= $settings['whatsapp_number'] ?>?text=Halo,%20saya%20telah%20melakukan%20pembayaran%20untuk%20pesanan%20<?= $order['nomor_pesanan'] ?>%20sebesar%20<?= formatRupiah($order['total_harga']) ?>" 
                                               class="btn btn-whatsapp" target="_blank">
                                                <i class="fab fa-whatsapp me-2"></i>
                                                Kirim Bukti Transfer via WhatsApp
                                            </a>
                                        </div>
                                    </li>
                                    <li>Lalu upload bukti pembayaran disini setelah dikonfirmasi oleh admin:
                                        <div class="mt-3">
                                            <!-- Replace the existing "Upload Bukti Transfer" button with this -->
                                            <a href="#" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                                <i class="fas fa-upload me-2"></i>
                                                Upload Bukti Transfer
                                            </a>
                                        </div>
                                    </li>
                                    <li>Setelah pembayaran diverifikasi oleh admin, Anda akan menerima QR Code sebagai bukti pesanan.</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="qr-code.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-qrcode me-2"></i>
                                Lihat QR Code Pesanan
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

<!-- Add this modal before the closing body tag -->
<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="fas fa-upload me-2"></i>
                    Upload Bukti Transfer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $success ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <!-- Order Summary -->
                <div class="order-details-section mb-4">
                    <h6 class="section-title">
                        <i class="fas fa-receipt"></i>&nbsp;
                        Ringkasan Pesanan
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>No. Pesanan:</strong></td>
                                        <td><?= $order['nomor_pesanan'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total:</strong></td>
                                        <td class="total-amount"><?= formatRupiah($order['total_harga']) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $order['status_pembayaran'] === 'belum_bayar' ? 'danger' : 
                                                ($order['status_pembayaran'] === 'menunggu_verifikasi' ? 'warning' : 'success') 
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $order['status_pembayaran'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Existing Upload Display -->
                <?php if ($bukti_transfer): ?>
                <div class="existing-upload">
                    <h6><i class="fas fa-check-circle me-2"></i>Bukti Transfer yang Telah Diupload</h6>
                    <div class="upload-info">
                        <div class="upload-details">
                            <div><strong><?= $bukti_transfer['nama_file'] ?></strong></div>
                            <div class="upload-date">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d M Y, H:i', strtotime($bukti_transfer['tanggal_upload'])) ?>
                            </div>
                        </div>
                        <div>
                            <span class="verification-status status-<?= $bukti_transfer['status_verifikasi'] ?>">
                                <?= ucfirst($bukti_transfer['status_verifikasi']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="text-center">
                        <img src="<?= $bukti_transfer['path_file'] ?>" 
                             alt="Bukti Transfer" 
                             class="uploaded-image">
                    </div>
                    <?php if ($bukti_transfer['catatan_admin']): ?>
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong>Catatan Admin:</strong><br>
                        <?= $bukti_transfer['catatan_admin'] ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Upload Form -->
                <?php if (!$bukti_transfer || $bukti_transfer['status_verifikasi'] === 'ditolak'): ?>
                <form id="uploadForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="upload_bukti" value="1">
                    
                    <div class="upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            Seret file ke sini atau klik untuk memilih
                        </div>
                        <div class="upload-subtext">
                            Maksimal 5MB - Format: JPG, PNG
                        </div>
                        <input type="file" name="bukti_transfer" id="bukti_transfer" class="file-input" accept="image/*">
                    </div>
                    
                    <!-- File Info -->
                    <div class="file-info">
                        <div class="file-info-item">
                            <span class="file-info-label">Nama File:</span>
                            <span class="file-info-value file-name">-</span>
                        </div>
                        <div class="file-info-item">
                            <span class="file-info-label">Ukuran:</span>
                            <span class="file-info-value file-size">-</span>
                        </div>
                        <div class="file-info-item">
                            <span class="file-info-label">Tipe:</span>
                            <span class="file-info-value file-type">-</span>
                        </div>
                    </div>
                    
                    <!-- Image Preview -->
                    <div class="preview-container">
                        <img id="imagePreview" class="image-preview" alt="Preview">
                        <button type="button" class="remove-image">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <!-- Upload Progress -->
                    <div class="upload-progress">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <!-- Upload Status -->
                    <div class="upload-status"></div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" id="submitUpload" class="btn btn-success btn-lg" disabled>
                            <i class="fas fa-upload me-2"></i>
                            Upload Bukti Transfer
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Image View Modal -->
<div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Bukti Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="Bukti Transfer" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // // Add loading state to buttons
    // $('.btn').on('click', function(e) {
    //     if ($(this).attr('href') && $(this).attr('href').startsWith('http')) {
    //         return; // Don't add loading state for external links
    //     }
        
    //     const btn = $(this);
    //     const originalText = btn.html();
        
    //     btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
    //     btn.prop('disabled', true);
        
    //     setTimeout(function() {
    //         btn.html(originalText);
    //         btn.prop('disabled', false);
    //     }, 2000);
    // });
    
    // Add copy functionality for account number
    $('.account-number').on('click', function() {
        const text = $(this).text();
        navigator.clipboard.writeText(text).then(function() {
            // Show temporary success message
            const original = $('.account-number');
            const originalText = original.text();
            original.text('Copied!').css('color', '#48bb78');
            setTimeout(function() {
                original.text(originalText).css('color', '');
            }, 2000);
        });
    }).css('cursor', 'pointer').attr('title', 'Click to copy');
    
    // Add hover effect for bank account card
    $('.bank-account-card').hover(
        function() {
            $(this).find('.account-number').css('background', 'linear-gradient(135deg, #719edd 0%, #90b4e6 100%)')
                   .css('-webkit-background-clip', 'text')
                   .css('-webkit-text-fill-color', 'transparent');
        },
        function() {
            $(this).find('.account-number').css('background', '')
                   .css('-webkit-background-clip', '')
                   .css('-webkit-text-fill-color', '');
        }
    );
    
    // Initialize tooltips if Bootstrap tooltips are available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Add animation to status badge
    setInterval(function() {
        $('.status-badge').addClass('animate__animated animate__pulse');
        setTimeout(function() {
            $('.status-badge').removeClass('animate__animated animate__pulse');
        }, 1000);
    }, 5000);
    
    // Add success sound effect (optional)
    function playSuccessSound() {
        // Create audio context for success sound
        if (typeof AudioContext !== 'undefined' || typeof webkitAudioContext !== 'undefined') {
            const audioContext = new (AudioContext || webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
            oscillator.frequency.setValueAtTime(1200, audioContext.currentTime + 0.2);
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        }
    }
    
    // Play success sound on page load
    setTimeout(playSuccessSound, 500);

    // File upload handling
    let selectedFile = null;
    
    // Drag and drop functionality
    $('.upload-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    $('.upload-area').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    $('.upload-area').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    // File input change
    $('#bukti_transfer').on('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    // Handle file selection
    function handleFileSelect(file) {
        // Validate file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!allowedTypes.includes(file.type)) {
            showUploadStatus('error', 'Tipe file tidak diizinkan. Gunakan JPG atau PNG.');
            return;
        }
        
        if (file.size > maxSize) {
            showUploadStatus('error', 'Ukuran file terlalu besar. Maksimal 5MB.');
            return;
        }
        
        selectedFile = file;
        
        // Show file info
        showFileInfo(file);
        
        // Show image preview
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').attr('src', e.target.result).show();
            $('.preview-container').show();
        };
        reader.readAsDataURL(file);
        
        // Hide upload status
        $('.upload-status').hide();
        
        // Enable submit button
        $('#submitUpload').prop('disabled', false);
    }
    
    // Show file information
    function showFileInfo(file) {
        $('.file-info').show();
        $('.file-name').text(file.name);
        $('.file-size').text(formatFileSize(file.size));
        $('.file-type').text(file.type);
    }
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Remove image preview
    $('.remove-image').on('click', function() {
        $('#imagePreview').hide();
        $('.preview-container').hide();
        $('.file-info').hide();
        $('.upload-status').hide();
        $('#bukti_transfer').val('');
        selectedFile = null;
        $('#submitUpload').prop('disabled', true);
    });
    
    // Show upload status
    function showUploadStatus(type, message) {
        $('.upload-status').removeClass('success error').addClass(type).text(message).show();
    }
    
    // Form submission with AJAX
    $('#uploadForm').on('submit', function(e) {
        console.log('Form submitted');
        e.preventDefault();
        
        if (!selectedFile) {
            showUploadStatus('error', 'Silakan pilih file terlebih dahulu');
            return;
        }
        
        const formData = new FormData();
        formData.append('bukti_transfer', selectedFile);
        formData.append('upload_bukti', '1');
        
        // Show progress
        $('.upload-progress').show();
        $('#submitUpload').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Uploading...');
        
        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(function() {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            $('.progress-bar').css('width', progress + '%');
        }, 200);
        
        $.ajax({
            url: '',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log('Upload response:', response);
                clearInterval(progressInterval);
                $('.progress-bar').css('width', '100%');
                
                setTimeout(function() {
                    $('.upload-progress').hide();
                    showUploadStatus('success', 'Bukti transfer berhasil diupload!');
                    $('#submitUpload').html('<i class="fas fa-check me-2"></i>Berhasil Diupload');
                    
                    // Reload page after 2 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }, 1000);
            },
            error: function() {
                clearInterval(progressInterval);
                $('.upload-progress').hide();
                showUploadStatus('error', 'Terjadi kesalahan saat upload');
                $('#submitUpload').prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Upload Bukti Transfer');
            }
        });
    });
    
    // Reset modal on close
    $('#uploadModal').on('hidden.bs.modal', function() {
        $('#uploadForm')[0].reset();
        $('#imagePreview').hide();
        $('.preview-container').hide();
        $('.file-info').hide();
        $('.upload-status').hide();
        $('.upload-progress').hide();
        selectedFile = null;
        $('#submitUpload').prop('disabled', true).html('<i class="fas fa-upload me-2"></i>Upload Bukti Transfer');
    });
    
    // Image preview modal
    $(document).on('click', '.uploaded-image, #imagePreview', function() {
        const src = $(this).attr('src');
        $('#imageViewModal img').attr('src', src);
        $('#imageViewModal').modal('show');
    });
});
</script>
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('user/produk.php');
}

$productId = $_GET['id'];

// Get product details
$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.kategori_id = k.id 
          WHERE p.id = :id AND p.status = 'aktif'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $productId);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    redirect('user/produk.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pembeli = sanitizeInput($_POST['nama_pembeli']);
    $no_hp = sanitizeInput($_POST['no_hp']);
    $alamat = sanitizeInput($_POST['alamat']);
    $jumlah = sanitizeInput($_POST['jumlah']);
    
    if (empty($nama_pembeli) || empty($no_hp) || empty($alamat) || empty($jumlah)) {
        $error = 'Semua field harus diisi';
    } elseif ($jumlah <= 0) {
        $error = 'Jumlah harus lebih dari 0';
    } elseif ($jumlah > $product['stok']) {
        $error = 'Jumlah melebihi stok yang tersedia';
    } else {
        // Calculate total price
        $total_harga = $product['harga'] * $jumlah;
        
        // Create order
        $nomor_pesanan = generateOrderNumber();
        
        $query = "INSERT INTO pesanan (nomor_pesanan, nama_pembeli, no_hp, alamat, total_harga) 
                  VALUES (:nomor_pesanan, :nama_pembeli, :no_hp, :alamat, :total_harga)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':nomor_pesanan', $nomor_pesanan);
        $stmt->bindParam(':nama_pembeli', $nama_pembeli);
        $stmt->bindParam(':no_hp', $no_hp);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':total_harga', $total_harga);
        
        if ($stmt->execute()) {
            $orderId = $db->lastInsertId();
            
            // Add order item
            $query = "INSERT INTO detail_pesanan (pesanan_id, produk_id, jumlah, harga_satuan, subtotal) 
                      VALUES (:pesanan_id, :produk_id, :jumlah, :harga_satuan, :subtotal)";
            $stmt = $db->prepare($query);
            
            $subtotal = $product['harga'] * $jumlah;
            
            $stmt->bindParam(':pesanan_id', $orderId);
            $stmt->bindParam(':produk_id', $productId);
            $stmt->bindParam(':jumlah', $jumlah);
            $stmt->bindParam(':harga_satuan', $product['harga']);
            $stmt->bindParam(':subtotal', $subtotal);
            $stmt->execute();
            
            // Update product stock
            $query = "UPDATE produk SET stok = stok - :jumlah WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':jumlah', $jumlah);
            $stmt->bindParam(':id', $productId);
            $stmt->execute();
            
            // Log stock change
            $query = "INSERT INTO log_stok (produk_id, jenis_transaksi, jumlah, stok_sebelum, stok_sesudah, keterangan, admin_id) 
                      SELECT :produk_id, 'keluar', :jumlah, stok, stok - :jumlah, 'Pesanan #$nomor_pesanan', NULL 
                      FROM produk WHERE id = :produk_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':produk_id', $productId);
            $stmt->bindParam(':jumlah', $jumlah);
            $stmt->execute();
            
            // Generate QR Code
            $qrData = json_encode([
                'order_id' => $orderId,
                'order_number' => $nomor_pesanan,
                'total' => $total_harga,
                'date' => date('Y-m-d H:i:s')
            ]);
            
            $qrInsideImageData = BASE_URL . 'user/konfirmasi-pembayaran.php?id=' . $orderId;
            $qrFilename = 'order_' . $orderId . '_' . time() . '.png';
            $qrPath = generateQRCode($qrInsideImageData, $qrFilename);
            
            // Save QR Code to database
            $query = "INSERT INTO qr_codes (pesanan_id, qr_code_data, qr_code_image) 
                      VALUES (:pesanan_id, :qr_code_data, :qr_code_image)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':pesanan_id', $orderId);
            $stmt->bindParam(':qr_code_data', $qrData);
            $stmt->bindParam(':qr_code_image', $qrFilename);
            $stmt->execute();
            
            // Update order with QR Code
            $query = "UPDATE pesanan SET qr_code = :qr_code WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':qr_code', $qrFilename);
            $stmt->bindParam(':id', $orderId);
            $stmt->execute();
            
            redirect('user/konfirmasi-pembayaran.php?id=' . $orderId);
        } else {
            $error = 'Gagal membuat pesanan';
        }
    }
}

$pageTitle = 'Pesan Produk - ' . $product['nama_produk'];
$prefixlogo = '../';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
/* Order Form Styles - Matching produk.php design */
.order-container {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    min-height: calc(100vh - 100px);
    padding: 20px; /* Reduced padding for more space */
}

.order-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    background: white;
    max-width: none; /* Remove max-width restrictions */
}

.order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(45, 55, 72, 0.2);
}

.card-header-custom {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    padding: 20px 30px; /* Reduced top/bottom padding */
    border: none;
    position: relative;
    overflow: hidden;
}

.card-header-custom::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.8s;
}

.order-card:hover .card-header-custom::before {
    left: 100%;
}

.card-header-custom h6 {
    color: white;
    margin: 0;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-preview {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.05) 0%, rgba(113, 158, 221, 0.02) 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    border: 1px solid rgba(113, 158, 221, 0.1);
    position: relative;
}

.product-preview::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border-radius: 3px 3px 0 0;
}

.product-image-preview {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: cover;
    border: 3px solid rgba(113, 158, 221, 0.2);
    transition: all 0.3s ease;
}

.product-image-preview:hover {
    transform: scale(1.05);
    border-color: #719edd;
}

.product-placeholder-small {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #cbd5e0;
}

.price-display {
    font-size: 1.4rem;
    font-weight: bold;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 2px 4px rgba(113, 158, 221, 0.2);
}

.stock-display {
    background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
    color: #4a5568;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    border: 1px solid rgba(113, 158, 221, 0.1);
}

.category-badge {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Form Styling */
.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-control, .form-select {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #fafafa;
}

.form-control:focus, .form-select:focus {
    border-color: #719edd;
    box-shadow: 0 0 0 0.25rem rgba(113, 158, 221, 0.15);
    background: white;
    transform: translateY(-1px);
}

.form-control:hover, .form-select:hover {
    border-color: #90b4e6;
    background: white;
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
    box-shadow: 0 4px 15px rgba(113, 158, 221, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.4);
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%);
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

/* Alert Styling */
.alert {
    border: none;
    border-radius: 12px;
    padding: 15px 20px;
    font-weight: 500;
    border-left: 4px solid;
}

.alert-danger {
    background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
    border-left-color: #e53e3e;
    color: #c53030;
}

.alert-success {
    background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
    border-left-color: #38a169;
    color: #2f855a;
}

/* Total Price Display */
.total-price-card {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border: 2px solid rgba(113, 158, 221, 0.2);
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
}

.total-price-card:hover {
    border-color: #719edd;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(113, 158, 221, 0.1);
}

.total-price {
    font-size: 1.5rem;
    font-weight: bold;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Quantity Input Special Styling */
.quantity-input-group {
    position: relative;
}

.quantity-input-group .form-control {
    text-align: center;
    font-weight: 600;
    font-size: 1.1rem;
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

.order-card {
    animation: fadeInUp 0.6s ease;
}
</style>

<div class="order-container">
    <div class="container-fluid"> <!-- Changed from container to container-fluid for full width -->
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11"> <!-- Changed from col-lg-8 col-md-10 to col-12 col-xl-10 for wider layout -->
                <div class="order-card">
                    <div class="card-header-custom">
                        <h6 class="mb-0">
                            <i class="fas fa-shopping-cart me-3"></i>
                            Form Pemesanan Produk
                        </h6>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success mb-4">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= $success ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Product Preview -->
                        <div class="product-preview">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <?php if ($product['gambar']): ?>
                                        <img src="<?= BASE_URL ?>assets/uploads/products/<?= $product['gambar'] ?>" 
                                             class="product-image-preview" 
                                             alt="<?= htmlspecialchars($product['nama_produk']) ?>">
                                    <?php else: ?>
                                        <div class="product-placeholder-small">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-1 fw-bold text-dark">
                                            <?= htmlspecialchars($product['nama_produk']) ?>
                                        </h5>
                                        <?php if ($product['nama_kategori']): ?>
                                            <span class="category-badge">
                                                <?= htmlspecialchars($product['nama_kategori']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="price-display">
                                            <?= formatRupiah($product['harga']) ?>
                                        </div>
                                        <div class="stock-display">
                                            <i class="fas fa-cubes me-1"></i>
                                            Stok: <?= $product['stok'] ?> pcs
                                        </div>
                                    </div>
                                    
                                    <?php if ($product['deskripsi']): ?>
                                        <p class="text-muted mt-2 mb-0 small">
                                            <?= htmlspecialchars($product['deskripsi']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Form -->
                        <form method="POST" id="orderForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama_pembeli" class="form-label">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        Nama Lengkap
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nama_pembeli" 
                                           name="nama_pembeli" 
                                           placeholder="Masukkan nama lengkap Anda"
                                           value="<?= isset($_POST['nama_pembeli']) ? htmlspecialchars($_POST['nama_pembeli']) : '' ?>"
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="no_hp" class="form-label">
                                        <i class="fas fa-phone me-2 text-primary"></i>
                                        No. HP/WhatsApp
                                    </label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="no_hp" 
                                           name="no_hp" 
                                           placeholder="08xxxxxxxxxx"
                                           value="<?= isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : '' ?>"
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                    Alamat Lengkap
                                </label>
                                <textarea class="form-control" 
                                          id="alamat" 
                                          name="alamat" 
                                          rows="3" 
                                          placeholder="Masukkan alamat lengkap untuk pengiriman"
                                          required><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="jumlah" class="form-label">
                                        <i class="fas fa-shopping-basket me-2 text-primary"></i>
                                        Jumlah Pesanan
                                    </label>
                                    <div class="quantity-input-group">
                                        <input type="number" 
                                               class="form-control" 
                                               id="jumlah" 
                                               name="jumlah" 
                                               min="1" 
                                               max="<?= $product['stok'] ?>" 
                                               value="<?= isset($_POST['jumlah']) ? $_POST['jumlah'] : '1' ?>" 
                                               required>
                                    </div>
                                    <small class="text-muted">
                                        Maksimal <?= $product['stok'] ?> pcs
                                    </small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calculator me-2 text-primary"></i>
                                        Total Harga
                                    </label>
                                    <div class="total-price-card">
                                        <div class="total-price" id="total-harga">
                                            <?= formatRupiah($product['harga']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6 mb-2">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Kirim Pesanan
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="d-grid">
                                        <a href="produk.php" class="btn btn-outline-secondary btn-lg">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Kembali ke Produk
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Calculate total price when quantity changes
    $('#jumlah').on('input change', function() {
        const quantity = parseInt($(this).val()) || 1;
        const price = <?= $product['harga'] ?>;
        const total = quantity * price;
        
        $('#total-harga').text('Rp ' + total.toLocaleString('id-ID'));
        
        // Add animation effect
        $('#total-harga').addClass('animate__animated animate__pulse');
        setTimeout(function() {
            $('#total-harga').removeClass('animate__animated animate__pulse');
        }, 600);
    });
    
    // Form validation and loading state
    $('#orderForm').on('submit', function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Add loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
        submitBtn.prop('disabled', true);
        
        // Basic validation
        const nama = $('#nama_pembeli').val().trim();
        const hp = $('#no_hp').val().trim();
        const alamat = $('#alamat').val().trim();
        const jumlah = $('#jumlah').val();
        
        if (!nama || !hp || !alamat || !jumlah) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang required');
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
            return false;
        }
        
        // Validate phone number format
        const phoneRegex = /^(\+62|62|0)8[1-9][0-9]{6,9}$/;
        if (!phoneRegex.test(hp.replace(/\s/g, ''))) {
            e.preventDefault();
            alert('Format nomor HP tidak valid. Gunakan format: 08xxxxxxxxxx');
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
            $('#no_hp').focus();
            return false;
        }
    });
    
    // Phone number formatting
    $('#no_hp').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.startsWith('62')) {
            value = '0' + value.substring(2);
        } else if (value.startsWith('+62')) {
            value = '0' + value.substring(3);
        }
        $(this).val(value);
    });
    
    // Auto-resize textarea
    $('#alamat').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
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
    
    // Add form field animations
    $('.form-control, .form-select').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        if (!$(this).val()) {
            $(this).parent().removeClass('focused');
        }
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
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
    redirect('admin/produk/');
}

$productId = $_GET['id'];
$error = '';
$success = '';
$prefixlogo = '../../';

// Get product details
$query = "SELECT * FROM produk WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $productId);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    redirect('admin/produk/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = sanitizeInput($_POST['nama_produk']);
    $kategori_id = sanitizeInput($_POST['kategori_id']);
    $harga = sanitizeInput($_POST['harga']);
    $stok = sanitizeInput($_POST['stok']);
    $deskripsi = sanitizeInput($_POST['deskripsi']);
    $status = sanitizeInput($_POST['status']);
    
    // Handle file upload
    $gambar = $product['gambar'];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload($_FILES['gambar'], SITE_ROOT . '/../assets/uploads/products/');
        
        if ($uploadResult['success']) {
            // Delete old image if exists
            if ($gambar && file_exists('assets/uploads/products/' . $gambar)) {
                unlink('assets/uploads/products/' . $gambar);
            }
            $gambar = $uploadResult['filename'];
        } else {
            $error = $uploadResult['error'];
        }
    }
    
    if (empty($error)) {
        $query = "UPDATE produk 
                  SET nama_produk = :nama_produk, 
                      kategori_id = :kategori_id, 
                      harga = :harga, 
                      stok = :stok, 
                      deskripsi = :deskripsi, 
                      gambar = :gambar, 
                      status = :status,
                      updated_at = NOW()
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':nama_produk', $nama_produk);
        $stmt->bindParam(':kategori_id', $kategori_id);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':stok', $stok);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':gambar', $gambar);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $productId);
        
        if ($stmt->execute()) {
            $success = 'Produk berhasil diperbarui';
            $success_product_name = $nama_produk;
            $success_product_code = $product['kode_produk'];
            // Refresh product data
            $query = "SELECT * FROM produk WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $productId);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Gagal memperbarui produk';
        }
    }
}

// Get categories for dropdown
$query = "SELECT * FROM kategori WHERE status = 'aktif' ORDER BY nama_kategori";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Edit Produk';
$showSidebar = true;
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
.form-header {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.9) 25%, rgba(113, 158, 221, 0.5) 100%);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 25px;
    color: #ffffff;
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.2);
}

.form-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
}

.form-card:hover {
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}

.form-control, .form-select {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #719edd;
    box-shadow: 0 0 0 3px rgba(113, 158, 221, 0.1);
    background: #ffffff;
}

.form-control::placeholder {
    color: #a0aec0;
    font-style: italic;
}

.form-label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
}

.required-field::after {
    content: ' *';
    color: #e53e3e;
    font-weight: bold;
}

.btn-gradient-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    transition: all 0.3s ease;
    box-shadow: 0 3px 12px rgba(113, 158, 221, 0.3);
}

.btn-gradient-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(113, 158, 221, 0.4);
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%);
    color: white;
}

.btn-gradient-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    transition: all 0.3s ease;
    box-shadow: 0 3px 12px rgba(160, 174, 192, 0.3);
}

.btn-gradient-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(160, 174, 192, 0.4);
    background: linear-gradient(135deg, #718096 0%, #a0aec0 100%);
    color: white;
}

.image-preview-container {
    border: 2px dashed #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    background: #f8fafc;
    transition: all 0.3s ease;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    cursor: pointer;
    position: relative;
}

.image-preview-container:hover {
    border-color: #719edd;
    background: rgba(113, 158, 221, 0.05);
}

.image-preview-container.has-image {
    border-style: solid;
    border-color: #719edd;
    background: #ffffff;
}

.image-preview {
    max-width: 100%;
    max-height: 180px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(45, 55, 72, 0.1);
}

.upload-placeholder {
    color: #a0aec0;
}

.upload-placeholder i {
    font-size: 3rem;
    margin-bottom: 10px;
}

.current-image-label {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(113, 158, 221, 0.9);
    color: white;
    padding: 4px 8px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
}

.change-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 10px;
}

.image-preview-container:hover .change-image-overlay {
    opacity: 1;
}

.form-section {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

.form-section-title {
    color: #4a5568;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}

.input-group-text {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-right: none;
    color: #4a5568;
}

.input-group .form-control {
    border-left: none;
}

.input-group .form-control:focus {
    border-left: none;
    box-shadow: none;
}

.input-group:focus-within .input-group-text {
    border-color: #719edd;
    background: rgba(113, 158, 221, 0.1);
}

.char-counter {
    font-size: 12px;
    color: #a0aec0;
    text-align: right;
    margin-top: 5px;
}

.stock-indicator {
    font-size: 12px;
    margin-top: 5px;
}

.stock-low { color: #e53e3e; }
.stock-medium { color: #f56500; }
.stock-good { color: #38a169; }

.product-code-display {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #4a5568;
}
</style>

<div class="container-fluid">
    <!-- Form Header -->
    <div class="form-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-edit me-2"></i>
            Edit Produk
        </h2>
        <p class="mb-0 fs-6">Perbarui informasi produk dalam katalog toko Anda</p>
    </div>

    <div class="form-card">
        <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border: none;">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-box me-2"></i>
                Informasi Produk
            </h5>
        </div>
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="editProductForm">
                <div class="row">
                    <!-- Left Column - Basic Info -->
                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi Dasar
                            </h6>
                            
                            <div class="mb-3">
                                <label for="kode_produk" class="form-label">Kode Produk</label>
                                <div class="product-code-display">
                                    <i class="fas fa-barcode me-2"></i>
                                    <?= htmlspecialchars($product['kode_produk']) ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nama_produk" class="form-label required-field">Nama Produk</label>
                                <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                                       value="<?= htmlspecialchars($product['nama_produk']) ?>" 
                                       placeholder="Contoh: Kemeja Lengan Panjang Premium" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label required-field">Kategori</label>
                                <select class="form-select select2-single" id="kategori_id" name="kategori_id" required>
                                    <option value="">-- Pilih Kategori Produk --</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                                <?= $category['id'] == $product['kategori_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['nama_kategori']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="harga" class="form-label required-field">Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="harga" name="harga" 
                                           value="<?= $product['harga'] ?>" 
                                           placeholder="150000" min="0" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="stok" class="form-label required-field">Stok</label>
                                <input type="number" class="form-control" id="stok" name="stok" 
                                       value="<?= $product['stok'] ?>" 
                                       placeholder="100" min="0" required>
                                <div class="stock-indicator" id="stockIndicator"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label required-field">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="aktif" <?= $product['status'] === 'aktif' ? 'selected' : '' ?>>
                                        Aktif - Produk dapat dilihat pelanggan
                                    </option>
                                    <option value="nonaktif" <?= $product['status'] === 'nonaktif' ? 'selected' : '' ?>>
                                        Nonaktif - Produk disembunyikan
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Description & Image -->
                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-image me-2"></i>
                                Gambar & Deskripsi
                            </h6>
                            
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar Produk</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" 
                                       accept="image/*" style="display: none;">
                                <div class="image-preview-container <?= $product['gambar'] ? 'has-image' : '' ?>" 
                                     id="imagePreviewContainer" 
                                     onclick="document.getElementById('gambar').click();">
                                    
                                    <?php if ($product['gambar']): ?>
                                        <div class="current-image-label">
                                            <i class="fas fa-image me-1"></i>
                                            Gambar Saat Ini
                                        </div>
                                        <img src="<?= BASE_URL ?>assets/uploads/products/<?= $product['gambar'] ?>" 
                                             class="image-preview" id="currentImage" alt="Current Product Image">
                                        <div class="change-image-overlay">
                                            <div class="text-center">
                                                <i class="fas fa-camera fa-2x mb-2"></i>
                                                <p class="mb-0">Klik untuk mengganti gambar</p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="upload-placeholder" id="uploadPlaceholder">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p class="mb-1">Klik untuk upload gambar</p>
                                            <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <img id="newImagePreview" class="image-preview" style="display: none;">
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Biarkan kosong jika tidak ingin mengubah gambar
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi Produk</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" 
                                          placeholder="Deskripsikan produk Anda secara detail, termasuk bahan, ukuran, warna, dan keunggulan produk..." 
                                          maxlength="500"><?= htmlspecialchars($product['deskripsi']) ?></textarea>
                                <div class="char-counter">
                                    <span id="charCount"><?= strlen($product['deskripsi']) ?></span>/500 karakter
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 2px solid #e2e8f0;">
                    <a href="index.php" class="btn btn-gradient-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-gradient-primary btn-lg" id="submitBtn">
                        <i class="fas fa-save me-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize Select2 for category dropdown
    $('.select2-single').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Kategori Produk --',
        allowClear: true
    });

    // Image preview functionality
    $('#gambar').on('change', function(e) {
        const file = e.target.files[0];
        const container = $('#imagePreviewContainer');
        const placeholder = $('#uploadPlaceholder');
        const currentImage = $('#currentImage');
        const newPreview = $('#newImagePreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                newPreview.attr('src', e.target.result);
                newPreview.show();
                currentImage.hide();
                placeholder.hide();
                container.addClass('has-image');
                
                // Update overlay text
                $('.change-image-overlay').html(`
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">Gambar baru dipilih</p>
                        <small>Klik untuk mengganti lagi</small>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        } else {
            newPreview.hide();
            <?php if ($product['gambar']): ?>
                currentImage.show();
                $('.change-image-overlay').html(`
                    <div class="text-center">
                        <i class="fas fa-camera fa-2x mb-2"></i>
                        <p class="mb-0">Klik untuk mengganti gambar</p>
                    </div>
                `);
            <?php else: ?>
                placeholder.show();
                container.removeClass('has-image');
            <?php endif; ?>
        }
    });

    // Character counter for description
    $('#deskripsi').on('input', function() {
        const length = $(this).val().length;
        $('#charCount').text(length);
        
        if (length > 400) {
            $('#charCount').css('color', '#e53e3e');
        } else if (length > 300) {
            $('#charCount').css('color', '#f56500');
        } else {
            $('#charCount').css('color', '#a0aec0');
        }
    });

    // Stock indicator
    function updateStockIndicator() {
        const stock = parseInt($('#stok').val());
        const indicator = $('#stockIndicator');
        
        if (stock <= 5) {
            indicator.html('<i class="fas fa-exclamation-triangle me-1"></i>Stok rendah - Segera tambah stok')
                     .removeClass('stock-medium stock-good').addClass('stock-low');
        } else if (stock <= 20) {
            indicator.html('<i class="fas fa-info-circle me-1"></i>Stok sedang - Pantau ketersediaan')
                     .removeClass('stock-low stock-good').addClass('stock-medium');
        } else {
            indicator.html('<i class="fas fa-check-circle me-1"></i>Stok mencukupi')
                     .removeClass('stock-low stock-medium').addClass('stock-good');
        }
    }

    $('#stok').on('input', updateStockIndicator);
    
    // Initialize stock indicator
    updateStockIndicator();

    // Format currency input
    $('#harga').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Form validation enhancement
    $('#editProductForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        submitBtn.prop('disabled', true);
        
        // Re-enable after 5 seconds if form doesn't submit properly
        setTimeout(() => {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }, 5000);
    });

    <?php if ($success): ?>
    // Show success SweetAlert
    Swal.fire({
        title: 'Berhasil Diperbarui!',
        html: `
            <div class="text-center">
                <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                <h4 class="mb-3">Produk Berhasil Diperbarui!</h4>
                <div class="card bg-light p-3 mb-3">
                    <div class="row">
                        <div class="col-6 text-end"><strong>Nama Produk:</strong></div>
                        <div class="col-6 text-start"><?= htmlspecialchars($success_product_name) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-6 text-end"><strong>Kode Produk:</strong></div>
                        <div class="col-6 text-start"><code><?= $success_product_code ?></code></div>
                    </div>
                </div>
                <p class="text-muted mb-0">Semua perubahan telah berhasil disimpan</p>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'Lihat Daftar Produk',
        showCancelButton: true,
        cancelButtonText: 'Lanjut Edit',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#719edd',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'swal2-popup-success',
            confirmButton: 'btn-success-custom',
            cancelButton: 'btn-primary-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php';
        }
        // If cancel (continue editing), just close the modal
    });
    <?php endif; ?>
});
</script>

<style>
/* Custom SweetAlert2 styling */
.swal2-popup-success {
    border-radius: 15px !important;
    padding: 2rem !important;
}

.btn-success-custom {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 10px 20px !important;
    font-weight: 600 !important;
}

.btn-success-custom:hover {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3) !important;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%) !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 10px 20px !important;
    font-weight: 600 !important;
}

.btn-primary-custom:hover {
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(113, 158, 221, 0.3) !important;
}

/* Form animations */
.form-section {
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-control:focus, .form-select:focus {
    animation: focusGlow 0.3s ease-out;
}

@keyframes focusGlow {
    0% { box-shadow: 0 0 0 0 rgba(113, 158, 221, 0.4); }
    50% { box-shadow: 0 0 0 5px rgba(113, 158, 221, 0.2); }
    100% { box-shadow: 0 0 0 3px rgba(113, 158, 221, 0.1); }
}
</style>
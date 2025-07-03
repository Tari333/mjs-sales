<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$error = '';
$success = '';
$prefixlogo = '../../';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = sanitizeInput($_POST['nama_produk']);
    $kategori_id = sanitizeInput($_POST['kategori_id']);
    $harga = sanitizeInput($_POST['harga']);
    $stok = sanitizeInput($_POST['stok']);
    $deskripsi = sanitizeInput($_POST['deskripsi']);
    $status = sanitizeInput($_POST['status']);
    
    // Handle file upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload($_FILES['gambar'], SITE_ROOT . '/../assets/uploads/products/');
        
        if ($uploadResult['success']) {
            $gambar = $uploadResult['filename'];
        } else {
            $error = $uploadResult['error'];
        }
    }
    
    if (empty($error)) {
        $query = "INSERT INTO produk (kode_produk, nama_produk, kategori_id, harga, stok, deskripsi, gambar, status) 
                  VALUES (:kode_produk, :nama_produk, :kategori_id, :harga, :stok, :deskripsi, :gambar, :status)";
        $stmt = $db->prepare($query);
        
        $kode_produk = 'PROD-' . strtoupper(uniqid());
        
        $stmt->bindParam(':kode_produk', $kode_produk);
        $stmt->bindParam(':nama_produk', $nama_produk);
        $stmt->bindParam(':kategori_id', $kategori_id);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':stok', $stok);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':gambar', $gambar);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            $success = 'Produk berhasil ditambahkan!';
            $success_product_name = $nama_produk;
            $success_product_code = $kode_produk;
        } else {
            $error = 'Gagal menambahkan produk. Silakan coba lagi.';
        }
    }
}

// Get categories for dropdown
$query = "SELECT * FROM kategori WHERE status = 'aktif' ORDER BY nama_kategori";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Tambah Produk';
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
</style>

<div class="container-fluid">
    <!-- Form Header -->
    <div class="form-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-plus-circle me-2"></i>
            Tambah Produk Baru
        </h2>
        <p class="mb-0 fs-6">Tambahkan produk baru ke dalam katalog toko Anda</p>
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
            
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <div class="row">
                    <!-- Left Column - Basic Info -->
                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi Dasar
                            </h6>
                            
                            <div class="mb-3">
                                <label for="nama_produk" class="form-label required-field">Nama Produk</label>
                                <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                                       placeholder="Contoh: Kemeja Lengan Panjang Premium" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label required-field">Kategori</label>
                                <select class="form-select select2-single" id="kategori_id" name="kategori_id" required>
                                    <option value="">-- Pilih Kategori Produk --</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['nama_kategori']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="harga" class="form-label required-field">Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="harga" name="harga" 
                                           placeholder="150000" min="0" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="stok" class="form-label required-field">Stok</label>
                                <input type="number" class="form-control" id="stok" name="stok" 
                                       placeholder="100" min="0" required>
                                <div class="stock-indicator" id="stockIndicator"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label required-field">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="aktif" selected>Aktif - Produk dapat dilihat pelanggan</option>
                                    <option value="nonaktif">Nonaktif - Produk disembunyikan</option>
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
                                <div class="image-preview-container" id="imagePreviewContainer" onclick="document.getElementById('gambar').click();">
                                    <div class="upload-placeholder" id="uploadPlaceholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p class="mb-1">Klik untuk upload gambar</p>
                                        <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                                    </div>
                                    <img id="imagePreview" class="image-preview" style="display: none;">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi Produk</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" 
                                          placeholder="Deskripsikan produk Anda secara detail, termasuk bahan, ukuran, warna, dan keunggulan produk..." 
                                          maxlength="500"></textarea>
                                <div class="char-counter">
                                    <span id="charCount">0</span>/500 karakter
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
                        Simpan Produk
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
        const preview = $('#imagePreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.attr('src', e.target.result);
                preview.show();
                placeholder.hide();
                container.addClass('has-image');
            };
            reader.readAsDataURL(file);
        } else {
            preview.hide();
            placeholder.show();
            container.removeClass('has-image');
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
    $('#stok').on('input', function() {
        const stock = parseInt($(this).val());
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
    });

    // Format currency input
    $('#harga').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Form validation enhancement
    $('#productForm').on('submit', function(e) {
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

    // Auto-focus on first input
    setTimeout(() => {
        $('#nama_produk').focus();
    }, 500);

    <?php if ($success): ?>
    // Show success SweetAlert
    Swal.fire({
        title: 'Berhasil!',
        html: `
            <div class="text-center">
                <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                <h4 class="mb-3">Produk Berhasil Ditambahkan!</h4>
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
                <p class="text-muted mb-0">Produk telah berhasil disimpan ke dalam database</p>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'Lihat Daftar Produk',
        showCancelButton: true,
        cancelButtonText: 'Tambah Produk Lagi',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'swal2-popup-success',
            confirmButton: 'btn-success-custom',
            cancelButton: 'btn-secondary-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php';
        } else {
            // Clear form for new product
            $('#productForm')[0].reset();
            $('.select2-single').val(null).trigger('change');
            $('#imagePreview').hide();
            $('#uploadPlaceholder').show();
            $('#imagePreviewContainer').removeClass('has-image');
            $('#charCount').text('0').css('color', '#a0aec0');
            $('#stockIndicator').html('');
            $('#nama_produk').focus();
        }
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

.btn-secondary-custom {
    background: #6c757d !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 10px 20px !important;
    font-weight: 600 !important;
}

.btn-secondary-custom:hover {
    background: #5a6268 !important;
    transform: translateY(-2px) !important;
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
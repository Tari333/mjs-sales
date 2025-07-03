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
    redirect('admin/kategori/');
}

$categoryId = $_GET['id'];
$error = '';
$success = '';
$prefixlogo = '../../';

// Get category details
$query = "SELECT * FROM kategori WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $categoryId);
$stmt->execute();
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    redirect('admin/kategori/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = sanitizeInput($_POST['nama_kategori']);
    $deskripsi = sanitizeInput($_POST['deskripsi']);
    $status = sanitizeInput($_POST['status']);
    
    // Validate input
    if (empty($nama_kategori)) {
        $error = 'Nama kategori wajib diisi';
    } else {
        // Check if category with same name already exists (excluding current category)
        $query = "SELECT id FROM kategori WHERE nama_kategori = :nama_kategori AND id != :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        $stmt->bindParam(':id', $categoryId);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error = 'Kategori dengan nama yang sama sudah ada';
        } else {
            // Update category
            $query = "UPDATE kategori 
                      SET nama_kategori = :nama_kategori, 
                          deskripsi = :deskripsi, 
                          status = :status
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':nama_kategori', $nama_kategori);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $categoryId);
            
            if ($stmt->execute()) {
                $success = 'Kategori berhasil diperbarui';
                $success_category_name = $nama_kategori;
                // Refresh category data
                $query = "SELECT * FROM kategori WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $categoryId);
                $stmt->execute();
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Gagal memperbarui kategori';
            }
        }
    }
}

$pageTitle = 'Edit Kategori';
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

.char-counter {
    font-size: 12px;
    color: #a0aec0;
    text-align: right;
    margin-top: 5px;
}

.category-id-display {
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
            Edit Kategori
        </h2>
        <p class="mb-0 fs-6">Perbarui informasi kategori produk</p>
    </div>

    <div class="form-card">
        <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border: none;">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-tag me-2"></i>
                Informasi Kategori
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
            
            <form method="POST" id="editCategoryForm">
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Dasar
                    </h6>
                    
                    <div class="mb-3">
                        <label class="form-label">ID Kategori</label>
                        <div class="category-id-display">
                            <i class="fas fa-hashtag me-2"></i>
                            <?= $category['id'] ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label required-field">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" 
                               value="<?= htmlspecialchars($category['nama_kategori']) ?>" 
                               placeholder="Contoh: Cat Tembok" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi Kategori</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                  placeholder="Deskripsikan kategori ini (opsional)" 
                                  maxlength="255"><?= htmlspecialchars($category['deskripsi']) ?></textarea>
                        <div class="char-counter">
                            <span id="charCount"><?= strlen($category['deskripsi']) ?></span>/255 karakter
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label required-field">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="aktif" <?= $category['status'] === 'aktif' ? 'selected' : '' ?>>
                                Aktif - Kategori dapat digunakan
                            </option>
                            <option value="nonaktif" <?= $category['status'] === 'nonaktif' ? 'selected' : '' ?>>
                                Nonaktif - Kategori disembunyikan
                            </option>
                        </select>
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
    // Character counter for description
    $('#deskripsi').on('input', function() {
        const length = $(this).val().length;
        $('#charCount').text(length);
        
        if (length > 200) {
            $('#charCount').css('color', '#e53e3e');
        } else if (length > 150) {
            $('#charCount').css('color', '#f56500');
        } else {
            $('#charCount').css('color', '#a0aec0');
        }
    });

    // Form validation enhancement
    $('#editCategoryForm').on('submit', function(e) {
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
                <h4 class="mb-3">Kategori Berhasil Diperbarui!</h4>
                <div class="card bg-light p-3 mb-3">
                    <div class="row">
                        <div class="col-6 text-end"><strong>Nama Kategori:</strong></div>
                        <div class="col-6 text-start"><?= htmlspecialchars($success_category_name) ?></div>
                    </div>
                </div>
                <p class="text-muted mb-0">Semua perubahan telah berhasil disimpan</p>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'Lihat Daftar Kategori',
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
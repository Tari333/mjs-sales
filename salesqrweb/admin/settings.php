<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$success = '';
$error = '';

// Get current settings
$query = "SELECT * FROM pengaturan WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        $nama_toko = $_POST['nama_toko'];
        $alamat_toko = $_POST['alamat_toko'];
        $no_hp_toko = $_POST['no_hp_toko'];
        $email_toko = $_POST['email_toko'];
        $no_rekening = $_POST['no_rekening'];
        $nama_bank = $_POST['nama_bank'];
        $atas_nama = $_POST['atas_nama'];
        $deskripsi_toko = $_POST['deskripsi_toko'];
        $whatsapp_number = $_POST['whatsapp_number'];

        // Handle logo upload
        $logo_toko = $settings['logo_toko'];
        if (!empty($_FILES['logo_toko']['name'])) {
            $uploadDir = __DIR__ . '/../assets/uploads/settings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExt = pathinfo($_FILES['logo_toko']['name'], PATHINFO_EXTENSION);
            $fileName = 'logo-' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;

            // Check file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($fileExt), $allowedTypes)) {
                throw new Exception('Format file tidak didukung. Hanya JPG, PNG, dan GIF yang diperbolehkan.');
            }

            // Check file size (max 2MB)
            if ($_FILES['logo_toko']['size'] > 2 * 1024 * 1024) {
                throw new Exception('Ukuran file terlalu besar. Maksimal 2MB.');
            }

            // Move uploaded file
            if (move_uploaded_file($_FILES['logo_toko']['tmp_name'], $targetPath)) {
                // Delete old logo if exists
                if ($logo_toko && file_exists(__DIR__ . '/../' . $logo_toko)) {
                    unlink(__DIR__ . '/../' . $logo_toko);
                }
                $logo_toko = 'assets/uploads/settings/' . $fileName;
            } else {
                throw new Exception('Gagal mengunggah logo.');
            }
        }

        // Handle logo removal
        if (isset($_POST['remove_logo']) && $_POST['remove_logo'] == '1') {
            if ($logo_toko && file_exists(__DIR__ . '/../' . $logo_toko)) {
                unlink(__DIR__ . '/../' . $logo_toko);
            }
            $logo_toko = '';
        }

        $query = "UPDATE pengaturan SET 
                  nama_toko = :nama_toko,
                  alamat_toko = :alamat_toko,
                  no_hp_toko = :no_hp_toko,
                  email_toko = :email_toko,
                  no_rekening = :no_rekening,
                  nama_bank = :nama_bank,
                  atas_nama = :atas_nama,
                  deskripsi_toko = :deskripsi_toko,
                  whatsapp_number = :whatsapp_number,
                  logo_toko = :logo_toko,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE id = 1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_toko', $nama_toko);
        $stmt->bindParam(':alamat_toko', $alamat_toko);
        $stmt->bindParam(':no_hp_toko', $no_hp_toko);
        $stmt->bindParam(':email_toko', $email_toko);
        $stmt->bindParam(':no_rekening', $no_rekening);
        $stmt->bindParam(':nama_bank', $nama_bank);
        $stmt->bindParam(':atas_nama', $atas_nama);
        $stmt->bindParam(':deskripsi_toko', $deskripsi_toko);
        $stmt->bindParam(':whatsapp_number', $whatsapp_number);
        $stmt->bindParam(':logo_toko', $logo_toko);
        $stmt->execute();

        $db->commit();
        $success = 'Pengaturan berhasil diperbarui!';
        
        // Refresh settings
        $stmt = $db->prepare("SELECT * FROM pengaturan WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

$pageTitle = 'Pengaturan Website';
$showSidebar = true;
$prefixlogo = '../';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
/* Settings Page Styles */
.settings-header {
    background: linear-gradient(
        135deg,
        rgba(113, 158, 221, 0.9) 25%,
        rgba(113, 158, 221, 0.5) 100%
    );
    border-radius: 25px;
    padding: 30px 20px;
    margin-bottom: 30px;
    color: #ffffff;
    box-shadow: 0 15px 35px rgba(113, 158, 221, 0.2);
}

.settings-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    margin-bottom: 20px;
}

.settings-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(45, 55, 72, 0.12);
}

.settings-card .card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-bottom: 2px solid #e2e8f0;
    border-radius: 20px 20px 0 0 !important;
    padding: 20px 25px;
}

.settings-card .card-header h5 {
    color: #2d3748;
    font-weight: 700;
    margin-bottom: 0;
}

.settings-card .card-body {
    padding: 25px;
}

.form-label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
}

.form-control, .form-select {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 15px;
    font-size: 14px;
    transition: all 0.3s ease;
    background-color: #ffffff;
}

.form-control:focus, .form-select:focus {
    border-color: #719edd;
    box-shadow: 0 0 0 3px rgba(113, 158, 221, 0.1);
    background-color: #ffffff;
}

.form-control:hover, .form-select:hover {
    border-color: #cbd5e0;
}

.btn {
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    padding: 12px 25px;
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

.btn-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    color: white;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(160, 174, 192, 0.3);
    background: linear-gradient(135deg, #718096 0%, #a0aec0 100%);
}

.btn-outline-danger {
    border: 2px solid #f56565;
    color: #f56565;
    background: transparent;
}

.btn-outline-danger:hover {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 101, 101, 0.3);
}

.alert {
    border: none;
    border-radius: 15px;
    padding: 15px 20px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

.alert-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    color: white;
}

.logo-preview {
    max-height: 200px;
    max-width: 200px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.1);
    border: 3px solid #e2e8f0;
    transition: all 0.3s ease;
}

.logo-preview:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.15);
}

.logo-upload-area {
    border: 2px dashed #cbd5e0;
    border-radius: 15px;
    padding: 30px;
    text-align: center;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    transition: all 0.3s ease;
}

.logo-upload-area:hover {
    border-color: #719edd;
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.05) 0%, rgba(113, 158, 221, 0.02) 100%);
}

.logo-upload-area i {
    font-size: 3rem;
    color: #a0aec0;
    margin-bottom: 15px;
}

.form-check-input:checked {
    background-color: #719edd;
    border-color: #719edd;
}

.text-muted {
    color: #718096 !important;
}

.section-divider {
    height: 2px;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border: none;
    border-radius: 2px;
    margin: 25px 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .settings-header {
        padding: 20px 15px;
    }
    
    .settings-card .card-body {
        padding: 20px;
    }
    
    .logo-upload-area {
        padding: 20px;
    }
    
    .logo-upload-area i {
        font-size: 2rem;
    }
}

/* Animation for form elements */
.form-floating > label {
    color: #718096;
}

.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label {
    color: #719edd;
}

/* Loading animation for save button */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top: 2px solid #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<div class="container-fluid">
    <!-- Settings Header -->
    <div class="settings-header text-center mb-4">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-cogs me-2"></i>
            Pengaturan Website
        </h2>
        <p class="mb-0 fs-6">Kelola informasi toko dan konfigurasi sistem</p>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="settingsForm">
        <div class="row">
            <!-- Store Information -->
            <div class="col-lg-8">
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-store me-2 text-primary"></i>
                            Informasi Toko
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_toko" class="form-label">
                                        <i class="fas fa-tag me-1"></i>
                                        Nama Toko
                                    </label>
                                    <input type="text" class="form-control" id="nama_toko" name="nama_toko" 
                                           value="<?= htmlspecialchars($settings['nama_toko']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="no_hp_toko" class="form-label">
                                        <i class="fas fa-phone me-1"></i>
                                        No. HP Toko
                                    </label>
                                    <input type="text" class="form-control" id="no_hp_toko" name="no_hp_toko" 
                                           value="<?= htmlspecialchars($settings['no_hp_toko']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email_toko" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>
                                        Email Toko
                                    </label>
                                    <input type="email" class="form-control" id="email_toko" name="email_toko" 
                                           value="<?= htmlspecialchars($settings['email_toko']) ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="whatsapp_number" class="form-label">
                                        <i class="fab fa-whatsapp me-1"></i>
                                        Nomor WhatsApp
                                    </label>
                                    <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" 
                                           value="<?= htmlspecialchars($settings['whatsapp_number']) ?>" required>
                                    <small class="text-muted">Format: 628123456789 (tanpa + atau 0)</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="alamat_toko" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Alamat Toko
                                    </label>
                                    <textarea class="form-control" id="alamat_toko" name="alamat_toko" 
                                              rows="3"><?= htmlspecialchars($settings['alamat_toko']) ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="section-divider">
                        
                        <div class="mb-3">
                            <label for="deskripsi_toko" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>
                                Deskripsi Toko
                            </label>
                            <textarea class="form-control" id="deskripsi_toko" name="deskripsi_toko" 
                                      rows="4" placeholder="Ceritakan tentang toko Anda..."><?= htmlspecialchars($settings['deskripsi_toko']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2 text-success"></i>
                            Informasi Pembayaran
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_bank" class="form-label">
                                        <i class="fas fa-university me-1"></i>
                                        Nama Bank
                                    </label>
                                    <input type="text" class="form-control" id="nama_bank" name="nama_bank" 
                                           value="<?= htmlspecialchars($settings['nama_bank']) ?>" 
                                           placeholder="Contoh: Bank BCA">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="atas_nama" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Atas Nama
                                    </label>
                                    <input type="text" class="form-control" id="atas_nama" name="atas_nama" 
                                           value="<?= htmlspecialchars($settings['atas_nama']) ?>" 
                                           placeholder="Nama pemilik rekening">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_rekening" class="form-label">
                                        <i class="fas fa-credit-card me-1"></i>
                                        Nomor Rekening
                                    </label>
                                    <input type="text" class="form-control" id="no_rekening" name="no_rekening" 
                                           value="<?= htmlspecialchars($settings['no_rekening']) ?>" 
                                           placeholder="1234567890">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logo Upload -->
            <div class="col-lg-4">
                <div class="settings-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-image me-2 text-info"></i>
                            Logo Toko
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($settings['logo_toko']): ?>
                            <div class="text-center mb-4">
                                <img src="<?= BASE_URL . $settings['logo_toko'] ?>" 
                                     alt="Logo Toko" 
                                     class="logo-preview mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                    <label class="form-check-label text-danger" for="remove_logo">
                                        <i class="fas fa-trash me-1"></i>
                                        Hapus logo saat ini
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="logo-upload-area">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h6 class="mb-2">Unggah Logo Baru</h6>
                            <input type="file" class="form-control mb-2" id="logo_toko" name="logo_toko" 
                                   accept="image/*" onchange="previewLogo(this)">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Format: JPG, PNG, GIF | Maksimal 2MB
                            </small>
                        </div>
                        
                        <div id="logo_preview" class="mt-3" style="display: none;">
                            <img id="preview_image" class="logo-preview" alt="Preview">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="settings-card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-2" id="saveBtn">
                            <i class="fas fa-save me-2"></i>
                            Simpan Perubahan
                        </button>
                        <button type="reset" class="btn btn-secondary w-100" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>
                            Reset Form
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize form validation
    $('#settingsForm').on('submit', function(e) {
        const saveBtn = $('#saveBtn');
        saveBtn.addClass('btn-loading').prop('disabled', true);
        
        // Reset after 3 seconds if form doesn't submit
        setTimeout(() => {
            saveBtn.removeClass('btn-loading').prop('disabled', false);
        }, 3000);
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Add smooth animations to cards
    $('.settings-card').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        $(this).delay(index * 150).animate({opacity: 1}, 600, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });

    // WhatsApp number formatting
    $('#whatsapp_number').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.startsWith('0')) {
            value = '62' + value.substring(1);
        } else if (!value.startsWith('62')) {
            value = '62' + value;
        }
        $(this).val(value);
    });

    // Phone number formatting
    $('#no_hp_toko').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Bank account number formatting
    $('#no_rekening').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });
});

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#preview_image').attr('src', e.target.result);
            $('#logo_preview').show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function resetForm() {
    if (confirm('Apakah Anda yakin ingin mereset form? Semua perubahan yang belum disimpan akan hilang.')) {
        $('#logo_preview').hide();
        $('#remove_logo').prop('checked', false);
    }
}

// Form validation
function validateForm() {
    let isValid = true;
    const requiredFields = ['nama_toko', 'whatsapp_number'];
    
    requiredFields.forEach(function(field) {
        const input = $('#' + field);
        if (!input.val().trim()) {
            input.addClass('is-invalid');
            isValid = false;
        } else {
            input.removeClass('is-invalid');
        }
    });
    
    // Validate WhatsApp number format
    const whatsapp = $('#whatsapp_number').val();
    if (whatsapp && !whatsapp.match(/^62\d{8,13}$/)) {
        $('#whatsapp_number').addClass('is-invalid');
        isValid = false;
    }
    
    return isValid;
}

// Real-time validation
$('input, textarea').on('blur', function() {
    if ($(this).prop('required') && !$(this).val().trim()) {
        $(this).addClass('is-invalid');
    } else {
        $(this).removeClass('is-invalid');
    }
});
</script>
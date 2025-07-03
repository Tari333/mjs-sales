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
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
    $email = sanitizeInput($_POST['email']);
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($confirm_password) || empty($nama_lengkap)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } else {
        // Check if username exists
        $query = "SELECT id FROM admin WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $query = "INSERT INTO admin (username, password, nama_lengkap, email) 
                      VALUES (:username, :password, :nama_lengkap, :email)";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':nama_lengkap', $nama_lengkap);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                $success = 'Pengguna berhasil ditambahkan!';
                $success_username = $username;
            } else {
                $error = 'Gagal menambahkan pengguna. Silakan coba lagi.';
            }
        }
    }
}

$pageTitle = 'Tambah Pengguna';
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

.password-strength {
    height: 5px;
    border-radius: 5px;
    margin-top: 5px;
    background: #e2e8f0;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease;
}

.password-weak { background-color: #e53e3e; }
.password-medium { background-color: #f56500; }
.password-strong { background-color: #38a169; }
</style>

<div class="container-fluid">
    <!-- Form Header -->
    <div class="form-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-user-plus me-2"></i>
            Tambah Pengguna Baru
        </h2>
        <p class="mb-0 fs-6">Tambahkan pengguna baru ke dalam sistem</p>
    </div>

    <div class="form-card">
        <div class="card-header p-2" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border: none;">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-user me-2"></i>
                Informasi Pengguna
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
            
            <form method="POST" id="userForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-user-circle me-2"></i>
                                Informasi Akun
                            </h6>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label required-field">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Masukkan username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label required-field">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan password" required>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                </div>
                                <small class="text-muted">Minimal 8 karakter</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label required-field">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Masukkan password lagi" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi Pribadi
                            </h6>
                            
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label required-field">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                       placeholder="Masukkan nama lengkap" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Masukkan email">
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
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strengthBar = $('#passwordStrengthBar');
        let strength = 0;
        
        if (password.length > 0) strength += 1;
        if (password.length >= 8) strength += 1;
        if (password.match(/[a-z]/)) strength += 1;
        if (password.match(/[A-Z]/)) strength += 1;
        if (password.match(/[0-9]/)) strength += 1;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
        
        // Update strength bar
        strengthBar.removeClass('password-weak password-medium password-strong');
        
        if (password.length === 0) {
            strengthBar.css('width', '0%');
        } else if (strength <= 2) {
            strengthBar.css('width', '25%').addClass('password-weak');
        } else if (strength <= 4) {
            strengthBar.css('width', '50%').addClass('password-medium');
        } else {
            strengthBar.css('width', '100%').addClass('password-strong');
        }
    });

    // Form validation enhancement
    $('#userForm').on('submit', function(e) {
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
        $('#username').focus();
    }, 500);

    <?php if ($success): ?>
    // Show success SweetAlert
    Swal.fire({
        title: 'Berhasil!',
        html: `
            <div class="text-center">
                <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                <h4 class="mb-3">Pengguna Berhasil Ditambahkan!</h4>
                <div class="card bg-light p-3 mb-3">
                    <div class="row">
                        <div class="col-6 text-end"><strong>Username:</strong></div>
                        <div class="col-6 text-start"><?= htmlspecialchars($success_username) ?></div>
                    </div>
                </div>
                <p class="text-muted mb-0">Pengguna telah berhasil ditambahkan ke dalam sistem</p>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'Lihat Daftar Pengguna',
        showCancelButton: true,
        cancelButtonText: 'Tambah Pengguna Lagi',
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
            // Clear form for new user
            $('#userForm')[0].reset();
            $('#passwordStrengthBar').css('width', '0%').removeClass('password-weak password-medium password-strong');
            $('#username').focus();
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
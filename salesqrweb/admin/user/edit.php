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
    redirect('admin/user/');
}

$userId = $_GET['id'];
$error = '';
$success = '';
$prefixlogo = '../../';

// Get user details
$query = "SELECT * FROM admin WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    redirect('admin/user/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($username) || empty($nama_lengkap)) {
        $error = 'Username dan nama lengkap wajib diisi!';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } else {
        // Check if username exists (excluding current user)
        $query = "SELECT id FROM admin WHERE username = :username AND id != :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Update user
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "UPDATE admin 
                          SET username = :username, 
                              nama_lengkap = :nama_lengkap, 
                              email = :email,
                              password = :password,
                              updated_at = NOW()
                          WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password', $hashed_password);
            } else {
                $query = "UPDATE admin 
                          SET username = :username, 
                              nama_lengkap = :nama_lengkap, 
                              email = :email,
                              updated_at = NOW()
                          WHERE id = :id";
                $stmt = $db->prepare($query);
            }
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':nama_lengkap', $nama_lengkap);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $userId);
            
            if ($stmt->execute()) {
                $success = 'Pengguna berhasil diperbarui!';
                $success_username = $username;
                // Refresh user data
                $query = "SELECT * FROM admin WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Gagal memperbarui pengguna. Silakan coba lagi.';
            }
        }
    }
}

$pageTitle = 'Edit Pengguna';
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

.password-change-note {
    font-size: 12px;
    color: #a0aec0;
    font-style: italic;
}
</style>

<div class="container-fluid">
    <!-- Form Header -->
    <div class="form-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-user-edit me-2"></i>
            Edit Pengguna
        </h2>
        <p class="mb-0 fs-6">Perbarui informasi pengguna sistem</p>
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
            
            <form method="POST" id="editUserForm">
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
                                       value="<?= htmlspecialchars($user['username']) ?>" 
                                       placeholder="Masukkan username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Kosongkan jika tidak ingin mengubah">
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                </div>
                                <small class="password-change-note">Biarkan kosong jika tidak ingin mengubah password</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Kosongkan jika tidak ingin mengubah">
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
                                       value="<?= htmlspecialchars($user['nama_lengkap']) ?>" 
                                       placeholder="Masukkan nama lengkap" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" 
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
    $('#editUserForm').on('submit', function(e) {
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
                <h4 class="mb-3">Pengguna Berhasil Diperbarui!</h4>
                <div class="card bg-light p-3 mb-3">
                    <div class="row">
                        <div class="col-6 text-end"><strong>Username:</strong></div>
                        <div class="col-6 text-start"><?= htmlspecialchars($success_username) ?></div>
                    </div>
                </div>
                <p class="text-muted mb-0">Semua perubahan telah berhasil disimpan</p>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'Lihat Daftar Pengguna',
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
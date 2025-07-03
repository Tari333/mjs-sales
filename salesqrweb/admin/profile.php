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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Semua field password harus diisi';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password baru minimal 6 karakter';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Password baru dan konfirmasi password tidak cocok';
        } elseif (changePassword($_SESSION['admin_id'], $currentPassword, $newPassword, $db)) {
            $success = 'Password berhasil diubah';
        } else {
            $error = 'Password saat ini salah';
        }
    }
}

$pageTitle = 'Profil Admin';
$showSidebar = true;
$prefixlogo = '../';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
/* Profile Cards */
.profile-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    height: 100%;
}

.profile-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(45, 55, 72, 0.12);
}

.profile-card .card-header {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: white;
    border-bottom: none;
    border-radius: 20px 20px 0 0 !important;
    padding: 20px 25px;
}

.profile-card .card-header h5 {
    font-weight: 600;
    margin: 0;
}

.profile-card .card-body {
    padding: 25px;
}

/* Welcome Header */
.profile-header {
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

/* Form Styling */
.form-label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: #f8fafc;
}

.form-control:focus {
    border-color: #719edd;
    box-shadow: 0 0 0 0.2rem rgba(113, 158, 221, 0.15);
    background-color: #ffffff;
}

.form-control[readonly] {
    background-color: #f1f5f9;
    cursor: not-allowed;
}

.input-group {
    position: relative;
}

.input-group .form-control {
    border-radius: 12px 0 0 12px;
}

.input-group .btn {
    border-radius: 0 12px 12px 0;
    border: 2px solid #e2e8f0;
    border-left: none;
    background-color: #f8fafc;
    color: #64748b;
    transition: all 0.3s ease;
}

.input-group .btn:hover {
    background-color: #719edd;
    color: white;
    border-color: #719edd;
}

/* Buttons */
.btn {
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    padding: 12px 24px;
}

.btn-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    box-shadow: 0 3px 12px rgba(113, 158, 221, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(113, 158, 221, 0.4);
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%);
}

.btn-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    color: white;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(160, 174, 192, 0.3);
}

/* Alerts */
.alert {
    border-radius: 15px;
    padding: 15px 20px;
    margin-bottom: 20px;
    border: none;
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

/* Profile Avatar Section */
.profile-avatar {
    text-align: center;
    margin-bottom: 25px;
}

.avatar-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.2);
}

.avatar-circle i {
    font-size: 3rem;
    color: white;
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
}

.profile-role {
    color: #719edd;
    font-weight: 500;
}

/* Password Strength Indicator */
.password-strength {
    height: 4px;
    background-color: #e2e8f0;
    border-radius: 2px;
    margin-top: 8px;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-weak { background-color: #f56565; width: 25%; }
.strength-fair { background-color: #ed8936; width: 50%; }
.strength-good { background-color: #f6ad55; width: 75%; }
.strength-strong { background-color: #48bb78; width: 100%; }

/* Info Cards */
.info-card {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 4px solid #719edd;
}

.info-card h6 {
    color: #719edd;
    font-weight: 600;
    margin-bottom: 10px;
}

.info-card p {
    color: #64748b;
    margin: 0;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-card .card-body {
        padding: 20px;
    }
    
    .profile-header {
        padding: 20px 15px;
    }
    
    .avatar-circle {
        width: 100px;
        height: 100px;
    }
    
    .avatar-circle i {
        font-size: 2.5rem;
    }
}

/* Animation */
.fade-in {
    animation: fadeInUp 0.6s ease-out;
}

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
</style>

<div class="container-fluid">
    <!-- Profile Header -->
    <div class="profile-header text-center mb-4">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-user-cog me-2"></i>
            Profil Admin
        </h2>
        <p class="mb-0 fs-6">Kelola informasi akun dan keamanan Anda</p>
    </div>

    <div class="row">
        <!-- Account Information -->
        <div class="col-lg-6 mb-4">
            <div class="profile-card fade-in">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informasi Akun
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Profile Avatar -->
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="profile-name"><?= htmlspecialchars($_SESSION['admin_nama']) ?></div>
                        <div class="profile-role">Administrator</div>
                    </div>

                    <!-- Account Details -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user me-2"></i>
                            Username
                        </label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['admin_username']) ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-id-card me-2"></i>
                            Nama Lengkap
                        </label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['admin_nama']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-calendar me-2"></i>
                            Terakhir Login
                        </label>
                        <input type="text" class="form-control" value="<?= date('d/m/Y H:i:s') ?>" readonly>
                    </div>

                    <!-- Info Card -->
                    <div class="info-card">
                        <h6><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                        <p>Data akun ini dikelola oleh sistem. Untuk mengubah informasi personal, hubungi super admin.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-lg-6 mb-4">
            <div class="profile-card fade-in" style="animation-delay: 0.2s">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2"></i>
                        Keamanan Akun
                    </h5>
                </div>
                <div class="card-body">
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
                    
                    <form method="POST" id="passwordForm">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">
                                <i class="fas fa-key me-2"></i>
                                Password Saat Ini
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">
                                <i class="fas fa-lock me-2"></i>
                                Password Baru
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="strengthBar"></div>
                            </div>
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-check-double me-2"></i>
                                Konfirmasi Password Baru
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="mt-2"></div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-2"></i>
                                Simpan Perubahan
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-2"></i>
                                Reset Form
                            </button>
                        </div>
                    </form>

                    <!-- Security Info -->
                    <div class="info-card mt-4">
                        <h6><i class="fas fa-shield-alt me-2"></i>Tips Keamanan</h6>
                        <p>Gunakan kombinasi huruf besar, kecil, angka, dan simbol untuk password yang kuat. Jangan gunakan informasi personal yang mudah ditebak.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Toggle password visibility
    $('.toggle-password').click(function() {
        const target = $(this).data('target');
        const input = $('#' + target);
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password strength checker
    $('#new_password').on('input', function() {
        const password = $(this).val();
        const strengthBar = $('#strengthBar');
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        strengthBar.removeClass('strength-weak strength-fair strength-good strength-strong');
        
        switch(strength) {
            case 1:
                strengthBar.addClass('strength-weak');
                break;
            case 2:
                strengthBar.addClass('strength-fair');
                break;
            case 3:
                strengthBar.addClass('strength-good');
                break;
            case 4:
                strengthBar.addClass('strength-strong');
                break;
        }
    });

    // Password match checker
    $('#confirm_password').on('input', function() {
        const newPassword = $('#new_password').val();
        const confirmPassword = $(this).val();
        const matchDiv = $('#passwordMatch');
        
        if (confirmPassword.length > 0) {
            if (newPassword === confirmPassword) {
                matchDiv.html('<small class="text-success"><i class="fas fa-check me-1"></i>Password cocok</small>');
            } else {
                matchDiv.html('<small class="text-danger"><i class="fas fa-times me-1"></i>Password tidak cocok</small>');
            }
        } else {
            matchDiv.html('');
        }
    });

    // Form validation
    $('#passwordForm').on('submit', function(e) {
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Password Tidak Cocok',
                text: 'Password baru dan konfirmasi password harus sama',
                confirmButtonColor: '#719edd'
            });
            return false;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Password Terlalu Pendek',
                text: 'Password minimal harus 6 karakter',
                confirmButtonColor: '#719edd'
            });
            return false;
        }

        // Show loading state
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        submitBtn.prop('disabled', true);
        
        // Re-enable after 3 seconds if form doesn't submit
        setTimeout(() => {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }, 3000);
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Add fade-in animation to cards
    $('.fade-in').each(function(index) {
        $(this).css('opacity', '0').delay(index * 200).animate({opacity: 1}, 600);
    });
});

// Reset form function
function resetForm() {
    $('#passwordForm')[0].reset();
    $('#passwordMatch').html('');
    $('#strengthBar').removeClass('strength-weak strength-fair strength-good strength-strong');
    
    // Reset password visibility
    $('input[type="text"]').each(function() {
        if ($(this).attr('id') !== 'username' && $(this).attr('id') !== 'nama_lengkap') {
            $(this).attr('type', 'password');
        }
    });
    $('.toggle-password i').removeClass('fa-eye-slash').addClass('fa-eye');
}

// Show success message if password was changed
<?php if ($success): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: 'Password berhasil diubah',
    confirmButtonColor: '#719edd',
    timer: 3000,
    timerProgressBar: true
});
<?php endif; ?>

// Show error message if there was an error
<?php if ($error): ?>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '<?= addslashes($error) ?>',
    confirmButtonColor: '#719edd'
});
<?php endif; ?>
</script>
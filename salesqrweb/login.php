<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/includes/auth.php';

$successpengaturan = '';
$errorpengaturan = '';

$querypengaturansss = "SELECT logo_toko, nama_toko FROM pengaturan LIMIT 1";
$stmtpengaturansss = $db->prepare($querypengaturansss);
$stmtpengaturansss->execute();
$pengagturansss = $stmtpengaturansss->fetch(PDO::FETCH_ASSOC);

$logopengaturansss = '';
$namatokopengaturansss = APP_NAME;
if ($pengagturansss) {
    $namatokopengaturansss = $pengagturansss['nama_toko'] ?: APP_NAME;
    if (!empty($pengagturansss['logo_toko'])) {
        $logopengaturansss = $pengagturansss['logo_toko'];
    }
}

$error = '';

// Enhanced security: Rate limiting check
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
}

// Security: Check for too many attempts
$max_attempts = 5;
$lockout_time = 900; // 15 minutes
$current_time = time();

if ($_SESSION['login_attempts'] >= $max_attempts && 
    ($current_time - $_SESSION['last_attempt']) < $lockout_time) {
    $remaining_time = $lockout_time - ($current_time - $_SESSION['last_attempt']);
    $error = 'Terlalu banyak percobaan login. Coba lagi dalam ' . ceil($remaining_time / 60) . ' menit.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    // Enhanced input validation and sanitization
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    
    // Additional security checks
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } elseif (strlen($username) > 50 || strlen($password) > 100) {
        $error = 'Input terlalu panjang';
    } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
        $error = 'Username mengandung karakter tidak valid';
    } else {
        // Check rate limiting
        if ($_SESSION['login_attempts'] >= $max_attempts && 
            ($current_time - $_SESSION['last_attempt']) < $lockout_time) {
            $error = 'Akun sementara dikunci karena terlalu banyak percobaan login';
        } else {
            if (loginAdmin($username, $password, $db)) {
                // Reset attempts on successful login
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt'] = 0;
                
                redirect('admin/');
                exit;
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = $current_time;
                $error = 'Username atau password salah. Percobaan ke-' . $_SESSION['login_attempts'] . ' dari ' . $max_attempts;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . htmlspecialchars($namatokopengaturansss) : htmlspecialchars($namatokopengaturansss) ?></title>
    
    <link rel="icon" type="image/x-icon" href="~/asset/favicon.ico">

    <!--Bootstrap & FontAwesome-->
    <link type="text/css" href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
    <link type="text/css" href="vendor/fontawesome-free/css/fontawesome.min.css" rel="stylesheet" />
    <link type="text/css" href="vendor/fontawesome-free/css/solid.min.css" rel="stylesheet" />
    <link type="text/css" href="vendor/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!--Important Addition-->
    <link type="text/css" href="vendor/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet" />
    <link type="text/css" href="vendor/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link type="text/css" href="vendor/select2/dist/css/select2.min.css" rel="stylesheet" />

    <!--Custom Styling-->
    <!-- <link type="text/css" href="assets/css/sass.css" rel="stylesheet" />
    <link type="text/css" href="assets/css/layers.css" rel="stylesheet" />
    <link type="text/css" href="assets/css/style.css" rel="stylesheet" />
    <link type="text/css" href="assets/css/responsive.css" rel="stylesheet" /> -->

    <style>
        /* Login Page Specific Styles */
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #719edd 0%,rgb(168, 242, 255) 100%);
            position: relative;
            overflow: hidden;
        }

        /* Floating Background Animation */
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 70%;
            left: 80%;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            top: 40%;
            left: 70%;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.12);
            transform: rotate(45deg);
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            top: 20%;
            left: 60%;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            animation-delay: 1s;
        }

        .shape:nth-child(5) {
            top: 80%;
            left: 20%;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.06);
            transform: rotate(45deg);
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        /* Login Card Styles */
        .login-card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 450px;
            margin: 0 auto;
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            padding: 2rem 2rem 1rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .logo-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #719edd,rgb(28, 111, 255));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .logo-container i {
            font-size: 2rem;
            color: white;
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #718096;
            font-size: 0.9rem;
        }

        .login-form {
            padding: 2rem;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control {
            height: 55px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem 0.75rem 3rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: #719edd;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.1rem;
            z-index: 5;
            transition: color 0.3s ease;
        }

        .form-control:focus + .input-icon {
            color: #719edd;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            font-size: 1rem;
            z-index: 5;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #719edd;
        }

        .btn-login {
            width: 100%;
            height: 55px;
            background: linear-gradient(135deg, #719edd 0%,rgb(75, 152, 162) 100%);
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .alert-danger {
            background: linear-gradient(135deg, #fed7d7, #feb2b2);
            color: #c53030;
        }

        .security-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            text-align: center;
        }

        .security-info small {
            color: #719edf;
            font-size: 0.8rem;
        }

        .attempts-counter {
            text-align: center;
            margin-top: 1rem;
            padding: 0.5rem;
            background: rgba(255, 193, 7, 0.1);
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }

        .attempts-counter small {
            color: #856404;
            font-weight: 600;
        }

        /* Loading Animation */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-login.loading .loading-spinner {
            display: inline-block;
        }

        .btn-login.loading .btn-text {
            opacity: 0.7;
        }

        /* Back Link Styles */
        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            z-index: 20;
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-5px);
            color: white;
            text-decoration: none;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .back-link i {
            margin-right: 0.5rem;
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .back-link:hover i {
            transform: translateX(-3px);
        }
    </style>
</head>

<div class="login-container d-flex align-items-center justify-content-center">
    <!-- Back Link -->
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Kembali ke Beranda
    </a>

    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-card">
        <div class="login-header">
            <div class="logo-container">
                <?php if (!empty($logopengaturansss)): ?>
                    <img src="<?= htmlspecialchars($logopengaturansss) ?>" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php else: ?>
                    <i class="fas fa-shield-alt"></i>
                <?php endif; ?>
            </div>
            <h2 class="login-title"><?= htmlspecialchars($namatokopengaturansss) ?></h2>
            <p class="login-subtitle">Masuk ke panel admin</p>
        </div>

        <div class="login-form">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <div id="usernameerror"></div>
                <div class="form-group">
                    <input type="text" 
                           class="form-control" 
                           name="username" 
                           id="username"
                           placeholder="Username" 
                           required
                           autocomplete="username"
                           maxlength="50"
                           pattern="[a-zA-Z0-9_.-]+"
                           title="Hanya huruf, angka, titik, garis bawah, dan strip yang diperbolehkan">
                    <i class="fas fa-user input-icon"></i>
                </div>
                
                <div id="passworderror"></div>
                <div class="form-group">
                    <input type="password" 
                           class="form-control" 
                           name="password" 
                           id="password"
                           placeholder="Password" 
                           required
                           autocomplete="current-password"
                           maxlength="100">
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-login" id="loginBtn">
                    <div class="loading-spinner"></div>
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Masuk
                    </span>
                </button>
            </form>

            <?php if ($_SESSION['login_attempts'] > 0 && $_SESSION['login_attempts'] < $max_attempts): ?>
                <div class="attempts-counter">
                    <small>
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Percobaan login: <?= $_SESSION['login_attempts'] ?>/<?= $max_attempts ?>
                    </small>
                </div>
            <?php endif; ?>

            <div class="security-info">
                <small>
                    <i class="fas fa-shield-alt me-1"></i>
                    Login dilindungi dengan enkripsi dan rate limiting
                </small>
            </div>
        </div>
    </div>
</div>

<!--Scripts-->
<script type="text/javascript" src="vendor/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="vendor/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="vendor/highcharts/highcharts.js"></script>
<script type="text/javascript" src="vendor/highcharts/highcharts-more.js"></script>
<script type="text/javascript" src="vendor/highcharts/modules/exporting.js"></script>
<script type="text/javascript" src="vendor/highcharts/modules/export-data.js"></script>
<script type="text/javascript" src="vendor/highcharts/modules/accessibility.js"></script>
<script type="text/javascript" src="vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="vendor/select2/dist/js/select2.full.min.js"></script>
<script type="text/javascript" src="vendor/fontawesome-free/js/all.min.js"></script>
<script type="text/javascript" src="vendor/fontawesome-free/js/fontawesome.min.js"></script>
<script type="text/javascript" src="vendor/fontawesome-free/js/solid.min.js"></script>

<script>
$(document).ready(function() {
    // Password Toggle Functionality
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const toggleIcon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Enhanced Form Validation
    $('#loginForm').on('submit', function(e) {
        const username = $('#username').val().trim();
        const password = $('#password').val();
        const loginBtn = $('#loginBtn');
        
        // Reset previous validation states
        $('.form-control').removeClass('is-invalid is-valid');
        
        let isValid = true;
        
        // Username validation
        if (username === '') {
            $('#username').addClass('is-invalid');
            showFieldError('username', 'Username harus diisi');
            isValid = false;
        } else if (username.length > 50) {
            $('#username').addClass('is-invalid');
            showFieldError('username', 'Username terlalu panjang');
            isValid = false;
        } else if (!/^[a-zA-Z0-9_.-]+$/.test(username)) {
            $('#username').addClass('is-invalid');
            showFieldError('username', 'Username mengandung karakter tidak valid');
            isValid = false;
        } else {
            $('#username').addClass('is-valid');
            // Clear error message
            showFieldError('username', null);
        }
        
        // Password validation
        if (password === '') {
            $('#password').addClass('is-invalid');
            showFieldError('password', 'Password harus diisi');
            isValid = false;
        } else if (password.length > 100) {
            $('#password').addClass('is-invalid');
            showFieldError('password', 'Password terlalu panjang');
            isValid = false;
        } else if (password.length < 6) {
            $('#password').addClass('is-invalid');
            showFieldError('password', 'Password minimal 6 karakter');
            isValid = false;
        } else {
            $('#password').addClass('is-valid');
            // Clear error message
            showFieldError('password', null);
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Shake animation for invalid form
            $('.login-card').addClass('animate__animated animate__headShake');
            setTimeout(() => {
                $('.login-card').removeClass('animate__animated animate__headShake');
            }, 1000);
            
            return false;
        }
        
        // Show loading state
        loginBtn.addClass('loading').prop('disabled', true);
        $('.btn-text').text('Memproses...');
        
        // Small delay to show loading animation
        setTimeout(() => {
            // Form will submit naturally
        }, 500);
    });
    
    // Real-time input validation
    $('#username').on('input', function() {
        const value = $(this).val().trim();
        $(this).removeClass('is-invalid is-valid');
        
        if (value.length > 0) {
            if (value.length <= 50 && /^[a-zA-Z0-9_.-]+$/.test(value)) {
                $(this).addClass('is-valid');
                showFieldError('username', null);
            } else {
                $(this).addClass('is-invalid');
            }
        }
    });
    
    $('#password').on('input', function() {
        const value = $(this).val();
        $(this).removeClass('is-invalid is-valid');
        
        if (value.length > 0) {
            if (value.length >= 6 && value.length <= 100 && 
                !['password', '123456'].includes(value.toLowerCase())) {
                $(this).addClass('is-valid');
                showFieldError('username', null);
            } else {
                $(this).addClass('is-invalid');
            }
        }
    });
    
    // Auto-focus first input
    $('#username').focus();
    
    // Prevent form submission on Enter if invalid
    $('#username, #password').keypress(function(e) {
        if (e.which === 13) { // Enter key
            $('#loginForm').submit();
        }
    });
    
    function showFieldError(fieldId, message) {
        // Remove existing error message
        $(`#${fieldId}`).next('.invalid-feedback').remove();
        
        // Add new error message
        // Find the corresponding error element
        const errorElement = document.getElementById(`${fieldId}error`);
        
        if (errorElement) {
            if (message) {
                // Show error message with styling
                errorElement.innerHTML = message;
                errorElement.style.display = 'block';
                errorElement.style.color = '#dc3545';
                errorElement.style.fontSize = '0.875rem';
                errorElement.style.marginTop = '0.25rem';
                errorElement.classList.add('active-error');
            } else {
                // Clear error message
                errorElement.innerHTML = '';
                errorElement.style.display = 'none';
                errorElement.classList.remove('active-error');
            }
        }
    }
    
    // Enhanced security: Disable right-click and F12
    $(document).contextmenu(function() {
        return false;
    });
    
    $(document).keydown(function(e) {
        // Disable F12, Ctrl+Shift+I, Ctrl+U
        if (e.keyCode === 123 || 
            (e.ctrlKey && e.shiftKey && e.keyCode === 73) || 
            (e.ctrlKey && e.keyCode === 85)) {
            return false;
        }
    });
    
    // Show SweetAlert for successful actions (if needed)
    <?php if (isset($_SESSION['success_message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= $_SESSION['success_message'] ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
});

// Additional security: Clear form data when page is hidden
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Clear sensitive data when tab is not visible
        setTimeout(() => {
            if (document.hidden) {
                $('#password').val('');
            }
        }, 30000); // Clear after 30 seconds of inactivity
    }
});

// Password strength indicator
function checkPasswordStrength(password) {
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Character variety
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Common passwords check
    const commonPasswords = ['password', '123456', 'admin', 'user'];
    if (commonPasswords.includes(password.toLowerCase())) {
        strength = 0;
    }
    
    return strength;
}
</script>

</html>
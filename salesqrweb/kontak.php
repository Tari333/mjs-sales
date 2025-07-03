<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Kontak Kami';
$prefixlogo = '';

// Get store settings
$query = "SELECT * FROM pengaturan WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<style>
/* Smooth animations and hover effects */
.card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(45, 55, 72, 0.15);
}

.btn {
    border-radius: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: none;
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

.btn-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
    background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
}

.btn-outline-primary {
    border: 2px solid #719edd;
    color: #719edd;
    background: transparent;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.3);
}

.welcome-section {
    background: linear-gradient(
        135deg,
        rgba(113, 158, 221, 0.9) 25%,
        rgba(113, 158, 221, 0.5) 100%
    );
    border-radius: 25px;
    padding: 40px 20px;
    margin-bottom: 30px;
    color: #ffffff;
    box-shadow: 0 15px 35px rgba(113, 158, 221, 0.2);
}

.contact-info {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    padding: 25px;
    border-radius: 20px;
    margin-bottom: 20px;
    border-left: 5px solid #719edd;
}

.contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 15px;
    transition: all 0.3s ease;
}

.contact-item:hover {
    transform: translateX(10px);
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.1);
}

.contact-icon {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    font-size: 1.2rem;
}

.operating-hours {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    padding: 20px;
    border-radius: 15px;
    border-left: 5px solid #f56565;
    margin-top: 20px;
}

.nav-link-btn {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffffff;
    padding: 12px 24px;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
    margin: 5px;
    display: inline-block;
}

.nav-link-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 255, 255, 0.2);
    color: #ffffff;
}
</style>

<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-section text-center">
                <h2 class="fw-bold mb-3">
                    <i class="fas fa-phone-alt me-2"></i>
                    Kontak Kami
                </h2>
                
                <h3 class="my-3"><?= htmlspecialchars($settings['nama_toko'] ?? 'PT. Megah Jaya Sakti') ?></h3>
                <p class="mb-4 fs-5">Hubungi kami untuk informasi lebih lanjut</p>
                
                <!-- Navigation Links -->
                <div class="text-center">
                    <a href="index.php" class="nav-link-btn">
                        <i class="fas fa-home me-2"></i>
                        Beranda
                    </a>
                    <a href="tentang.php" class="nav-link-btn">
                        <i class="fas fa-info-circle me-2"></i>
                        Tentang Kami
                    </a>
                    <a href="user/produk.php" class="nav-link-btn">
                        <i class="fas fa-list me-2"></i>
                        Produk
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-4">
                        <i class="fas fa-address-book me-2 text-primary"></i>
                        Informasi Kontak
                    </h4>
                    
                    <div class="contact-info">
                        <!-- Store Name -->
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-store"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Nama Toko</h6>
                                <p class="mb-0 text-muted"><?= htmlspecialchars($settings['nama_toko'] ?? 'PT. Megah Jaya Sakti') ?></p>
                            </div>
                        </div>
                        
                        <!-- Address -->
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Alamat Lengkap</h6>
                                <p class="mb-0 text-muted"><?= htmlspecialchars($settings['alamat_toko'] ?? 'Jl. Sudirman, Batu Aji, Batam, Kepulauan Riau') ?></p>
                            </div>
                        </div>
                        
                        <!-- Phone Number -->
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Nomor Telepon/WhatsApp</h6>
                                <p class="mb-0 text-muted"><?= htmlspecialchars($settings['whatsapp_number'] ?? '089513914420') ?></p>
                                <a href="https://wa.me/<?= htmlspecialchars($settings['whatsapp_number'] ?? '089513914420') ?>" 
                                   class="btn btn-success btn-sm mt-2" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i>Chat WhatsApp
                                </a>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <?php if (!empty($settings['email_toko'])): ?>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <p class="mb-0 text-muted"><?= htmlspecialchars($settings['email_toko']) ?></p>
                                <a href="mailto:<?= htmlspecialchars($settings['email_toko']) ?>" 
                                   class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-envelope me-2"></i>Kirim Email
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-12">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-4">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Jam Operasional
                    </h4>
                    
                    <div class="operating-hours">
                        <div class="mb-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-calendar-day me-2"></i>
                                Senin - Jumat
                            </h6>
                            <p class="mb-0 fs-5 fw-bold text-success">10.00 - 17.00 WIB</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-calendar-week me-2"></i>
                                Sabtu - Minggu
                            </h6>
                            <p class="mb-0 fs-5 fw-bold text-warning">10.00 - 15.00 WIB</p>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Untuk pemesanan di luar jam operasional, silakan hubungi WhatsApp kami</small>
                        </div>
                    </div>
                    
                    <?php if (!empty($settings['no_rekening']) && !empty($settings['nama_bank'])): ?>
                    <div class="mt-4 p-3" style="background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%); border-radius: 15px;">
                        <h6 class="fw-bold">
                            <i class="fas fa-university me-2"></i>
                            Informasi Rekening
                        </h6>
                        <p class="mb-1"><strong>Bank:</strong> <?= htmlspecialchars($settings['nama_bank']) ?></p>
                        <p class="mb-1"><strong>No. Rekening:</strong> <?= htmlspecialchars($settings['no_rekening']) ?></p>
                        <p class="mb-0"><strong>Atas Nama:</strong> <?= htmlspecialchars($settings['atas_nama']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fas fa-rocket me-2 text-primary"></i>
                        Aksi Cepat
                    </h5>
                    <div class="d-flex justify-content-center flex-wrap gap-3">
                        <a href="https://wa.me/<?= htmlspecialchars($settings['whatsapp_number'] ?? '089513914420') ?>" 
                           class="btn btn-success" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>
                            Chat WhatsApp
                        </a>
                        <a href="user/produk.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>
                            Lihat Produk
                        </a>
                        <a href="tentang.php" class="btn btn-outline-primary">
                            <i class="fas fa-info-circle me-2"></i>
                            Tentang Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
// Add smooth scroll behavior for all anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add animation on scroll
window.addEventListener('scroll', function() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        const cardTop = card.getBoundingClientRect().top;
        const cardVisible = 150;
        
        if (cardTop < window.innerHeight - cardVisible) {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }
    });
});
</script>
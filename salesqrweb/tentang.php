<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tentang Kami';
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

.about-section {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    padding: 30px;
    border-radius: 20px;
    margin-bottom: 25px;
    border-left: 5px solid #719edd;
}

.feature-box {
    background: rgba(255, 255, 255, 0.9);
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid rgba(113, 158, 221, 0.1);
    height: 100%;
}

.feature-box:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(113, 158, 221, 0.2);
    border-color: #719edd;
}

.feature-icon {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: white;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 1.8rem;
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

.highlight-text {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: bold;
}

.stats-box {
    background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 20px;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #719edd;
    line-height: 1;
}

.company-info {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    padding: 25px;
    border-radius: 15px;
    border-left: 5px solid #f56565;
}
</style>

<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-section text-center">
                <h2 class="fw-bold mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Tentang Kami
                </h2>
                
                <h3 class="my-3"><?= htmlspecialchars($settings['nama_toko'] ?? 'PT. Megah Jaya Sakti') ?></h3>
                <p class="mb-4 fs-5">Mengenal lebih dekat dengan perusahaan kami</p>
                
                <!-- Navigation Links -->
                <div class="text-center">
                    <a href="index.php" class="nav-link-btn">
                        <i class="fas fa-home me-2"></i>
                        Beranda
                    </a>
                    <a href="kontak.php" class="nav-link-btn">
                        <i class="fas fa-phone me-2"></i>
                        Kontak Kami
                    </a>
                    <a href="user/produk.php" class="nav-link-btn">
                        <i class="fas fa-list me-2"></i>
                        Produk
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- About Company Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-4">
                        <i class="fas fa-building me-2 text-primary"></i>
                        Tentang Kami
                    </h4>
                    
                    <div class="about-section">
                        <p class="fs-5 mb-4">
                            <span class="highlight-text"><?= htmlspecialchars($settings['nama_toko'] ?? 'PT. Megah Jaya Sakti') ?></span> 
                            adalah usaha yang bergerak di bidang penjual berbagai jenis material bangunan yang berkualitas. 
                            Kami menyediakan berbagai perlengkapan bangunan.
                        </p>
                        
                        <p class="mb-4">
                            Melalui aplikasi penjualan berbasis web ini, kami berkomitmen memberikan kemudahan bagi pelanggan 
                            dalam melakukan pemesanan produk secara online dengan sistem QR Code.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="company-info">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informasi Perusahaan
                                    </h6>
                                    <p><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($settings['nama_toko'] ?? 'PT. Megah Jaya Sakti') ?></p>
                                    <p><strong>Alamat:</strong> <?= htmlspecialchars($settings['alamat_toko'] ?? 'Batam, Kepulauan Riau') ?></p>
                                    <p><strong>Telepon:</strong> <?= htmlspecialchars($settings['no_hp_toko'] ?? '6282269343968') ?></p>
                                    <?php if (!empty($settings['email_toko'])): ?>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($settings['email_toko']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-box">
                                    <div class="stats-number">2025</div>
                                    <p class="mb-0 fw-bold">Tahun Berdiri</p>
                                </div>
                                <div class="stats-box">
                                    <div class="stats-number">100%</div>
                                    <p class="mb-0 fw-bold">Kepuasan Pelanggan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-4 text-center">
                        <i class="fas fa-star me-2 text-primary"></i>
                        Keunggulan Kami
                    </h4>
                    
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="feature-box">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h5 class="fw-bold">Produk Berkualitas</h5>
                                <p class="text-muted">Kami menyediakan material bangunan dengan kualitas terbaik dan terjamin</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="feature-box">
                                <div class="feature-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <h5 class="fw-bold">Pengiriman Cepat</h5>
                                <p class="text-muted">Sistem pengiriman yang efisien dan tepat waktu ke seluruh wilayah</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="feature-box">
                                <div class="feature-icon">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <h5 class="fw-bold">Sistem QR Code</h5>
                                <p class="text-muted">Pemesanan modern dengan teknologi QR Code untuk kemudahan transaksi</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="feature-box">
                                <div class="feature-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <h5 class="fw-bold">Layanan 24/7</h5>
                                <p class="text-muted">Tim customer service siap membantu Anda kapan saja melalui WhatsApp</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vision & Mission Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-4">
                        <i class="fas fa-eye me-2 text-primary"></i>
                        Visi Kami
                    </h4>
                    <div class="about-section">
                        <p class="fs-5">
                            Menjadi perusahaan terdepan dalam penyediaan material bangunan berkualitas tinggi dengan 
                            pelayanan terbaik dan teknologi modern untuk memenuhi kebutuhan konstruksi di Indonesia.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-4">
                        <i class="fas fa-bullseye me-2 text-primary"></i>
                        Misi Kami
                    </h4>
                    <div class="about-section">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Menyediakan material bangunan berkualitas dengan harga kompetitif
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Memberikan pelayanan terbaik kepada setiap pelanggan
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Mengembangkan teknologi digital untuk kemudahan transaksi
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Membangun kepercayaan jangka panjang dengan mitra bisnis
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Call to Action Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="card-title fw-bold mb-4">
                        <i class="fas fa-handshake me-2 text-primary"></i>
                        Mari Berkerja Sama
                    </h4>
                    <p class="fs-5 mb-4">
                        Bergabunglah dengan ribuan pelanggan yang telah mempercayai kami sebagai partner terbaik 
                        untuk kebutuhan material bangunan Anda.
                    </p>
                    
                    <div class="d-flex justify-content-center flex-wrap gap-3">
                        <a href="https://wa.me/<?= htmlspecialchars($settings['whatsapp_number'] ?? '6282269343968') ?>" 
                           class="btn btn-success btn-lg" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>
                            Hubungi Kami
                        </a>
                        <a href="user/produk.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>
                            Lihat Produk
                        </a>
                        <a href="kontak.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Lokasi Kami
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

// Add animation on scroll for feature boxes
window.addEventListener('scroll', function() {
    const featureBoxes = document.querySelectorAll('.feature-box');
    featureBoxes.forEach((box, index) => {
        const boxTop = box.getBoundingClientRect().top;
        const boxVisible = 150;
        
        if (boxTop < window.innerHeight - boxVisible) {
            setTimeout(() => {
                box.style.opacity = '1';
                box.style.transform = 'translateY(0)';
            }, index * 100);
        }
    });
});

// Initialize feature boxes with hidden state
document.addEventListener('DOMContentLoaded', function() {
    const featureBoxes = document.querySelectorAll('.feature-box');
    featureBoxes.forEach(box => {
        box.style.opacity = '0';
        box.style.transform = 'translateY(30px)';
        box.style.transition = 'all 0.6s ease';
    });
});

// Counter animation for stats
function animateCounter(element, target) {
    let current = 0;
    const increment = target / 100;
    const timer = setInterval(() => {
        current += increment;
        element.textContent = Math.floor(current);
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        }
    }, 20);
}

// Trigger counter animation when stats come into view
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statsNumbers = entry.target.querySelectorAll('.stats-number');
            statsNumbers.forEach(stat => {
                if (stat.textContent === '2025') {
                    animateCounter(stat, 2025);
                } else if (stat.textContent === '100%') {
                    stat.textContent = '100%';
                }
            });
        }
    });
});

document.querySelectorAll('.stats-box').forEach(box => {
    observer.observe(box);
});
</script>
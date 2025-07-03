<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Beranda';
$prefixlogo = '';

// Get active products
$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.kategori_id = k.id 
          WHERE p.status = 'aktif' 
          ORDER BY p.created_at DESC LIMIT 4";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

.card-img-top {
    border-radius: 0;
    transition: transform 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
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

.btn-secondary {
    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
    border-radius: 15px;
}

.badge {
    border-radius: 25px;
    padding: 8px 16px;
    font-weight: 500;
    font-size: 0.75rem;
}

.price-tag {
    font-size: 1.5rem;
    font-weight: bold;
    color: #719edd;
    text-shadow: 0 2px 4px rgba(113, 158, 221, 0.2);
}

.stock-info {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 0.9rem;
    color: #4a5568;
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

.nav-links {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 25px;
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
}

.nav-link-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.scroll-down-btn {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.scroll-down-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(113, 158, 221, 0.3);
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%);
}

.alert {
    border-radius: 20px;
    border: none;
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.08);
}
</style>

<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="welcome-section text-center d-flex flex-column justify-content-center align-items-center">
                <h2 class="fw-bold mb-3">
                    <i class="fas fa-boxes me-2"></i>
                    Welcome to the Catalog Home of our Website !!
                </h2>
                <?php if (!empty($logopengaturansss) && file_exists($pengagturansss['logo_toko'])): ?>
                    <img src="<?= $logopengaturansss ?>" alt="<?= htmlspecialchars($namatokopengaturansss) ?>" class="me-2" style="height: 100px; width: auto;">
                <?php else: ?>
                    <i class="fas fa-store me-2"></i>
                <?php endif; ?>
                <h2 class="my-4"><b><?= htmlspecialchars($namatokopengaturansss) ?></b></h2> 
                <p class="mb-4 fs-5">Pilih produk yang ingin Anda beli</p>
                
                <!-- Navigation Links -->
                <div class="nav-links">
                    <a href="tentang.php" class="nav-link-btn">
                        <i class="fas fa-info-circle me-2"></i>
                        Tentang Kami
                    </a>
                    <a href="contact.php" class="nav-link-btn">
                        <i class="fas fa-envelope me-2"></i>
                        Hubungi Kami
                    </a>
                    <button onclick="scrollToProducts()" class="scroll-down-btn">
                        <i class="fas fa-arrow-down me-2"></i>
                        Lihat Produk Terbaru
                    </button>
                    <a href="user/produk.php" class="nav-link-btn">
                        <i class="fas fa-list me-2"></i>
                        Daftar Lengkap Produk
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products Section -->
    <div class="row" id="products-section">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Belum ada produk yang tersedia
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100">
                        <?php if ($product['gambar']): ?>
                            <img src="<?= BASE_URL ?>assets/uploads/products/<?= $product['gambar'] ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($product['nama_produk']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <?php if ($product['nama_kategori']): ?>
                                <span class="badge bg-primary mb-2"><?= $product['nama_kategori'] ?></span>
                            <?php endif; ?>
                            
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($product['nama_produk']) ?></h5>
                            <div class="price-tag mb-2"><?= formatRupiah($product['harga']) ?></div>
                            
                            <div class="stock-info mb-3">
                                <i class="fas fa-cubes me-1"></i>
                                Stok: <?= $product['stok'] ?> pcs
                            </div>
                            
                            <?php if ($product['deskripsi']): ?>
                                <p class="card-text text-muted small"><?= htmlspecialchars(substr($product['deskripsi'], 0, 100)) ?>...</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-transparent border-0">
                            <?php if ($product['stok'] > 0): ?>
                                <a href="user/pesan.php?id=<?= $product['id'] ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Pesan Sekarang
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-times me-2"></i>
                                    Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
function scrollToProducts() {
    document.getElementById('products-section').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

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
</script>
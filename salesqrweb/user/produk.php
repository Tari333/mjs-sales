<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Semua Produk';
$prefixlogo = '../';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';

// Build query
$whereConditions = ["p.status = 'aktif'"];
$params = [];

if ($search) {
    $whereConditions[] = "(p.nama_produk LIKE :search OR p.deskripsi LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($kategori_id > 0) {
    $whereConditions[] = "p.kategori_id = :kategori_id";
    $params[':kategori_id'] = $kategori_id;
}

$whereClause = implode(' AND ', $whereConditions);

// Sorting options
$orderBy = match($sort) {
    'nama_asc' => 'p.nama_produk ASC',
    'nama_desc' => 'p.nama_produk DESC',
    'harga_asc' => 'p.harga ASC',
    'harga_desc' => 'p.harga DESC',
    'stok_asc' => 'p.stok ASC',
    'stok_desc' => 'p.stok DESC',
    default => 'p.created_at DESC'
};

// Get products
$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.kategori_id = k.id 
          WHERE $whereClause 
          ORDER BY $orderBy";
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$kategoris = $db->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$countQuery = "SELECT COUNT(*) FROM produk p WHERE $whereClause";
$countStmt = $db->prepare($countQuery);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
/* Product Cards */
.product-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(45, 55, 72, 0.15);
}

.product-image {
    height: 180px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.03);
}

.product-placeholder {
    height: 180px;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Buttons */
.btn {
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
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

.btn-outline-primary {
    border: 2px solid #719edd;
    color: #719edd;
    background: transparent;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(113, 158, 221, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.875rem;
}

/* Badges */
.badge {
    border-radius: 20px;
    padding: 6px 12px;
    font-weight: 500;
    font-size: 0.7rem;
}

/* Price and Stock */
.price-tag {
    font-size: 1.25rem;
    font-weight: bold;
    color: #719edd;
    text-shadow: 0 1px 3px rgba(113, 158, 221, 0.2);
}

.stock-badge {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    color: #4a5568;
    border-radius: 10px;
    padding: 4px 8px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Search and Filter Section */
.filter-section {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
}

.form-control, .form-select {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #719edd;
    box-shadow: 0 0 0 0.2rem rgba(113, 158, 221, 0.25);
}

/* Results Header */
.results-header {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.1) 0%, rgba(113, 158, 221, 0.05) 100%);
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 20px;
    border-left: 4px solid #719edd;
}

/* Pagination */
.pagination .page-link {
    border-radius: 8px;
    border: none;
    color: #719edd;
    background: transparent;
    margin: 0 2px;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    color: white;
    transform: translateY(-2px);
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border-color: transparent;
}
</style>

<div class="container-fluid py-2">
    <!-- Search and Filter Section -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="filter-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-search me-1"></i>
                            Cari Produk
                        </label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nama produk atau deskripsi..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-tags me-1"></i>
                            Kategori
                        </label>
                        <select name="kategori" class="form-select">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategoris as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>" <?= $kategori_id == $kategori['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kategori['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-sort me-1"></i>
                            Urutkan
                        </label>
                        <select name="sort" class="form-select">
                            <option value="terbaru" <?= $sort == 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                            <option value="nama_asc" <?= $sort == 'nama_asc' ? 'selected' : '' ?>>Nama A-Z</option>
                            <option value="nama_desc" <?= $sort == 'nama_desc' ? 'selected' : '' ?>>Nama Z-A</option>
                            <option value="harga_asc" <?= $sort == 'harga_asc' ? 'selected' : '' ?>>Harga Terendah</option>
                            <option value="harga_desc" <?= $sort == 'harga_desc' ? 'selected' : '' ?>>Harga Tertinggi</option>
                            <option value="stok_desc" <?= $sort == 'stok_desc' ? 'selected' : '' ?>>Stok Terbanyak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="results-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">
                            <i class="fas fa-cube me-2"></i>
                            Menampilkan <?= count($products) ?> dari <?= $totalProducts ?> produk
                        </h5>
                        <?php if ($search || $kategori_id): ?>
                            <p class="mb-0 text-muted">
                                <?php if ($search): ?>
                                    Pencarian: "<strong><?= htmlspecialchars($search) ?></strong>"
                                <?php endif; ?>
                                <?php if ($kategori_id): ?>
                                    <?php $kategori_name = array_filter($kategoris, fn($k) => $k['id'] == $kategori_id)[0]['nama_kategori'] ?? ''; ?>
                                    <?php if ($search): ?> | <?php endif; ?>
                                    Kategori: <strong><?= htmlspecialchars($kategori_name) ?></strong>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php if ($search || $kategori_id): ?>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-times me-1"></i>
                            Reset Filter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Tidak ada produk ditemukan</h4>
                    <p class="text-muted">Coba ubah kata kunci pencarian atau filter kategori</p>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-primary">
                        <i class="fas fa-refresh me-2"></i>
                        Lihat Semua Produk
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
                    <div class="product-card">
                        <?php if ($product['gambar']): ?>
                            <img src="<?= BASE_URL ?>assets/uploads/products/<?= $product['gambar'] ?>" 
                                 class="product-image w-100" 
                                 alt="<?= htmlspecialchars($product['nama_produk']) ?>">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <i class="fas fa-image fa-2x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <?php if ($product['nama_kategori']): ?>
                                    <span class="badge bg-primary"><?= $product['nama_kategori'] ?></span>
                                <?php endif; ?>
                                <span class="stock-badge">
                                    <i class="fas fa-cubes me-1"></i>
                                    <?= $product['stok'] ?>
                                </span>
                            </div>
                            
                            <h6 class="card-title fw-bold mb-2" title="<?= htmlspecialchars($product['nama_produk']) ?>">
                                <?= htmlspecialchars(strlen($product['nama_produk']) > 50 ? substr($product['nama_produk'], 0, 50) . '...' : $product['nama_produk']) ?>
                            </h6>
                            
                            <div class="price-tag mb-2"><?= formatRupiah($product['harga']) ?></div>
                            
                            <?php if ($product['deskripsi']): ?>
                                <p class="card-text text-muted small mb-3">
                                    <?= htmlspecialchars(strlen($product['deskripsi']) > 80 ? substr($product['deskripsi'], 0, 80) . '...' : $product['deskripsi']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-transparent border-0 p-3 pt-0">
                            <div class="d-grid">
                                <?php if ($product['stok'] > 0): ?>
                                    <a href="pesan.php?id=<?= $product['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        Pesan Sekarang
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-times me-2"></i>
                                        Stok Habis
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-4" 
            style="display: none; z-index: 1000; border-radius: 50%; width: 50px; height: 50px;">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Back to top button
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });

    $('#backToTop').click(function() {
        $('html, body').animate({scrollTop: 0}, 300);
        return false;
    });

    // Initialize Select2 for better dropdowns
    if (typeof $.fn.select2 !== 'undefined') {
        $('.form-select').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }

    // Add loading state to filter form
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i> Mencari...');
    });

    // Smooth scroll for internal links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 20
            }, 300);
        }
    });

    // Add animation to cards on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all product cards
    $('.product-card').each(function() {
        this.style.opacity = '0';
        this.style.transform = 'translateY(20px)';
        this.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(this);
    });
});
</script>
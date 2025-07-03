<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

requireLogin();
$pageTitle = 'Manajemen Pengguna';
$showSidebar = true;
$prefixlogo = '../../';

// Get all users
$query = "SELECT * FROM admin ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user statistics
$queryStats = "SELECT COUNT(*) as total_pengguna FROM admin";
$stmtStats = $db->prepare($queryStats);
$stmtStats->execute();
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<style>
.user-stats-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}
.user-stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}
.user-stats-card.bg-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%) !important;
}
.user-header {
    background: linear-gradient(135deg, rgba(113, 158, 221, 0.9) 25%, rgba(113, 158, 221, 0.5) 100%);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 25px;
    color: #ffffff;
    box-shadow: 0 8px 25px rgba(113, 158, 221, 0.2);
}
.btn-gradient-primary {
    background: linear-gradient(135deg, #719edd 0%, #90b4e6 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 3px 12px rgba(113, 158, 221, 0.3);
}
.btn-gradient-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(113, 158, 221, 0.4);
    background: linear-gradient(135deg, #90b4e6 0%, #719edd 100%);
    color: white;
}
.card-modern {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(45, 55, 72, 0.08);
    transition: all 0.3s ease;
}
.card-modern:hover {
    box-shadow: 0 8px 25px rgba(45, 55, 72, 0.12);
}
.table-modern th {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border: none;
    color: #4a5568;
    font-weight: 600;
    padding: 15px;
}
.table-modern td {
    padding: 12px 15px;
    vertical-align: middle;
    border-color: #e2e8f0;
}
.table-modern tbody tr:hover {
    background-color: rgba(113, 158, 221, 0.05);
}
.btn-action {
    border-radius: 8px;
    padding: 6px 10px;
    margin: 0 2px;
    transition: all 0.3s ease;
}
.btn-action:hover {
    transform: translateY(-1px);
}
</style>

<div class="container-fluid">
    <!-- User Header -->
    <div class="user-header text-center">
        <h2 class="fw-bold mb-2">
            <i class="fas fa-users me-2"></i> Manajemen Pengguna
        </h2>
        <p class="mb-3 fs-6">Kelola semua pengguna sistem Anda dengan mudah</p>
        <a href="tambah.php" class="btn btn-gradient-primary btn-lg">
            <i class="fas fa-plus me-2"></i> Tambah Pengguna Baru
        </a>
    </div>

    <!-- User Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="user-stats-card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Pengguna</h6>
                            <h3 class="mb-0"><?= $stats['total_pengguna'] ?></h3>
                        </div>
                        <i class="fas fa-user fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card-modern p-3">
        <div class="card-header bg-light mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list me-2"></i> Daftar Pengguna
                </h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Belum ada pengguna</p>
                                <a href="tambah.php" class="btn btn-gradient-primary mt-2">
                                    <i class="fas fa-plus me-1"></i> Tambah Pengguna Pertama
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $index => $user): ?>
                        <tr class="text-center align-middle align-items-center">
                            <td class="fw-bold"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary btn-action" title="Edit Pengguna">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger btn-action confirm-delete" 
                                            title="Hapus Pengguna" data-id="<?= $user['id'] ?>" 
                                            data-name="<?= htmlspecialchars($user['username']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#usersTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [5] } // Disable sorting for action column
        ],
        language: {
            search: "Cari Pengguna:",
            lengthMenu: "Tampilkan _MENU_ pengguna per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengguna",
            infoEmpty: "Tidak ada pengguna",
            infoFiltered: "(difilter dari _MAX_ total pengguna)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Belum ada pengguna"
        }
    });

    // Delete confirmation
    $('.confirm-delete').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus Pengguna',
            html: `Yakin ingin menghapus pengguna <strong>${userName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#a0aec0',
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
            customClass: {
                popup: 'swal-wide'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `hapus.php?id=${userId}`;
            }
        });
    });

    // Animate cards on load
    $('.user-stats-card').each(function(index) {
        $(this).css('opacity', '0').css('transform', 'translateY(20px)');
        $(this).delay(index * 100).animate({opacity: 1}, 500, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
});
</script>
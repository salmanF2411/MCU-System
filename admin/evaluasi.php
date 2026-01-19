<?php
$page_title = 'Evaluasi Feedback - Sistem MCU';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

// Check if authorized roles (super admin, doctors, etc.)
$authorized_roles = ['super_admin', 'dokter_mata', 'dokter_umum', 'pendaftaran'];
if (!in_array($_SESSION['role'], $authorized_roles)) {
    header('Location: dashboard.php');
    exit();
}

// Handle mark as read
if (isset($_POST['mark_read']) && isset($_POST['feedback_id'])) {
    $feedback_id = (int)$_POST['feedback_id'];
    $query = "UPDATE feedback_pasien SET status = 'read' WHERE id = $feedback_id";
    mysqli_query($conn, $query);
    $_SESSION['success'] = 'Feedback telah ditandai sebagai sudah dibaca.';
    header('Location: evaluasi.php');
    exit();
}

// Handle delete feedback
if (isset($_POST['delete_feedback']) && isset($_POST['feedback_id'])) {
    $feedback_id = (int)$_POST['feedback_id'];
    $query = "DELETE FROM feedback_pasien WHERE id = $feedback_id";
    mysqli_query($conn, $query);
    $_SESSION['success'] = 'Feedback telah dihapus.';
    header('Location: evaluasi.php');
    exit();
}

// Get feedback statistics
$total_feedback_query = "SELECT COUNT(*) as total FROM feedback_pasien";
$total_feedback_result = mysqli_query($conn, $total_feedback_query);
$total_feedback = mysqli_fetch_assoc($total_feedback_result)['total'];

$unread_feedback_query = "SELECT COUNT(*) as total FROM feedback_pasien WHERE status = 'unread'";
$unread_feedback_result = mysqli_query($conn, $unread_feedback_query);
$unread_feedback = mysqli_fetch_assoc($unread_feedback_result)['total'];

$avg_rating_query = "SELECT AVG(rating) as avg_rating FROM feedback_pasien";
$avg_rating_result = mysqli_query($conn, $avg_rating_query);
$avg_rating = round(mysqli_fetch_assoc($avg_rating_result)['avg_rating'], 1);

// Get all feedback
$query = "SELECT f.*, p.nama as nama_pasien_real FROM feedback_pasien f
          LEFT JOIN pasien p ON f.pasien_id = p.id
          ORDER BY f.tanggal_submit DESC";
$result = mysqli_query($conn, $query);
?>

<?php include '../includes/admin-header.php'; ?>
<?php include 'includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include 'includes/admin-sidebar.php'; ?>
        </div>
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-comments me-2"></i> Evaluasi Feedback Pasien
                </h1>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Feedback
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_feedback; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-comments fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Belum Dibaca
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $unread_feedback; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-envelope fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Rata-rata Rating
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $avg_rating; ?>/5
                                        <small class="d-block">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $avg_rating ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-warning"></i>';
                                            }
                                            ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-star fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Sudah Dibaca
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_feedback - $unread_feedback; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Feedback Pasien</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>Rating</th>
                                        <th>Kesan</th>
                                        <th>Saran</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php while ($feedback = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo formatDateIndo($feedback['tanggal_submit']); ?></td>
                                            <td>
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $feedback['rating'] ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted"></i>';
                                                }
                                                ?>
                                                <br><small class="text-muted"><?php echo $feedback['rating']; ?>/5</small>
                                            </td>
                                            <td>
                                                <?php echo $feedback['kesan'] ? htmlspecialchars(substr($feedback['kesan'], 0, 50)) . (strlen($feedback['kesan']) > 50 ? '...' : '') : '-'; ?>
                                            </td>
                                            <td>
                                                <?php echo $feedback['saran'] ? htmlspecialchars(substr($feedback['saran'], 0, 50)) . (strlen($feedback['saran']) > 50 ? '...' : '') : '-'; ?>
                                            </td>
                                            <td>
                                                <?php if ($feedback['status'] == 'unread'): ?>
                                                    <span class="badge bg-warning">Belum Dibaca</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Sudah Dibaca</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $feedback['id']; ?>" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($feedback['status'] == 'unread'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                                            <button type="submit" name="mark_read" class="btn btn-sm btn-outline-success" onclick="return confirm('Tandai feedback ini sebagai sudah dibaca?')" title="Tandai sebagai Dibaca">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                                            <button type="submit" name="delete_feedback" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus feedback ini?')" title="Hapus Feedback">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Detail Modal -->
                                        <div class="modal fade" id="detailModal<?php echo $feedback['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Feedback</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Informasi Pasien</h6>
                                                                <p><strong>Nama:</strong> <?php echo htmlspecialchars($feedback['nama_pasien']); ?></p>
                                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($feedback['email']); ?></p>
                                                                <p><strong>Kode MCU:</strong> <?php echo $feedback['kode_mcu'] ?: '-'; ?></p>
                                                                <p><strong>Tanggal Submit:</strong> <?php echo formatDateIndo($feedback['tanggal_submit']); ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Rating</h6>
                                                                <div class="mb-3">
                                                                    <?php
                                                                    for ($i = 1; $i <= 5; $i++) {
                                                                        echo $i <= $feedback['rating'] ? '<i class="fas fa-star text-warning fa-lg"></i>' : '<i class="far fa-star text-muted fa-lg"></i>';
                                                                    }
                                                                    ?>
                                                                    <span class="ms-2"><?php echo $feedback['rating']; ?>/5</span>
                                                                </div>
                                                                <p><strong>Status:</strong>
                                                                    <?php if ($feedback['status'] == 'unread'): ?>
                                                                        <span class="badge bg-warning">Belum Dibaca</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-success">Sudah Dibaca</span>
                                                                    <?php endif; ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Kesan</h6>
                                                                <p><?php echo nl2br(htmlspecialchars($feedback['kesan'])) ?: '-'; ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Saran</h6>
                                                                <p><?php echo nl2br(htmlspecialchars($feedback['saran'])) ?: '-'; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        <?php if ($feedback['status'] == 'unread'): ?>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                                                <button type="submit" name="mark_read" class="btn btn-success">Tandai sebagai Dibaca</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Belum ada feedback yang diterima.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>

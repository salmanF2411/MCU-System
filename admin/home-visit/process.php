<?php
$page_title = 'Proses Home Visit - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? escape($_GET['search']) : '';

// Build query
$where = "1=1";

if ($search) {
    $where .= " AND (nama_pasien LIKE '%$search%' OR keluhan LIKE '%$search%')";
}

if ($status != 'all') {
    $where .= " AND hv.status = '$status'";
}

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records
$total_query = "SELECT COUNT(*) as total FROM home_visit hv WHERE $where";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Get home visit registrations
$query = "SELECT hv.*, hvs.judul_layanan, hvs.harga as harga_setting
          FROM home_visit hv
          LEFT JOIN home_visit_setting hvs ON hv.id_setting = hvs.id_setting
          WHERE $where
          ORDER BY hv.created_at DESC
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
?>

<?php include '../../includes/admin-header.php'; ?>
<?php include '../includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include '../includes/admin-sidebar.php'; ?>
        </div>
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-home me-2"></i> Proses Home Visit
                </h1>
            </div>

            <!-- Filter and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Cari nama pasien atau keluhan..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="diproses" <?php echo $status == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                <option value="selesai" <?php echo $status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="batal" <?php echo $status == 'batal' ? 'selected' : ''; ?>>Batal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="process.php" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Registrations Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        Daftar Pendaftaran Home Visit
                        <span class="badge bg-primary ms-2"><?php echo $total_records; ?> pendaftaran</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Nama Pasien</th>
                                        <th>Layanan</th>
                                        <th>Keluhan</th>
                                        <th>Alamat</th>
                                        <th width="120">Harga</th>
                                        <th width="100">Status</th>
                                        <th width="120">Tanggal Daftar</th>
                                        <th width="120">Tanggal Kunjungan</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = $offset + 1; ?>
                                    <?php while ($registration = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($registration['nama_pasien']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($registration['judul_layanan']); ?></td>
                                            <td>
                                                <?php echo substr(strip_tags($registration['keluhan']), 0, 50) . '...'; ?>
                                            </td>
                                            <td>
                                                <?php echo substr(strip_tags($registration['alamat_visit']), 0, 50) . '...'; ?>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    Rp <?php echo number_format($registration['harga'], 0, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($registration['status']) {
                                                    case 'pending': $status_class = 'bg-warning'; break;
                                                    case 'diproses': $status_class = 'bg-info'; break;
                                                    case 'selesai': $status_class = 'bg-success'; break;
                                                    case 'batal': $status_class = 'bg-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($registration['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo formatDateIndo($registration['created_at'], true); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo $registration['tanggal_kunjungan'] ? formatDateIndo($registration['tanggal_kunjungan']) : '-'; ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                            class="btn btn-info"
                                                            onclick="viewDetails(<?php echo $registration['id_visit']; ?>)"
                                                            title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($registration['status'] == 'pending'): ?>
                                                        <button type="button"
                                                                class="btn btn-success"
                                                                onclick="updateStatus(<?php echo $registration['id_visit']; ?>, 'diproses')"
                                                                title="Proses">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    <?php elseif ($registration['status'] == 'diproses'): ?>
                                                        <button type="button"
                                                                class="btn btn-success"
                                                                onclick="updateStatus(<?php echo $registration['id_visit']; ?>, 'selesai')"
                                                                title="Selesai">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button"
                                                            class="btn btn-danger"
                                                            onclick="updateStatus(<?php echo $registration['id_visit']; ?>, 'batal')"
                                                            title="Batal">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                &laquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                &raquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Tidak ada pendaftaran home visit.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i> Detail Pendaftaran Home Visit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(id) {
    fetch(`get-detail.php?id=${id}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('detailContent').innerHTML = data;
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        })
        .catch(error => {
            alert('Error loading details: ' + error);
        });
}

function updateStatus(id, status) {
    if (confirm(`Apakah Anda yakin ingin mengubah status menjadi "${status}"?`)) {
        fetch('update-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error updating status: ' + error);
        });
    }
}
</script>

<?php include '../../includes/admin-footer.php'; ?>

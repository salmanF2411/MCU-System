<?php
$page_title = 'Daftar Pasien - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

// Check role permissions - allow all medical staff roles
$authorized_roles = ['super_admin', 'pendaftaran', 'dokter_mata', 'dokter_umum'];
if (!in_array($_SESSION['role'], $authorized_roles)) {
    header('Location: ../dashboard.php');
    exit();
}

// Filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? escape($_GET['search']) : '';

// Build query
$where = "1=1";

if ($search) {
    $where .= " AND (p.nama LIKE '%$search%' OR p.kode_mcu LIKE '%$search%' OR p.no_telp LIKE '%$search%')";
}

if ($filter != 'all') {
    $where .= " AND p.status_pendaftaran = '$filter'";
}

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records
$total_query = "SELECT COUNT(*) as total FROM pasien p WHERE $where";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Get patients
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM pemeriksaan WHERE pasien_id = p.id AND pemeriksa_role = 'pendaftaran') as cek_pendaftaran,
          (SELECT COUNT(*) FROM pemeriksaan WHERE pasien_id = p.id AND pemeriksa_role = 'dokter_mata') as cek_mata,
          (SELECT COUNT(*) FROM pemeriksaan WHERE pasien_id = p.id AND pemeriksa_role = 'dokter_umum') as cek_umum
          FROM pasien p 
          WHERE $where 
          ORDER BY p.created_at DESC 
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
                    <i class="fas fa-users me-2"></i> Daftar Pasien
                </h1>
                <!-- <div>
                    <?php if (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin'): ?>
                    <a href="javascript:void(0)" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                        <i class="fas fa-plus me-2"></i> Tambah Pasien
                    </a>
                    <?php endif; ?>
                </div> -->
            </div>
            
            <!-- Filter and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Cari nama/kode MCU/telp..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="filter" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="menunggu" <?php echo $filter == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="proses" <?php echo $filter == 'proses' ? 'selected' : ''; ?>>Proses</option>
                                <option value="selesai" <?php echo $filter == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="<?php echo ADMIN_URL; ?>/pasien/list.php" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Patients Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        Data Pasien
                        <span class="badge bg-primary ms-2"><?php echo $total_records; ?> data</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Kode MCU</th>
                                        <th>Nama</th>
                                        <th>Usia</th>
                                        <th>Perusahaan</th>
                                        <th>Tanggal MCU</th>
                                        <th>Status</th>
                                        <th>Pemeriksaan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = $offset + 1; ?>
                                    <?php while ($patient = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <strong><?php echo $patient['kode_mcu']; ?></strong>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($patient['nama']); ?></div>
                                                <small class="text-muted"><?php echo $patient['no_telp']; ?></small>
                                            </td>
                                            <td><?php echo $patient['usia']; ?> thn</td>
                                            <td><?php echo $patient['perusahaan'] ?: '-'; ?></td>
                                            <td><?php echo formatDateIndo($patient['tanggal_mcu']); ?></td>
                                            <td><?php echo getStatusBadge($patient['status_pendaftaran']); ?></td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <span class="badge <?php echo $patient['cek_pendaftaran'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                        P
                                                    </span>
                                                    <span class="badge <?php echo $patient['cek_mata'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                        M
                                                    </span>
                                                    <span class="badge <?php echo $patient['cek_umum'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                        U
                                                    </span>
                                                </div>
                                                <small class="d-block text-muted">P=Reg, M=Mata, U=Umum</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo ADMIN_URL; ?>/pasien/detail.php?id=<?php echo $patient['id']; ?>"
                                                       class="btn btn-info"
                                                       title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin'): ?>
                                                        <?php if (!$patient['cek_pendaftaran']): ?>
                                                        <a href="<?php echo ADMIN_URL; ?>/pasien/pemeriksaan.php?role=pendaftaran&id=<?php echo $patient['id']; ?>"
                                                           class="btn btn-warning"
                                                           title="Pemeriksaan Pendaftaran">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (hasRole('dokter_mata') || $_SESSION['role'] == 'super_admin'): ?>
                                                        <?php if ($patient['cek_pendaftaran'] && !$patient['cek_mata']): ?>
                                                        <a href="<?php echo ADMIN_URL; ?>/pasien/pemeriksaan.php?role=dokter_mata&id=<?php echo $patient['id']; ?>"
                                                           class="btn btn-primary"
                                                           title="Pemeriksaan Mata">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (hasRole('dokter_umum') || $_SESSION['role'] == 'super_admin'): ?>
                                                        <?php if ($patient['cek_mata'] && !$patient['cek_umum']): ?>
                                                        <a href="<?php echo ADMIN_URL; ?>/pasien/pemeriksaan.php?role=dokter_umum&id=<?php echo $patient['id']; ?>"
                                                           class="btn btn-success"
                                                           title="Pemeriksaan Umum">
                                                            <i class="fas fa-stethoscope"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <a href="<?php echo ADMIN_URL; ?>/laporan/cetak-hasil.php?id=<?php echo $patient['id']; ?>"
                                                       target="_blank"
                                                       class="btn btn-secondary"
                                                       title="Cetak Hasil">
                                                        <i class="fas fa-print"></i>
                                                    </a>
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
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">
                                                &laquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">
                                                &raquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Tidak ada data pasien.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<?php if (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin'): ?>
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i> Tambah Pasien Manual
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPatientForm" action="add-patient.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin *</label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="">- Pilih -</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir *</label>
                            <input type="date" class="form-control" name="tanggal_lahir" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon *</label>
                            <input type="tel" class="form-control" name="no_telp" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perusahaan</label>
                            <input type="text" class="form-control" name="perusahaan">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal MCU *</label>
                            <input type="date" class="form-control" name="tanggal_mcu" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="addPatientForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '../../includes/admin-footer.php'; ?>

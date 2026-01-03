<?php
ob_start();
$page_title = 'Dashboard Admin - Sistem MCU';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

// Check if patients page is requested
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? escape($_GET['search']) : '';

if ($page == 'patients') {
    // Check role permissions
    if (!hasRole('pendaftaran') && $_SESSION['role'] != 'super_admin') {
        requireRole('dokter_mata'); // Dokter mata dan dokter umum bisa melihat list
    }

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
    $page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
    $offset = ($page_num - 1) * $limit;

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
} else {
    // Get statistics
    $user_id = $_SESSION['admin_id'];
    $user_role = $_SESSION['role'];

    // Total patients
    $total_pasien_query = "SELECT COUNT(*) as total FROM pasien";
    $total_pasien_result = mysqli_query($conn, $total_pasien_query);
    $total_pasien = mysqli_fetch_assoc($total_pasien_result)['total'];

    // Today's patients
    $today = date('Y-m-d');
    $today_pasien_query = "SELECT COUNT(*) as total FROM pasien WHERE DATE(created_at) = '$today'";
    $today_pasien_result = mysqli_query($conn, $today_pasien_query);
    $today_pasien = mysqli_fetch_assoc($today_pasien_result)['total'];

    // Waiting patients
    $waiting_query = "SELECT COUNT(*) as total FROM pasien WHERE status_pendaftaran = 'menunggu'";
    $waiting_result = mysqli_query($conn, $waiting_query);
    $waiting_pasien = mysqli_fetch_assoc($waiting_result)['total'];

    // Completed MCUs this month
    $month = date('Y-m');
    $completed_query = "SELECT COUNT(DISTINCT p.id) as total
                       FROM pasien p
                       JOIN pemeriksaan px ON p.id = px.pasien_id
                       WHERE px.pemeriksa_role = 'dokter_umum'
                       AND DATE(p.created_at) LIKE '$month%'";
    $completed_result = mysqli_query($conn, $completed_query);
    $completed_pasien = mysqli_fetch_assoc($completed_result)['total'];

    // Recent patients
    $recent_query = "SELECT * FROM pasien ORDER BY created_at DESC LIMIT 5";
    $recent_result = mysqli_query($conn, $recent_query);

    // MCU status stats
    $fit_query = "SELECT COUNT(DISTINCT pasien_id) as total FROM pemeriksaan WHERE status_mcu = 'FIT'";
    $unfit_query = "SELECT COUNT(DISTINCT pasien_id) as total FROM pemeriksaan WHERE status_mcu = 'UNFIT'";
    $fit_note_query = "SELECT COUNT(DISTINCT pasien_id) as total FROM pemeriksaan WHERE status_mcu = 'FIT WITH NOTE'";

    $fit_result = mysqli_query($conn, $fit_query);
    $unfit_result = mysqli_query($conn, $unfit_query);
    $fit_note_result = mysqli_query($conn, $fit_note_query);

    $fit_total = mysqli_fetch_assoc($fit_result)['total'];
    $unfit_total = mysqli_fetch_assoc($unfit_result)['total'];
    $fit_note_total = mysqli_fetch_assoc($fit_note_result)['total'];
}
?>

<?php include '../includes/admin-header.php'; ?>
<?php include 'includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include 'includes/admin-sidebar.php'; ?>
        </div>
        <div class="col-lg-10">
            <?php if ($page == 'patients'): ?>
                <!-- Patient List Page -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-users me-2"></i> Daftar Pasien
                    </h1>
                    <div>
                        <?php if (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin'): ?>
                        <a href="javascript:void(0)" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                            <i class="fas fa-plus me-2"></i> Tambah Pasien
                        </a>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <input type="hidden" name="page" value="patients">
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
                                <a href="dashboard.php?page=patients" class="btn btn-secondary w-100">Reset</a>
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
                                                        <a href="pasien/detail.php?id=<?php echo $patient['id']; ?>"
                                                           class="btn btn-info"
                                                           title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        <?php if (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin'): ?>
                                                            <?php if (!$patient['cek_pendaftaran']): ?>
                                                            <a href="pasien/pemeriksaan.php?role=pendaftaran&id=<?php echo $patient['id']; ?>"
                                                               class="btn btn-warning"
                                                               title="Pemeriksaan Pendaftaran">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <?php if (hasRole('dokter_mata') || $_SESSION['role'] == 'super_admin'): ?>
                                                            <?php if ($patient['cek_pendaftaran'] && !$patient['cek_mata']): ?>
                                                            <a href="pasien/pemeriksaan.php?role=dokter_mata&id=<?php echo $patient['id']; ?>"
                                                               class="btn btn-primary"
                                                               title="Pemeriksaan Mata">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <?php if (hasRole('dokter_umum') || $_SESSION['role'] == 'super_admin'): ?>
                                                            <?php if ($patient['cek_mata'] && !$patient['cek_umum']): ?>
                                                            <a href="pasien/pemeriksaan.php?role=dokter_umum&id=<?php echo $patient['id']; ?>"
                                                               class="btn btn-success"
                                                               title="Pemeriksaan Umum">
                                                                <i class="fas fa-stethoscope"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <a href="laporan/cetak-hasil.php?id=<?php echo $patient['id']; ?>"
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
                                        <?php if ($page_num > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=patients&page_num=<?php echo $page_num-1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">
                                                    &laquo;
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page_num ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=patients&page_num=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page_num < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=patients&page_num=<?php echo $page_num+1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">
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
            <?php elseif ($page == 'reports'): ?>
                <!-- Reports Page -->
                <?php if ($filter == 'pasien'): ?>
                    <!-- Patient Report -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-users me-2"></i> Data Pasien
                        </h1>
                        <div>
                            <a href="laporan/cetak-pasien.php" target="_blank" class="btn btn-primary">
                                <i class="fas fa-print me-2"></i> Cetak Laporan
                            </a>
                        </div>
                    </div>

                    <!-- Patients Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Data Pasien</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get all patients for report
                            $patient_query = "SELECT p.*,
                                             (SELECT COUNT(*) FROM pemeriksaan WHERE pasien_id = p.id AND pemeriksa_role = 'pendaftaran') as cek_pendaftaran,
                                             (SELECT COUNT(*) FROM pemeriksaan WHERE pasien_id = p.id AND pemeriksa_role = 'dokter_mata') as cek_mata,
                                             (SELECT COUNT(*) FROM pemeriksaan WHERE pasien_id = p.id AND pemeriksa_role = 'dokter_umum') as cek_umum
                                             FROM pasien p
                                             ORDER BY p.created_at DESC";
                            $patient_result = mysqli_query($conn, $patient_query);
                            ?>

                            <?php if (!$patient_result): ?>
                                <div class="alert alert-danger">Error: <?php echo mysqli_error($conn); ?></div>
                            <?php else: ?>
                                <?php if (mysqli_num_rows($patient_result) > 0): ?>
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; ?>
                                                <?php while ($patient = mysqli_fetch_assoc($patient_result)): ?>
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
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Tidak ada data pasien.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($filter == 'hasil'): ?>
                    <!-- MCU Results Report -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-file-alt me-2"></i> Hasil MCU
                        </h1>
                        <div>
                            <a href="laporan/cetak-hasil.php" target="_blank" class="btn btn-primary">
                                <i class="fas fa-print me-2"></i> Cetak Laporan
                            </a>
                        </div>
                    </div>

                    <!-- MCU Results Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Hasil Pemeriksaan MCU</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get MCU results
                            $mcu_query = "SELECT p.kode_mcu, p.nama, p.perusahaan, p.tanggal_mcu,
                                         px.status_mcu, px.pemeriksa_role, px.tanggal_periksa,
                                         px.catatan_kesimpulan
                                         FROM pasien p
                                         JOIN pemeriksaan px ON p.id = px.pasien_id
                                         WHERE px.pemeriksa_role = 'dokter_umum'
                                         ORDER BY p.created_at DESC";
                            $mcu_result = mysqli_query($conn, $mcu_query);
                            ?>

                            <?php if (!$mcu_result): ?>
                                <div class="alert alert-danger">Error: <?php echo mysqli_error($conn); ?></div>
                            <?php else: ?>
                                <?php if (mysqli_num_rows($mcu_result) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Kode MCU</th>
                                                    <th>Nama</th>
                                                    <th>Perusahaan</th>
                                                    <th>Tanggal MCU</th>
                                                    <th>Hasil</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; ?>
                                                <?php while ($mcu = mysqli_fetch_assoc($mcu_result)): ?>
                                                    <tr>
                                                        <td><?php echo $no++; ?></td>
                                                        <td>
                                                            <strong><?php echo $mcu['kode_mcu']; ?></strong>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($mcu['nama']); ?></td>
                                                        <td><?php echo $mcu['perusahaan'] ?: '-'; ?></td>
                                                        <td><?php echo formatDateIndo($mcu['tanggal_mcu']); ?></td>
                                                        <td>
                                                            <?php
                                                            $status_class = '';
                                                            switch($mcu['status_mcu']) {
                                                                case 'FIT':
                                                                    $status_class = 'bg-success';
                                                                    break;
                                                                case 'UNFIT':
                                                                    $status_class = 'bg-danger';
                                                                    break;
                                                                case 'FIT WITH NOTE':
                                                                    $status_class = 'bg-warning';
                                                                    break;
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $status_class; ?>"><?php echo $mcu['status_mcu']; ?></span>
                                                        </td>
                                                        <td><?php echo $mcu['catatan_kesimpulan'] ?: '-'; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Tidak ada data hasil MCU.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
        <?php else: ?>
                <!-- Dashboard Page -->
                <!-- Page Title -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Dashboard</h1>
                    <div class="text-muted">
                        <i class="fas fa-user me-1"></i> <?php echo $_SESSION['nama_lengkap']; ?>
                        <span class="badge bg-primary ms-2"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?></span>
                    </div>
                </div>



                <!-- Welcome Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title">Selamat datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h5>
                                        <p class="card-text mb-0">
                                            <i class="fas fa-calendar me-1"></i> <?php echo formatDateIndo(date('Y-m-d'), false) . ' - ' . date('H:i'); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <i class="fas fa-user-md fa-4x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Pasien
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_pasien; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-primary"></i>
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
                                            Pasien Hari Ini
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_pasien; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-day fa-2x text-success"></i>
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
                                            Menunggu Pemeriksaan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $waiting_pasien; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
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
                                            MCU Bulan Ini
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $completed_pasien; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-check fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Recent Data -->
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- MCU Status Chart -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Statistik Hasil MCU</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <canvas id="mcuChart" height="200"></canvas>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="list-group">
                                            <div class="list-group-item border-0">
                                                <span class="badge bg-success me-2">FIT</span>
                                                <span class="fw-bold"><?php echo $fit_total; ?> pasien</span>
                                            </div>
                                            <div class="list-group-item border-0">
                                                <span class="badge bg-danger me-2">UNFIT</span>
                                                <span class="fw-bold"><?php echo $unfit_total; ?> pasien</span>
                                            </div>
                                            <div class="list-group-item border-0">
                                                <span class="badge bg-warning me-2">FIT WITH NOTE</span>
                                                <span class="fw-bold"><?php echo $fit_note_total; ?> pasien</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Patients -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-history me-2"></i> Pasien Terbaru</h6>
                                <a href="dashboard.php?page=patients&filter=all" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kode MCU</th>
                                                <th>Nama</th>
                                                <th>Tanggal Daftar</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($patient = mysqli_fetch_assoc($recent_result)): ?>
                                            <tr>
                                                <td><strong><?php echo $patient['kode_mcu']; ?></strong></td>
                                                <td><?php echo $patient['nama']; ?></td>
                                                <td><?php echo formatDateIndo($patient['created_at']); ?></td>
                                                <td><?php echo getStatusBadge($patient['status_pendaftaran']); ?></td>
                                                <td>
                                                    <a href="pasien/detail.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>

                                            <?php if (mysqli_num_rows($recent_result) == 0): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Belum ada data pasien</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Quick Actions -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-bolt me-2"></i> Akses Cepat</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php if (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin'): ?>
                                    <a href="dashboard.php?page=patients&filter=menunggu" class="list-group-item list-group-item-action">
                                        <i class="fas fa-user-clock text-warning me-2"></i> Pasien Menunggu
                                        <span class="badge bg-warning float-end"><?php echo $waiting_pasien; ?></span>
                                    </a>
                                    <?php endif; ?>

                                    <?php if (hasRole('dokter_mata') || $_SESSION['role'] == 'super_admin'): ?>
                                    <a href="pasien/pemeriksaan-mata.php" class="list-group-item list-group-item-action">
                                        <i class="fas fa-eye text-info me-2"></i> Pemeriksaan Mata
                                    </a>
                                    <?php endif; ?>

                                    <?php if (hasRole('dokter_umum') || $_SESSION['role'] == 'super_admin'): ?>
                                    <a href="pasien/pemeriksaan-umum.php" class="list-group-item list-group-item-action">
                                        <i class="fas fa-stethoscope text-success me-2"></i> Pemeriksaan Umum
                                    </a>
                                    <?php endif; ?>

                                    <a href="laporan/cetak-pasien.php" class="list-group-item list-group-item-action">
                                        <i class="fas fa-print text-secondary me-2"></i> Cetak Laporan
                                    </a>

                                    <?php if ($_SESSION['role'] == 'super_admin'): ?>
                                    <a href="artikel/add.php" class="list-group-item list-group-item-action">
                                        <i class="fas fa-edit text-primary me-2"></i> Buat Artikel Baru
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- System Info -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informasi Sistem</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-server text-primary me-2"></i>
                                        <strong>Versi Sistem:</strong> 1.0.0
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-database text-primary me-2"></i>
                                        <strong>Database:</strong> MySQL
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-calendar text-primary me-2"></i>
                                        <strong>Hari Ini:</strong> <?php echo date('l, d F Y'); ?>
                                    </li>
                                    <li>
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <strong>Waktu Server:</strong> <?php echo date('H:i:s'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// MCU Status Chart
var ctx = document.getElementById('mcuChart').getContext('2d');
var mcuChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['FIT', 'UNFIT', 'FIT WITH NOTE'],
        datasets: [{
            data: [<?php echo $fit_total; ?>, <?php echo $unfit_total; ?>, <?php echo $fit_note_total; ?>],
            backgroundColor: [
                '#28a745',
                '#dc3545',
                '#ffc107'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<!-- Add Patient Modal -->
<?php if ($page == 'patients' && (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin')): ?>
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
                <form id="addPatientForm" action="pasien/add-patient.php" method="POST">
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

<?php include '../includes/admin-footer.php'; ?>

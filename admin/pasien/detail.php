<?php
$page_title = 'Detail Pasien - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

// Get patient ID and source page
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$from = isset($_GET['from']) ? $_GET['from'] : '';

if ($id == 0) {
    $_SESSION['error'] = "Pasien tidak ditemukan";
    redirect('list.php');
}

// Get patient data
$query = "SELECT p.* FROM pasien p WHERE p.id = $id";
$result = mysqli_query($conn, $query);
$patient = mysqli_fetch_assoc($result);

if (!$patient) {
    $_SESSION['error'] = "Pasien tidak ditemukan";
    redirect('list.php');
}

// Get family data
$family_query = "SELECT * FROM keluarga_pasien WHERE pasien_id = $id";
$family_result = mysqli_query($conn, $family_query);

// Get medical history
$history_query = "SELECT * FROM riwayat_kesehatan WHERE pasien_id = $id";
$history_result = mysqli_query($conn, $history_query);
$histories = [];
while ($row = mysqli_fetch_assoc($history_result)) {
    $histories[$row['kategori']][] = $row['nilai'];
}

// Get habits
$habits_query = "SELECT * FROM kebiasaan_pasien WHERE pasien_id = $id";
$habits_result = mysqli_query($conn, $habits_query);

// Get pemeriksaan data
$pemeriksaan_query = "SELECT * FROM pemeriksaan WHERE pasien_id = $id ORDER BY pemeriksa_role, tanggal_periksa";
$pemeriksaan_result = mysqli_query($conn, $pemeriksaan_query);
?>

<?php include '../../includes/admin-header.php'; ?>
<?php include '../includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include '../includes/admin-sidebar.php'; ?>
        </div>
        <div class="col-lg-10">
            <!-- Patient Header -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i> Detail Pasien
                    </h5>
                    <div>
                        <a href="<?php echo $from == 'cetak-hasil' ? '../laporan/cetak-hasil.php' : 'list.php'; ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="../laporan/cetak-hasil.php?id=<?php echo $id; ?>" 
                           target="_blank" 
                           class="btn btn-light btn-sm">
                            <i class="fas fa-print me-1"></i> Cetak
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3><?php echo htmlspecialchars($patient['nama']); ?></h3>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Kode MCU</th>
                                            <td><strong class="text-primary"><?php echo $patient['kode_mcu']; ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Jenis Kelamin</th>
                                            <td><?php echo $patient['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tempat/Tgl Lahir</th>
                                            <td><?php echo $patient['tempat_lahir'] . ', ' . formatDateIndo($patient['tanggal_lahir']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Usia</th>
                                            <td><?php echo $patient['usia']; ?> tahun</td>
                                        </tr>
                                        <tr>
                                            <th>Agama</th>
                                            <td><?php echo $patient['agama']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Gol. Darah</th>
                                            <td><?php echo $patient['golongan_darah'] ?: '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>No. Telepon</th>
                                            <td><?php echo $patient['no_telp']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td><?php echo $patient['email'] ?: '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Perusahaan</th>
                                            <td><?php echo $patient['perusahaan'] ?: '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Posisi</th>
                                            <td><?php echo $patient['posisi_pekerjaan'] ?: '-'; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Status</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <?php echo getStatusBadge($patient['status_pendaftaran']); ?>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Tanggal Daftar</small>
                                        <strong><?php echo formatDateIndo($patient['created_at']); ?></strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Tanggal MCU</small>
                                        <strong><?php echo formatDateIndo($patient['tanggal_mcu']); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <table class="table table-sm">
                                <tr>
                                    <th width="15%">Alamat</th>
                                    <td><?php echo nl2br(htmlspecialchars($patient['alamat'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Pendidikan</th>
                                    <td><?php echo $patient['pendidikan']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="patientTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="family-tab" data-bs-toggle="tab" data-bs-target="#family" type="button">
                        <i class="fas fa-users me-2"></i> Data Keluarga
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="medical-tab" data-bs-toggle="tab" data-bs-target="#medical" type="button">
                        <i class="fas fa-heartbeat me-2"></i> Riwayat Kesehatan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="habits-tab" data-bs-toggle="tab" data-bs-target="#habits" type="button">
                        <i class="fas fa-smoking me-2"></i> Kebiasaan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="examination-tab" data-bs-toggle="tab" data-bs-target="#examination" type="button">
                        <i class="fas fa-stethoscope me-2"></i> Pemeriksaan
                    </button>
                </li>
            </ul>
            
            <!-- Tabs Content -->
            <div class="tab-content" id="patientTabsContent">
                <!-- Family Tab -->
                <div class="tab-pane fade show active" id="family" role="tabpanel">
                    <div class="card border-top-0">
                        <div class="card-body">
                            <?php if (mysqli_num_rows($family_result) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Hubungan</th>
                                                <th>Nama</th>
                                                <th>Usia</th>
                                                <th>Kondisi</th>
                                                <th>Meninggal Tahun</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($family = mysqli_fetch_assoc($family_result)): ?>
                                                <tr>
                                                    <td><?php echo $family['hubungan']; ?></td>
                                                    <td><?php echo $family['nama']; ?></td>
                                                    <td><?php echo $family['usia']; ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $family['kondisi'] == 'Sehat' ? 'bg-success' : 'bg-warning'; ?>">
                                                            <?php echo $family['kondisi']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $family['meninggal_tahun'] ?: '-'; ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Tidak ada data keluarga.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Medical History Tab -->
                <div class="tab-pane fade" id="medical" role="tabpanel">
                    <div class="card border-top-0">
                        <div class="card-body">
                            <div class="row">
                                <!-- Current Health Status -->
                                <div class="col-md-6 mb-4">
                                    <h6>Status Kesehatan Sekarang</h6>
                                    <div class="list-group">
                                        <?php if (isset($histories['kesehatan_sekarang'])): ?>
                                            <?php foreach ($histories['kesehatan_sekarang'] as $status): ?>
                                                <div class="list-group-item">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <?php echo $status; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-secondary">
                                                Tidak ada data
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Past Diseases -->
                                <div class="col-md-6 mb-4">
                                    <h6>Penyakit Dahulu</h6>
                                    <div class="list-group">
                                        <?php if (isset($histories['penyakit_dahulu'])): ?>
                                            <?php foreach ($histories['penyakit_dahulu'] as $disease): ?>
                                                <div class="list-group-item">
                                                    <i class="fas fa-hospital text-danger me-2"></i>
                                                    <?php echo $disease; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-secondary">
                                                Tidak ada riwayat penyakit
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- Family History -->
                                <div class="col-md-6 mb-4">
                                    <h6>Riwayat Penyakit Keluarga</h6>
                                    <div class="list-group">
                                        <?php if (isset($histories['riwayat_keluarga'])): ?>
                                            <?php foreach ($histories['riwayat_keluarga'] as $family_disease): ?>
                                                <div class="list-group-item">
                                                    <i class="fas fa-hospital text-danger me-2"></i>
                                                    <?php echo $family_disease; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-secondary">
                                                Tidak ada riwayat penyakit keluarga
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Allergies -->
                                <div class="col-md-6 mb-4">
                                    <h6>Alergi</h6>
                                    <div class="list-group">
                                        <?php if (isset($histories['alergi'])): ?>
                                            <?php foreach ($histories['alergi'] as $allergy): ?>
                                                <div class="list-group-item">
                                                    <i class="fas fa-allergies text-info me-2"></i>
                                                    <?php echo $allergy; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-secondary">
                                                Tidak ada alergi
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Complaints -->
                            <div class="row">
                                <div class="col-12">
                                    <h6>Keluhan Kesehatan Sebelum/Seseudah Medical Check Up</h6>
                                    <div class="list-group">
                                        <?php if (isset($histories['keluhan'])): ?>
                                            <?php foreach ($histories['keluhan'] as $complaint): ?>
                                                <div class="list-group-item">
                                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                    <?php echo $complaint; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-secondary">
                                                Tidak ada keluhan
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Habits Tab -->
                <div class="tab-pane fade" id="habits" role="tabpanel">
                    <div class="card border-top-0">
                        <div class="card-body">
                            <?php if (mysqli_num_rows($habits_result) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Jenis Kebiasaan</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($habit = mysqli_fetch_assoc($habits_result)): ?>
                                                <tr>
                                                    <td width="30%">
                                                        <strong>
                                                            <?php echo ucfirst($habit['jenis']); ?>
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($habit['jenis'] == 'merokok') {
                                                            echo $habit['keterangan'] . ' batang/hari';
                                                        } else {
                                                            echo $habit['keterangan'];
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Tidak ada data kebiasaan.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Examination Tab -->
                <div class="tab-pane fade" id="examination" role="tabpanel">
                    <div class="card border-top-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i> Hasil Pemeriksaan
                            </h5>
                            <div>
                                <?php
                                // Check which examinations are missing
                                $cek_pendaftaran = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pemeriksaan WHERE pasien_id = $id AND pemeriksa_role = 'pendaftaran'"));
                                $cek_mata = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pemeriksaan WHERE pasien_id = $id AND pemeriksa_role = 'dokter_mata'"));
                                $cek_umum = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pemeriksaan WHERE pasien_id = $id AND pemeriksa_role = 'dokter_umum'"));

                                // Show examination buttons based on role and completion status
                                if (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin') {
                                    if (!$cek_pendaftaran) {
                                        echo '<a href="pemeriksaan.php?role=pendaftaran&id=' . $id . '" class="btn btn-warning btn-sm me-2">
                                                <i class="fas fa-edit me-1"></i> Periksa Awal
                                              </a>';
                                    }
                                }

                                if (hasRole('dokter_mata') || $_SESSION['role'] == 'super_admin') {
                                    if ($cek_pendaftaran && !$cek_mata) {
                                        echo '<a href="pemeriksaan.php?role=dokter_mata&id=' . $id . '" class="btn btn-primary btn-sm me-2">
                                                <i class="fas fa-eye me-1"></i> Periksa Mata
                                              </a>';
                                    }
                                }

                                if (hasRole('dokter_umum') || $_SESSION['role'] == 'super_admin') {
                                    if ($cek_mata && !$cek_umum) {
                                        echo '<a href="pemeriksaan.php?role=dokter_umum&id=' . $id . '" class="btn btn-success btn-sm">
                                                <i class="fas fa-stethoscope me-1"></i> Periksa Umum
                                              </a>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get all pemeriksaan data
                            $exams_query = "SELECT * FROM pemeriksaan WHERE pasien_id = $id ORDER BY pemeriksa_role, tanggal_periksa";
                            $exams_result = mysqli_query($conn, $exams_query);

                            if (mysqli_num_rows($exams_result) > 0):
                                while ($exam = mysqli_fetch_assoc($exams_result)):
                                    $role = $exam['pemeriksa_role'];
                                    $role_name = str_replace('_', ' ', $role);
                                    $role_name = ucwords($role_name);
                            ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <?php echo $role_name; ?>
                                            <small class="text-muted ms-2">
                                                <?php echo formatDateIndo($exam['tanggal_periksa'], true); ?>
                                            </small>
                                        </h6>
                                        <span class="badge bg-success">Selesai</span>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($role == 'pendaftaran'): ?>
                                            <!-- Sirkulasi Data -->
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Sirkulasi</h6>
                                                </div>
                                                <div class="card-body">
                                                    <table class="table table-sm table-borderless">
                                                        <tr>
                                                            <th width="40%">Tekanan Darah</th>
                                                            <td class="<?php echo getValueClass('tekanan_darah', $exam['tekanan_darah']); ?>"><?php echo $exam['tekanan_darah'] ? $exam['tekanan_darah'] . ' mmHg' : '-'; ?></td>
                                                        </tr>
                                                         <tr>
                                                            <th>Respirasi</th>
                                                            <td class="<?php echo getValueClass('respirasi', $exam['respirasi']); ?>"><?php echo $exam['respirasi'] ? $exam['respirasi'] . ' x/menit' : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nadi</th>
                                                            <td class="<?php echo getValueClass('nadi', $exam['nadi']); ?>"><?php echo $exam['nadi'] ? $exam['nadi'] . ' x/menit' : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Suhu</th>
                                                            <td class="<?php echo getValueClass('suhu', $exam['suhu']); ?>"><?php echo $exam['suhu'] ? $exam['suhu'] . ' °C' : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Tinggi Badan</th>
                                                            <td><?php echo $exam['tinggi_badan'] ? $exam['tinggi_badan'] . ' cm' : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Berat Badan</th>
                                                            <td class="<?php echo isBMIAbnormal($exam['berat_badan'], $exam['tinggi_badan']) ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['berat_badan'] ? $exam['berat_badan'] . ' kg' : '-'; ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                        <?php elseif ($role == 'dokter_mata'): ?>
                                            <!-- Mata Data -->
                                            <div class="card border-secondary">
                                                <div class="card-header bg-secondary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Mata</h6>
                                                </div>
                                                <div class="card-body">
                                                    <table class="table table-sm table-borderless">
                                                        <tr>
                                                            <th width="40%">Visus Kanan (Jauh/Dekat)</th>
                                                            <td>
                                                                <span class="<?php echo getValueClass('visus', $exam['visus_kanan_jauh']); ?>"><?php echo $exam['visus_kanan_jauh'] ?: '-'; ?></span> /
                                                                <span class="<?php echo getValueClass('visus', $exam['visus_kanan_dekat']); ?>"><?php echo $exam['visus_kanan_dekat'] ?: '-'; ?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Visus Kiri (Jauh/Dekat)</th>
                                                            <td>
                                                                <span class="<?php echo getValueClass('visus', $exam['visus_kiri_jauh']); ?>"><?php echo $exam['visus_kiri_jauh'] ?: '-'; ?></span> /
                                                                <span class="<?php echo getValueClass('visus', $exam['visus_kiri_dekat']); ?>"><?php echo $exam['visus_kiri_dekat'] ?: '-'; ?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Anemis : Ikterik</th>
                                                            <td class="<?php echo getStatusClass($exam['anemia']); ?>"><?php echo $exam['anemia'] ?: '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Keterangan</th>
                                                            <td class="<?php echo getDescriptionClass($exam['ikterik_keterangan']); ?>"><?php echo $exam['ikterik_keterangan'] ?: '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Buta Warna</th>
                                                            <td class="<?php echo getStatusClass($exam['buta_warna']); ?>"><?php echo $exam['buta_warna'] ?: '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Keterangan</th>
                                                            <td class="<?php echo getDescriptionClass($exam['buta_warna_keterangan']); ?>"><?php echo $exam['buta_warna_keterangan'] ?: '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Lapang Pandang</th>
                                                            <td class="<?php echo getStatusClass($exam['lapang_pandang']); ?>"><?php echo $exam['lapang_pandang'] ?: '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Keterangan</th>
                                                            <td class="<?php echo getDescriptionClass($exam['lapang_pandang_keterangan']); ?>"><?php echo $exam['lapang_pandang_keterangan'] ?: '-'; ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                        <?php elseif ($role == 'dokter_umum'): ?>
                                            <!-- General Examination Data -->
                                            <div class="row">
                                                <div class="col-md-6 mb-4">
                                                    <div class="card border-primary">
                                                        <div class="card-header bg-primary text-white">
                                                            <h6 class="mb-0"><i class="fas fa-ear-listen me-2"></i>THT & Gigi</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <th width="40%">Telinga</th>
                                                                    <td class="<?php echo getStatusClass($exam['telinga_status']); ?>"><?php echo $exam['telinga_status'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Keterangan</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['telinga_keterangan']); ?>"><?php echo $exam['telinga_keterangan'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Hidung</th>
                                                                    <td class="<?php echo getStatusClass($exam['hidung_status']); ?>"><?php echo $exam['hidung_status'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Keterangan</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['hidung_keterangan']); ?>"><?php echo $exam['hidung_keterangan'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Tenggorokan</th>
                                                                    <td class="<?php echo getStatusClass($exam['tenggorokan_status']); ?>"><?php echo $exam['tenggorokan_status'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Keterangan</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['tenggorokan_keterangan']); ?>"><?php echo $exam['tenggorokan_keterangan'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Gigi</th>
                                                                    <td class="<?php echo getStatusClass($exam['gigi_status']); ?>"><?php echo $exam['gigi_status'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Keterangan</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['gigi_keterangan']); ?>"><?php echo $exam['gigi_keterangan'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Leher (KGB)</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['leher_kgb']); ?>"><?php echo $exam['leher_kgb'] ?: '-'; ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-4">
                                                    <div class="card border-success">
                                                        <div class="card-header bg-success text-white">
                                                            <h6 class="mb-0"><i class="fas fa-lungs me-2"></i>Thorax PARU - PARU</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <th width="40%">Auskultasi</th>
                                                                    <td><?php echo $exam['paru_auskultasi'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Keterangan</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['auskultasi_keterangan']); ?>"><?php echo $exam['auskultasi_keterangan'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Palpasi</th>
                                                                    <td><?php echo $exam['paru_palpasi'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Perkusi</th>
                                                                    <td><?php echo $exam['paru_perkusi'] ?: '-'; ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="card border-danger mt-3">
                                                        <div class="card-header bg-danger text-white">
                                                            <h6 class="mb-0"><i class="fas fa-heart me-2"></i>Thorax JANTUNG</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <th width="40%">Auskultasi</th>
                                                                    <td><?php echo $exam['jantung_auskultasi'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Keterangan</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['jantung_keterangan']); ?>"><?php echo $exam['jantung_keterangan'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Perkusi</th>
                                                                    <td><?php echo $exam['jantung_perkusi'] ?: '-'; ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-4">
                                                    <div class="card border-warning">
                                                        <div class="card-header bg-warning text-dark">
                                                            <h6 class="mb-0"><i class="fas fa-user-md me-2"></i>Abdominal</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <th width="50%">Operasi</th>
                                                                    <td><?php echo $exam['operasi'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Keterangan Operasi/Penyakit Perut</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['keterangan_operasi']); ?>"><?php echo $exam['keterangan_operasi'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Obesitas</th>
                                                                    <td class="<?php echo $exam['obesitas'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['obesitas'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Organomegali</th>
                                                                    <td class="<?php echo $exam['organomegali'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['organomegali'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Hernia</th>
                                                                    <td class="<?php echo $exam['hernia'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['hernia'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Nyeri Tekan Epigastrium</th>
                                                                    <td><?php echo $exam['nyeri_epigastrium'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Nyeri Tekan Abdomen</th>
                                                                    <td class="<?php echo $exam['nyeri_abdomen'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['nyeri_abdomen'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Bising Usus</th>
                                                                    <td class="<?php echo $exam['bising_usus'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['bising_usus'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Hepar</th>
                                                                    <td class="<?php echo $exam['hepar'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['hepar'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Striae</th>
                                                                    <td class="<?php echo $exam['striae'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['striae'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Sikatriks</th>
                                                                    <td class="<?php echo $exam['sikatriks'] ? 'text-danger fw-bold' : ''; ?>"><?php echo $exam['sikatriks'] ? 'Ya' : 'Tidak'; ?></td>
                                                                </tr>
                                                                <!-- <tr>
                                                                    <th>Penyakit Perut</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['keterangan_perut']); ?>"><?php echo $exam['keterangan_perut'] ?: '-'; ?></td>
                                                                </tr> -->
                                                                <tr>
                                                                    <th>PSOAS SIGN</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['psoas_sign']); ?>"><?php echo $exam['psoas_sign'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Genitalia</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['hepatomegali']); ?>"><?php echo $exam['hepatomegali'] ?: '-'; ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-4">
                                                    <div class="card border-info">
                                                        <div class="card-header bg-info text-white">
                                                            <h6 class="mb-0"><i class="fas fa-brain me-2"></i>Refleks</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <th width="50%">Biceps</th>
                                                                    <td class="<?php echo getStatusClass($exam['biceps']); ?>"><?php echo $exam['biceps'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Triceps</th>
                                                                    <td class="<?php echo getStatusClass($exam['triceps']); ?>"><?php echo $exam['triceps'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Patella</th>
                                                                    <td class="<?php echo getStatusClass($exam['patella']); ?>"><?php echo $exam['patella'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Achilles</th>
                                                                    <td class="<?php echo getStatusClass($exam['achilles']); ?>"><?php echo $exam['achilles'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Plantar Response</th>
                                                                    <td class="<?php echo getStatusClass($exam['plantar_response']); ?>"><?php echo $exam['plantar_response'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Penyakit Tangan</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['keterangan_tangan']); ?>"><?php echo $exam['keterangan_tangan'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Penyakit Kaki</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['keterangan_kaki']); ?>"><?php echo $exam['keterangan_kaki'] ?: '-'; ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                                <div class="col-md-6 mb-4">
                                                    <div class="card border-success">
                                                        <div class="card-header bg-success text-white">
                                                            <h6 class="mb-0"><i class="fas fa-flask me-2"></i>Hasil Lanjutan</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <th width="50%">Hasil Laboratorium</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['hasil_lab']); ?>"><?php echo $exam['hasil_lab'] ?: '-'; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Riwayat Penyakit Dahulu</th>
                                                                    <td class="<?php echo getDescriptionClass($exam['keterangan_penyakit']); ?>"><?php echo $exam['keterangan_penyakit'] ?: '-'; ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Conclusion -->
                                            <?php if ($exam['kesimpulan'] || $exam['status_mcu']): ?>
                                                <div class="card mt-3 bg-light">
                                                    <div class="card-body">
                                                        <!-- <h6 style="font-weight: bold;">Kesimpulan</h6>
                                                        <p><?php echo nl2br($exam['kesimpulan']); ?></p> -->
                                                        <h6 style="font-weight: bold;">Saran</h6>
                                                        <p><?php echo nl2br($exam['saran']); ?></p>
                                                        <div class="mt-2">
                                                            <strong>Status MCU:</strong>
                                                            <?php echo getMCUStatusBadge($exam['status_mcu']); ?>
                                                        </div>
                                                        <?php if ($exam['dokter_pemeriksa']): ?>
                                                            <div class="mt-2">
                                                                <strong>Dokter Pemeriksa:</strong>
                                                                <?php echo $exam['dokter_pemeriksa']; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php
                                endwhile;
                            else:
                            ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Belum ada data pemeriksaan.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize tabs
    var patientTabs = document.getElementById('patientTabs');
    if (patientTabs) {
        var triggerTabList = [].slice.call(patientTabs.querySelectorAll('button'));
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });
    }
</script>

<?php include '../../includes/admin-footer.php'; ?>

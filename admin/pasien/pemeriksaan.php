<?php
$page_title = 'Pemeriksaan Pasien - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

// Get parameters
$role = isset($_GET['role']) ? $_GET['role'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate role
$valid_roles = ['pendaftaran', 'dokter_mata', 'dokter_umum'];
if (!in_array($role, $valid_roles)) {
    $_SESSION['error'] = "Role pemeriksaan tidak valid";
    redirect('list.php');
}

// Check permission
if (!hasRole($role) && $_SESSION['role'] != 'super_admin') {
    $_SESSION['error'] = "Anda tidak memiliki izin untuk mengakses halaman ini";
    redirect('dashboard.php');
}

// Get patient data
$query = "SELECT * FROM pasien WHERE id = $id";
$result = mysqli_query($conn, $query);
$patient = mysqli_fetch_assoc($result);

if (!$patient) {
    $_SESSION['error'] = "Pasien tidak ditemukan";
    redirect('list.php');
}

// Check if already examined
$check_query = "SELECT * FROM pemeriksaan WHERE pasien_id = $id AND pemeriksa_role = '$role'";
$check_result = mysqli_query($conn, $check_query);
if (mysqli_num_rows($check_result) > 0) {
    $_SESSION['warning'] = "Pemeriksaan ini sudah dilakukan";
    redirect('detail.php?id=' . $id);
}

// Check prerequisites based on role
if ($role == 'dokter_mata') {
    $prereq_query = "SELECT COUNT(*) as total FROM pemeriksaan WHERE pasien_id = $id AND pemeriksa_role = 'pendaftaran'";
    $prereq_result = mysqli_query($conn, $prereq_query);
    $prereq = mysqli_fetch_assoc($prereq_result);
    if ($prereq['total'] == 0) {
        $_SESSION['error'] = "Pasien harus diperiksa oleh pendaftaran terlebih dahulu";
        redirect('detail.php?id=' . $id);
    }
} elseif ($role == 'dokter_umum') {
    $prereq_query = "SELECT COUNT(*) as total FROM pemeriksaan WHERE pasien_id = $id AND pemeriksa_role = 'dokter_mata'";
    $prereq_result = mysqli_query($conn, $prereq_query);
    $prereq = mysqli_fetch_assoc($prereq_result);
    if ($prereq['total'] == 0) {
        $_SESSION['error'] = "Pasien harus diperiksa oleh dokter mata terlebih dahulu";
        redirect('detail.php?id=' . $id);
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    if ($role == 'pendaftaran') {
        $tekanan_darah = escape($_POST['tekanan_darah']);
        $nadi = (int)$_POST['nadi'];
        $suhu = (float)$_POST['suhu'];
        $respirasi = (int)$_POST['respirasi'];
        $tinggi_badan = (int)$_POST['tinggi_badan'];
        $berat_badan = (int)$_POST['berat_badan'];
        
        // Update patient status
        $update_query = "UPDATE pasien SET status_pendaftaran = 'proses' WHERE id = $id";
        mysqli_query($conn, $update_query);
        
        // Insert pemeriksaan
        $insert_query = "INSERT INTO pemeriksaan (pasien_id, pemeriksa_role, tekanan_darah, nadi, suhu, respirasi, tinggi_badan, berat_badan, pemeriksa_id) 
                         VALUES ($id, '$role', '$tekanan_darah', $nadi, $suhu, $respirasi, $tinggi_badan, $berat_badan, {$_SESSION['admin_id']})";
        
    } elseif ($role == 'dokter_mata') {
        $visus_kanan_jauh = escape($_POST['visus_kanan_jauh']);
        $visus_kanan_dekat = escape($_POST['visus_kanan_dekat']);
        $visus_kiri_jauh = escape($_POST['visus_kiri_jauh']);
        $visus_kiri_dekat = escape($_POST['visus_kiri_dekat']);
        $anemia = escape($_POST['anemia']);
        $buta_warna = escape($_POST['buta_warna']);
        $lapang_pandang = escape($_POST['lapang_pandang']);
        
        $insert_query = "INSERT INTO pemeriksaan (pasien_id, pemeriksa_role, visus_kanan_jauh, visus_kanan_dekat, visus_kiri_jauh, visus_kiri_dekat, anemia, buta_warna, lapang_pandang, pemeriksa_id) 
                         VALUES ($id, '$role', '$visus_kanan_jauh', '$visus_kanan_dekat', '$visus_kiri_jauh', '$visus_kiri_dekat', '$anemia', '$buta_warna', '$lapang_pandang', {$_SESSION['admin_id']})";
        
    } elseif ($role == 'dokter_umum') {
        // THT & Gigi
        $telinga_status = escape($_POST['telinga_status']);
        $telinga_keterangan = escape($_POST['telinga_keterangan']);
        $hidung_status = escape($_POST['hidung_status']);
        $hidung_keterangan = escape($_POST['hidung_keterangan']);
        $tenggorokan_status = escape($_POST['tenggorokan_status']);
        $tenggorokan_keterangan = escape($_POST['tenggorokan_keterangan']);
        $gigi_keterangan = escape($_POST['gigi_keterangan']);
        $leher_kgb = escape($_POST['leher_kgb']);
        
        // Thorax
        $paru_auskultasi = escape($_POST['paru_auskultasi']);
        $paru_palpasi = escape($_POST['paru_palpasi']);
        $paru_perkusi = escape($_POST['paru_perkusi']);
        
        // Abdominal (boolean fields)
        $operasi = isset($_POST['operasi']) ? 1 : 0;
        $keterangan_operasi = escape($_POST['keterangan_operasi']);
        $obesitas = isset($_POST['obesitas']) ? 1 : 0;
        $organomegali = isset($_POST['organomegali']) ? 1 : 0;
        $hernia = isset($_POST['hernia']) ? 1 : 0;
        $nyeri_epigastrium = isset($_POST['nyeri_epigastrium']) ? 1 : 0;
        $nyeri_abdomen = isset($_POST['nyeri_abdomen']) ? 1 : 0;
        $bising_usus = isset($_POST['bising_usus']) ? 1 : 0;
        $hepatomegali = escape($_POST['hepatomegali']);
        
        // Refleks
        $biceps = escape($_POST['biceps']);
        $triceps = escape($_POST['triceps']);
        $patella = escape($_POST['patella']);
        $achilles = escape($_POST['achilles']);
        $plantar_response = escape($_POST['plantar_response']);
        
        // Kesimpulan
        $kesimpulan = escape($_POST['kesimpulan']);
        $saran = escape($_POST['saran']);
        $status_mcu = escape($_POST['status_mcu']);
        $dokter_pemeriksa = escape($_POST['dokter_pemeriksa']);
        
        // Update patient status to completed
        $update_query = "UPDATE pasien SET status_pendaftaran = 'selesai' WHERE id = $id";
        mysqli_query($conn, $update_query);
        
        $insert_query = "INSERT INTO pemeriksaan (
            pasien_id, pemeriksa_role, 
            telinga_status, telinga_keterangan, hidung_status, hidung_keterangan, 
            tenggorokan_status, tenggorokan_keterangan, gigi_keterangan, leher_kgb,
            paru_auskultasi, paru_palpasi, paru_perkusi,
            operasi, keterangan_operasi, obesitas, organomegali, hernia, 
            nyeri_epigastrium, nyeri_abdomen, bising_usus, hepatomegali,
            biceps, triceps, patella, achilles, plantar_response,
            kesimpulan, saran, status_mcu, dokter_pemeriksa, pemeriksa_id
        ) VALUES (
            $id, '$role',
            '$telinga_status', '$telinga_keterangan', '$hidung_status', '$hidung_keterangan',
            '$tenggorokan_status', '$tenggorokan_keterangan', '$gigi_keterangan', '$leher_kgb',
            '$paru_auskultasi', '$paru_palpasi', '$paru_perkusi',
            $operasi, '$keterangan_operasi', $obesitas, $organomegali, $hernia,
            $nyeri_epigastrium, $nyeri_abdomen, $bising_usus, '$hepatomegali',
            '$biceps', '$triceps', '$patella', '$achilles', '$plantar_response',
            '$kesimpulan', '$saran', '$status_mcu', '$dokter_pemeriksa', {$_SESSION['admin_id']}
        )";
    }
    
    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success'] = "Pemeriksaan berhasil disimpan!";
        redirect('detail.php?id=' . $id);
    } else {
        $_SESSION['error'] = "Gagal menyimpan pemeriksaan: " . mysqli_error($conn);
    }
}

// Set role title
$role_titles = [
    'pendaftaran' => 'Pendaftaran (Sirkulasi)',
    'dokter_mata' => 'Dokter Mata',
    'dokter_umum' => 'Dokter Umum'
];

$role_title = $role_titles[$role];
?>

<?php include '../../includes/header.php'; ?>
<?php include '../includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include '../includes/admin-sidebar.php'; ?>
        </div>

        <div class="col-lg-10">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="../list.php">Pasien</a></li>
                    <li class="breadcrumb-item"><a href="../detail.php?id=<?php echo $id; ?>">Detail</a></li>
                    <li class="breadcrumb-item active">Pemeriksaan <?php echo $role_title; ?></li>
                </ol>
            </nav>
            
            <!-- Patient Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i> Informasi Pasien
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">Kode MCU</th>
                                    <td><strong><?php echo $patient['kode_mcu']; ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td><?php echo htmlspecialchars($patient['nama']); ?></td>
                                </tr>
                                <tr>
                                    <th>Usia</th>
                                    <td><?php echo $patient['usia']; ?> tahun</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">Perusahaan</th>
                                    <td><?php echo $patient['perusahaan'] ?: '-'; ?></td>
                                </tr>
                                <tr>
                                    <th>Posisi</th>
                                    <td><?php echo $patient['posisi_pekerjaan'] ?: '-'; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal MCU</th>
                                    <td><?php echo formatDateIndo($patient['tanggal_mcu']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Examination Form -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i> Formulir Pemeriksaan <?php echo $role_title; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="examinationForm">
                        
                        <?php if ($role == 'pendaftaran'): ?>
                            <!-- SIRKULASI FORM -->
                            <h6 class="border-bottom pb-2 mb-3">SIRKULASI</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tekanan Darah (mmHg)</label>
                                    <input type="text" class="form-control" name="tekanan_darah" 
                                           placeholder="120/80" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nadi (bpm)</label>
                                    <input type="number" class="form-control" name="nadi" 
                                           min="40" max="200" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Suhu (°C)</label>
                                    <input type="number" step="0.1" class="form-control" name="suhu" 
                                           min="35" max="42" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Respirasi (x/menit)</label>
                                    <input type="number" class="form-control" name="respirasi" 
                                           min="10" max="40" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tinggi Badan (cm)</label>
                                    <input type="number" class="form-control" name="tinggi_badan" 
                                           min="100" max="250" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Berat Badan (kg)</label>
                                    <input type="number" step="0.1" class="form-control" name="berat_badan" 
                                           min="20" max="200" required>
                                </div>
                            </div>
                            
                        <?php elseif ($role == 'dokter_mata'): ?>
                            <!-- MATA FORM -->
                            <h6 class="border-bottom pb-2 mb-3">PEMERIKSAAN MATA</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>VISUS KANAN</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Jauh</label>
                                            <select class="form-select" name="visus_kanan_jauh">
                                                <option value="">- Pilih -</option>
                                                <option value="6/6">6/6</option>
                                                <option value="6/9">6/9</option>
                                                <option value="6/12">6/12</option>
                                                <option value="6/18">6/18</option>
                                                <option value="6/24">6/24</option>
                                                <option value="6/36">6/36</option>
                                                <option value="6/60">6/60</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Dekat</label>
                                            <select class="form-select" name="visus_kanan_dekat">
                                                <option value="">- Pilih -</option>
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>VISUS KIRI</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Jauh</label>
                                            <select class="form-select" name="visus_kiri_jauh">
                                                <option value="">- Pilih -</option>
                                                <option value="6/6">6/6</option>
                                                <option value="6/9">6/9</option>
                                                <option value="6/12">6/12</option>
                                                <option value="6/18">6/18</option>
                                                <option value="6/24">6/24</option>
                                                <option value="6/36">6/36</option>
                                                <option value="6/60">6/60</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Dekat</label>
                                            <select class="form-select" name="visus_kiri_dekat">
                                                <option value="">- Pilih -</option>
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Anemia</label>
                                    <select class="form-select" name="anemia">
                                        <option value="">- Pilih -</option>
                                        <option value="Tidak">Tidak</option>
                                        <option value="Ringan">Ringan</option>
                                        <option value="Sedang">Sedang</option>
                                        <option value="Berat">Berat</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Buta Warna</label>
                                    <select class="form-select" name="buta_warna">
                                        <option value="">- Pilih -</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Merah/Hijau">Merah/Hijau</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lapang Pandang</label>
                                    <select class="form-select" name="lapang_pandang">
                                        <option value="">- Pilih -</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Abnormal">Abnormal</option>
                                    </select>
                                </div>
                            </div>
                            
                        <?php elseif ($role == 'dokter_umum'): ?>
                            <!-- DOKTER UMUM FORM -->
                            
                            <!-- TELINGA, HIDUNG, TENGGOROKAN -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">TELINGA, HIDUNG, TENGGOROKAN</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Telinga</label>
                                            <select class="form-select" name="telinga_status">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                            <textarea class="form-control mt-2" name="telinga_keterangan" 
                                                      rows="2" placeholder="Keterangan..."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Hidung</label>
                                            <select class="form-select" name="hidung_status">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                            <textarea class="form-control mt-2" name="hidung_keterangan" 
                                                      rows="2" placeholder="Keterangan..."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Tenggorokan</label>
                                            <select class="form-select" name="tenggorokan_status">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                            <textarea class="form-control mt-2" name="tenggorokan_keterangan" 
                                                      rows="2" placeholder="Keterangan..."></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Gigi</label>
                                            <textarea class="form-control" name="gigi_keterangan" 
                                                      rows="2" placeholder="Kondisi gigi..."></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Leher (KGB)</label>
                                            <textarea class="form-control" name="leher_kgb" 
                                                      rows="2" placeholder="Pembesaran KGB..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PEMERIKSAAN THORAX -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">PEMERIKSAAN THORAX</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Auskultasi Paru</label>
                                            <select class="form-select" name="paru_auskultasi">
                                                <option value="Normal">Normal</option>
                                                <option value="Wheezing">Wheezing</option>
                                                <option value="Ronchi">Ronchi</option>
                                                <option value="Crackles">Crackles</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Palpasi</label>
                                            <input type="text" class="form-control" name="paru_palpasi" 
                                                   placeholder="Vokal Fremitus...">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Perkusi</label>
                                            <select class="form-select" name="paru_perkusi">
                                                <option value="Sonor">Sonor</option>
                                                <option value="Hipersonor">Hipersonor</option>
                                                <option value="Pekak">Pekak</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ABDOMINAL -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">ABDOMINAL</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="operasi" id="operasi">
                                                <label class="form-check-label" for="operasi">
                                                    Riwayat Operasi
                                                </label>
                                            </div>
                                            <textarea class="form-control" name="keterangan_operasi" 
                                                      rows="2" placeholder="Keterangan operasi..."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="obesitas" id="obesitas">
                                                <label class="form-check-label" for="obesitas">
                                                    Obesitas
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="organomegali" id="organomegali">
                                                <label class="form-check-label" for="organomegali">
                                                    Organomegali
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="hernia" id="hernia">
                                                <label class="form-check-label" for="hernia">
                                                    Hernia
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="nyeri_epigastrium" id="nyeri_epigastrium">
                                                <label class="form-check-label" for="nyeri_epigastrium">
                                                    Nyeri Epigastrium
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="nyeri_abdomen" id="nyeri_abdomen">
                                                <label class="form-check-label" for="nyeri_abdomen">
                                                    Nyeri Abdomen
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="bising_usus" id="bising_usus">
                                                <label class="form-check-label" for="bising_usus">
                                                    Bising Usus
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label">Hepatomegali</label>
                                            <input type="text" class="form-control" name="hepatomegali" 
                                                   placeholder="Ukuran hepar...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- REFLEKS -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">REFLEKS</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Biceps</label>
                                            <select class="form-select" name="biceps">
                                                <option value="Normal">Normal</option>
                                                <option value="Hiperaktif">Hiperaktif</option>
                                                <option value="Hipoaktif">Hipoaktif</option>
                                                <option value="Negatif">Negatif</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Triceps</label>
                                            <select class="form-select" name="triceps">
                                                <option value="Normal">Normal</option>
                                                <option value="Hiperaktif">Hiperaktif</option>
                                                <option value="Hipoaktif">Hipoaktif</option>
                                                <option value="Negatif">Negatif</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Patella</label>
                                            <select class="form-select" name="patella">
                                                <option value="Normal">Normal</option>
                                                <option value="Hiperaktif">Hiperaktif</option>
                                                <option value="Hipoaktif">Hipoaktif</option>
                                                <option value="Negatif">Negatif</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Achilles</label>
                                            <select class="form-select" name="achilles">
                                                <option value="Normal">Normal</option>
                                                <option value="Hiperaktif">Hiperaktif</option>
                                                <option value="Hipoaktif">Hipoaktif</option>
                                                <option value="Negatif">Negatif</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Plantar Response</label>
                                            <select class="form-select" name="plantar_response">
                                                <option value="Normal">Normal</option>
                                                <option value="Babinski +">Babinski +</option>
                                                <option value="Babinski -">Babinski -</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- KESIMPULAN -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">KESIMPULAN HASIL MCU</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nama Dokter Pemeriksa</label>
                                            <input type="text" class="form-control" name="dokter_pemeriksa" 
                                                   value="<?php echo $_SESSION['nama_lengkap']; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status MCU</label>
                                            <select class="form-select" name="status_mcu" required>
                                                <option value="">- Pilih -</option>
                                                <option value="FIT">FIT TO WORK</option>
                                                <option value="UNFIT">UNFIT</option>
                                                <option value="FIT WITH NOTE">FIT WITH NOTE</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Kesimpulan</label>
                                            <textarea class="form-control" name="kesimpulan" 
                                                      rows="4" placeholder="Kesimpulan pemeriksaan..." required></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label">Saran</label>
                                            <textarea class="form-control" name="saran" 
                                                      rows="3" placeholder="Saran untuk pasien..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        <?php endif; ?>
                        
                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <a href="detail.php?id=<?php echo $id; ?>" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Simpan Pemeriksaan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('examinationForm').addEventListener('submit', function(e) {
    var isValid = true;
    var requiredFields = this.querySelectorAll('[required]');
    
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Harap lengkapi semua field yang wajib diisi!');
    }
});

// Remove invalid class when user starts typing
var inputs = document.querySelectorAll('input, select, textarea');
inputs.forEach(function(input) {
    input.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>

<?php include '../../includes/admin-footer.php'; ?>

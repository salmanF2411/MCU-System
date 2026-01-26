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
        $ikterik_keterangan = escape($_POST['ikterik_keterangan']);
        $buta_warna = escape($_POST['buta_warna']);
        $buta_warna_keterangan = escape($_POST['buta_warna_keterangan']);
        $lapang_pandang = escape($_POST['lapang_pandang']);
        $lapang_pandang_keterangan = escape($_POST['lapang_pandang_keterangan']);
        
        $insert_query = "INSERT INTO pemeriksaan (pasien_id, pemeriksa_role, visus_kanan_jauh, visus_kanan_dekat, visus_kiri_jauh, visus_kiri_dekat, anemia,ikterik_keterangan, buta_warna,buta_warna_keterangan, lapang_pandang,lapang_pandang_keterangan, pemeriksa_id) 
                         VALUES ($id, '$role', '$visus_kanan_jauh', '$visus_kanan_dekat', '$visus_kiri_jauh', '$visus_kiri_dekat', '$anemia', '$ikterik_keterangan', '$buta_warna','$buta_warna_keterangan', '$lapang_pandang','$lapang_pandang_keterangan', {$_SESSION['admin_id']})";
        
    } elseif ($role == 'dokter_umum') {
        // THT & Gigi
        $telinga_status = escape($_POST['telinga_status']);
        $telinga_keterangan = escape($_POST['telinga_keterangan']);
        $hidung_status = escape($_POST['hidung_status']);
        $hidung_keterangan = escape($_POST['hidung_keterangan']);
        $tenggorokan_status = escape($_POST['tenggorokan_status']);
        $tenggorokan_keterangan = escape($_POST['tenggorokan_keterangan']);
        $gigi_status = escape($_POST['gigi_status']);
        $gigi_keterangan = escape($_POST['gigi_keterangan']);
        $leher_kgb = escape($_POST['leher_kgb']);
        
        // Thorax Paru - Paru
        $paru_auskultasi = escape($_POST['paru_auskultasi']);
        $auskultasi_keterangan = escape($_POST['auskultasi_keterangan']);
        $paru_palpasi = escape($_POST['paru_palpasi']);
        $paru_perkusi = escape($_POST['paru_perkusi']);

        // Thorax Jantung
        $jantung_auskultasi = escape($_POST['jantung_auskultasi']);
        $jantung_keterangan = escape($_POST['jantung_keterangan']);
        $jantung_perkusi = escape($_POST['jantung_perkusi']);
        
        // Abdominal (boolean fields)
        $operasi = isset($_POST['operasi']) ? 1 : 0;
        $keterangan_operasi = escape($_POST['keterangan_operasi']);
        $obesitas = isset($_POST['obesitas']) ? 1 : 0;
        $organomegali = isset($_POST['organomegali']) ? 1 : 0;
        $hernia = isset($_POST['hernia']) ? 1 : 0;
        $nyeri_epigastrium = isset($_POST['nyeri_epigastrium']) ? 1 : 0;
        $nyeri_abdomen = isset($_POST['nyeri_abdomen']) ? 1 : 0;
        $bising_usus = isset($_POST['bising_usus']) ? 1 : 0;
        $hepar = isset($_POST['hepar']) ? 1 : 0;
        $striae = escape($_POST['striae']);
        $sikatriks = escape($_POST['sikatriks']);
        $psoas_sign = escape($_POST['psoas_sign']);
        $hepatomegali = escape($_POST['hepatomegali']);
        
        // Refleks
        $biceps = escape($_POST['biceps']);
        $triceps = escape($_POST['triceps']);
        $patella = escape($_POST['patella']);
        $achilles = escape($_POST['achilles']);
        $plantar_response = escape($_POST['plantar_response']);
        
        // Kesimpulan
        $kesimpulan = escape($_POST['kesimpulan']);

        // Process saran from checkboxes and manual input
        $saran_options = isset($_POST['saran_options']) ? $_POST['saran_options'] : [];
        $saran_manual = escape($_POST['saran_manual']);
        $saran_combined = '';

        if (!empty($saran_options)) {
            $saran_combined = implode("\n", array_map(function($saran) {
                return "- " . $saran;
            }, $saran_options));
        }

        if (!empty($saran_manual)) {
            if (!empty($saran_combined)) {
                $saran_combined .= "\n\nSaran Tambahan:\n" . $saran_manual;
            } else {
                $saran_combined = $saran_manual;
            }
        }

        $saran = $saran_combined;
        $status_mcu = escape($_POST['status_mcu']);
        $dokter_pemeriksa = escape($_POST['dokter_pemeriksa']);
        
        // Update patient status to completed
        $update_query = "UPDATE pasien SET status_pendaftaran = 'selesai' WHERE id = $id";
        mysqli_query($conn, $update_query);
        
        $insert_query = "INSERT INTO pemeriksaan (
            pasien_id, pemeriksa_role, 
            telinga_status, telinga_keterangan, hidung_status, hidung_keterangan, 
            tenggorokan_status, tenggorokan_keterangan,gigi_status, gigi_keterangan, leher_kgb,
            paru_auskultasi,auskultasi_keterangan, paru_palpasi, paru_perkusi,jantung_auskultasi, jantung_keterangan, jantung_perkusi,
            operasi, keterangan_operasi, obesitas, organomegali, hernia, 
            nyeri_epigastrium, nyeri_abdomen, bising_usus,hepar,striae, sikatriks, psoas_sign, hepatomegali,
            biceps, triceps, patella, achilles, plantar_response,
            kesimpulan, saran, status_mcu, dokter_pemeriksa, pemeriksa_id
        ) VALUES (
            $id, '$role',
            '$telinga_status', '$telinga_keterangan', '$hidung_status', '$hidung_keterangan',
            '$tenggorokan_status', '$tenggorokan_keterangan', '$gigi_status', '$gigi_keterangan', '$leher_kgb',
            '$paru_auskultasi', '$auskultasi_keterangan', '$paru_palpasi', '$paru_perkusi','$jantung_auskultasi', '$jantung_keterangan', '$jantung_perkusi',
            $operasi, '$keterangan_operasi', $obesitas, $organomegali, $hernia,
            $nyeri_epigastrium, $nyeri_abdomen, $bising_usus, $hepar,'$striae','$sikatriks','$psoas_sign', '$hepatomegali',
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

<?php include '../../includes/admin-header.php'; ?>
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
                    <li class="breadcrumb-item"><a href="../dashboard.php?page=patients">Pasien</a></li>
                    <li class="breadcrumb-item"><a href="detail.php?id=<?php echo $id; ?>">Detail</a></li>
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
                                    <label class="form-label">Nadi (x/menit)</label>
                                    <input type="number" class="form-control" name="nadi" 
                                           min="40" max="200" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Suhu (°C)</label>
                                    <input type="number" step="0.1" class="form-control" name="suhu" 
                                           min="20" max="50" required>
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
                                            <textarea class="form-control" name="visus_kanan_jauh" 
                                                      rows="1" placeholder="6/"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Dekat</label>
                                            <textarea class="form-control" name="visus_kanan_dekat" 
                                                      rows="1" placeholder="6/"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>VISUS KIRI</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Jauh</label>
                                            <textarea class="form-control" name="visus_kiri_jauh" 
                                                      rows="1" placeholder="6/"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Dekat</label>
                                           <textarea class="form-control" name="visus_kiri_dekat" 
                                                      rows="1" placeholder="6/"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Anemis : Ikterik</label>
                                    <select class="form-select" name="anemia">
                                        <option value="">- Pilih -</option>
                                        <option value="Ikterik(-)">Ikterik (-) </option>
                                        <option value="Ikterik(+)">Ikterik (+) </option>
                                    </select>
                                    <textarea class="form-control mt-2" name="ikterik_keterangan" 
                                                      rows="2" placeholder="Keterangan Anemis : Ikterik..."></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Buta Warna</label>
                                    <select class="form-select" name="buta_warna">
                                        <option value="">- Pilih -</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Merah/Hijau">Merah/Hijau</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <textarea class="form-control mt-2" name="buta_warna_keterangan" 
                                                      rows="2" placeholder="Keterangan Buta Warna..."></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lapang Pandang</label>
                                    <select class="form-select" name="lapang_pandang">
                                        <option value="">- Pilih -</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Abnormal">Abnormal</option>
                                    </select>
                                    <textarea class="form-control mt-2" name="lapang_pandang_keterangan" 
                                                      rows="2" placeholder="Keterangan Lapang Pandang..."></textarea>
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
                                                      rows="2" placeholder="Keterangan Telinga..."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Hidung</label>
                                            <select class="form-select" name="hidung_status">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                            <textarea class="form-control mt-2" name="hidung_keterangan" 
                                                      rows="2" placeholder="Keterangan Hidung..."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Tenggorokan</label>
                                            <select class="form-select" name="tenggorokan_status">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                            <textarea class="form-control mt-2" name="tenggorokan_keterangan" 
                                                      rows="2" placeholder="Keterangan Tenggorokan..."></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Gigi</label>
                                            <select class="form-select" name="gigi_status">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                            <textarea class="form-control mt-2" name="gigi_keterangan" 
                                                      rows="2" placeholder="Keterangan Gigi..."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Leher (KGB)</label>
                                            <textarea class="form-control" name="leher_kgb" 
                                                      rows="2" placeholder="Pembesaran KGB..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PEMERIKSAAN THORAX PARU - PARU-->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">PEMERIKSAAN THORAX PARU - PARU</h6>
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
                                            <textarea class="form-control mt-2" name="auskultasi_keterangan" 
                                                      rows="2" placeholder="Keterangan Auskultasi Paru..."></textarea>
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

                            <!-- PEMERIKSAAN THORAX JANTUNG-->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">PEMERIKSAAN THORAX JANTUNG</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Auskultasi Jantung</label>
                                            <select class="form-select" name="jantung_auskultasi">
                                                <option value="Suara Normal">Suara Normal</option>
                                                <option value="Suara Tambahan">Suara Tambahan</option>
                                            </select>
                                            <textarea class="form-control mt-2" name="jantung_keterangan" 
                                                      rows="2" placeholder="Keterangan Auskultasi Jantung..."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Perkusi (Batas Jantung)</label>
                                            <select class="form-select" name="jantung_perkusi">
                                                <option value="Apex">Apex</option>
                                                <option value="Basal">Basal</option>
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
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="hepar" id="hepar">
                                                <label class="form-check-label" for="hepar">
                                                    Hepar
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label">GENITALIA</label>
                                            <input type="text" class="form-control" name="hepatomegali"
                                                   placeholder="Genitalia...">
                                        </div>
                                    </div>
                                    <br>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label">PEMERIKSAAN</label>
                                            <br>
                                            <label class="form-label">Striae</label>
                                            <input type="text" class="form-control" name="striae"
                                                   placeholder="Striae...">
                                            <label class="form-label">Sikatriks</label>
                                            <input type="text" class="form-control" name="sikatriks"
                                                   placeholder="Sikatriks...">
                                            <label class="form-label">PSOAS SIGN</label>
                                            <input type="text" class="form-control" name="psoas_sign"
                                                   placeholder="PSOAS SIGN...">
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
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Triceps</label>
                                            <select class="form-select" name="triceps">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Patella</label>
                                            <select class="form-select" name="patella">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Achilles</label>
                                            <select class="form-select" name="achilles">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Plantar Response</label>
                                            <select class="form-select" name="plantar_response">
                                                <option value="Normal">Normal</option>
                                                <option value="Abnormal">Abnormal</option>
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
                                            <div class="mb-3">
                                                <small class="text-muted">Pilih saran yang sesuai (boleh lebih dari satu):</small>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="saran_options[]" value="Jika terdapat keluhan mengenai Gigi, Konsultasi ke Dentist" id="saran1">
                                                            <label class="form-check-label" for="saran1">
                                                                - Jika terdapat keluhan mengenai Gigi, Konsultasi ke Dentist
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="saran_options[]" value="Konsultasi ke Opthalmologist jika penglihatan makin menurun" id="saran2">
                                                            <label class="form-check-label" for="saran2">
                                                                - Konsultasi ke Opthalmologist jika penglihatan makin menurun
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="saran_options[]" value="Diet Tinggi Zat Besi" id="saran3">
                                                            <label class="form-check-label" for="saran3">
                                                                - Diet Tinggi Zat Besi
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="saran_options[]" value="Jangan mengkonsumsi alkohol sehari sebelum bekerja." id="saran4">
                                                            <label class="form-check-label" for="saran4">
                                                                - Jangan mengkonsumsi alkohol sehari sebelum bekerja.
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="saran_options[]" value="Batasi aktivitas yang dapat mengganggu kesehatan saat kehamilan." id="saran5">
                                                            <label class="form-check-label" for="saran5">
                                                                - Batasi aktivitas yang dapat mengganggu kesehatan saat kehamilan.
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="saran_options[]" value="Jaga kebersihan telinga secara rutin dan berkala" id="saran6">
                                                            <label class="form-check-label" for="saran6">
                                                                - Jaga kebersihan telinga secara rutin dan berkala
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <textarea class="form-control" name="saran_manual" 
                                                      rows="3" placeholder="Saran tambahan (manual)..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        <?php endif; ?>
                        
                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <a href="../dashboard.php?page=patients" class="btn btn-secondary me-2">
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

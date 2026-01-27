<?php
ob_start();
$page_title = 'Pendaftaran MCU - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Generate kode MCU
    $kode_mcu = generateKodeMCU();

    // Data pasien
    $nama = escape($_POST['nama']);
    $jenis_kelamin = escape($_POST['jenis_kelamin']);
    $tempat_lahir = escape($_POST['tempat_lahir']);
    $tanggal_lahir = escape($_POST['tanggal_lahir']);
    $usia = calculateAge($tanggal_lahir);
    $alamat = escape($_POST['alamat']);
    $pendidikan = escape($_POST['pendidikan']);
    $agama = escape($_POST['agama']);
    $golongan_darah = escape($_POST['golongan_darah']);
    $no_telp = escape($_POST['no_telp']);
    $email = escape($_POST['email']);
    $perusahaan = escape($_POST['perusahaan']);
    $posisi_pekerjaan = escape($_POST['posisi_pekerjaan']);
    $tanggal_mcu = escape($_POST['tanggal_mcu']);

    // Insert pasien
    $sql = "INSERT INTO pasien (
        kode_mcu, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, usia,
        alamat, pendidikan, agama, golongan_darah, no_telp, email,
        perusahaan, posisi_pekerjaan, tanggal_mcu, status_pendaftaran
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssssisssssssss',
        $kode_mcu, $nama, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $usia,
        $alamat, $pendidikan, $agama, $golongan_darah, $no_telp, $email,
        $perusahaan, $posisi_pekerjaan, $tanggal_mcu
    );

    if (mysqli_stmt_execute($stmt)) {
        $pasien_id = mysqli_insert_id($conn);

        // Data keluarga (ayah)
        if (!empty($_POST['nama_ayah'])) {
            $nama_ayah = escape($_POST['nama_ayah']);
            $usia_ayah = escape($_POST['usia_ayah']);
            $kondisi_ayah = escape($_POST['kondisi_ayah']);
            $meninggal_ayah = escape($_POST['meninggal_ayah']);

            $sql_keluarga = "INSERT INTO keluarga_pasien (pasien_id, hubungan, nama, usia, kondisi, meninggal_tahun)
                            VALUES ($pasien_id, 'Ayah', '$nama_ayah', '$usia_ayah', '$kondisi_ayah', '$meninggal_ayah')";
            mysqli_query($conn, $sql_keluarga);
        }

        // Data keluarga (ibu)
        if (!empty($_POST['nama_ibu'])) {
            $nama_ibu = escape($_POST['nama_ibu']);
            $usia_ibu = escape($_POST['usia_ibu']);
            $kondisi_ibu = escape($_POST['kondisi_ibu']);
            $meninggal_ibu = escape($_POST['meninggal_ibu']);

            $sql_keluarga = "INSERT INTO keluarga_pasien (pasien_id, hubungan, nama, usia, kondisi, meninggal_tahun)
                            VALUES ($pasien_id, 'Ibu', '$nama_ibu', '$usia_ibu', '$kondisi_ibu', '$meninggal_ibu')";
            mysqli_query($conn, $sql_keluarga);
        }

        // Riwayat kesehatan sekarang
        if (isset($_POST['kesehatan_sekarang'])) {
            foreach ($_POST['kesehatan_sekarang'] as $kesehatan) {
                $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                               VALUES ($pasien_id, 'kesehatan_sekarang', '$kesehatan')";
                mysqli_query($conn, $sql_riwayat);
            }
        }

        // Penyakit dahulu
        if (isset($_POST['penyakit_dahulu'])) {
            foreach ($_POST['penyakit_dahulu'] as $penyakit) {
                $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                               VALUES ($pasien_id, 'penyakit_dahulu', '$penyakit')";
                mysqli_query($conn, $sql_riwayat);
            }
        }

        // Jika ada penyakit lain
        if (!empty($_POST['penyakit_lain'])) {
            $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                           VALUES ($pasien_id, 'penyakit_dahulu', '{$_POST['penyakit_lain']}')";
            mysqli_query($conn, $sql_riwayat);
        }

        // Riwayat penyakit keluarga
        if (isset($_POST['riwayat_keluarga'])) {
            foreach ($_POST['riwayat_keluarga'] as $riwayat) {
                $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                               VALUES ($pasien_id, 'riwayat_keluarga', '$riwayat')";
                mysqli_query($conn, $sql_riwayat);
            }
        }

        // Jika ada riwayat keluarga lain
        if (!empty($_POST['riwayat_keluarga_lain'])) {
            $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                           VALUES ($pasien_id, 'riwayat_keluarga', '{$_POST['riwayat_keluarga_lain']}')";
            mysqli_query($conn, $sql_riwayat);
        }

        // Alergi
        if (isset($_POST['alergi'])) {
            foreach ($_POST['alergi'] as $alergi) {
                $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                               VALUES ($pasien_id, 'alergi', '$alergi')";
                mysqli_query($conn, $sql_riwayat);
            }
        }

        // Jika ada alergi lain
        if (!empty($_POST['alergi_lain'])) {
            $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                           VALUES ($pasien_id, 'alergi', '{$_POST['alergi_lain']}')";
            mysqli_query($conn, $sql_riwayat);
        }

        // Kebiasaan merokok
        if (isset($_POST['merokok'])) {
            $merokok = escape($_POST['merokok']);
            $jumlah_rokok = isset($_POST['jumlah_rokok']) ? escape($_POST['jumlah_rokok']) : '';

            $sql_kebiasaan = "INSERT INTO kebiasaan_pasien (pasien_id, jenis, keterangan)
                             VALUES ($pasien_id, 'merokok', '$merokok - $jumlah_rokok')";
            mysqli_query($conn, $sql_kebiasaan);
        }

        // Kebiasaan alkohol
        if (isset($_POST['alkohol'])) {
            $alkohol = escape($_POST['alkohol']);
            $jenis_alkohol = isset($_POST['jenis_alkohol']) ? escape($_POST['jenis_alkohol']) : '';

            $sql_kebiasaan = "INSERT INTO kebiasaan_pasien (pasien_id, jenis, keterangan)
                             VALUES ($pasien_id, 'alkohol', '$alkohol - $jenis_alkohol')";
            mysqli_query($conn, $sql_kebiasaan);
        }

        // Keluhan
        if (isset($_POST['keluhan'])) {
            foreach ($_POST['keluhan'] as $keluhan) {
                $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                               VALUES ($pasien_id, 'keluhan', '$keluhan')";
                mysqli_query($conn, $sql_riwayat);
            }
        }

        // Jika ada keluhan lain
        if (!empty($_POST['keluhan_lain'])) {
            $sql_riwayat = "INSERT INTO riwayat_kesehatan (pasien_id, kategori, nilai)
                           VALUES ($pasien_id, 'keluhan', '{$_POST['keluhan_lain']}')";
            mysqli_query($conn, $sql_riwayat);
        }

        $_SESSION['success'] = "Pendaftaran berhasil! Kode MCU Anda: <strong>$kode_mcu</strong>. Harap simpan kode ini untuk keperluan berikutnya.";
        redirect('daftar-success.php?kode=' . $kode_mcu);
    } else {
        $_SESSION['error'] = "Gagal melakukan pendaftaran. Silakan coba lagi.";
    }
}

require_once '../includes/header.php';
?>





<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-section text-center">
                <div class="welcome-icon mb-3">
                    <i class="fas fa-stethoscope fa-3x text-primary"></i>
                </div>
                <h1 class="page-title">Pendaftaran Medical Check Up</h1>
                <p class="lead text-muted" style="font-weight: bold;">Isi formulir pendaftaran dengan lengkap dan akurat untuk proses Medical Check Up yang optimal</p>
                <div class="alert alert-info border-0 shadow-sm">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Penting:</strong> Pastikan semua data yang Anda isi adalah benar dan akurat. Kode MCU akan dikirimkan setelah pendaftaran berhasil.
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-lg border-0">
        <div class="card-header bg-gradient-primary text-white">
            <div class="d-flex align-items-center">
                <i class="fas fa-clipboard-list fa-lg me-3 text-primary"></i>
                <div>
                    <h4 class="mb-0 text-primary">Formulir Pendaftaran MCU</h4>
                    <small class="text-primary">Sistem Pendaftaran Medical Check Up Klinik</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="" id="form-mcu" onsubmit="return validateForm()" novalidate>
                
                <!-- Bagian 1: Biodata -->
                <div class="section mb-5">
                    <h5 class="section-title border-bottom pb-2 mb-4">
                        <i class="fas fa-user me-2"></i> 1. BIODATA PELAMAR
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">- Pilih -</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="usia" class="form-label">Usia</label>
                            <input type="number" class="form-control" id="usia" name="usia" readonly>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tempat_lahir" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="golongan_darah" class="form-label">Golongan Darah</label>
                            <select class="form-select" id="golongan_darah" name="golongan_darah">
                                <option value="">- Pilih -</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                                <option value="O">O</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="pendidikan" class="form-label">Pendidikan Terakhir</label>
                            <select class="form-select" id="pendidikan" name="pendidikan">
                                <option value="">- Pilih -</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA">SMA/SMK</option>
                                <option value="D1/D2/D3">D1/D2/D3</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="agama" class="form-label">Agama</label>
                            <select class="form-select" id="agama" name="agama">
                                <option value="">- Pilih -</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="no_telp" class="form-label">Nomor Telepon/HP <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="no_telp" name="no_telp" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="perusahaan" class="form-label">Nama Perusahaan</label>
                            <input type="text" class="form-control" id="perusahaan" name="perusahaan">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="posisi_pekerjaan" class="form-label">Posisi Pekerjaan</label>
                            <input type="text" class="form-control" id="posisi_pekerjaan" name="posisi_pekerjaan">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mcu" class="form-label">Tanggal MCU yang Diinginkan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_mcu" name="tanggal_mcu" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- Bagian 2: Data Keluarga -->
                <div class="section mb-5">
                    <h5 class="section-title border-bottom pb-2 mb-4">
                        <i class="fas fa-users me-2"></i> 2. DATA KELUARGA
                    </h5>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6>Ayah</h6>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nama_ayah" class="form-label">Nama Ayah</label>
                            <input type="text" class="form-control" id="nama_ayah" name="nama_ayah">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="usia_ayah" class="form-label">Usia</label>
                            <input type="number" class="form-control" id="usia_ayah" name="usia_ayah" min="0" max="150">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="kondisi_ayah" class="form-label">Kondisi</label>
                            <select class="form-select" id="kondisi_ayah" name="kondisi_ayah">
                                <option value="">- Pilih -</option>
                                <option value="Sehat">Sehat</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Meninggal">Meninggal</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="meninggal_ayah" class="form-label">Meninggal Tahun</label>
                            <input type="number" class="form-control" id="meninggal_ayah" name="meninggal_ayah" min="1900" max="<?php echo date('Y'); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Ibu</h6>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nama_ibu" class="form-label">Nama Ibu</label>
                            <input type="text" class="form-control" id="nama_ibu" name="nama_ibu">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="usia_ibu" class="form-label">Usia</label>
                            <input type="number" class="form-control" id="usia_ibu" name="usia_ibu" min="0" max="150">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="kondisi_ibu" class="form-label">Kondisi</label>
                            <select class="form-select" id="kondisi_ibu" name="kondisi_ibu">
                                <option value="">- Pilih -</option>
                                <option value="Sehat">Sehat</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Meninggal">Meninggal</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="meninggal_ibu" class="form-label">Meninggal Tahun</label>
                            <input type="number" class="form-control" id="meninggal_ibu" name="meninggal_ibu" min="1900" max="<?php echo date('Y'); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Bagian 3: Riwayat Kesehatan -->
                <div class="section mb-5">
                    <h5 class="section-title border-bottom pb-2 mb-4">
                        <i class="fas fa-heartbeat me-2"></i> 3. RIWAYAT KESEHATAN
                    </h5>
                    
                    <!-- Status Kesehatan Sekarang -->
                    <div class="row mb-3">
                        <div class="col-12 mb-3">
                            <h6>Status Kesehatan Sekarang</h6>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kesehatan_sekarang[]" value="Sehat" id="sehat">
                                <label class="form-check-label" for="sehat">Sehat</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kesehatan_sekarang[]" value="Sakit" id="sakit">
                                <label class="form-check-label" for="sakit">Sakit</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kesehatan_sekarang[]" value="Konsumsi Obat" id="konsumsi_obat">
                                <label class="form-check-label" for="konsumsi_obat">Konsumsi Obat</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kesehatan_sekarang[]" value="Pemulihan" id="pemulihan">
                                <label class="form-check-label" for="pemulihan">Pemulihan</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Penyakit Dahulu -->
                    <div class="row mb-3">
                        <div class="col-12 mb-3">
                            <h6>Penyakit Dahulu <small class="text-muted">(Centang semua yang sesuai)</small></h6>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="penyakit_dahulu[]" value="TIDAK" id="tidak_penyakit">
                                <label class="form-check-label" for="tidak_penyakit">Tidak</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="penyakit_dahulu[]" value="HIPERTENSI" id="hipertensi">
                                <label class="form-check-label" for="hipertensi">Hipertensi</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="penyakit_dahulu[]" value="DIABETES" id="diabetes">
                                <label class="form-check-label" for="diabetes">Diabetes</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="penyakit_dahulu[]" value="TBC" id="tbc">
                                <label class="form-check-label" for="tbc">TBC</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="penyakit_dahulu[]" value="TRAUMA" id="trauma">
                                <label class="form-check-label" for="trauma">Trauma</label>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <label for="penyakit_lain" class="form-label">Penyakit Lainnya (jika ada)</label>
                            <input type="text" class="form-control" id="penyakit_lain" name="penyakit_lain" placeholder="Tulis penyakit lain yang pernah diderita">
                        </div>
                    </div>
                    
                    <!-- Riwayat Penyakit Keluarga -->
                    <div class="row mb-3">
                        <div class="col-12 mb-3">
                            <h6>Riwayat Penyakit Keluarga</h6>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="riwayat_keluarga[]" value="TIDAK" id="tidak_keluarga">
                                <label class="form-check-label" for="tidak_keluarga">Tidak</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="riwayat_keluarga[]" value="DIABETES" id="keluarga_diabetes">
                                <label class="form-check-label" for="keluarga_diabetes">Diabetes</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="riwayat_keluarga[]" value="HIPERTENSI" id="keluarga_hipertensi">
                                <label class="form-check-label" for="keluarga_hipertensi">Hipertensi</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="riwayat_keluarga[]" value="HEMOPILIA" id="hemofilia">
                                <label class="form-check-label" for="hemofilia">Hemofilia</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="riwayat_keluarga[]" value="TBC" id="keluarga_tbc">
                                <label class="form-check-label" for="keluarga_tbc">TBC</label>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <label for="riwayat_keluarga_lain" class="form-label">Penyakit Keluarga Lainnya (jika ada)</label>
                            <input type="text" class="form-control" id="riwayat_keluarga_lain" name="riwayat_keluarga_lain" placeholder="Tulis penyakit keluarga lainnya">
                        </div>
                    </div>
                    
                    <!-- Alergi -->
                    <div class="row mb-3">
                        <div class="col-12 mb-3">
                            <h6>Riwayat Alergi</h6>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="alergi[]" value="TIDAK" id="tidak_alergi">
                                <label class="form-check-label" for="tidak_alergi">Tidak</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="alergi[]" value="OBAT" id="alergi_obat">
                                <label class="form-check-label" for="alergi_obat">Obat</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="alergi[]" value="MAKANAN" id="alergi_makanan">
                                <label class="form-check-label" for="alergi_makanan">Makanan</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="alergi[]" value="CUACA PANAS" id="alergi_panas">
                                <label class="form-check-label" for="alergi_panas">Cuaca Panas</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="alergi[]" value="CUACA DINGIN" id="alergi_dingin">
                                <label class="form-check-label" for="alergi_dingin">Cuaca Dingin</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="alergi[]" value="DEBU" id="alergi_debu">
                                <label class="form-check-label" for="alergi_debu">Debu</label>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <label for="alergi_lain" class="form-label">Alergi Lainnya (jika ada)</label>
                            <input type="text" class="form-control" id="alergi_lain" name="alergi_lain" placeholder="Tulis alergi lainnya">
                        </div>
                    </div>
                </div>
                
                <!-- Bagian 4: Kebiasaan -->
                <div class="section mb-5">
                    <h5 class="section-title border-bottom pb-2 mb-4">
                        <i class="fas fa-smoking me-2"></i> 4. KEBIASAAN SEHARI-HARI
                    </h5>
                    
                    <!-- Merokok -->
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <h6>Merokok</h6>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="merokok" value="Tidak" id="tidak_merokok" checked>
                                <label class="form-check-label" for="tidak_merokok">Tidak</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="merokok" value="Kretek" id="kretek">
                                <label class="form-check-label" for="kretek">Kretek</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="merokok" value="Filter" id="filter">
                                <label class="form-check-label" for="filter">Filter</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="jumlah_rokok" class="form-label">Jumlah (batang/hari)</label>
                            <input type="number" class="form-control" id="jumlah_rokok" name="jumlah_rokok" min="0" max="100">
                        </div>
                    </div>
                    
                    <!-- Alkohol -->
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <h6>Konsumsi Alkohol</h6>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="alkohol" value="Tidak" id="tidak_alkohol" checked>
                                <label class="form-check-label" for="tidak_alkohol">Tidak</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="alkohol" value="Ya" id="ya_alkohol">
                                <label class="form-check-label" for="ya_alkohol">Ya</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="jenis_alkohol" class="form-label">Jenis Alkohol</label>
                            <input type="text" class="form-control" id="jenis_alkohol" name="jenis_alkohol" placeholder="Jenis alkohol yang dikonsumsi">
                        </div>
                    </div>
                </div>
                
                <!-- Bagian 5: Keluhan -->
                <div class="section mb-5">
                    <h5 class="section-title border-bottom pb-2 mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> 5. UMUM
                    </h5>
                    <p class="text-muted mb-3">Ceklis jika ada keluhan Sebelum/Sesudah Medical Check Up:</p>
                    
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="keluhan[]" value="EPILEPSI" id="epilepsi">
                                <label class="form-check-label" for="epilepsi">Epilepsi</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="keluhan[]" value="GANGUAN PENGLIHATAN" id="gangguan_penglihatan">
                                <label class="form-check-label" for="gangguan_penglihatan">Gangguan Penglihatan</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="keluhan[]" value="PENYAKIT PARU" id="penyakit_paru">
                                <label class="form-check-label" for="penyakit_paru">Penyakit Paru</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="keluhan[]" value="ASMA" id="asma">
                                <label class="form-check-label" for="asma">Asma</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="keluhan[]" value="GANGUAN PENDENGARAN" id="gangguan_pendengaran">
                                <label class="form-check-label" for="gangguan_pendengaran">Gangguan Pendengaran</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="keluhan[]" value="PEMBEDAHAN OPERASI" id="pembedahan">
                                <label class="form-check-label" for="pembedahan">Pembedahan/Operasi</label>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <label for="keluhan_lain" class="form-label">Keluhan Lainnya (jika ada)</label>
                            <input type="text" class="form-control" id="keluhan_lain" name="keluhan_lain" placeholder="Tulis keluhan lainnya">
                        </div>
                    </div>
                </div>
                
                <!-- Declaration -->
                <div class="section mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="declaration" required>
                        <label class="form-check-label" for="declaration">
                            Saya menyatakan bahwa data yang saya isi dalam formulir ini adalah benar dan akurat. 
                            Saya memahami dan menyetujui bahwa informasi yang berhubungan dengan pemeriksaan medis 
                            dan copy catatan medis saya dapat digunakan untuk kepentingan perusahaan maupun medis. 
                            Pernyataan ini saya buat dengan sebenar-benarnya, dengan akal dan pikiran sehat.
                        </label>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5 btn-mobile-responsive">
                        <i class="fas fa-paper-plane me-2"></i> Kirim Pendaftaran
                    </button>
                    <br>
                    <br>
                    <button type="reset" class="btn btn-secondary btn-lg px-5 ms-2 btn-mobile-responsive">
                        <i class="fas fa-redo me-2"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Calculate age from birth date
    document.getElementById('tanggal_lahir').addEventListener('change', function() {
        var birthDate = new Date(this.value);
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        document.getElementById('usia').value = age;
    });
    
    // Set minimum date for MCU date (tomorrow)
    document.getElementById('tanggal_mcu').min = new Date().toISOString().split('T')[0];
    
    // Form validation
    function validateForm() {
        // Check required fields
        var requiredFields = document.querySelectorAll('[required]');
        for (var i = 0; i < requiredFields.length; i++) {
            if (!requiredFields[i].value.trim()) {
                alert('Harap lengkapi semua field yang wajib diisi!');
                requiredFields[i].focus();
                return false;
            }
        }
        
        // Validate email if filled
        var email = document.getElementById('email').value;
        if (email && !validateEmail(email)) {
            alert('Format email tidak valid!');
            return false;
        }
        
        // Validate phone number
        var phone = document.getElementById('no_telp').value;
        if (!validatePhone(phone)) {
            alert('Format nomor telepon tidak valid! Minimal 10 digit angka.');
            return false;
        }
        
        return true;
    }
    
    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function validatePhone(phone) {
        // Remove non-digits
        var digits = phone.replace(/\D/g, '');
        return digits.length >= 10;
    }
    
    // Show/hide fields based on kondisi
    document.getElementById('kondisi_ayah').addEventListener('change', function() {
        var meninggalField = document.getElementById('meninggal_ayah');
        if (this.value === 'Meninggal') {
            meninggalField.required = true;
        } else {
            meninggalField.required = false;
            meninggalField.value = '';
        }
    });

    document.getElementById('kondisi_ibu').addEventListener('change', function() {
        var meninggalField = document.getElementById('meninggal_ibu');
        if (this.value === 'Meninggal') {
            meninggalField.required = true;
        } else {
            meninggalField.required = false;
            meninggalField.value = '';
        }
    });

    // Handle "Tidak" option logic for health history sections
    function handleTidakOption(sectionName, tidakId) {
        var checkboxes = document.querySelectorAll('input[name="' + sectionName + '[]"]');
        var tidakCheckbox = document.getElementById(tidakId);

        checkboxes.forEach(function(checkbox) {
            if (checkbox.id !== tidakId) {
                checkbox.addEventListener('change', function() {
                    if (this.checked && tidakCheckbox.checked) {
                        tidakCheckbox.checked = false;
                        checkboxes.forEach(function(cb) {
                            cb.disabled = false;
                        });
                    }
                });
            }
        });

        tidakCheckbox.addEventListener('change', function() {
            if (this.checked) {
                checkboxes.forEach(function(checkbox) {
                    if (checkbox.id !== tidakId) {
                        checkbox.checked = false;
                        checkbox.disabled = true;
                    }
                });
            } else {
                checkboxes.forEach(function(checkbox) {
                    checkbox.disabled = false;
                });
            }
        });
    }

    // Initialize the logic for each section
    handleTidakOption('penyakit_dahulu', 'tidak_penyakit');
    handleTidakOption('riwayat_keluarga', 'tidak_keluarga');
    handleTidakOption('alergi', 'tidak_alergi');
</script>

<style>
    .section {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid var(--primary-color);
    }

    .section-title {
        color: var(--primary-color);
        font-weight: 600;
    }

    .form-check-label {
        font-weight: 500;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 5px;
    }

    /* Mobile responsive adjustments */
    @media (max-width: 768px) {
        .btn-mobile-responsive {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>

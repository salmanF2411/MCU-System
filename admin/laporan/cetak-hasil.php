<?php
$page_title = 'Cetak Hasil MCU - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ==========================================
// LOGIKA GENERATE PDF
// ==========================================
if ($id > 0) {
    require_once('../../libs/fpdf/fpdf.php');

    // 1. Ambil Data Pasien
    $query = "SELECT p.* FROM pasien p WHERE p.id = $id";
    $result = mysqli_query($conn, $query);
    $patient = mysqli_fetch_assoc($result);
    if (!$patient) die("Pasien tidak ditemukan");

    // 2. Ambil Data Pemeriksaan (Flatten Data)
    $pemeriksaan_query = "SELECT * FROM pemeriksaan WHERE pasien_id = $id";
    $pemeriksaan_result = mysqli_query($conn, $pemeriksaan_query);

    $data = []; // Array tunggal untuk menampung semua data
    $dokters = [];

    // --- PERBAIKAN UTAMA DI SINI ---
    while ($row = mysqli_fetch_assoc($pemeriksaan_result)) {
        // Loop setiap kolom. Hanya masukkan ke $data jika nilainya TIDAK KOSONG.
        // Ini mencegah data Visus (dari row dokter mata) tertimpa kosong (oleh row dokter umum)
        foreach ($row as $key => $val) {
            if ($val !== null && $val !== '') {
                $data[$key] = $val;
            }
        }

        // Simpan nama dokter
        if($row['pemeriksa_role'] == 'dokter_umum') $dokters['umum'] = $row['dokter_pemeriksa'];
        if($row['pemeriksa_role'] == 'dokter_mata') $dokters['mata'] = $row['dokter_pemeriksa'];
    }

    // Prioritaskan data vital signs dari role 'pendaftaran' jika ada
    $vital_query = "SELECT tekanan_darah, nadi, suhu, respirasi, tinggi_badan, berat_badan FROM pemeriksaan WHERE pasien_id = $id AND pemeriksa_role = 'pendaftaran'";
    $vital_result = mysqli_query($conn, $vital_query);
    $vital_data = mysqli_fetch_assoc($vital_result);

    if ($vital_data) {
        // Pastikan vital data tidak kosong sebelum override
        foreach($vital_data as $k => $v) {
            if(!empty($v)) $data[$k] = $v;
        }
    }

    // Ambil Pengaturan Klinik
    $settings_query = "SELECT * FROM pengaturan LIMIT 1";
    $settings_result = mysqli_query($conn, $settings_query);
    $settings = mysqli_fetch_assoc($settings_result);

    // --- CLASS PDF CUSTOM ---
    class MCU_PDF extends FPDF {
        private $col_header = [196, 215, 155]; // Warna Hijau Muda (RGB)
        
        // Header Halaman (Kop Surat Custom)
        function Header() {
            global $settings;
            // Garis Dekorasi Atas (Arrow Style)
            $this->SetLineWidth(0.5);
            $this->SetDrawColor(0, 150, 0); // Garis Hijau Tua
            
            // Panah Kiri
            $this->Line(10, 10, 20, 10); $this->Line(20, 10, 25, 15); $this->Line(20, 20, 25, 15); $this->Line(10, 20, 20, 20);
            
            // Judul Tengah
            $this->SetFont('Arial','B',14);
            $this->SetTextColor(0, 100, 150); // Biru Laut
            $this->Cell(0, 8, 'HASIL MEDICAL CHECK UP', 0, 1, 'C');
            
            // Panah Kanan
            $maxX = 200; 
            $this->Line($maxX, 10, $maxX-10, 10); $this->Line($maxX-10, 10, $maxX-15, 15); $this->Line($maxX-10, 20, $maxX-15, 15); $this->Line($maxX, 20, $maxX-10, 20);
            
            // Garis Bawah Header
            $this->Line(10, 22, 200, 22);

            // Info Kontak Kecil
            $this->SetY(23);
            $this->SetFont('Arial','B',8);
            $this->SetTextColor(0);
            $kontak = "Mail: " . ($settings['email'] ?? 'alvarishklinik@gmail.com') . " ; Phone: " . ($settings['telepon'] ?? '(0263) 295 1465') . " ; WhatsApp: " . ($settings['whatsapp'] ?? '081234567890');
            $this->Cell(0, 5, $kontak, 0, 1, 'C');
            $this->Ln(5);
        }
        // Fungsi Helper: Baris Tabel dengan logika Warna Merah
        function RowResult($label, $value, $is_abnormal = false) {
            $this->SetFont('Arial','',9);
            $this->SetTextColor(0); // Default Hitam

            // Kolom Label (Kiri)
            $this->Cell(95, 6, '  ' . $label, 1, 0, 'L');

            // Kolom Nilai (Kanan)
            if ($is_abnormal) {
                $this->SetTextColor(255, 0, 0); // Merah
                $this->SetFont('Arial','B',9); // Tebal
            }

            $this->Cell(95, 6, '  ' . ($value ?: '-'), 1, 1, 'L');

            // Reset Warna
            $this->SetTextColor(0);
            $this->SetFont('Arial','',9);
        }
    }

    $pdf = new MCU_PDF('P','mm','A4');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(false); // Disable auto page break to force one page

    // --- BAGIAN 1: HEADER INFO ---
    $pdf->SetFont('Arial','',9);

    // Baris 1: Nama Perusahaan
    $pdf->Cell(35, 5, 'Nama Perusahaan', 0, 0);
    $pdf->Cell(5, 5, ':', 0, 0);
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(100, 5, strtoupper($patient['perusahaan'] ?: '-'), 0, 1);

    // Baris 2: Tanggal MCU
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(35, 5, 'Tanggal MCU', 0, 0);
    $pdf->Cell(5, 5, ':', 0, 0);
    $pdf->Cell(100, 5, formatDateIndo($patient['tanggal_mcu']), 0, 1);
    $pdf->Ln(2);

    // --- BAGIAN 2: GRID BIODATA & TIM ---
    $pdf->SetFillColor(196, 215, 155); // Hijau Header
    
    // Header Grid
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 7, 'BIODATA PELAMAR', 0, 0, 'L'); 
    $pdf->Cell(45, 7, 'TIM MEDICAL CHECK UP', 0, 1, 'R'); 
    
    // Baris 1 Grid
    $pdf->Cell(25, 7, ' NAMA', 1, 0, 'L', true);
    $pdf->SetTextColor(255, 0, 0); // Merah untuk Nama Pasien
    $pdf->Cell(70, 7, '  ' . strtoupper($patient['nama']), 1, 0, 'L');
    $pdf->SetTextColor(0); // Reset Hitam
    
    $pdf->Cell(35, 7, ' KOORDINATOR', 1, 0, 'C', true);
    $pdf->Cell(60, 7, '  dr ALDOS IRAWAN,MMRS', 1, 1, 'L');

    // Baris 2 Grid (Posisi & Anggota)
    $h_multi = 14; 
    $y_start = $pdf->GetY();
    
    // Kiri (Posisi)
    $pdf->Cell(25, $h_multi, ' POSISI', 1, 0, 'L', true);
    $pdf->Cell(70, $h_multi, '  ' . strtoupper($patient['posisi_pekerjaan'] ?: '-'), 1, 0, 'L');
    
    // Kanan (Anggota)
    $pdf->Cell(35, $h_multi, ' ANGGOTA', 1, 0, 'C', true);
    
    $x_now = $pdf->GetX();
    $pdf->SetFont('Arial','', 9);
    $pdf->Cell(60, $h_multi, '', 1, 0); // Bingkai luar
    
    // Isi Anggota Manual
    $pdf->SetXY($x_now, $y_start);
    $pdf->Cell(60, 4, '  Zr. Eneng Lisna Ependi', 0, 1);
    $pdf->SetX($x_now);
    $pdf->Cell(60, 5, '  Zr. Hartia Amelia', 0, 1);
    $pdf->SetX($x_now);
    $pdf->Cell(60, 5, '  Zr. Annisa Laila Amaliah', 0, 1);
    
    
    $pdf->SetY($y_start + $h_multi);
    $pdf->Ln(5);

    // --- BAGIAN 3: TABEL HASIL ---
    
    // Header Tabel
    $pdf->SetFillColor(196, 215, 155);
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(95, 6, 'PEMERIKSAAN', 1, 0, 'C', true);
    $pdf->Cell(95, 6, 'HASIL PEMERIKSAAN', 1, 1, 'C', true);

    // Sub-Header
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(190, 6, '  Tanda Vital Tubuh', 1, 1, 'L');

    // --- LOGIKA NORMAL/ABNORMAL ---
    function checkNormal($val, $type) {
        if (empty($val) || $val === '-') return false;
        
        $val_clean = strtolower(trim($val));

        switch($type) {
            case 'suhu':
                // Merah jika > 37.5
                $temp = floatval(str_replace(',', '.', $val));
                return $temp > 37.5;

            case 'tensi':
                if (preg_match('/(\d+)\/(\d+)/', $val, $matches)) {
                    $systolic = intval($matches[1]);
                    $diastolic = intval($matches[2]);
                    return $systolic > 140 || $diastolic > 90;
                }
                return false;

            case 'respirasi':
                return intval($val) > 20;

            case 'nadi':
                return intval($val) > 100;

            case 'visus':
                // 1. Jika "normal" atau "6/6" atau "6/6 (jauh)", return False (HITAM)
                if ($val_clean == 'normal' || $val_clean == '6/6' || strpos($val_clean, '6/6') === 0) {
                    return false;
                }

                // 2. Cek format "6/angka" menggunakan Regex
                if (preg_match('/6\/\s*(\d+)/', $val_clean, $matches)) {
                    $penyebut = intval($matches[1]);
                    // Jika penyebut > 6 (misal 6/7, 6/15) maka Abnormal (MERAH)
                    if ($penyebut > 6) return true; 
                    return false; // Jika 6/6 atau 6/5 (Hitam)
                }

                // 3. Fallback: Jika tidak mengandung '6/6' sama sekali, anggap abnormal (MERAH)
                // (Misal: 5/60, 1/300)
                return (strpos($val_clean, '6/6') === false);

            case 'fisik':
                $bad_words = ['karang', 'lubang', 'karies', 'radang', 'bengkak', 'nyeri', 'merah'];
                foreach($bad_words as $word) {
                    if (stripos($val, $word) !== false) return true;
                }
                return (stripos($val, 'tidak ada kelainan') === false && stripos($val, 'normal') === false);

            default: return false;
        }
    }

    // A - F (Vital Signs)
    $pdf->RowResult('A. Tekanan Darah', ($data['tekanan_darah'] ?? '-') . ' mmHg', checkNormal($data['tekanan_darah'] ?? '', 'tensi'));
    $pdf->RowResult('B. Respirasi', ($data['respirasi'] ?? '-') . ' x/menit', checkNormal($data['respirasi'] ?? '', 'respirasi'));
    $pdf->RowResult('C. Nadi', ($data['nadi'] ?? '-') . ' x/menit', checkNormal($data['nadi'] ?? '', 'nadi'));
    
    // Suhu
    $suhu = $data['suhu'] ?? 0;
    $is_suhu_abnormal = ($suhu > 37.5); 
    $pdf->RowResult('D. Suhu', $suhu . ' C', $is_suhu_abnormal);
    
    $pdf->RowResult('E. Tinggi Badan', ($data['tinggi_badan'] ?? '-') . ' cm');
    $pdf->RowResult('F. Berat Badan', ($data['berat_badan'] ?? '-') . ' kg');
    
    // G. Header Fisik
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(190, 6, '  G. Pemeriksaan Fisik Tubuh (Head to Toe)', 1, 1, 'L');
    
    // H - N (Fisik)
    $gigi = ($data['gigi_keterangan'] ?? '') ?: ($data['gigi_status'] ?? 'Tidak Ada Kelainan');
    $pdf->RowResult('H. Kepala (Gigi)', $gigi, checkNormal($gigi, 'fisik'));
    
    $leher = ($data['leher_kgb'] ?? 'Tidak Ada Kelainan');
    $pdf->RowResult('I. Leher', $leher, checkNormal($leher, 'fisik'));
    
    $dada = ($data['paru_auskultasi'] ?? 'Tidak Ada Kelainan');
    $pdf->RowResult('J. Dada', $dada, checkNormal($dada, 'fisik'));
    
    $perut = ($data['nyeri_abdomen'] ? 'Nyeri Tekan' : 'Tidak Ada Kelainan');
    $pdf->RowResult('K. Perut', $perut, $data['nyeri_abdomen']);
    
    $pdf->RowResult('L. Kelamin', 'Tidak Ada Kelainan'); 
    
    $tangan = ($data['tangan'] ?? 'Tidak Ada Kelainan');
    $pdf->RowResult('M. Tangan', 'Tidak Ada Kelainan'); 
    $pdf->RowResult('N. Kaki', 'Tidak Ada Kelainan'); 

    // --- BAGIAN VISUS KHUSUS (WARNA TERPISAH) ---
    $visus_ka = isset($data['visus_kanan_jauh']) ? $data['visus_kanan_jauh'] : '-';
    $visus_ki = isset($data['visus_kiri_jauh']) ? $data['visus_kiri_jauh'] : '-';
    
    // Cek status masing-masing mata
    $abnormal_ka = checkNormal($visus_ka, 'visus');
    $abnormal_ki = checkNormal($visus_ki, 'visus');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(95, 6, '  Hasil Pemeriksaan VISUS Mata', 1, 0, 'L');
    
    // Teknik membuat kotak manual agar bisa beda warna teks
    $x_now = $pdf->GetX();
    $y_now = $pdf->GetY();
    
    // 1. Gambar Border Kotak Kosong
    $pdf->Cell(95, 6, '', 1, 0); 
    
    // 2. Tulis Isi di dalam kotak (pakai XY manual)
    $pdf->SetXY($x_now, $y_now);
    
    $text_1 = '  Kanan = ';
    $text_val_1 = $visus_ka;
    $text_2 = ' dan Kiri = ';
    $text_val_2 = $visus_ki;
    
    // Kanan Label (Hitam)
    $pdf->SetFont('Arial','',9);
    $pdf->SetTextColor(0);
    $pdf->Cell($pdf->GetStringWidth($text_1), 6, $text_1, 0, 0);
    
    // Kanan Value (Merah jika abnormal)
    if($abnormal_ka) { $pdf->SetTextColor(255,0,0); $pdf->SetFont('Arial','B',9); }
    else { $pdf->SetTextColor(0); $pdf->SetFont('Arial','',9); }
    $pdf->Cell($pdf->GetStringWidth($text_val_1), 6, $text_val_1, 0, 0);
    
    // Separator (Hitam)
    $pdf->SetTextColor(0); $pdf->SetFont('Arial','',9);
    $pdf->Cell($pdf->GetStringWidth($text_2), 6, $text_2, 0, 0);
    
    // Kiri Value (Merah jika abnormal)
    if($abnormal_ki) { $pdf->SetTextColor(255,0,0); $pdf->SetFont('Arial','B',9); }
    else { $pdf->SetTextColor(0); $pdf->SetFont('Arial','',9); }
    $pdf->Cell($pdf->GetStringWidth($text_val_2), 6, $text_val_2, 0, 0);
    
    $pdf->SetTextColor(0); // Reset ke Hitam
    
    // Reset Posisi untuk baris berikutnya
    $pdf->SetXY($x_now + 95, $y_now); 
    $pdf->Ln(6); 

    // --- Lanjut PDF ---
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 7, '  Hasil Pemeriksaan Penunjang Laboratorium', 1, 0, 'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(95, 7, '  (-)', 1, 1, 'L');
    
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 7, '  Riwayat Penyakit Dahulu / Sekarang', 1, 0, 'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(95, 7, '  (-)', 1, 1, 'L');
    
    $pdf->Ln(5);

    // --- KESIMPULAN & SARAN (DINAMIS) ---
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(190, 6, 'KESIMPULAN DAN SARAN HASIL MCU', 0, 1, 'C');
    $pdf->Ln(2);

    // 4.1 Status Kesehatan
    $pdf->SetFillColor(196, 215, 155);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(55, 8, ' STATUS KESEHATAN', 1, 0, 'C', true);
    
    $status_mcu = strtoupper($data['status_mcu'] ?? '-');
    $pdf->SetFont('Arial','BU',11);
    if ($status_mcu == 'FIT WITH NOTE' || $status_mcu == 'UNFIT') {
        $pdf->SetTextColor(255, 0, 0);
    } else {
        $pdf->SetTextColor(0, 150, 0);
    }
    $pdf->Cell(135, 8, $status_mcu, 1, 1, 'C');
    $pdf->SetTextColor(0);

    // 4.2 Saran (Logic Tinggi Dinamis)
    $saran_text = $data['saran'] ?: '-';
    
    $pdf->SetFont('Arial','',9); // Font saran diperkecil sedikit
    $line_height = 4.5; // Jarak baris rapat
    $cell_width_label = 55;
    $cell_width_content = 135;
    
    $x_start = $pdf->GetX();
    $y_start = $pdf->GetY();
    
    // Gambar Konten Saran (Kanan) dulu untuk hitung tinggi
    $pdf->SetXY($x_start + $cell_width_label, $y_start);
    $pdf->MultiCell($cell_width_content, $line_height, $saran_text, 1, 'L');
    
    $y_end = $pdf->GetY();
    $dynamic_height = $y_end - $y_start;
    
    // Minimal tinggi 10mm
    if ($dynamic_height < 10) {
        $dynamic_height = 10;
        $pdf->SetY($y_start + 10);
    }

    // Gambar Label "SARAN" (Kiri) menyesuaikan tinggi konten
    $pdf->SetXY($x_start, $y_start);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell($cell_width_label, $dynamic_height, ' SARAN', 1, 0, 'C', true);
    
    // Pindahkan kursor ke bawah kotak
    $pdf->SetY($y_end < ($y_start + 10) ? ($y_start + 10) : $y_end);

    // --- TANDA TANGAN ---
    $pdf->Ln(3);
    if ($pdf->GetY() > 250) $pdf->AddPage();

    $pdf->SetX(120);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(80, 4, 'Cianjur, ' . formatDateIndo($patient['tanggal_mcu']), 0, 1, 'C');
    $pdf->SetX(120);
    $pdf->Cell(80, 4, 'Mengetahui,', 0, 1, 'C');

    $pdf->Ln(15);

    $pdf->SetX(120);
    $pdf->SetFont('Arial','BU',10);
    $pdf->Cell(80, 4, 'dr. Hj Siti Isye Nasripah', 0, 1, 'C');
    $pdf->SetX(120);
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(80, 3, '(Penanggung Jawab MCU - Klinik)', 0, 1, 'C');

    // Output PDF
    $filename = 'Hasil_MCU_' . preg_replace('/[^a-zA-Z0-9]/', '_', $patient['nama']) . '.pdf';
    $pdf->Output('I', $filename);
    exit;
}
// Filter parameters for listing page
$search = isset($_GET['search']) ? escape($_GET['search']) : '';

// Build query
$where = "p.status_pendaftaran = 'selesai'";

if ($search) {
    $where .= " AND (p.nama LIKE '%$search%' OR p.kode_mcu LIKE '%$search%' OR p.no_telp LIKE '%$search%')";
}

// Get patients with completed MCU results
$query = "SELECT p.* FROM pasien p
          WHERE $where
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Get statistics
$stats_query = "SELECT
                COUNT(DISTINCT p.id) as total
                FROM pasien p
                WHERE $where";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
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
                    <i class="fas fa-file-medical me-2"></i> Cetak Hasil MCU
                </h1>
            </div>

            <!-- Filter Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Pencarian</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-12">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Cari nama/kode MCU/telp..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="cetak-hasil.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Data Hasil MCU
                        <span class="badge bg-primary ms-2"><?php echo mysqli_num_rows($result); ?> data</span>
                    </h5>
                    <!-- <div>
                        <button onclick="printReport()" class="btn btn-success me-2">
                            <i class="fas fa-print me-2"></i> Cetak Semua
                        </button>
                        <button onclick="exportToExcel()" class="btn btn-primary">
                            <i class="fas fa-file-excel me-2"></i> Excel
                        </button>
                    </div> -->
                </div>
                <div class="card-body">
                    <!-- Print Header -->
                    <div id="printHeader" class="d-none">
                        <div class="text-center mb-4">
                            <h3><?php echo getSetting('nama_klinik'); ?></h3>
                            <h4>Laporan Hasil MCU</h4>
                            <p>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
                        </div>
                    </div>

                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="reportTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Kode MCU</th>
                                        <th>Nama</th>
                                        <th>Usia</th>
                                        <th>Perusahaan</th>
                                        <th>Tanggal MCU</th>
                                        <th>Alamat</th>
                                        <th>No HP</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php while ($patient = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $patient['kode_mcu']; ?></td>
                                            <td><?php echo htmlspecialchars($patient['nama']); ?></td>
                                            <td><?php echo $patient['usia']; ?> thn</td>
                                            <td><?php echo $patient['perusahaan'] ?: '-'; ?></td>
                                            <td><?php echo formatDateIndo($patient['tanggal_mcu']); ?></td>
                                            <td><?php echo $patient['alamat'] ?: '-'; ?></td>
                                            <td><?php echo $patient['no_telp'] ?: '-'; ?></td>
                                            <td>
                                                <a href="../pasien/detail.php?id=<?php echo $patient['id']; ?>&from=cetak-hasil"
                                                   class="btn btn-sm btn-info me-1" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="cetak-hasil.php?id=<?php echo $patient['id']; ?>"
                                                   class="btn btn-sm btn-success" target="_blank">
                                                    <i class="fas fa-print me-1"></i> Cetak PDF
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Tidak ada data hasil MCU untuk periode yang dipilih.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Print function
function printReport() {
    const printContent = document.getElementById('printHeader').innerHTML +
                         document.getElementById('reportTable').outerHTML;

    const originalContent = document.body.innerHTML;

    document.body.innerHTML = `
        <html>
        <head>
            <title>Laporan Hasil MCU</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                @media print {
                    @page { size: landscape; }
                }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `;

    window.print();
    document.body.innerHTML = originalContent;
    window.location.reload();
}

// Export to Excel function
function exportToExcel() {
    const table = document.getElementById('reportTable');
    let csv = [];

    // Get headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent);
    });
    csv.push(headers.join(','));

    // Get rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push(`"${td.textContent}"`);
        });
        csv.push(row.join(','));
    });

    // Create download link
    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `laporan-hasil-mcu-${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php include '../../includes/footer.php'; ?>
?>
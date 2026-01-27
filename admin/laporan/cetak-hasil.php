<?php
$page_title = 'Cetak Hasil MCU - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- LOGIKA UTAMA GENERATE PDF ---
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

    while ($row = mysqli_fetch_assoc($pemeriksaan_result)) {
        // Gabungkan semua field ke array $data
        $data = array_merge($data, $row);

        // Simpan nama dokter
        if($row['pemeriksa_role'] == 'dokter_umum') $dokters['umum'] = $row['dokter_pemeriksa'];
        if($row['pemeriksa_role'] == 'dokter_mata') $dokters['mata'] = $row['dokter_pemeriksa'];
    }

    // Ambil data vital signs dari pendaftaran secara spesifik
    $vital_query = "SELECT tekanan_darah, nadi, suhu, respirasi, tinggi_badan, berat_badan FROM pemeriksaan WHERE pasien_id = $id AND pemeriksa_role = 'pendaftaran'";
    $vital_result = mysqli_query($conn, $vital_query);
    $vital_data = mysqli_fetch_assoc($vital_result);

    // Override data vital signs dengan data dari pendaftaran
    if ($vital_data) {
        $data['tekanan_darah'] = $vital_data['tekanan_darah'];
        $data['nadi'] = $vital_data['nadi'];
        $data['suhu'] = $vital_data['suhu'];
        $data['respirasi'] = $vital_data['respirasi'];
        $data['tinggi_badan'] = $vital_data['tinggi_badan'];
        $data['berat_badan'] = $vital_data['berat_badan'];
    }

    // Ambil Pengaturan
    $settings_query = "SELECT * FROM pengaturan LIMIT 1";
    $settings_result = mysqli_query($conn, $settings_query);
    $settings = mysqli_fetch_assoc($settings_result);

    class MCU_PDF extends FPDF {
        private $col_header = [196, 215, 155]; // Warna Hijau Muda (RGB)
        
        // Header Halaman (Kop Surat Custom)
        function Header() {
            global $settings;
            // 1. Garis Dekorasi Atas (Arrow Style Sederhana)
            $this->SetLineWidth(0.5);
            $this->SetDrawColor(0, 150, 0); // Garis Hijau Tua
            
            // Panah Kiri
            $this->Line(10, 10, 20, 10); $this->Line(20, 10, 25, 15); $this->Line(20, 20, 25, 15); $this->Line(10, 20, 20, 20);
            
            // Judul Tengah
            $this->SetFont('Arial','B',16);
            $this->SetTextColor(0, 100, 150); // Biru Laut
            $this->Cell(0, 10, 'HASIL MEDICAL CHECK UP', 0, 1, 'C');
            
            // Panah Kanan
            $maxX = 200; 
            $this->Line($maxX, 10, $maxX-10, 10); $this->Line($maxX-10, 10, $maxX-15, 15); $this->Line($maxX-10, 20, $maxX-15, 15); $this->Line($maxX, 20, $maxX-10, 20);
            
            // Garis Bawah Header
            $this->Line(10, 22, 200, 22);

            // Info Kontak Kecil
            $this->SetY(23);
            $this->SetFont('Arial','B',8);
            $this->SetTextColor(0);
            $kontak = "Mail: " . ($settings['email'] ?? 'alvarishklinik@gmail.com') . " ; Phone: " . ($settings['telepon'] ?? '(0263) 295 1465');
            $this->Cell(0, 5, $kontak, 0, 1, 'C');
            $this->Ln(5);
        }

        // Footer Halaman
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'R');
        }

        // Fungsi Helper untuk baris tabel dengan warna merah kondisional
        function RowResult($label, $value, $is_abnormal = false) {
            $this->SetFont('Arial','',10);
            $this->SetTextColor(0); // Default Hitam
            
            // Kolom Label (Kiri)
            $this->Cell(95, 7, '  ' . $label, 1, 0, 'L');
            
            // Kolom Nilai (Kanan)
            if ($is_abnormal) {
                $this->SetTextColor(255, 0, 0); // Merah
                $this->SetFont('Arial','B',10); // Tebal
            }
            
            $this->Cell(95, 7, '  ' . ($value ?: '-'), 1, 1, 'L');
            
            // Reset Warna
            $this->SetTextColor(0);
            $this->SetFont('Arial','',10);
        }

        // Fungsi Helper Header Section Hijau
        function SectionHeader($text) {
            $this->SetFillColor($this->col_header[0], $this->col_header[1], $this->col_header[2]);
            $this->SetFont('Arial','B',10);
            $this->Cell(190, 7, $text, 1, 1, 'C', true);
        }
    }

    $pdf = new MCU_PDF('P','mm','A4');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 10);

    // --- BAGIAN 1: HEADER INFORMASI PERUSAHAAN ---
    $pdf->SetFont('Arial','',10);
    
    // Baris 1: Nama Perusahaan
    $pdf->Cell(35, 6, 'Nama Perusahaan', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(100, 6, strtoupper($patient['perusahaan'] ?: '-'), 0, 1);
    
    // Baris 2: Tanggal MCU
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(35, 6, 'Tanggal MCU', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(100, 6, formatDateIndo($patient['tanggal_mcu']), 0, 1);
    $pdf->Ln(2);

    // --- BAGIAN 2: BIODATA & TIM MCU (GRID) ---
    // Warna Header Tabel
    $pdf->SetFillColor(196, 215, 155); 
    
    // Header Grid
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 7, 'BIODATA PELAMAR', 0, 0, 'L'); // Judul Kiri (Tanpa kotak)
    $pdf->Cell(95, 7, 'TIM MEDICAL CHECK UP', 0, 1, 'R'); // Judul Kanan
    
    // Baris 1 Grid: NAMA & KOORDINATOR
    $pdf->Cell(25, 7, ' NAMA', 1, 0, 'L', true);
    $pdf->SetTextColor(255, 0, 0); // Merah untuk Nama Pasien
    $pdf->Cell(70, 7, '  ' . strtoupper($patient['nama']), 1, 0, 'L');
    $pdf->SetTextColor(0); // Reset Hitam
    
    $pdf->Cell(35, 7, ' KOORDINATOR', 1, 0, 'C', true);
    $pdf->Cell(60, 7, '  dr. ' . ($dokters['umum'] ?? '-'), 1, 1, 'L');

    // Baris 2 Grid: POSISI & ANGGOTA
    $h_multi = 14; // Tinggi baris untuk anggota (multicell simulation)
    $y_start = $pdf->GetY();
    
    // Kiri (Posisi)
    $pdf->Cell(25, $h_multi, ' POSISI', 1, 0, 'L', true);
    $pdf->Cell(70, $h_multi, '  ' . strtoupper($patient['posisi_pekerjaan'] ?: '-'), 1, 0, 'L');
    
    // Kanan (Anggota - Hardcode atau dari DB)
    $pdf->Cell(35, $h_multi, ' ANGGOTA', 1, 0, 'C', true);
    
    // Simpan posisi X,Y untuk MultiCell manual
    $x_now = $pdf->GetX();
    $pdf->SetFont('Arial','', 9);
    $pdf->Cell(60, $h_multi, '', 1, 0); // Bingkai kosong dulu
    
    $pdf->SetXY($x_now, $y_start);
    $pdf->Cell(60, 5, '  Zr. Eneng Lisna Ependi', 0, 1);
    $pdf->SetX($x_now);
    $pdf->Cell(60, 5, '  Zr. Hartia Amelia', 0, 1);
    $pdf->SetX($x_now);
    $pdf->Cell(60, 4, '  ' . ($dokters['mata'] ?? '-'), 0, 0); // Dokter mata sbg anggota
    
    $pdf->SetY($y_start + $h_multi); // Reset Y ke bawah grid
    $pdf->Ln(5);

    // --- BAGIAN 3: TABEL HASIL PEMERIKSAAN ---
    
    // Header Tabel Utama
    $pdf->SetFillColor(196, 215, 155);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 7, 'PEMERIKSAAN', 1, 0, 'C', true);
    $pdf->Cell(95, 7, 'HASIL PEMERIKSAAN', 1, 1, 'C', true);

    // Sub-Header: Tanda Vital
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(190, 7, '  Tanda Vital Tubuh', 1, 1, 'L');

    // --- LOGIKA NORMAL/ABNORMAL ---
    // Fungsi cek normalitas sesuai dengan detail page
    function checkNormal($val, $type) {
        if (empty($val) || $val === '-') {
            return false;
        }

        switch($type) {
            case 'suhu':
                // Normal temperature: 36.5 - 37.5 °C
                $temp = floatval($val);
                return $temp < 36.5 || $temp > 37.5;

            case 'tensi':
                // Normal blood pressure: systolic 90-140, diastolic 60-90
                if (preg_match('/(\d+)\/(\d+)/', $val, $matches)) {
                    $systolic = intval($matches[1]);
                    $diastolic = intval($matches[2]);
                    return $systolic < 90 || $systolic > 140 || $diastolic < 60 || $diastolic > 90;
                }
                return false;

            case 'nadi':
                // Normal pulse: 60-100 bpm
                $pulse = intval($val);
                return $pulse < 60 || $pulse > 100;

            case 'respirasi':
                // Normal respiration: 12-20 breaths/min
                $resp = intval($val);
                return $resp < 12 || $resp > 20;

            case 'visus':
                // Merah jika tidak 6/6 atau normal
                return (strpos($val, '6/6') === false && stripos($val, 'normal') === false);

            case 'fisik':
                // Merah jika ada kata-kata negatif
                $bad_words = ['karang', 'lubang', 'karies', 'radang', 'bengkak', 'caries'];
                foreach($bad_words as $word) {
                    if (stripos($val, $word) !== false) return true;
                }
                return (stripos($val, 'tidak ada kelainan') === false && stripos($val, 'normal') === false);

            default: return false;
        }
    }

    // A. Tekanan Darah
    $pdf->RowResult('A. Tekanan Darah', ($data['tekanan_darah'] ?? '-') . ' mmHg', checkNormal($data['tekanan_darah'] ?? '', 'tensi'));
    
    // B. Respirasi
    $pdf->RowResult('B. Respirasi', ($data['respirasi'] ?? '-') . ' x/menit'); // Biasanya jarang merah kecuali sesak
    
    // C. Nadi
    $pdf->RowResult('C. Nadi', ($data['nadi'] ?? '-') . ' x/menit');
    
    // D. Suhu (CUSTOM LOGIC REQUESTED)
    $suhu = $data['suhu'] ?? 0;
    // Ubah angka 37.5 di bawah ini menjadi 35 jika Anda ingin merah saat > 35
    $is_suhu_abnormal = ($suhu > 37.5); 
    $pdf->RowResult('D. Suhu', $suhu . ' C', $is_suhu_abnormal);
    
    // E. Tinggi & Berat
    $pdf->RowResult('E. Tinggi Badan', ($data['tinggi_badan'] ?? '-') . ' cm');
    $pdf->RowResult('F. Berat Badan', ($data['berat_badan'] ?? '-') . ' kg');
    
    // G. Header Fisik
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(190, 7, '  G. Pemeriksaan Fisik Tubuh (Head to Toe)', 1, 1, 'L');
    
    // H. Kepala (Gigi)
    // Gabungkan status gigi
    $gigi = ($data['gigi_keterangan'] ?? '') ?: ($data['gigi_status'] ?? 'Tidak Ada Kelainan');
    $pdf->RowResult('H. Kepala (Gigi)', $gigi, checkNormal($gigi, 'fisik'));
    
    // I. Leher
    $leher = ($data['leher_kgb'] ?? 'Tidak Ada Kelainan');
    $pdf->RowResult('I. Leher', $leher, checkNormal($leher, 'fisik'));
    
    // J. Dada (Thorax)
    $dada = ($data['paru_auskultasi'] ?? 'Tidak Ada Kelainan');
    $pdf->RowResult('J. Dada', $dada, checkNormal($dada, 'fisik'));
    
    // K. Perut
    $perut = ($data['nyeri_abdomen'] ? 'Nyeri Tekan' : 'Tidak Ada Kelainan');
    $pdf->RowResult('K. Perut', $perut, $data['nyeri_abdomen']);
    
    // L. Kelamin (Sesuai data hepar/hernia dll jika mau dimasukkan, disini saya default)
    $pdf->RowResult('L. Kelamin', 'Tidak Ada Kelainan'); // Data sensitif biasanya manual/default
    
    // M. Tangan & Kaki (Ekstremitas)
    $tangan = ($data['tangan'] ?? 'Tidak Ada Kelainan'); // Pastikan field ini ada di DB atau sesuaikan
    $pdf->RowResult('M. Tangan', 'Tidak Ada Kelainan'); 
    $pdf->RowResult('N. Kaki', 'Tidak Ada Kelainan'); 

    // O. VISUS MATA (CUSTOM LOGIC)
    $visus_ka = ($data['visus_kanan_jauh'] ?? '-') . ' (Jauh)';
    $visus_ki = ($data['visus_kiri_jauh'] ?? '-') . ' (Jauh)';
    $visus_full = "Kanan = $visus_ka dan Kiri = $visus_ki";
    
    // Cek apakah visus normal (6/6)
    $abnormal_mata = (strpos($visus_ka, '6/6') === false || strpos($visus_ki, '6/6') === false);
    
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 7, '  Hasil Pemeriksaan VISUS Mata', 1, 0, 'L');
    if($abnormal_mata) $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(95, 7, '  ' . $visus_full, 1, 1, 'L');
    $pdf->SetTextColor(0); // Reset

    // P. Penunjang & Riwayat
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 7, '  Hasil Pemeriksaan Penunjang Laboratorium', 1, 0, 'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(95, 7, '  (-)', 1, 1, 'L'); // Sesuaikan jika ada data lab
    
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 7, '  Riwayat Penyakit Dahulu / Sekarang', 1, 0, 'L');
    $pdf->SetFont('Arial','',10);
    // Ambil riwayat jika ada
    $riwayat = '(-)'; // Default strip
    // Logika ambil riwayat dari detail.php logic bisa ditaruh sini
    $pdf->Cell(95, 7, '  ' . $riwayat, 1, 1, 'L');
    
    $pdf->Ln(5);

    // --- BAGIAN 4: KESIMPULAN & SARAN (FOOTER) ---
    
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(190, 7, 'KESIMPULAN DAN SARAN HASIL MCU', 0, 1, 'C');
    $pdf->Ln(2);

    // Tabel Kesimpulan
    $col_h = 20; // Tinggi kotak saran
    
    // Status Kesehatan
    $pdf->SetFillColor(196, 215, 155);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(55, 10, ' STATUS KESEHATAN', 1, 0, 'C', true);
    
    // Logic Status (BOLD, RED, UNDERLINE)
    $status_mcu = strtoupper($data['status_mcu'] ?? '-');
    $pdf->SetFont('Arial','BU',12); // Bold Underline
    if ($status_mcu == 'FIT WITH NOTE' || $status_mcu == 'UNFIT') {
        $pdf->SetTextColor(255, 0, 0);
    } else {
        $pdf->SetTextColor(0, 150, 0); // Hijau jika Fit
    }
    $pdf->Cell(135, 10, $status_mcu, 1, 1, 'C');
    $pdf->SetTextColor(0); // Reset

    // Saran
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(55, $col_h, ' SARAN', 1, 0, 'C', true);
    
    // MultiCell untuk Saran
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $saran_text = $data['saran'] ?: '1. Istirahat yang cukup.';
    $pdf->MultiCell(135, $col_h, $saran_text, 1, 'L');
    
    // --- TANDA TANGAN ---
    $pdf->Ln(5);
    $pdf->SetX(120);
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(80, 5, 'Cianjur, ' . formatDateIndo($patient['tanggal_mcu']), 0, 1, 'C');
    $pdf->SetX(120);
    $pdf->Cell(80, 5, 'Mengetahui,', 0, 1, 'C');
    
    $pdf->Ln(20); // Spasi TTD
    
    $pdf->SetX(120);
    $pdf->SetFont('Arial','BU',11); // Nama Dokter Garis Bawah
    $pdf->Cell(80, 5, 'dr. Hj Siti Isye Nasripah', 0, 1, 'C'); // Bisa ganti dynamic
    $pdf->SetX(120);
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(80, 4, '(Penanggung Jawab MCU - Klinik)', 0, 1, 'C');

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
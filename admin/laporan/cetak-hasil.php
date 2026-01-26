<?php
$page_title = 'Cetak Hasil MCU - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

// Check if patient ID is provided for PDF generation
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Generate PDF for specific patient
    require_once('../../libs/fpdf/fpdf.php');

    // Get patient data
    $query = "SELECT p.* FROM pasien p WHERE p.id = $id";
    $result = mysqli_query($conn, $query);
    $patient = mysqli_fetch_assoc($result);

    if (!$patient) {
        die("Pasien tidak ditemukan");
    }

    // Get pemeriksaan data
    $pemeriksaan_query = "SELECT * FROM pemeriksaan WHERE pasien_id = $id ORDER BY pemeriksa_role";
    $pemeriksaan_result = mysqli_query($conn, $pemeriksaan_query);

    // Prepare data arrays
    $exams = [];
    while ($row = mysqli_fetch_assoc($pemeriksaan_result)) {
        $exams[$row['pemeriksa_role']] = $row;
    }

    // Get settings
    $settings_query = "SELECT * FROM pengaturan LIMIT 1";
    $settings_result = mysqli_query($conn, $settings_query);
    $settings = mysqli_fetch_assoc($settings_result);

    // Create PDF class
    class MCU_PDF extends FPDF {
        private $clinic_name;
        private $clinic_address;

        function __construct($clinic_name, $clinic_address) {
            parent::__construct();
            $this->clinic_name = $clinic_name;
            $this->clinic_address = $clinic_address;
        }

        // Page header
        function Header() {
            // Clinic info
            $this->SetFont('Arial','B',14);
            $this->Cell(0,10,$this->clinic_name,0,1,'C');
            $this->SetFont('Arial','',10);
            $this->Cell(0,5,$this->clinic_address,0,1,'C');
            $this->Cell(0,5,'Telp: ' . ($GLOBALS['settings']['telepon'] ?? '-'),0,1,'C');

            // Title
            $this->Ln(5);
            $this->SetFont('Arial','B',16);
            $this->Cell(0,10,'HASIL MEDICAL CHECK UP',0,1,'C');
            $this->Ln(5);

            // Line
            $this->SetLineWidth(0.5);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->Ln(5);
        }

        // Page footer
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
        }

        // Patient info section
        function PatientInfo($patient) {
            $this->SetFont('Arial','B',12);
            $this->Cell(0,10,'DATA PASIEN',0,1);

            $this->SetFont('Arial','',10);

            $this->Cell(40,6,'Kode MCU:',0,0);
            $this->Cell(60,6,$patient['kode_mcu'],0,0);
            $this->Cell(40,6,'Tanggal MCU:',0,0);
            $this->Cell(0,6,formatDateIndo($patient['tanggal_mcu']),0,1);

            $this->Cell(40,6,'Nama:',0,0);
            $this->Cell(60,6,$patient['nama'],0,0);
            $this->Cell(40,6,'Jenis Kelamin:',0,0);
            $this->Cell(0,6,$patient['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan',0,1);

            $this->Cell(40,6,'Tempat/Tgl Lahir:',0,0);
            $this->Cell(60,6,$patient['tempat_lahir'] . ', ' . formatDateIndo($patient['tanggal_lahir']),0,0);
            $this->Cell(40,6,'Usia:',0,0);
            $this->Cell(0,6,$patient['usia'] . ' tahun',0,1);

            $this->Cell(40,6,'Alamat:',0,0);
            $this->Cell(0,6,$patient['alamat'],0,1);

            $this->Cell(40,6,'No. Telepon:',0,0);
            $this->Cell(60,6,$patient['no_telp'],0,0);
            $this->Cell(40,6,'Email:',0,0);
            $this->Cell(0,6,$patient['email'] ?: '-',0,1);

            $this->Cell(40,6,'Perusahaan:',0,0);
            $this->Cell(60,6,$patient['perusahaan'] ?: '-',0,0);
            $this->Cell(40,6,'Posisi:',0,0);
            $this->Cell(0,6,$patient['posisi_pekerjaan'] ?: '-',0,1);

            $this->Ln(5);
        }

        // Examination section
        function ExaminationSection($title, $data) {
            $this->SetFont('Arial','B',12);
            $this->Cell(0,10,$title,0,1);

            $this->SetFont('Arial','',10);

            if ($title == 'SIRKULASI') {
                $this->Cell(40,6,'Tekanan Darah:',0,0);
                $this->Cell(30,6,$data['tekanan_darah'] ?: '-',0,0);
                $this->Cell(40,6,'Nadi:',0,0);
                $this->Cell(30,6,$data['nadi'] ? $data['nadi'] . ' bpm' : '-',0,1);

                $this->Cell(40,6,'Suhu:',0,0);
                $this->Cell(30,6,$data['suhu'] ? $data['suhu'] . ' °C' : '-',0,0);
                $this->Cell(40,6,'Respirasi:',0,0);
                $this->Cell(30,6,$data['respirasi'] ? $data['respirasi'] . ' x/mnt' : '-',0,1);

                $this->Cell(40,6,'Tinggi Badan:',0,0);
                $this->Cell(30,6,$data['tinggi_badan'] ? $data['tinggi_badan'] . ' cm' : '-',0,0);
                $this->Cell(40,6,'Berat Badan:',0,0);
                $this->Cell(30,6,$data['berat_badan'] ? $data['berat_badan'] . ' kg' : '-',0,1);

            } elseif ($title == 'PEMERIKSAAN MATA') {
                $this->Cell(40,6,'Visus Kanan:',0,0);
                $this->Cell(50,6,($data['visus_kanan_jauh'] ?: '-') . ' / ' . ($data['visus_kanan_dekat'] ?: '-'),0,1);

                $this->Cell(40,6,'Visus Kiri:',0,0);
                $this->Cell(50,6,($data['visus_kiri_jauh'] ?: '-') . ' / ' . ($data['visus_kiri_dekat'] ?: '-'),0,1);

                $this->Cell(40,6,'Anemia:',0,0);
                $this->Cell(50,6,$data['anemia'] ?: '-',0,1);

                $this->Cell(40,6,'Buta Warna:',0,0);
                $this->Cell(50,6,$data['buta_warna'] ?: '-',0,1);

                $this->Cell(40,6,'Lapang Pandang:',0,0);
                $this->Cell(50,6,$data['lapang_pandang'] ?: '-',0,1);

            } elseif ($title == 'PEMERIKSAAN UMUM') {
                // THT & Gigi
                $this->SetFont('Arial','B',10);
                $this->Cell(0,6,'TELINGA, HIDUNG, TENGGOROKAN',0,1);
                $this->SetFont('Arial','',10);

                $this->Cell(40,6,'Telinga:',0,0);
                $this->Cell(50,6,$data['telinga_status'] ?: 'Normal',0,1);

                $this->Cell(40,6,'Hidung:',0,0);
                $this->Cell(50,6,$data['hidung_status'] ?: 'Normal',0,1);

                $this->Cell(40,6,'Tenggorokan:',0,0);
                $this->Cell(50,6,$data['tenggorokan_status'] ?: 'Normal',0,1);

                if ($data['gigi_keterangan']) {
                    $this->Cell(40,6,'Gigi:',0,0);
                    $this->MultiCell(0,6,$data['gigi_keterangan']);
                }

                $this->Ln(2);

                // Thorax
                $this->SetFont('Arial','B',10);
                $this->Cell(0,6,'PEMERIKSAAN THORAX',0,1);
                $this->SetFont('Arial','',10);

                $this->Cell(40,6,'Auskultasi:',0,0);
                $this->Cell(0,6,$data['paru_auskultasi'] ?: 'Normal',0,1);

                $this->Cell(40,6,'Palpasi:',0,0);
                $this->Cell(0,6,$data['paru_palpasi'] ?: '-',0,1);

                $this->Cell(40,6,'Perkusi:',0,0);
                $this->Cell(0,6,$data['paru_perkusi'] ?: 'Sonor',0,1);

                $this->Ln(2);

                // Abdominal
                $this->SetFont('Arial','B',10);
                $this->Cell(0,6,'ABDOMINAL',0,1);
                $this->SetFont('Arial','',10);

                $this->Cell(40,6,'Riwayat Operasi:',0,0);
                $this->Cell(0,6,$data['operasi'] ? 'Ya' : 'Tidak',0,1);

                $this->Cell(40,6,'Obesitas:',0,0);
                $this->Cell(0,6,$data['obesitas'] ? 'Ya' : 'Tidak',0,1);

                $this->Cell(40,6,'Organomegali:',0,0);
                $this->Cell(0,6,$data['organomegali'] ? 'Ya' : 'Tidak',0,1);

                $this->Cell(40,6,'Hernia:',0,0);
                $this->Cell(0,6,$data['hernia'] ? 'Ya' : 'Tidak',0,1);

                if ($data['hepatomegali']) {
                    $this->Cell(40,6,'Hepatomegali:',0,0);
                    $this->Cell(0,6,$data['hepatomegali'],0,1);
                }

                $this->Ln(2);

                // Refleks
                $this->SetFont('Arial','B',10);
                $this->Cell(0,6,'REFLEKS',0,1);
                $this->SetFont('Arial','',10);

                $this->Cell(30,6,'Biceps:',0,0);
                $this->Cell(20,6,$data['biceps'] ?: 'Normal',0,0);
                $this->Cell(30,6,'Triceps:',0,0);
                $this->Cell(20,6,$data['triceps'] ?: 'Normal',0,1);

                $this->Cell(30,6,'Patella:',0,0);
                $this->Cell(20,6,$data['patella'] ?: 'Normal',0,0);
                $this->Cell(30,6,'Achilles:',0,0);
                $this->Cell(20,6,$data['achilles'] ?: 'Normal',0,1);

                $this->Cell(30,6,'Plantar Response:',0,0);
                $this->Cell(0,6,$data['plantar_response'] ?: 'Normal',0,1);
            }

            $this->Ln(5);
        }

        // Conclusion section
        function ConclusionSection($data) {
            $this->SetFont('Arial','B',12);
            $this->Cell(0,10,'KESIMPULAN DAN SARAN',0,1);

            $this->SetFont('Arial','',10);

            if ($data['kesimpulan']) {
                $this->MultiCell(0,6,$data['kesimpulan']);
                $this->Ln(2);
            }

            if ($data['saran']) {
                $this->Cell(0,6,'Saran:',0,1);
                $this->MultiCell(0,6,$data['saran']);
                $this->Ln(2);
            }

            $this->Cell(40,6,'Status MCU:',0,0);
            $this->SetFont('Arial','B',10);

            if ($data['status_mcu'] == 'FIT') {
                $this->Cell(0,6,'FIT TO WORK',0,1);
            } elseif ($data['status_mcu'] == 'UNFIT') {
                $this->Cell(0,6,'UNFIT',0,1);
            } elseif ($data['status_mcu'] == 'FIT WITH NOTE') {
                $this->Cell(0,6,'FIT WITH NOTE',0,1);
            } else {
                $this->Cell(0,6,'-',0,1);
            }

            $this->SetFont('Arial','',10);

            if ($data['dokter_pemeriksa']) {
                $this->Cell(40,6,'Dokter Pemeriksa:',0,0);
                $this->Cell(0,6,$data['dokter_pemeriksa'],0,1);
            }

            $this->Ln(5);
        }

        // Signature section
        function SignatureSection() {
            $this->Ln(10);
            $this->Cell(0,6,'Jakarta, ' . date('d F Y'),0,1,'R');
            $this->Ln(20);

            $this->Cell(0,6,'______________________________',0,1,'R');
            $this->Cell(0,6,'Dokter Pemeriksa',0,1,'R');

            $this->Ln(10);

            $this->SetFont('Arial','I',8);
            $this->MultiCell(0,4,'Catatan: Hasil MCU ini hanya berlaku untuk keperluan yang disebutkan di atas. Untuk pemeriksaan lebih lanjut silakan konsultasi dengan dokter spesialis terkait.',0,'L');
        }
    }

    // Create PDF instance
    $pdf = new MCU_PDF(
        $settings['nama_klinik'] ?? 'Klinik MCU',
        $settings['alamat'] ?? ''
    );

    $pdf->AliasNbPages();
    $pdf->AddPage('P', 'A4');

    // Add patient info
    $pdf->PatientInfo($patient);

    // Add sirkulasi data
    if (isset($exams['pendaftaran'])) {
        $pdf->ExaminationSection('SIRKULASI', $exams['pendaftaran']);
    }

    // Add mata data
    if (isset($exams['dokter_mata'])) {
        $pdf->ExaminationSection('PEMERIKSAAN MATA', $exams['dokter_mata']);
    }

    // Add umum data
    if (isset($exams['dokter_umum'])) {
        $pdf->ExaminationSection('PEMERIKSAAN UMUM', $exams['dokter_umum']);
        $pdf->ConclusionSection($exams['dokter_umum']);
    }

    // Add signature
    $pdf->SignatureSection();

    // Output PDF
    $filename = 'Hasil_MCU_' . $patient['kode_mcu'] . '.pdf';
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
                    <div>
                        <button onclick="printReport()" class="btn btn-success me-2">
                            <i class="fas fa-print me-2"></i> Cetak Semua
                        </button>
                        <button onclick="exportToExcel()" class="btn btn-primary">
                            <i class="fas fa-file-excel me-2"></i> Excel
                        </button>
                    </div>
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
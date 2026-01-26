<?php
$page_title = 'Cetak Data Pasien - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

// Tambahkan fitur export Excel
if (isset($_GET['export_excel']) && $_GET['export_excel'] == '1') {
    // Filter parameters for export
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';

    // Build query (sama dengan query utama)
    $where = "1=1";

    if ($start_date && $end_date) {
        $where .= " AND DATE(p.created_at) BETWEEN '$start_date' AND '$end_date'";
    }

    if ($status != 'all') {
        $where .= " AND p.status_pendaftaran = '$status'";
    }

    // Get all patients for export
    $query = "SELECT p.* FROM pasien p
              WHERE $where
              ORDER BY p.created_at DESC";
    $result = mysqli_query($conn, $query);

    // Ekspor ke Excel
    exportToExcel($result, $start_date, $end_date, $status);
    exit;
}

// Filter parameters for listing page
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$where = "1=1";

if ($start_date && $end_date) {
    $where .= " AND DATE(p.created_at) BETWEEN '$start_date' AND '$end_date'";
}

if ($status != 'all') {
    $where .= " AND p.status_pendaftaran = '$status'";
}

// Get all patients
$query = "SELECT p.* FROM pasien p
          WHERE $where
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Get statistics
$stats_query = "SELECT
                COUNT(DISTINCT p.id) as total,
                SUM(CASE WHEN p.status_pendaftaran = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN p.status_pendaftaran = 'proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN p.status_pendaftaran = 'selesai' THEN 1 ELSE 0 END) as selesai
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
                    <i class="fas fa-print me-2"></i> Cetak Data Pasien
                </h1>
            </div>
            
            <!-- Filter Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Filter Laporan</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="menunggu" <?php echo $status == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="proses" <?php echo $status == 'proses' ? 'selected' : ''; ?>>Proses</option>
                                <option value="selesai" <?php echo $status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="d-grid w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Statistics Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card text-center border-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Total</h5>
                                    <h2 class="text-primary"><?php echo $stats['total']; ?></h2>
                                    <p class="card-text">Pasien</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-warning">
                                <div class="card-body">
                                    <h5 class="card-title">Menunggu</h5>
                                    <h2 class="text-warning"><?php echo $stats['menunggu']; ?></h2>
                                    <p class="card-text">Pasien</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-info">
                                <div class="card-body">
                                    <h5 class="card-title">Proses</h5>
                                    <h2 class="text-info"><?php echo $stats['proses']; ?></h2>
                                    <p class="card-text">Pasien</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-success">
                                <div class="card-body">
                                    <h5 class="card-title">Selesai</h5>
                                    <h2 class="text-success"><?php echo $stats['selesai']; ?></h2>
                                    <p class="card-text">Pasien</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Report Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Data Pasien
                        <span class="badge bg-primary ms-2"><?php echo mysqli_num_rows($result); ?> data</span>
                    </h5>
                    <div>
                        <button onclick="printReport()" class="btn btn-success me-2">
                            <i class="fas fa-print me-2"></i> Cetak Semua
                        </button>
                        <a href="?export_excel=1&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&status=<?php echo $status; ?>"
                           class="btn btn-primary">
                            <i class="fas fa-file-excel me-2"></i> Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Print Header -->
                    <div id="printHeader" class="d-none">
                        <div class="text-center mb-4">
                            <h3><?php echo getSetting('nama_klinik'); ?></h3>
                            <h4>Laporan Data Pasien</h4>
                            <p>
                                Periode: <?php echo formatDateIndo($start_date); ?> - <?php echo formatDateIndo($end_date); ?>
                                | Status: <?php echo $status == 'all' ? 'Semua' : ucfirst($status); ?>
                            </p>
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
                                        <th>Status</th>
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
                                            <td><?php echo htmlspecialchars($patient['alamat']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['no_telp']); ?></td>
                                            <td>
                                                <?php
                                                if ($patient['status_pendaftaran'] == 'menunggu') echo 'Menunggu';
                                                elseif ($patient['status_pendaftaran'] == 'proses') echo 'Proses';
                                                else echo 'Selesai';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Tidak ada data pasien untuk periode yang dipilih.
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
            <title>Laporan Data Pasien</title>
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


</script>

<?php
/**
 * Fungsi untuk ekspor data ke Excel menggunakan PHPSpreadsheet
 */
function exportToExcel($result, $start_date, $end_date, $status) {
    // Require PHPSpreadsheet
    require '../../vendor/autoload.php';

    // Create new Spreadsheet
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set judul laporan
    $sheet->setTitle('Laporan Pasien');

    // Header informasi
    $sheet->setCellValue('A1', 'LAPORAN DATA PASIEN')
          ->setCellValue('A2', getSetting('nama_klinik'))
          ->setCellValue('A3', 'Periode: ' . formatDateIndo($start_date) . ' - ' . formatDateIndo($end_date))
          ->setCellValue('A4', 'Status: ' . ($status == 'all' ? 'Semua Status' : ucfirst($status)))
          ->setCellValue('A5', 'Tanggal Cetak: ' . date('d/m/Y H:i:s'));

    // Header tabel
    $headers = ['No', 'Kode MCU', 'Nama', 'Usia', 'Perusahaan', 'Tanggal MCU', 'Alamat', 'No HP', 'Status'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '7', $header);
        $sheet->getStyle($col . '7')->getFont()->setBold(true);
        $col++;
    }

    // Isi data
    $row = 8;
    $no = 1;

    while ($patient = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue('A' . $row, $no)
              ->setCellValue('B' . $row, $patient['kode_mcu'])
              ->setCellValue('C' . $row, $patient['nama'])
              ->setCellValue('D' . $row, $patient['usia'] . ' thn')
              ->setCellValue('E' . $row, $patient['perusahaan'] ?: '-')
              ->setCellValue('F' . $row, formatDateIndo($patient['tanggal_mcu']))
              ->setCellValue('G' . $row, $patient['alamat'])
              ->setCellValue('H' . $row, $patient['no_telp'])
              ->setCellValue('I' . $row,
                  $patient['status_pendaftaran'] == 'menunggu' ? 'Menunggu' :
                  ($patient['status_pendaftaran'] == 'proses' ? 'Proses' : 'Selesai'));

        $no++;
        $row++;
    }

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(6); // No column - fixed small width
    // Auto size other columns
    foreach (range('B', 'I') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Style untuk judul
    $sheet->getStyle('A1:I1')->getFont()->setSize(16)->setBold(true);
    $sheet->mergeCells('A1:I1');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    // Style untuk header tabel
    $sheet->getStyle('A7:I7')->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setARGB('FFE0E0E0');

    // Border untuk tabel
    $lastRow = $row - 1;
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
    $sheet->getStyle('A7:I' . $lastRow)->applyFromArray($styleArray);

    // Set header untuk download
    $filename = 'laporan-pasien-' . date('Y-m-d-His') . '.xlsx';

    // Clean output buffer to prevent corruption
    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit;
}
?>

<?php include '../../includes/admin-footer.php'; ?>

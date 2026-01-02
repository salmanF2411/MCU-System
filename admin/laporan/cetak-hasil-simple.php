<?php
$page_title = 'Cetak Hasil MCU - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

// Get patient ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    $_SESSION['error'] = "Pasien tidak ditemukan";
    redirect('../pasien/list.php');
}

// Get patient data
$query = "SELECT p.* FROM pasien p WHERE p.id = $id";
$result = mysqli_query($conn, $query);
$patient = mysqli_fetch_assoc($result);

if (!$patient) {
    $_SESSION['error'] = "Pasien tidak ditemukan";
    redirect('../pasien/list.php');
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Hasil MCU - <?php echo $patient['kode_mcu']; ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            @page { size: A4; margin: 20mm; }
            body { font-size: 12pt; }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        
        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .clinic-name {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .clinic-address {
            font-size: 11pt;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            text-decoration: underline;
        }
        
        .patient-info table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .patient-info th,
        .patient-info td {
            padding: 8px;
            text-align: left;
            vertical-align: top;
            border: 1px solid #ddd;
        }
        
        .patient-info th {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 25%;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 25px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #000;
        }
        
        .exam-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .exam-table th,
        .exam-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .exam-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .conclusion {
            margin: 30px 0;
            padding: 15px;
            border: 1px solid #000;
            background-color: #f9f9f9;
        }
        
        .signature {
            margin-top: 60px;
            text-align: right;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            width: 200px;
            display: inline-block;
        }
        
        .footer-note {
            margin-top: 40px;
            font-size: 9pt;
            font-style: italic;
            color: #666;
        }
        
        .status-badge {
            padding: 3px 10px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-fit {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-unfit {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-note {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .print-actions {
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Print Actions -->
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn-print">
            🖨️ Cetak Halaman
        </button>
        <a href="../pasien/detail.php?id=<?php echo $id; ?>" class="btn-back">
            ↩ Kembali ke Detail Pasien
        </a>
    </div>
    
    <!-- Print Content -->
    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">
                <?php echo htmlspecialchars($settings['nama_klinik'] ?? 'KLINIK MCU'); ?>
            </div>
            <div class="clinic-address">
                <?php echo htmlspecialchars($settings['alamat'] ?? ''); ?>
            </div>
            <div class="clinic-address">
                Telp: <?php echo $settings['telepon'] ?? ''; ?> | 
                Email: <?php echo $settings['email'] ?? ''; ?>
            </div>
        </div>
        
        <!-- Report Title -->
        <div class="report-title">
            HASIL PEMERIKSAAN MEDICAL CHECK UP
        </div>
        
        <!-- Patient Information -->
        <div class="patient-info">
            <table>
                <tr>
                    <th>Kode MCU</th>
                    <td><?php echo $patient['kode_mcu']; ?></td>
                    <th>Tanggal MCU</th>
                    <td><?php echo formatDateIndo($patient['tanggal_mcu']); ?></td>
                </tr>
                <tr>
                    <th>Nama Lengkap</th>
                    <td colspan="3"><?php echo htmlspecialchars($patient['nama']); ?></td>
                </tr>
                <tr>
                    <th>Tempat/Tgl Lahir</th>
                    <td><?php echo $patient['tempat_lahir'] . ', ' . formatDateIndo($patient['tanggal_lahir']); ?></td>
                    <th>Jenis Kelamin</th>
                    <td><?php echo $patient['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                </tr>
                <tr>
                    <th>Usia</th>
                    <td><?php echo $patient['usia']; ?> tahun</td>
                    <th>Perusahaan</th>
                    <td><?php echo $patient['perusahaan'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <th>Posisi Pekerjaan</th>
                    <td><?php echo $patient['posisi_pekerjaan'] ?: '-'; ?></td>
                    <th>Alamat</th>
                    <td><?php echo nl2br(htmlspecialchars($patient['alamat'])); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- SIRKULASI -->
        <?php if (isset($exams['pendaftaran'])): ?>
            <div class="section-title">SIRKULASI</div>
            <table class="exam-table">
                <tr>
                    <th width="25%">Tekanan Darah</th>
                    <td><?php echo $exams['pendaftaran']['tekanan_darah'] ?: '-'; ?> mmHg</td>
                    <th width="25%">Nadi</th>
                    <td><?php echo $exams['pendaftaran']['nadi'] ? $exams['pendaftaran']['nadi'] . ' bpm' : '-'; ?></td>
                </tr>
                <tr>
                    <th>Suhu Tubuh</th>
                    <td><?php echo $exams['pendaftaran']['suhu'] ? $exams['pendaftaran']['suhu'] . ' °C' : '-'; ?></td>
                    <th>Respirasi</th>
                    <td><?php echo $exams['pendaftaran']['respirasi'] ? $exams['pendaftaran']['respirasi'] . ' x/menit' : '-'; ?></td>
                </tr>
                <tr>
                    <th>Tinggi Badan</th>
                    <td><?php echo $exams['pendaftaran']['tinggi_badan'] ? $exams['pendaftaran']['tinggi_badan'] . ' cm' : '-'; ?></td>
                    <th>Berat Badan</th>
                    <td><?php echo $exams['pendaftaran']['berat_badan'] ? $exams['pendaftaran']['berat_badan'] . ' kg' : '-'; ?></td>
                </tr>
            </table>
        <?php endif; ?>
        
        <!-- PEMERIKSAAN MATA -->
        <?php if (isset($exams['dokter_mata'])): ?>
            <div class="section-title">PEMERIKSAAN MATA</div>
            <table class="exam-table">
                <tr>
                    <th width="25%">Visus Kanan (Jauh/Dekat)</th>
                    <td><?php echo $exams['dokter_mata']['visus_kanan_jauh'] ?: '-' ?> / <?php echo $exams['dokter_mata']['visus_kanan_dekat'] ?: '-'; ?></td>
                    <th width="25%">Visus Kiri (Jauh/Dekat)</th>
                    <td><?php echo $exams['dokter_mata']['visus_kiri_jauh'] ?: '-' ?> / <?php echo $exams['dokter_mata']['visus_kiri_dekat'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <th>Anemia</th>
                    <td><?php echo $exams['dokter_mata']['anemia'] ?: '-'; ?></td>
                    <th>Buta Warna</th>
                    <td><?php echo $exams['dokter_mata']['buta_warna'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <th>Lapang Pandang</th>
                    <td colspan="3"><?php echo $exams['dokter_mata']['lapang_pandang'] ?: '-'; ?></td>
                </tr>
            </table>
        <?php endif; ?>
        
        <!-- PEMERIKSAAN UMUM -->
        <?php if (isset($exams['dokter_umum'])): ?>
            <div class="section-title">PEMERIKSAAN UMUM</div>
            
            <!-- THT & Gigi -->
            <h4>TELINGA, HIDUNG, TENGGOROKAN</h4>
            <table class="exam-table">
                <tr>
                    <th width="20%">Telinga</th>
                    <td width="30%"><?php echo $exams['dokter_umum']['telinga_status'] ?: 'Normal'; ?></td>
                    <th width="20%">Hidung</th>
                    <td width="30%"><?php echo $exams['dokter_umum']['hidung_status'] ?: 'Normal'; ?></td>
                </tr>
                <tr>
                    <th>Tenggorokan</th>
                    <td><?php echo $exams['dokter_umum']['tenggorokan_status'] ?: 'Normal'; ?></td>
                    <th>Gigi & Mulut</th>
                    <td><?php echo $exams['dokter_umum']['gigi_keterangan'] ?: 'Normal'; ?></td>
                </tr>
            </table>
            
            <!-- Thorax -->
            <h4>PEMERIKSAAN THORAX</h4>
            <table class="exam-table">
                <tr>
                    <th width="25%">Auskultasi</th>
                    <td><?php echo $exams['dokter_umum']['paru_auskultasi'] ?: 'Normal'; ?></td>
                    <th width="25%">Palpasi</th>
                    <td><?php echo $exams['dokter_umum']['paru_palpasi'] ?: '-'; ?></td>
                </tr>
                <tr>
                    <th>Perkusi</th>
                    <td colspan="3"><?php echo $exams['dokter_umum']['paru_perkusi'] ?: 'Sonor'; ?></td>
                </tr>
            </table>
            
            <!-- Abdominal -->
            <h4>ABDOMINAL</h4>
            <table class="exam-table">
                <tr>
                    <th width="25%">Riwayat Operasi</th>
                    <td width="25%"><?php echo $exams['dokter_umum']['operasi'] ? 'Ya' : 'Tidak'; ?></td>
                    <th width="25%">Obesitas</th>
                    <td width="25%"><?php echo $exams['dokter_umum']['obesitas'] ? 'Ya' : 'Tidak'; ?></td>
                </tr>
                <tr>
                    <th>Organomegali</th>
                    <td><?php echo $exams['dokter_umum']['organomegali'] ? 'Ya' : 'Tidak'; ?></td>
                    <th>Hernia</th>
                    <td><?php echo $exams['dokter_umum']['hernia'] ? 'Ya' : 'Tidak'; ?></td>
                </tr>
                <?php if ($exams['dokter_umum']['hepatomegali']): ?>
                <tr>
                    <th>Hepatomegali</th>
                    <td colspan="3"><?php echo $exams['dokter_umum']['hepatomegali']; ?></td>
                </tr>
                <?php endif; ?>
            </table>
            
            <!-- Refleks -->
            <h4>REFLEKS</h4>
            <table class="exam-table">
                <tr>
                    <th width="25%">Biceps</th>
                    <td width="25%"><?php echo $exams['dokter_umum']['biceps'] ?: 'Normal'; ?></td>
                    <th width="25%">Triceps</th>
                    <td width="25%"><?php echo $exams['dokter_umum']['triceps'] ?: 'Normal'; ?></td>
                </tr>
                <tr>
                    <th>Patella</th>
                    <td><?php echo $exams['dokter_umum']['patella'] ?: 'Normal'; ?></td>
                    <th>Achilles</th>
                    <td><?php echo $exams['dokter_umum']['achilles'] ?: 'Normal'; ?></td>
                </tr>
                <tr>
                    <th>Plantar Response</th>
                    <td colspan="3"><?php echo $exams['dokter_umum']['plantar_response'] ?: 'Normal'; ?></td>
                </tr>
            </table>
            
            <!-- KESIMPULAN -->
            <div class="conclusion">
                <h4>KESIMPULAN HASIL MCU</h4>
                
                <?php if ($exams['dokter_umum']['kesimpulan']): ?>
                <p><strong>Kesimpulan:</strong><br>
                <?php echo nl2br(htmlspecialchars($exams['dokter_umum']['kesimpulan'])); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($exams['dokter_umum']['saran']): ?>
                <p><strong>Saran:</strong><br>
                <?php echo nl2br(htmlspecialchars($exams['dokter_umum']['saran'])); ?>
                </p>
                <?php endif; ?>
                
                <p><strong>Status MCU:</strong> 
                    <?php 
                    $status_class = '';
                    if ($exams['dokter_umum']['status_mcu'] == 'FIT') {
                        $status_class = 'status-fit';
                    } elseif ($exams['dokter_umum']['status_mcu'] == 'UNFIT') {
                        $status_class = 'status-unfit';
                    } elseif ($exams['dokter_umum']['status_mcu'] == 'FIT WITH NOTE') {
                        $status_class = 'status-note';
                    }
                    ?>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php 
                        if ($exams['dokter_umum']['status_mcu'] == 'FIT') echo 'FIT TO WORK';
                        elseif ($exams['dokter_umum']['status_mcu'] == 'UNFIT') echo 'UNFIT';
                        elseif ($exams['dokter_umum']['status_mcu'] == 'FIT WITH NOTE') echo 'FIT WITH NOTE';
                        else echo '-';
                        ?>
                    </span>
                </p>
                
                <?php if ($exams['dokter_umum']['dokter_pemeriksa']): ?>
                <p><strong>Dokter Pemeriksa:</strong><br>
                <?php echo htmlspecialchars($exams['dokter_umum']['dokter_pemeriksa']); ?>
                </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Signature -->
        <div class="signature">
            <div class="signature-line"></div>
            <p>Dokter Pemeriksa</p>
        </div>
        
        <!-- Footer Note -->
        <div class="footer-note">
            <p><strong>Catatan:</strong></p>
            <p>1. Hasil MCU ini hanya berlaku untuk keperluan yang disebutkan di atas.</p>
            <p>2. Untuk pemeriksaan lebih lanjut atau keluhan kesehatan, silakan konsultasi dengan dokter spesialis terkait.</p>
            <p>3. Dokumen ini dicetak secara elektronik dan tidak memerlukan tanda tangan basah.</p>
        </div>
    </div>
    
    <script>
        // Auto print when page loads (optional)
        window.onload = function() {
            // Uncomment to auto print
            // window.print();
        };
        
        // Print button
        document.querySelector('.btn-print').addEventListener('click', function() {
            window.print();
        });
        
        // Style buttons
        const style = document.createElement('style');
        style.textContent = `
            .btn-print, .btn-back {
                display: inline-block;
                padding: 10px 20px;
                margin: 0 10px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                font-size: 14px;
                font-weight: bold;
            }
            
            .btn-print {
                background: #28a745;
                color: white;
            }
            
            .btn-back {
                background: #6c757d;
                color: white;
            }
            
            .btn-print:hover {
                background: #218838;
            }
            
            .btn-back:hover {
                background: #5a6268;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
<?php
$page_title = 'Pendaftaran Berhasil - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/header.php';

$kode_mcu = isset($_GET['kode']) ? $_GET['kode'] : '';

if (empty($kode_mcu)) {
    redirect('daftar-mcu.php');
}
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card text-center border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i> Pendaftaran Berhasil!</h4>
                </div>
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-5x text-success"></i>
                    </div>
                    
                    <h3 class="card-title mb-3">Terima kasih telah mendaftar MCU</h3>
                    
                    <div class="alert alert-info mb-4">
                        <h5>Kode MCU Anda:</h5>
                        <div class="display-4 fw-bold text-primary"><?php echo $kode_mcu; ?></div>
                        <p class="mt-2">Harap simpan kode ini untuk keperluan berikutnya.</p>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Informasi Penting</h5>
                        </div>
                        <div class="card-body text-start">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-calendar-check text-primary me-2"></i> <strong>Tanggal MCU:</strong> <?php echo formatDateIndo($_GET['tanggal'] ?? date('Y-m-d')); ?></li>
                                <li class="mb-2"><i class="fas fa-clock text-primary me-2"></i> <strong>Jam Pelayanan:</strong> 08:00 - 16:00 WIB</li>
                                <li class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i> <strong>Lokasi Klinik:</strong> <?php echo getSetting('alamat'); ?></li>
                                <li><i class="fas fa-phone text-primary me-2"></i> <strong>Kontak:</strong> <?php echo getSetting('telepon'); ?> / <?php echo getSetting('whatsapp'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i> Persyaratan yang Harus Dibawa:</h6>
                        <ul class="mb-0">
                            <li>Kode MCU (tunjukkan di loket pendaftaran)</li>
                            <li>Kartu Identitas (KTP/SIM/Paspor)</li>
                            <li>Puasa 8-10 jam sebelum pemeriksaan (jika diperlukan)</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg me-2">
                            <i class="fas fa-home me-2"></i> Kembali ke Beranda
                        </a>
                        <button onclick="window.print()" class="btn btn-success btn-lg">
                            <i class="fas fa-print me-2"></i> Cetak Halaman
                        </button>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    Jika ada pertanyaan, hubungi kami di <?php echo getSetting('telepon'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .navbar, footer, .btn {
            display: none !important;
        }
        
        .card {
            border: 2px solid #000 !important;
        }
        
        .alert-info {
            background-color: #d1ecf1 !important;
            border-color: #bee5eb !important;
            color: #0c5460 !important;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>

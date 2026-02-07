<?php
$page_title = 'Tentang Kami - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/header.php';

// Get settings
$query = "SELECT * FROM pengaturan LIMIT 1";
$result = mysqli_query($conn, $query);
$setting = mysqli_fetch_assoc($result);
?>

<div class="container">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="page-title">Tentang Kami</h1>
        </div>
    </div>

    <!-- About Content -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-hospital me-2"></i><?php echo $setting['nama_klinik']; ?></h4>
                </div>
                <div class="card-body">
                    <p class="card-text" style="text-align: justify; line-height: 1.8; color: #555;">
                        <?php echo $setting['tentang'] ?: 'Kami adalah klinik Medical Check Up profesional yang berkomitmen untuk memberikan layanan pemeriksaan kesehatan terbaik bagi perusahaan dan individu. Dengan tim dokter spesialis berpengalaman dan peralatan medis terkini, kami siap membantu menjaga kesehatan Anda.'; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Kontak</h4>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-hospital me-2 text-primary"></i>
                            <strong>Nama Klinik:</strong> <?php echo $setting['nama_klinik']; ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            <strong>Alamat:</strong> <?php echo $setting['alamat']; ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            <strong>Telepon:</strong> <?php echo $setting['telepon']; ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <strong>Email:</strong> <?php echo $setting['email']; ?>
                        </li>
                        <li class="mb-2">
                            <i class="fab fa-whatsapp me-2 text-primary"></i>
                            <strong>WhatsApp:</strong> <?php echo $setting['whatsapp']; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Vision & Mission -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-eye me-2"></i> Visi</h4>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Mewujudkan Klinik yang Islami, terbaik pilihan seluruh masyarakat Cianjur</li>
                        <li>Memajukan kehidupan masyarakat sebagai aktualisasi Rahmatan lil'alamin.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-bullseye me-2"></i> Misi</h4>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Melaksanakan dakwah dalam pelayanan kesehatan</li>
                        <li>Memberikan pelayanan yang profesional terhadap seluruh masyarakat tanpa melupakan fungsi sosial keberadaan Klinik Alvarisha.</li>
                        <li>Mengutamakan keselamatan dan kepuasan pasien</li>
                        <li>Mengikuti perkembangan ilmu dalam rangka meningkatkan pengetahuan dan keterampilan karyawan.</li>
                        <li>Melaksanakan pelayanan sesuai standar akreditasi klinik.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Services -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="page-title">Layanan Kami</h2>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-stethoscope fa-3x text-primary"></i>
                    </div>
                    <h5>Pemeriksaan Fisik</h5>
                    <p>Pemeriksaan fisik lengkap oleh dokter spesialis</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-vial fa-3x text-success"></i>
                    </div>
                    <h5>Pemeriksaan Laboratorium</h5>
                    <p>Test darah, urine, dan pemeriksaan laboratorium lainnya</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-x-ray fa-3x text-info"></i>
                    </div>
                    <h5>Radiologi</h5>
                    <p>Pemeriksaan X-Ray, USG, dan EKG</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Map (Optional) -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i> Lokasi Kami</h4>
                </div>
                <div class="card-body">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d14940.57612772112!2d107.05798757269284!3d-6.752563714709638!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sid!2sid!4v1770438852589!5m2!1sid!2sid" width="100%" height="500" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<?php
$page_title = 'Beranda - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/header.php';

// Get settings for hero image
$query = "SELECT * FROM pengaturan LIMIT 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);
?>

<a href="daftar-mcu.php" style="text-decoration: none; color: inherit; display: block;">
    <div class="hero-section mb-5" style="background: linear-gradient(rgba(255,255,255,0.4), rgba(255,255,255,0.4)), url('<?php echo $settings && $settings['hero_image'] ? ASSETS_URL . '/' . $settings['hero_image'] : ASSETS_URL . '/images/hero-bg.jpg'; ?>'); background-size: cover; background-position: center; background-repeat: no-repeat; color: #333; padding: 100px 0; border-radius: 15px;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold" style="color: white; text-shadow: 3px 3px 8px rgba(0,0,0,0.9), 0px 0px 20px rgba(0,0,0,0.7);">Medical Check Up Profesional</h1>
                    <p class="lead" style="color: white; text-shadow: 2px 2px 6px rgba(9, 6, 6, 0.9), 0px 0px 15px rgba(3, 2, 2, 0.7);">Layanan pemeriksaan kesehatan lengkap untuk perusahaan dan individu dengan tim dokter profesional dan peralatan medis terkini.</p>
                    <span class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-check"></i> Daftar MCU Sekarang
                    </span>
                </div>
            </div>
        </div>
    </div>
</a>

<div class="container">
    <!-- Features -->
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-user-md fa-3x text-primary"></i>
                    </div>
                    <h4>Dokter Profesional</h4>
                    <p>Tim dokter spesialis berpengalaman siap memberikan pelayanan terbaik untuk pemeriksaan kesehatan Anda.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-clipboard-check fa-3x text-success"></i>
                    </div>
                    <h4>Pemeriksaan Lengkap</h4>
                    <p>Pemeriksaan mulai dari laboratorium, radiologi, hingga pemeriksaan fisik oleh dokter spesialis.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-clock fa-3x text-info"></i>
                    </div>
                    <h4>Hasil Cepat</h4>
                    <p>Hasil pemeriksaan dapat diperoleh dalam waktu singkat dengan sistem yang terintegrasi.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Articles -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="page-title">Artikel Kesehatan Terbaru</h2>
        </div>
        
        <?php
        $query = "SELECT * FROM artikel 
                  WHERE status = 'published' 
                  ORDER BY tanggal_publish DESC 
                  LIMIT 3";
        $result = mysqli_query($conn, $query);
        
        while ($article = mysqli_fetch_assoc($result)):
        ?>
        <div class="col-md-4">
            <div class="card h-100">
                <?php if (!empty($article['gambar'])): ?>
                <img src="<?php echo ASSETS_URL . '/' . $article['gambar']; ?>" class="card-img-top" alt="<?php echo $article['judul']; ?>" style="height: 200px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo $article['judul']; ?></h5>
                    <div class="card-text flex-grow-1">
                        <?php echo substr(strip_tags($article['konten']), 0, 100) . '...'; ?>
                    </div>
                    <div class="mt-auto">
                        <small class="text-muted d-block">
                            <i class="fas fa-calendar"></i> Dipublikasikan <?php echo formatDateIndo($article['tanggal_publish']); ?>
                        </small>
                        <a href="artikel-detail.php?id=<?php echo $article['id']; ?>" class="btn btn-primary btn-sm mt-2">
                            Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        
        <?php if (mysqli_num_rows($result) == 0): ?>
        <div class="col-12">
            <div class="alert alert-info">
                Belum ada artikel yang dipublikasikan.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Why Choose Us -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="page-title">Mengapa Memilih Kami?</h2>
        </div>
        <div class="col-md-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-3"></i>
                    <span>Layanan MCU untuk perusahaan dan perorangan</span>
                </li>
                <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-3"></i>
                    <span>Hasil pemeriksaan akurat dan terpercaya</span>
                </li>
                <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-3"></i>
                    <span>Tim medis profesional dan berpengalaman</span>
                </li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-3"></i>
                    <span>Peralatan medis lengkap dan modern</span>
                </li>
                <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-3"></i>
                    <span>Pelayanan cepat dan ramah</span>
                </li>
                <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-3"></i>
                    <span>Harga kompetitif dan transparan</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-5">
                    <h2 class="card-title">Siap untuk Medical Check Up?</h2>
                    <p class="card-text lead mb-4">Daftarkan diri atau karyawan Anda sekarang untuk mendapatkan pelayanan terbaik dari kami.</p>
                    <a href="daftar-mcu.php" class="btn btn-light btn-lg">
                        <i class="fas fa-calendar-plus"></i> Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

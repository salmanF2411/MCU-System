<?php
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Check if we're on dashboard with patients page
$is_patients_page = ($current_page == 'dashboard.php' && isset($_GET['page']) && $_GET['page'] == 'patients');
?>

<!-- Admin Sidebar -->
<div class="sidebar">
    <div class="p-3">
        <!-- User Info -->
        <div class="text-center mb-4">
            <div class="mb-3">
                <?php if (!empty($_SESSION['foto'])): ?>
                    <img src="<?php echo ASSETS_URL . '/' . $_SESSION['foto']; ?>" 
                         class="rounded-circle" 
                         alt="<?php echo $_SESSION['nama_lengkap']; ?>"
                         width="80" height="80">
                <?php else: ?>
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px; margin: 0 auto;">
                        <span class="text-white fs-4">
                            <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <h6 class="mb-1"><?php echo $_SESSION['nama_lengkap']; ?></h6>
            <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?></small>
        </div>
        
        <!-- Navigation -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php' && !isset($_GET['page'])) ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item mt-2">
                <small class="text-muted ps-3">MANAJEMEN PASIEN</small>
            </li>
            
            <?php if (hasRole('pendaftaran') || $_SESSION['role'] == 'super_admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (($current_page == 'list.php' && strpos($_SERVER['REQUEST_URI'], '/pasien/') !== false) || $is_patients_page) ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/pasien/list.php">
                    <i class="fas fa-users"></i> Daftar Pasien
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasRole('dokter_mata') || hasRole('dokter_umum') || $_SESSION['role'] == 'super_admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (($current_page == 'list.php' && strpos($_SERVER['REQUEST_URI'], '/pasien/') !== false) || $is_patients_page) ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/pasien/list.php">
                    <i class="fas fa-stethoscope"></i> Pemeriksaan Pasien
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item mt-2">
                <small class="text-muted ps-3">LAPORAN</small>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'cetak-pasien.php') ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/laporan/cetak-pasien.php">
                    <i class="fas fa-print"></i> Cetak Data Pasien
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'cetak-hasil.php') ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/laporan/cetak-hasil.php">
                    <i class="fas fa-print"></i> Cetak Hasil MCU
                </a>
            </li>

            <li class="nav-item mt-2">
                <small class="text-muted ps-3">EVALUASI</small>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'evaluasi.php') ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/evaluasi.php">
                    <i class="fas fa-comments"></i> Feedback Pasien
                </a>
            </li>

            <li class="nav-item mt-2">
                <small class="text-muted ps-3">PROFILE</small>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>

            <?php if ($_SESSION['role'] == 'super_admin'): ?>
            <li class="nav-item mt-2">
                <small class="text-muted ps-3">MANAJEMEN USER</small>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'list.php' && strpos($_SERVER['REQUEST_URI'], '/users/') !== false) ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/users/list.php">
                    <i class="fas fa-user-cog"></i> Kelola User
                </a>
            </li>

            <li class="nav-item mt-2">
                <small class="text-muted ps-3">KONTEN</small>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'list.php' && strpos($_SERVER['REQUEST_URI'], '/artikel/') !== false) ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/artikel/list.php">
                    <i class="fas fa-newspaper"></i> Artikel
                </a>
            </li>

            <li class="nav-item mt-2">
                <small class="text-muted ps-3">PENGATURAN</small>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'pengaturan.php') ? 'active' : ''; ?>"
                   href="<?php echo ADMIN_URL; ?>/pengaturan.php">
                    <i class="fas fa-cog"></i> Pengaturan Sistem
                </a>
            </li>
            <?php endif; ?>

        </ul>
    </div>
</div>
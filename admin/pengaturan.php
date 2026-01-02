<?php
$page_title = 'Pengaturan Sistem - Sistem MCU';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Get current settings
$query = "SELECT * FROM pengaturan LIMIT 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);

// If no settings exist, create default
if (!$settings) {
    $default_query = "INSERT INTO pengaturan (nama_klinik, alamat, telepon, email, whatsapp) 
                      VALUES ('Klinik MCU', 'Jl. Kesehatan No. 123', '(021) 1234567', 'info@klinikmcu.com', '081234567890')";
    mysqli_query($conn, $default_query);
    $settings = [
        'nama_klinik' => 'Klinik MCU',
        'alamat' => 'Jl. Kesehatan No. 123',
        'telepon' => '(021) 1234567',
        'email' => 'info@klinikmcu.com',
        'whatsapp' => '081234567890',
        'tentang' => ''
    ];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_klinik = escape($_POST['nama_klinik']);
    $alamat = escape($_POST['alamat']);
    $telepon = escape($_POST['telepon']);
    $email = escape($_POST['email']);
    $whatsapp = escape($_POST['whatsapp']);
    $tentang = escape($_POST['tentang']);
    
    // Handle logo upload
    $logo = $settings['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $upload_result = uploadFile($_FILES['logo'], 'uploads/logo/');
        if (isset($upload_result['success'])) {
            // Delete old logo if exists
            if ($logo && file_exists('../assets/' . $logo)) {
                unlink('../assets/' . $logo);
            }
            $logo = $upload_result['success'];
        }
    }
    
    // Delete logo if checkbox is checked
    if (isset($_POST['delete_logo']) && $_POST['delete_logo'] == '1') {
        if ($logo && file_exists('../assets/' . $logo)) {
            unlink('../assets/' . $logo);
        }
        $logo = '';
    }
    
    // Update settings
    $query = "UPDATE pengaturan SET 
              nama_klinik = '$nama_klinik',
              alamat = '$alamat',
              telepon = '$telepon',
              email = '$email',
              whatsapp = '$whatsapp',
              tentang = '$tentang',
              logo = '$logo'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Pengaturan berhasil diperbarui!";
        // Reload settings
        $query = "SELECT * FROM pengaturan LIMIT 1";
        $result = mysqli_query($conn, $query);
        $settings = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error'] = "Gagal memperbarui pengaturan: " . mysqli_error($conn);
    }
}
?>

<?php include '../includes/admin-header.php'; ?>
<?php include 'includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include 'includes/admin-sidebar.php'; ?>
        </div>
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-cog me-2"></i> Pengaturan Sistem
                </h1>
            </div>
            
            <!-- Settings Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sliders-h me-2"></i> Konfigurasi Sistem
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Clinic Information -->
                        <h5 class="border-bottom pb-2 mb-3">Informasi Klinik</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Klinik *</label>
                                <input type="text" class="form-control" name="nama_klinik" 
                                       value="<?php echo htmlspecialchars($settings['nama_klinik']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Logo Klinik</label>
                                <input type="file" class="form-control" name="logo" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF. Max: 5MB</small>
                            </div>
                        </div>
                        
                        <!-- Current Logo -->
                        <?php if ($settings['logo']): ?>
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-6">
                                <div class="border p-3 rounded">
                                    <p class="mb-2"><strong>Logo Saat Ini:</strong></p>
                                    <img src="<?php echo ASSETS_URL . '/' . $settings['logo']; ?>" 
                                         class="img-fluid" style="max-height: 100px;">
                                    <div class="mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="delete_logo" value="1" id="deleteLogo">
                                            <label class="form-check-label text-danger" for="deleteLogo">
                                                Hapus logo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Alamat Lengkap *</label>
                                <textarea class="form-control" name="alamat" rows="3" required><?php echo htmlspecialchars($settings['alamat']); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">Kontak</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Telepon *</label>
                                <input type="text" class="form-control" name="telepon" 
                                       value="<?php echo htmlspecialchars($settings['telepon']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($settings['email']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" class="form-control" name="whatsapp" 
                                       value="<?php echo htmlspecialchars($settings['whatsapp']); ?>">
                            </div>
                        </div>
                        
                        <!-- About Clinic -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">Tentang Klinik</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Deskripsi Klinik</label>
                                <textarea class="form-control" name="tentang" rows="6"><?php echo htmlspecialchars($settings['tentang']); ?></textarea>
                                <small class="text-muted">Teks ini akan ditampilkan di halaman "Tentang Kami"</small>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i> Informasi Sistem
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Versi Sistem</th>
                                    <td>1.0.0</td>
                                </tr>
                                <tr>
                                    <th>PHP Version</th>
                                    <td><?php echo phpversion(); ?></td>
                                </tr>
                                <tr>
                                    <th>Server Software</th>
                                    <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Database</th>
                                    <td>MySQL</td>
                                </tr>
                                <tr>
                                    <th>Server Time</th>
                                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                </tr>
                                <tr>
                                    <th>Timezone</th>
                                    <td><?php echo date_default_timezone_get(); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Delete logo confirmation
const deleteCheckbox = document.getElementById('deleteLogo');
if (deleteCheckbox) {
    deleteCheckbox.addEventListener('change', function() {
        if (this.checked) {
            if (!confirm('Yakin ingin menghapus logo?')) {
                this.checked = false;
            }
        }
    });
}

// System actions
function clearCache() {
    if (confirm('Clear cache sistem?')) {
        // AJAX request to clear cache
        alert('Cache berhasil dibersihkan!');
    }
}

function systemMaintenance() {
    if (confirm('Aktifkan mode maintenance? Sistem akan sementara tidak dapat diakses oleh pengunjung.')) {
        // AJAX request to toggle maintenance mode
        alert('Mode maintenance diaktifkan!');
    }
}
</script>

<?php include '../includes/admin-footer.php'; ?>

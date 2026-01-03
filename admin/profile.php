<?php
$page_title = 'Profile - Sistem MCU';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

// Get current user data
$user_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admin_users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile
        $nama_lengkap = escape($_POST['nama_lengkap']);
        $email = escape($_POST['email']);
        
        // Handle photo upload
        $foto = $user['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload_result = uploadFile($_FILES['foto'], 'uploads/profiles/');
            if (isset($upload_result['success'])) {
                // Delete old photo if exists
                if ($foto && file_exists('../assets/' . $foto)) {
                    unlink('../assets/' . $foto);
                }
                $foto = $upload_result['success'];
                $_SESSION['foto'] = $foto;
            }
        }
        
        // Delete photo if checkbox is checked
        if (isset($_POST['delete_foto']) && $_POST['delete_foto'] == '1') {
            if ($foto && file_exists('../assets/' . $foto)) {
                unlink('../assets/' . $foto);
            }
            $foto = '';
            $_SESSION['foto'] = '';
        }
        
        $query = "UPDATE admin_users SET 
                  nama_lengkap = '$nama_lengkap',
                  email = '$email',
                  foto = '$foto'
                  WHERE id = $user_id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['success'] = "Profile berhasil diperbarui!";
            redirect('profile.php');
        }
        
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (md5($current_password) != $user['password']) {
            $_SESSION['error'] = "Password saat ini salah!";
            redirect('profile.php');
        }
        
        if (strlen($new_password) < 6) {
            $_SESSION['error'] = "Password baru minimal 6 karakter!";
            redirect('profile.php');
        }
        
        if ($new_password != $confirm_password) {
            $_SESSION['error'] = "Konfirmasi password tidak sesuai!";
            redirect('profile.php');
        }
        
        $hashed_password = md5($new_password);
        $query = "UPDATE admin_users SET password = '$hashed_password' WHERE id = $user_id";

        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Password berhasil diubah!";
            redirect('profile.php');
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat mengubah password. Silakan coba lagi.";
            redirect('profile.php');
        }
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
                    <i class="fas fa-user me-2"></i> Profile Pengguna
                </h1>
            </div>
            
            <!-- Profile Card -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Profile</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <!-- Profile Photo -->
                            <div class="mb-4">
                                <?php if ($user['foto']): ?>
                                    <img src="<?php echo ASSETS_URL . '/' . $user['foto']; ?>" 
                                         class="rounded-circle" 
                                         width="150" height="150"
                                         style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 150px; height: 150px;">
                                        <span class="text-white fs-1">
                                            <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- User Stats -->
                            <div class="card">
                                <div class="card-body">
                                    <h6>Statistik Akun</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Role:</td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Status:</td>
                                            <td>
                                                <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $user['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Bergabung:</td>
                                            <td><?php echo formatDateIndo($user['created_at']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Login Terakhir:</td>
                                            <td>
                                                <?php echo $user['last_login'] ? formatDateIndo($user['last_login'], true) : 'Belum'; ?>
                                            </td>
                                        </tr>
                                    </table>
        </div>
    </div>
</div>
                        
                        <div class="col-md-8">
                            <!-- Tabs -->
                            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit">
                                        <i class="fas fa-edit me-2"></i> Edit Profile
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password">
                                        <i class="fas fa-key me-2"></i> Ubah Password
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content mt-4" id="profileTabsContent">
                                <!-- Edit Profile Tab -->
                                <div class="tab-pane fade show active" id="edit" role="tabpanel">
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="update_profile" value="1">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Username</label>
                                                <input type="text" class="form-control" 
                                                       value="<?php echo $user['username']; ?>" disabled>
                                                <small class="text-muted">Username tidak dapat diubah</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nama Lengkap *</label>
                                                <input type="text" class="form-control" name="nama_lengkap" 
                                                       value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" 
                                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Foto Profile</label>
                                                <input type="file" class="form-control" name="foto" accept="image/*">
                                                <small class="text-muted">Format: JPG, PNG, GIF. Max: 5MB</small>
                                            </div>
                                        </div>
                                        
                                        <?php if ($user['foto']): ?>
                                        <div class="row mb-3">
                                            <div class="col-md-6 offset-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="delete_foto" value="1" id="deleteFoto">
                                                    <label class="form-check-label text-danger" for="deleteFoto">
                                                        Hapus foto profile
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Change Password Tab -->
                                <div class="tab-pane fade" id="password" role="tabpanel">
                                    <form method="POST" action="">
                                        <input type="hidden" name="change_password" value="1">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Password Saat Ini *</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control password-field" name="current_password" id="currentPassword" required>
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            onclick="togglePasswordVisibility('currentPassword')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Password Baru *</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control password-field" name="new_password" id="newPassword" required>
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            onclick="togglePasswordVisibility('newPassword')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted">Minimal 6 karakter</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Konfirmasi Password Baru *</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control password-field" name="confirm_password" id="confirmPassword" required>
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            onclick="togglePasswordVisibility('confirmPassword')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Pastikan password baru kuat dan mudah diingat.
                                        </div>
                                        
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-key me-2"></i> Ubah Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Hide browser's default password toggle */
.password-field::-ms-reveal,
.password-field::-ms-clear {
    display: none;
}

.password-field::-webkit-credentials-auto-fill-button {
    display: none;
}

.password-field::-webkit-contacts-auto-fill-button {
    display: none;
}

.password-field::-webkit-credit-card-auto-fill-button {
    display: none;
}

/* Hide Chrome's password reveal icon */
input[type="password"]::-webkit-password-toggle {
    display: none;
}
</style>

<script>
// Initialize tabs
const triggerTabList = document.querySelectorAll('#profileTabs button');
triggerTabList.forEach(triggerEl => {
    const tabTrigger = new bootstrap.Tab(triggerEl);
    triggerEl.addEventListener('click', event => {
        event.preventDefault();
        tabTrigger.show();
    });
});

// Toggle password visibility
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Delete photo confirmation
const deleteCheckbox = document.getElementById('deleteFoto');
if (deleteCheckbox) {
    deleteCheckbox.addEventListener('change', function() {
        if (this.checked) {
            if (!confirm('Yakin ingin menghapus foto profile?')) {
                this.checked = false;
            }
        }
    });
}
</script>

<?php include '../includes/admin-footer.php'; ?>

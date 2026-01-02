<?php
$page_title = 'Edit User - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Get user ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    $_SESSION['error'] = "User tidak ditemukan";
    redirect('list.php');
}

// Get user data
$query = "SELECT * FROM admin_users WHERE id = $id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $_SESSION['error'] = "User tidak ditemukan";
    redirect('list.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escape($_POST['username']);
    $nama_lengkap = escape($_POST['nama_lengkap']);
    $email = escape($_POST['email']);
    $role = escape($_POST['role']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Check password change
    $password_update = '';
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 6) {
            $_SESSION['error'] = "Password minimal 6 karakter";
            redirect("edit.php?id=$id");
        }
        
        if ($_POST['password'] != $_POST['confirm_password']) {
            $_SESSION['error'] = "Konfirmasi password tidak sesuai";
            redirect("edit.php?id=$id");
        }
        
        $hashed_password = md5($_POST['password']);
        $password_update = ", password = '$hashed_password'";
    }
    
    // Check if username exists (excluding current user)
    $check_query = "SELECT id FROM admin_users WHERE username = '$username' AND id != $id";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Username sudah digunakan";
        redirect("edit.php?id=$id");
    }
    
    // Validate email
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid";
        redirect("edit.php?id=$id");
    }
    
    // Update user
    $query = "UPDATE admin_users SET 
              username = '$username',
              nama_lengkap = '$nama_lengkap',
              email = '$email',
              role = '$role',
              is_active = $is_active
              $password_update
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "User berhasil diperbarui!";
        redirect('list.php');
    } else {
        $_SESSION['error'] = "Gagal memperbarui user: " . mysqli_error($conn);
    }
}
?>

<?php include '../../includes/admin-header.php'; ?>
<?php include '../includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include '../includes/admin-sidebar.php'; ?>
        </div>
        <div class="col-lg-10">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="list.php">Users</a></li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
            </nav>
            
            <!-- User Form -->
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i> Edit User
                        <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                            <span class="badge bg-info ms-2">Akun Anda</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="userForm">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Username -->
                                <div class="mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" class="form-control" name="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                
                                <!-- Password (optional) -->
                                <div class="mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" name="password">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                </div>
                                
                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Full Name -->
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" name="nama_lengkap" 
                                           value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                </div>
                                
                                <!-- Email -->
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                                
                                <!-- Role -->
                                <div class="mb-3">
                                    <label class="form-label">Role *</label>
                                    <select class="form-select" name="role" required>
                                        <option value="super_admin" <?php echo $user['role'] == 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                        <option value="pendaftaran" <?php echo $user['role'] == 'pendaftaran' ? 'selected' : ''; ?>>Staff Pendaftaran</option>
                                        <option value="dokter_mata" <?php echo $user['role'] == 'dokter_mata' ? 'selected' : ''; ?>>Dokter Mata</option>
                                        <option value="dokter_umum" <?php echo $user['role'] == 'dokter_umum' ? 'selected' : ''; ?>>Dokter Umum</option>
                                    </select>
                                </div>
                                
                                <!-- Status -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="is_active" id="is_active" 
                                               value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Akun Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Stats -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Informasi Akun:</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <small class="text-muted">Dibuat</small><br>
                                                <strong><?php echo formatDateIndo($user['created_at'], true); ?></strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted">Login Terakhir</small><br>
                                                <strong>
                                                    <?php echo $user['last_login'] ? formatDateIndo($user['last_login'], true) : 'Belum login'; ?>
                                                </strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted">Status Saat Ini</small><br>
                                                <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $user['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg me-2">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                            <a href="list.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('userForm').addEventListener('submit', function(e) {
    const password = this.querySelector('input[name="password"]');
    const confirmPassword = this.querySelector('input[name="confirm_password"]');
    
    // Check if password is being changed
    if (password.value.trim() !== '') {
        if (password.value.length < 6) {
            e.preventDefault();
            alert('Password minimal 6 karakter!');
            password.focus();
            return false;
        }
        
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Konfirmasi password tidak sesuai!');
            confirmPassword.focus();
            return false;
        }
    }
    
    return true;
});
</script>

<?php include '../../includes/admin-footer.php'; ?>

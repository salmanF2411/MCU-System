<?php
$page_title = 'Tambah User - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escape($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = escape($_POST['nama_lengkap']);
    $email = escape($_POST['email']);
    $role = escape($_POST['role']);
    
    // Validation
    $errors = [];
    
    // Check if username exists
    $check_query = "SELECT id FROM admin_users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Username sudah digunakan";
    }
    
    // Check password
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if ($password != $confirm_password) {
        $errors[] = "Konfirmasi password tidak sesuai";
    }
    
    // Validate email
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // If no errors, insert user
    if (empty($errors)) {
        $hashed_password = md5($password); // Use md5 for simplicity, consider password_hash() for production
        
        $query = "INSERT INTO admin_users (username, password, nama_lengkap, email, role) 
                  VALUES ('$username', '$hashed_password', '$nama_lengkap', '$email', '$role')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "User berhasil ditambahkan!";
            redirect('list.php');
        } else {
            $_SESSION['error'] = "Gagal menambahkan user: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
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
                    <li class="breadcrumb-item active">Tambah User</li>
                </ol>
            </nav>
            
            <!-- User Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i> Tambah User Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="userForm">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Username -->
                                <div class="mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" class="form-control" name="username" required>
                                    <small class="text-muted">Username untuk login</small>
                                </div>
                                
                                <!-- Password -->
                                <div class="mb-3">
                                    <label class="form-label">Password *</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control" name="password" id="password" required>
                                        <i class="fas fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted" id="togglePassword" style="cursor: pointer;"></i>
                                    </div>
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label class="form-label">Konfirmasi Password *</label>
                                    <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Full Name -->
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" name="nama_lengkap" required>
                                </div>
                                
                                <!-- Email -->
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                                
                                <!-- Role -->
                                <div class="mb-3">
                                    <label class="form-label">Role *</label>
                                    <select class="form-select" name="role" required>
                                        <option value="">- Pilih Role -</option>
                                        <option value="super_admin">Super Admin</option>
                                        <option value="pendaftaran">Staff Pendaftaran</option>
                                        <option value="dokter_mata">Dokter Mata</option>
                                        <option value="dokter_umum">Dokter Umum</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Role Descriptions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6>Deskripsi Role:</h6>
                                    <ul class="mb-0">
                                        <li><strong>Super Admin:</strong> Akses penuh ke semua fitur</li>
                                        <li><strong>Staff Pendaftaran:</strong> Input data sirkulasi dan manajemen pasien</li>
                                        <li><strong>Dokter Mata:</strong> Pemeriksaan mata saja</li>
                                        <li><strong>Dokter Umum:</strong> Pemeriksaan umum dan kesimpulan akhir</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg me-2">
                                <i class="fas fa-save me-2"></i> Simpan User
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

    return true;
});

// Password toggle functionality
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
    }
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const confirmPasswordInput = document.getElementById('confirmPassword');

    if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
    } else {
        confirmPasswordInput.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
    }
});
</script>

<?php include '../../includes/admin-footer.php'; ?>

<?php
$page_title = 'Tambah Layanan Home Visit - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_layanan = escape($_POST['judul_layanan']);
    $deskripsi = escape($_POST['deskripsi']);
    $harga = escape($_POST['harga']);
    $status = escape($_POST['status']);

    // Handle image upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = uploadFile($_FILES['gambar'], 'uploads/home-visit/', 'image');
        if (isset($upload_result['success'])) {
            $gambar = $upload_result['success'];
        } else {
            $_SESSION['error'] = $upload_result['error'];
            header("Location: add.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Gambar wajib diupload!";
        header("Location: add.php");
        exit();
    }

    // Insert service
    $query = "INSERT INTO home_visit_setting (judul_layanan, deskripsi, harga, gambar, status)
              VALUES ('$judul_layanan', '$deskripsi', '$harga', '$gambar', '$status')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Layanan home visit berhasil ditambahkan!";
        header("Location: list.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan layanan: " . mysqli_error($conn);
        header("Location: add.php");
        exit();
    }
}

include '../../includes/admin-header.php';
?>
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
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="list.php">Home Visit</a></li>
                    <li class="breadcrumb-item active">Tambah Baru</li>
                </ol>
            </nav>

            <!-- Service Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i> Tambah Layanan Home Visit Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="serviceForm">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Service Title -->
                                <div class="mb-3">
                                    <label class="form-label">Judul Layanan *</label>
                                    <input type="text" class="form-control" name="judul_layanan" required>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi *</label>
                                    <textarea class="form-control" name="deskripsi" rows="5" required></textarea>
                                </div>

                                <!-- Price -->
                                <div class="mb-3">
                                    <label class="form-label">Harga (Rp) *</label>
                                    <input type="number" class="form-control" name="harga" min="0" step="0.01" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Sidebar -->
                                <div class="card">
                                    <div class="card-body">
                                        <!-- Status -->
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="aktif">Aktif</option>
                                                <option value="nonaktif">Nonaktif</option>
                                            </select>
                                        </div>

                                        <!-- Image Upload -->
                                        <div class="mb-3">
                                            <label class="form-label">Gambar Layanan *</label>
                                            <input type="file" class="form-control" name="gambar"
                                                   accept="image/*" required>
                                            <small class="text-muted">Max. 5MB, format: JPG, PNG, GIF</small>
                                            <div id="imagePreview" class="mt-2"></div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save me-2"></i> Simpan Layanan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview
document.querySelector('input[name="gambar"]').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="border p-2 rounded">
                    <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">
                    <div class="mt-2 text-center">
                        <small class="text-muted">Preview</small>
                    </div>
                </div>
            `;
        }
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});

// Form validation
document.getElementById('serviceForm').addEventListener('submit', function(e) {
    const title = this.querySelector('input[name="judul_layanan"]');
    const description = this.querySelector('textarea[name="deskripsi"]');
    const price = this.querySelector('input[name="harga"]');
    const image = this.querySelector('input[name="gambar"]');

    let isValid = true;

    if (!title.value.trim()) {
        title.classList.add('is-invalid');
        isValid = false;
    } else {
        title.classList.remove('is-invalid');
    }

    if (!description.value.trim()) {
        description.classList.add('is-invalid');
        isValid = false;
    } else {
        description.classList.remove('is-invalid');
    }

    if (!price.value || price.value <= 0) {
        price.classList.add('is-invalid');
        isValid = false;
    } else {
        price.classList.remove('is-invalid');
    }

    if (!image.files[0]) {
        image.classList.add('is-invalid');
        isValid = false;
    } else {
        image.classList.remove('is-invalid');
    }

    if (!isValid) {
        e.preventDefault();
        alert('Harap lengkapi semua field yang wajib diisi!');
    }
});
</script>

<style>
.is-invalid {
    border-color: #dc3545;
}
</style>

<?php include '../../includes/admin-footer.php'; ?>

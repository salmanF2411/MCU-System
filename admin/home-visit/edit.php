<?php
$page_title = 'Edit Layanan Home Visit - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Get service ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    $_SESSION['error'] = "Layanan tidak ditemukan";
    redirect('list.php');
}

// Get service data
$query = "SELECT * FROM home_visit_setting WHERE id_setting = $id";
$result = mysqli_query($conn, $query);
$service = mysqli_fetch_assoc($result);

if (!$service) {
    $_SESSION['error'] = "Layanan tidak ditemukan";
    redirect('list.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_layanan = escape($_POST['judul_layanan']);
    $deskripsi = escape($_POST['deskripsi']);
    $harga = escape($_POST['harga']);
    $status = escape($_POST['status']);

    // Handle image upload
    $gambar = $service['gambar']; // Keep existing image

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = uploadFile($_FILES['gambar'], 'uploads/home-visit/', 'image');
        if (isset($upload_result['success'])) {
            // Delete old image if exists
            if ($gambar && file_exists('../../assets/' . $gambar)) {
                unlink('../../assets/' . $gambar);
            }
            $gambar = $upload_result['success'];
        } else {
            $_SESSION['error'] = $upload_result['error'];
            redirect("edit.php?id=$id");
        }
    }

    // Delete image if checkbox is checked
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        if ($gambar && file_exists('../../assets/' . $gambar)) {
            unlink('../../assets/' . $gambar);
        }
        $gambar = '';
    }

    // Update service
    $query = "UPDATE home_visit_setting SET
              judul_layanan = '$judul_layanan',
              deskripsi = '$deskripsi',
              harga = '$harga',
              gambar = '$gambar',
              status = '$status',
              updated_at = NOW()
              WHERE id_setting = $id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Layanan home visit berhasil diperbarui!";
        redirect('list.php');
    } else {
        $_SESSION['error'] = "Gagal memperbarui layanan: " . mysqli_error($conn);
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
                    <li class="breadcrumb-item active">Edit Layanan</li>
                </ol>
            </nav>

            <!-- Service Form -->
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i> Edit Layanan Home Visit
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="serviceForm">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Service Title -->
                                <div class="mb-3">
                                    <label class="form-label">Judul Layanan *</label>
                                    <input type="text" class="form-control" name="judul_layanan"
                                           value="<?php echo htmlspecialchars($service['judul_layanan']); ?>" required>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi *</label>
                                    <textarea class="form-control" name="deskripsi" rows="5" required><?php echo htmlspecialchars($service['deskripsi']); ?></textarea>
                                </div>

                                <!-- Price -->
                                <div class="mb-3">
                                    <label class="form-label">Harga (Rp) *</label>
                                    <input type="number" class="form-control" name="harga" min="0" step="0.01"
                                           value="<?php echo $service['harga']; ?>" required>
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
                                                <option value="aktif" <?php echo $service['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="nonaktif" <?php echo $service['status'] == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                            </select>
                                        </div>

                                        <!-- Current Image -->
                                        <?php if ($service['gambar']): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Gambar Saat Ini</label>
                                            <div class="border p-2 rounded text-center">
                                                <img src="<?php echo ASSETS_URL . '/' . $service['gambar']; ?>"
                                                     alt="<?php echo htmlspecialchars($service['judul_layanan']); ?>"
                                                     class="img-fluid rounded" style="max-height: 150px;">
                                                <div class="mt-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="delete_image" value="1" id="deleteImage">
                                                        <label class="form-check-label text-danger" for="deleteImage">
                                                            Hapus gambar ini
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- New Image -->
                                        <div class="mb-3">
                                            <label class="form-label"><?php echo $service['gambar'] ? 'Ganti Gambar' : 'Tambah Gambar'; ?></label>
                                            <input type="file" class="form-control" name="gambar" accept="image/*">
                                            <small class="text-muted">Max. 5MB, format: JPG, PNG, GIF</small>
                                            <div id="imagePreview" class="mt-2"></div>
                                        </div>

                                        <!-- Service Stats -->
                                        <div class="mb-3">
                                            <div class="border p-3 rounded bg-light">
                                                <h6>Statistik Layanan</h6>
                                                <table class="table table-sm mb-0">
                                                    <tr>
                                                        <td>Dibuat:</td>
                                                        <td><?php echo formatDateIndo($service['created_at'], true); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Diupdate:</td>
                                                        <td><?php echo formatDateIndo($service['updated_at'], true); ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                                            </button>
                                            <a href="list.php" class="btn btn-secondary">
                                                <i class="fas fa-times me-2"></i> Batal
                                            </a>
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
const imageInput = document.querySelector('input[name="gambar"]');
if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const file = e.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="border p-2 rounded">
                        <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">
                        <div class="mt-2 text-center">
                            <small class="text-muted">Preview Gambar Baru</small>
                        </div>
                    </div>
                `;
            }
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    });
}

// Delete image checkbox
const deleteCheckbox = document.getElementById('deleteImage');
if (deleteCheckbox) {
    deleteCheckbox.addEventListener('change', function() {
        if (this.checked) {
            if (!confirm('Yakin ingin menghapus gambar ini?')) {
                this.checked = false;
            }
        }
    });
}

// Form validation
document.getElementById('serviceForm').addEventListener('submit', function(e) {
    const title = this.querySelector('input[name="judul_layanan"]');
    const description = this.querySelector('textarea[name="deskripsi"]');
    const price = this.querySelector('input[name="harga"]');

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

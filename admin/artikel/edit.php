<?php
$page_title = 'Edit Artikel - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Get article ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    $_SESSION['error'] = "Artikel tidak ditemukan";
    redirect('list.php');
}

// Get article data
$query = "SELECT * FROM artikel WHERE id = $id";
$result = mysqli_query($conn, $query);
$article = mysqli_fetch_assoc($result);

if (!$article) {
    $_SESSION['error'] = "Artikel tidak ditemukan";
    redirect('list.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = escape($_POST['judul']);
    $konten = escape($_POST['konten']);
    $kategori = escape($_POST['kategori']);
    $penulis = escape($_POST['penulis']);
    $tanggal_publish = escape($_POST['tanggal_publish']);
    $status = escape($_POST['status']);
    
    // Generate slug
    $slug = strtolower(str_replace(' ', '-', $judul));
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Handle image upload
    $gambar = $article['gambar']; // Keep existing image

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = uploadFile($_FILES['gambar'], 'uploads/artikel/', 'image');
        if (isset($upload_result['success'])) {
            // Delete old image if exists
            if ($gambar && file_exists('../../assets/' . $gambar)) {
                unlink('../../assets/' . $gambar);
            }
            $gambar = $upload_result['success'];
        }
    }

    // Delete image if checkbox is checked
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        if ($gambar && file_exists('../../assets/' . $gambar)) {
            unlink('../../assets/' . $gambar);
        }
        $gambar = '';
    }

    // Handle video upload
    $video = $article['video']; // Keep existing video

    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $upload_result = uploadFile($_FILES['video'], 'uploads/artikel/', 'video');
        if (isset($upload_result['success'])) {
            // Delete old video if exists
            if ($video && file_exists('../../assets/' . $video)) {
                unlink('../../assets/' . $video);
            }
            $video = $upload_result['success'];
        }
    }

    // Delete video if checkbox is checked
    if (isset($_POST['delete_video']) && $_POST['delete_video'] == '1') {
        if ($video && file_exists('../../assets/' . $video)) {
            unlink('../../assets/' . $video);
        }
        $video = '';
    }

    // Update article
    $query = "UPDATE artikel SET
              judul = '$judul',
              slug = '$slug',
              konten = '$konten',
              gambar = '$gambar',
              video = '$video',
              kategori = '$kategori',
              penulis = '$penulis',
              tanggal_publish = '$tanggal_publish',
              status = '$status',
              updated_at = NOW()
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Artikel berhasil diperbarui!";
        redirect('list.php');
    } else {
        $_SESSION['error'] = "Gagal memperbarui artikel: " . mysqli_error($conn);
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
                    <li class="breadcrumb-item"><a href="list.php">Artikel</a></li>
                    <li class="breadcrumb-item active">Edit Artikel</li>
                </ol>
            </nav>
            
            <!-- Article Form -->
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i> Edit Artikel
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="articleForm">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Title -->
                                <div class="mb-3">
                                    <label class="form-label">Judul Artikel *</label>
                                    <input type="text" class="form-control" name="judul" 
                                           value="<?php echo htmlspecialchars($article['judul']); ?>" required>
                                </div>
                                
                                <!-- Content -->
                                <div class="mb-3">
                                    <label class="form-label">Konten *</label>
                                    <textarea class="form-control" name="konten" id="editor" rows="15" required><?php echo htmlspecialchars($article['konten']); ?></textarea>
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
                                                <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Category -->
                                        <div class="mb-3">
                                            <label class="form-label">Kategori</label>
                                            <input type="text" class="form-control" name="kategori" 
                                                   value="<?php echo htmlspecialchars($article['kategori']); ?>"
                                                   placeholder="Kesehatan, Tips, dll">
                                        </div>
                                        
                                        <!-- Author -->
                                        <div class="mb-3">
                                            <label class="form-label">Penulis *</label>
                                            <input type="text" class="form-control" name="penulis" 
                                                   value="<?php echo htmlspecialchars($article['penulis']); ?>" required>
                                        </div>
                                        
                                        <!-- Publish Date -->
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Publikasi</label>
                                            <input type="date" class="form-control" name="tanggal_publish" 
                                                   value="<?php echo $article['tanggal_publish']; ?>" required>
                                        </div>
                                        
                                        <!-- Current Image -->
                                        <?php if ($article['gambar']): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Gambar Saat Ini</label>
                                            <div class="border p-2 rounded text-center">
                                                <img src="<?php echo ASSETS_URL . '/' . $article['gambar']; ?>" 
                                                     class="img-fluid rounded" 
                                                     style="max-height: 150px;">
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
                                            <label class="form-label"><?php echo $article['gambar'] ? 'Ganti Gambar' : 'Tambah Gambar'; ?></label>
                                            <input type="file" class="form-control" name="gambar"
                                                   accept="image/*">
                                            <small class="text-muted">Max. 5MB, format: JPG, PNG, GIF</small>
                                            <div id="imagePreview" class="mt-2"></div>
                                        </div>

                                        <!-- Current Video -->
                                        <?php if ($article['video']): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Video Saat Ini</label>
                                            <div class="border p-2 rounded text-center">
                                                <video src="<?php echo ASSETS_URL . '/' . $article['video']; ?>"
                                                       class="img-fluid rounded"
                                                       style="max-height: 150px;" controls></video>
                                                <div class="mt-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="delete_video" value="1" id="deleteVideo">
                                                        <label class="form-check-label text-danger" for="deleteVideo">
                                                            Hapus video ini
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- New Video -->
                                        <div class="mb-3">
                                            <label class="form-label"><?php echo $article['video'] ? 'Ganti Video' : 'Tambah Video'; ?></label>
                                            <input type="file" class="form-control" name="video"
                                                   accept="video/*">
                                            <small class="text-muted">Max. 50MB, format: MP4, AVI, MOV, WMV, FLV</small>
                                            <div id="videoPreview" class="mt-2"></div>
                                        </div>

                                        <!-- Article Stats -->
                                        <div class="mb-3">
                                            <div class="border p-3 rounded bg-light">
                                                <h6>Statistik Artikel</h6>
                                                <table class="table table-sm mb-0">
                                                    <tr>
                                                        <td>Dibuat:</td>
                                                        <td><?php echo formatDateIndo($article['created_at'], true); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Diupdate:</td>
                                                        <td><?php echo formatDateIndo($article['updated_at'], true); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Views:</td>
                                                        <td><span class="badge bg-info"><?php echo $article['views']; ?></span></td>
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

// Video preview
const videoInput = document.querySelector('input[name="video"]');
if (videoInput) {
    videoInput.addEventListener('change', function(e) {
        const preview = document.getElementById('videoPreview');
        const file = e.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="border p-2 rounded">
                        <video src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;" controls></video>
                        <div class="mt-2 text-center">
                            <small class="text-muted">Preview Video Baru</small>
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

// Delete video checkbox
const deleteVideoCheckbox = document.getElementById('deleteVideo');
if (deleteVideoCheckbox) {
    deleteVideoCheckbox.addEventListener('change', function() {
        if (this.checked) {
            if (!confirm('Yakin ingin menghapus video ini?')) {
                this.checked = false;
            }
        }
    });
}

// Form validation
document.getElementById('articleForm').addEventListener('submit', function(e) {
    const title = this.querySelector('input[name="judul"]');
    const content = this.querySelector('textarea[name="konten"]');
    const author = this.querySelector('input[name="penulis"]');
    
    let isValid = true;
    
    if (!title.value.trim()) {
        title.classList.add('is-invalid');
        isValid = false;
    } else {
        title.classList.remove('is-invalid');
    }
    
    if (!content.value.trim()) {
        content.classList.add('is-invalid');
        isValid = false;
    } else {
        content.classList.remove('is-invalid');
    }
    
    if (!author.value.trim()) {
        author.classList.add('is-invalid');
        isValid = false;
    } else {
        author.classList.remove('is-invalid');
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('Harap lengkapi semua field yang wajib diisi!');
    }
});
</script>

<?php include '../../includes/admin-footer.php'; ?>

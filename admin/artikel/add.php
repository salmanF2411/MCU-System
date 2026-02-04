<?php
$page_title = 'Tambah Artikel - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

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

    // Check if slug already exists
    $check_query = "SELECT id FROM artikel WHERE slug = '$slug'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Judul artikel sudah ada. Silakan gunakan judul yang berbeda.";
        header("Location: add.php");
        exit();
    }

    // Handle image upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_result = uploadFile($_FILES['gambar'], 'uploads/artikel/', 'image');
        if (isset($upload_result['success'])) {
            $gambar = $upload_result['success'];
        }
    }

    // Handle video upload
    $video = '';
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $upload_result = uploadFile($_FILES['video'], 'uploads/artikel/', 'video');
        if (isset($upload_result['success'])) {
            $video = $upload_result['success'];
        }
    }

    // Insert article
    $query = "INSERT INTO artikel (judul, slug, konten, gambar, video, kategori, penulis, tanggal_publish, status)
              VALUES ('$judul', '$slug', '$konten', '$gambar', '$video', '$kategori', '$penulis', '$tanggal_publish', '$status')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Artikel berhasil ditambahkan!";
        header("Location: list.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan artikel: " . mysqli_error($conn) . " | Query: " . $query;
        // For debugging, also show on page
        echo "Error: " . mysqli_error($conn) . "<br>";
        echo "Query: " . $query;
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
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="list.php">Artikel</a></li>
                    <li class="breadcrumb-item active">Tambah Baru</li>
                </ol>
            </nav>
            
            <!-- Article Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i> Tambah Artikel Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="articleForm">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Title -->
                                <div class="mb-3">
                                    <label class="form-label">Judul Artikel *</label>
                                    <input type="text" class="form-control <?php echo isset($_SESSION['error']) && strpos($_SESSION['error'], 'Judul artikel sudah ada') !== false ? 'is-invalid' : ''; ?>" name="judul" required>
                                    <?php if (isset($_SESSION['error']) && strpos($_SESSION['error'], 'Judul artikel sudah ada') !== false): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $_SESSION['error']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Content -->
                                <div class="mb-3">
                                    <label class="form-label">Konten *</label>
                                    <textarea class="form-control" name="konten" id="editor" rows="15" required></textarea>
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
                                                <option value="draft">Draft</option>
                                                <option value="published">Published</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Category -->
                                        <div class="mb-3">
                                            <label class="form-label">Kategori</label>
                                            <input type="text" class="form-control" name="kategori" 
                                                   placeholder="Kesehatan, Tips, dll">
                                        </div>
                                        
                                        <!-- Author -->
                                        <div class="mb-3">
                                            <label class="form-label">Penulis *</label>
                                            <input type="text" class="form-control" name="penulis" 
                                                   value="<?php echo $_SESSION['nama_lengkap']; ?>" required>
                                        </div>
                                        
                                        <!-- Publish Date -->
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Publikasi</label>
                                            <input type="date" class="form-control" name="tanggal_publish" 
                                                   value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        
                                        <!-- Featured Image -->
                                        <div class="mb-3">
                                            <label class="form-label">Gambar Utama</label>
                                            <input type="file" class="form-control" name="gambar"
                                                   accept="image/*">
                                            <small class="text-muted">Max. 5MB, format: JPG, PNG, GIF</small>
                                            <div id="imagePreview" class="mt-2"></div>
                                        </div>

                                        <!-- Featured Video -->
                                        <div class="mb-3">
                                            <label class="form-label">Video Utama</label>
                                            <input type="file" class="form-control" name="video"
                                                   accept="video/*">
                                            <small class="text-muted">Max. 50MB, format: MP4, AVI, MOV, WMV, FLV</small>
                                            <div id="videoPreview" class="mt-2"></div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save me-2"></i> Simpan Artikel
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

<!-- TinyMCE Editor (Optional) -->
<!--
<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#editor',
    height: 500,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
});
</script>
-->

<script>
// Simple image preview
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

// Simple video preview
document.querySelector('input[name="video"]').addEventListener('change', function(e) {
    const preview = document.getElementById('videoPreview');
    const file = e.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="border p-2 rounded">
                    <video src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;" controls></video>
                    <div class="mt-2 text-center">
                        <small class="text-muted">Preview Video</small>
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

<style>
#editor {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 16px;
    line-height: 1.6;
}

.is-invalid {
    border-color: #dc3545;
}
</style>

<?php include '../../includes/admin-footer.php'; ?>

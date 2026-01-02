<?php
$page_title = 'Manajemen Artikel - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? escape($_GET['search']) : '';

// Build query
$where = "1=1";

if ($search) {
    $where .= " AND (judul LIKE '%$search%' OR konten LIKE '%$search%' OR penulis LIKE '%$search%')";
}

if ($status != 'all') {
    $where .= " AND status = '$status'";
}

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records
$total_query = "SELECT COUNT(*) as total FROM artikel WHERE $where";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Get articles
$query = "SELECT * FROM artikel 
          WHERE $where 
          ORDER BY tanggal_publish DESC, created_at DESC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
?>

<?php include '../../includes/admin-header.php'; ?>
<?php include '../includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include '../includes/admin-sidebar.php'; ?>
        </div>
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-newspaper me-2"></i> Manajemen Artikel
                </h1>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Artikel Baru
                </a>
            </div>
            
            <!-- Filter and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Cari artikel..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="published" <?php echo $status == 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $status == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="list.php" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Articles Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        Daftar Artikel
                        <span class="badge bg-primary ms-2"><?php echo $total_records; ?> artikel</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Judul</th>
                                        <th width="100">Penulis</th>
                                        <th width="120">Tanggal</th>
                                        <th width="100">Status</th>
                                        <th width="80">Views</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = $offset + 1; ?>
                                    <?php while ($article = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($article['judul']); ?></strong>
                                                <?php if ($article['kategori']): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-tag"></i> <?php echo $article['kategori']; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $article['penulis']; ?></td>
                                            <td>
                                                <small><?php echo formatDateIndo($article['tanggal_publish']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($article['status'] == 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $article['views']; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo BASE_URL; ?>/artikel-detail.php?id=<?php echo $article['id']; ?>" 
                                                       target="_blank" 
                                                       class="btn btn-info" 
                                                       title="Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $article['id']; ?>" 
                                                       class="btn btn-warning" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger delete-article" 
                                                            data-id="<?php echo $article['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($article['judul']); ?>"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                &laquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                                &raquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Tidak ada artikel.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus artikel <strong id="articleTitle"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" action="delete.php" style="display: inline;">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Delete confirmation
document.querySelectorAll('.delete-article').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const title = this.getAttribute('data-title');
        
        document.getElementById('deleteId').value = id;
        document.getElementById('articleTitle').textContent = title;
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });
});
</script>

<?php include '../../includes/admin-footer.php'; ?>

<?php
$page_title = 'Artikel Kesehatan - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/header.php';

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$search_params = [];

if (!empty($search)) {
    $search_condition = " AND (judul LIKE ? OR konten LIKE ? OR kategori LIKE ? OR penulis LIKE ?)";
    $search_term = '%' . $search . '%';
    $search_params = [$search_term, $search_term, $search_term, $search_term];
}

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total articles
$total_query = "SELECT COUNT(*) as total FROM artikel WHERE status = 'published'" . $search_condition;
$total_stmt = mysqli_prepare($conn, $total_query);
if (!empty($search_params)) {
    mysqli_stmt_bind_param($total_stmt, str_repeat('s', count($search_params)), ...$search_params);
}
mysqli_stmt_execute($total_stmt);
$total_result = mysqli_stmt_get_result($total_stmt);
$total_row = mysqli_fetch_assoc($total_result);
$total_articles = $total_row['total'];
$total_pages = ceil($total_articles / $limit);

// Get articles
$query = "SELECT * FROM artikel
          WHERE status = 'published'" . $search_condition . "
          ORDER BY created_at DESC
          LIMIT $limit OFFSET $offset";
$stmt = mysqli_prepare($conn, $query);
if (!empty($search_params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($search_params)), ...$search_params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="page-title">Artikel Kesehatan</h1>
            <p class="lead">Informasi dan tips kesehatan terbaru dari tim dokter kami.</p>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" action="" class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari artikel..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($article = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($article['gambar'])): ?>
                            <img src="<?php echo ASSETS_URL . '/' . $article['gambar']; ?>"
                                 class="card-img-top"
                                 alt="<?php echo htmlspecialchars($article['judul']); ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($article['judul']); ?></h5>
                            <div class="card-text flex-grow-1 article-description">
                                <?php
                                $content = strip_tags($article['konten']);
                                echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
                                ?>
                            </div>
                            <div class="mt-auto">
                                <small class="text-muted d-block">
                                    <i class="fas fa-calendar"></i> Dipublikasikan <?php echo formatDateIndo($article['tanggal_publish']); ?>
                                </small>
                                <small class="text-muted d-block">
                                    <i class="fas fa-user"></i> Oleh <?php echo $article['penulis']; ?>
                                </small>
                                <a href="artikel-detail.php?id=<?php echo $article['id']; ?>" class="btn btn-primary btn-sm mt-2">
                                    Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Belum ada artikel yang tersedia.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<style>
.article-description {
    display: block;
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    line-height: 1.5;
    margin-bottom: 1rem;
}
</style>

<?php require_once '../includes/footer.php'; ?>

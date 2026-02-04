<?php
$page_title = 'Detail Artikel - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    $_SESSION['error'] = "Artikel tidak ditemukan";
    redirect('artikel.php');
}

// Get article
$query = "SELECT * FROM artikel WHERE id = $id AND status = 'published'";
$result = mysqli_query($conn, $query);
$article = mysqli_fetch_assoc($result);

if (!$article) {
    $_SESSION['error'] = "Artikel tidak ditemukan";
    redirect('artikel.php');
}

// Update view count
$update_query = "UPDATE artikel SET views = views + 1 WHERE id = $id";
mysqli_query($conn, $update_query);

// Get related articles
$related_query = "SELECT * FROM artikel 
                  WHERE id != $id 
                  AND status = 'published' 
                  AND (kategori = '{$article['kategori']}' OR penulis = '{$article['penulis']}')
                  ORDER BY tanggal_publish DESC 
                  LIMIT 3";
$related_result = mysqli_query($conn, $related_query);
?>

<div class="container">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8 mb-3">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="artikel.php">Artikel</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($article['judul']); ?></li>
                </ol>
            </nav>

            <article>
                <header class="mb-4">
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($article['judul']); ?></h1>
                    
                    <div class="text-muted mb-4">
                        <i class="fas fa-calendar me-1"></i> <?php echo formatDateIndo($article['tanggal_publish']); ?>
                        <i class="fas fa-user ms-3 me-1"></i> <?php echo $article['penulis']; ?>
                        <i class="fas fa-eye ms-3 me-1"></i> <?php echo $article['views'] + 1; ?> views
                        <?php if (!empty($article['kategori'])): ?>
                            <span class="badge bg-primary ms-3"><?php echo $article['kategori']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($article['gambar'])): ?>
                        <img src="<?php echo ASSETS_URL . '/' . $article['gambar']; ?>"
                             class="img-fluid rounded mb-4"
                             alt="<?php echo htmlspecialchars($article['judul']); ?>">
                    <?php endif; ?>

                    <?php if (!empty($article['video'])): ?>
                        <video src="<?php echo ASSETS_URL . '/' . $article['video']; ?>"
                               class="img-fluid rounded mb-4"
                               controls
                               style="max-width: 100%; height: auto;">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>
                </header>

                <div class="article-content">
                    <?php echo nl2br($article['konten']); ?>
                </div>
                
                <footer class="mt-3 pt-3 border-top">
                    <div class="text-center">
                        <small class="text-muted">
                            Artikel ini diterbitkan oleh <?php echo $article['penulis']; ?>
                        </small>
                    </div>
                </footer>
            </article>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Related Articles -->
            <?php if (mysqli_num_rows($related_result) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i> Artikel Terkait</h5>
                    </div>
                    <div class="card-body">
                        <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <h6 class="mb-1">
                                    <a href="artikel-detail.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($related['judul']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> <?php echo formatDateIndo($related['tanggal_publish']); ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Categories -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i> Kategori</h5>
                </div>
                <div class="card-body">
                    <?php
                    $categories_query = "SELECT DISTINCT kategori FROM artikel WHERE status = 'published' AND kategori IS NOT NULL";
                    $categories_result = mysqli_query($conn, $categories_query);
                    
                    if (mysqli_num_rows($categories_result) > 0):
                        while ($cat = mysqli_fetch_assoc($categories_result)):
                    ?>
                        <a href="artikel.php?kategori=<?php echo urlencode($cat['kategori']); ?>" 
                           class="badge bg-light text-dark me-1 mb-1 text-decoration-none">
                            <?php echo $cat['kategori']; ?>
                        </a>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <p class="text-muted mb-0">Belum ada kategori</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Latest Articles -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i> Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php
                    $latest_query = "SELECT id, judul, tanggal_publish FROM artikel 
                                     WHERE status = 'published' 
                                     AND id != $id
                                     ORDER BY tanggal_publish DESC 
                                     LIMIT 5";
                    $latest_result = mysqli_query($conn, $latest_query);
                    
                    if (mysqli_num_rows($latest_result) > 0):
                        while ($latest = mysqli_fetch_assoc($latest_result)):
                    ?>
                        <div class="mb-2">
                            <a href="artikel-detail.php?id=<?php echo $latest['id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($latest['judul']); ?>
                            </a>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> <?php echo formatDateIndo($latest['tanggal_publish']); ?>
                            </small>
                        </div>
                        <hr class="my-2">
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <p class="text-muted mb-0">Belum ada artikel lain</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .article-content {
        font-size: 1.1rem;
        line-height: 1.8;
        text-align: justify;
    }
    
    .article-content h2 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }
    
    .article-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .article-content p {
        margin-bottom: 1.5rem;
    }
    
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
    }
    
    .article-content ul, .article-content ol {
        margin-bottom: 1.5rem;
        padding-left: 2rem;
    }
    
    .article-content blockquote {
        border-left: 4px solid var(--primary-color);
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #666;
    }
</style>

<?php require_once '../includes/footer.php'; ?>

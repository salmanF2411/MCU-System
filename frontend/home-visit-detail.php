<?php
$page_title = 'Detail Layanan Home Visit - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/header.php';

// Get service ID from URL
$id_setting = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_setting <= 0) {
    header('Location: home-visit.php');
    exit;
}

// Get service details
$query = "SELECT * FROM home_visit_setting WHERE id_setting = $id_setting AND status = 'aktif'";
$result = mysqli_query($conn, $query);
$service = mysqli_fetch_assoc($result);

if (!$service) {
    header('Location: home-visit.php');
    exit;
}
?>

<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
            <li class="breadcrumb-item"><a href="home-visit.php">Home Visit</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($service['judul_layanan']); ?></li>
        </ol>
    </nav>

    <!-- Service Detail -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-home me-2"></i><?php echo htmlspecialchars($service['judul_layanan']); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Service Image -->
                    <div class="text-center mb-4">
                        <?php if ($service['gambar']): ?>
                            <img src="<?php echo ASSETS_URL . '/' . $service['gambar']; ?>"
                                 class="img-fluid rounded shadow"
                                 alt="<?php echo htmlspecialchars($service['judul_layanan']); ?>"
                                 style="max-height: 400px; width: auto;">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto"
                                 style="height: 300px; width: 100%; max-width: 500px;">
                                <i class="fas fa-home fa-5x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Service Description -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Deskripsi Layanan</h5>
                        <div class="service-description">
                            <?php echo nl2br(htmlspecialchars($service['deskripsi'])); ?>
                        </div>
                    </div>

                    <!-- Service Price -->
                    <div class="row align-items-center mb-4">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <h6 class="mb-1">Harga Layanan</h6>
                                <p class="text-success fw-bold fs-4 mb-0">
                                    Rp <?php echo number_format($service['harga'], 0, ',', '.'); ?>
                                </p>
                            </div>
                        </div>
                        <!-- <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <h6 class="mb-1">Status</h6>
                                <span class="badge bg-success fs-6">Aktif</span>
                            </div>
                        </div> -->
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="button" class="btn btn-primary btn-lg select-service"
                                data-id="<?php echo $service['id_setting']; ?>"
                                data-title="<?php echo htmlspecialchars($service['judul_layanan']); ?>"
                                data-price="<?php echo $service['harga']; ?>">
                            <i class="fas fa-calendar-plus me-2"></i>Pilih Layanan Ini
                        </button>
                        <a href="home-visit.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Layanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Service selection - redirect to booking page with pre-selected service
document.querySelectorAll('.select-service').forEach(button => {
    button.addEventListener('click', function() {
        const serviceId = this.getAttribute('data-id');
        const serviceTitle = this.getAttribute('data-title');
        const servicePrice = this.getAttribute('data-price');

        // Store service data in sessionStorage for the booking page
        sessionStorage.setItem('selectedService', JSON.stringify({
            id: serviceId,
            title: serviceTitle,
            price: servicePrice
        }));

        // Redirect to booking page
        window.location.href = 'home-visit.php';
    });
});
</script>

<style>
.service-description {
    line-height: 1.6;
    font-size: 1.1rem;
}

.breadcrumb {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

.card {
    border: none;
    border-radius: 0.75rem;
}

.card-header {
    border-radius: 0.75rem 0.75rem 0 0 !important;
}
</style>

<?php require_once '../includes/footer.php'; ?>

<?php
$page_title = 'Home Visit - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/header.php';

// Get active home visit services
$query = "SELECT * FROM home_visit_setting WHERE status = 'aktif' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$services = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<div class="container">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="page-title">Layanan Home Visit</h1>
            <p class="lead">Pilih layanan kesehatan yang Anda butuhkan dan pesan kunjungan dokter ke rumah Anda</p>
        </div>
    </div>

    <!-- Services Gallery -->
    <?php if (count($services) > 0): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="mb-4">Layanan Tersedia</h3>
            <div class="services-gallery" style="overflow-x: auto; white-space: nowrap; padding-bottom: 20px;">
                <div class="d-flex" style="gap: 20px;">
                    <?php foreach ($services as $service): ?>
                    <div class="service-card" style="min-width: 300px; max-width: 300px; display: inline-block; vertical-align: top; cursor: pointer;">
                        <div class="card shadow-sm h-100">
                            <?php if ($service['gambar']): ?>
                            <img src="<?php echo ASSETS_URL . '/' . $service['gambar']; ?>"
                                 class="card-img-top" alt="<?php echo htmlspecialchars($service['judul_layanan']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <i class="fas fa-home fa-3x text-muted"></i>
                            </div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($service['judul_layanan']); ?></h5>
                                <p class="card-text" style="flex-grow: 1; word-wrap: break-word; overflow-wrap: break-word; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical;">
                                    <?php echo htmlspecialchars($service['deskripsi']); ?>
                                </p>
                                <div class="mt-auto">
                                    <p class="text-success fw-bold mb-2">
                                        Rp <?php echo number_format($service['harga'], 0, ',', '.'); ?>
                                    </p>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm view-detail"
                                                data-id="<?php echo $service['id_setting']; ?>">
                                            <i class="fas fa-eye me-1"></i>Lihat Detail
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm select-service"
                                                data-id="<?php echo $service['id_setting']; ?>"
                                                data-title="<?php echo htmlspecialchars($service['judul_layanan']); ?>"
                                                data-price="<?php echo $service['harga']; ?>">
                                            Pilih Layanan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row mb-5">
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>Tidak ada layanan tersedia saat ini</h4>
                <p>Silakan kembali lagi nanti atau hubungi kami untuk informasi lebih lanjut.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Booking Form -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-plus me-2"></i> Pesan Layanan Home Visit
                    </h5>
                </div>
                <div class="card-body">
                    <form id="bookingForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Pasien *</label>
                                    <input type="text" class="form-control" name="nama_pasien" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Layanan yang Dipilih *</label>
                                    <select class="form-select" name="id_setting" id="serviceSelect" required>
                                        <option value="">Pilih Layanan</option>
                                        <?php foreach ($services as $service): ?>
                                        <option value="<?php echo $service['id_setting']; ?>"
                                                data-price="<?php echo $service['harga']; ?>">
                                            <?php echo htmlspecialchars($service['judul_layanan']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keluhan *</label>
                            <textarea class="form-control" name="keluhan" rows="4" required
                                      placeholder="Jelaskan keluhan atau gejala yang Anda alami..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Kunjungan</label>
                                    <input type="date" class="form-control" name="tanggal_kunjungan"
                                           min="<?php echo date('Y-m-d'); ?>">
                                    <small class="text-muted">Kosongkan jika belum ada preferensi tanggal</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Biaya</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="totalPrice" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane me-2"></i> Kirim Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i> Pesanan Berhasil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h4>Terima Kasih!</h4>
                <p>Pesanan layanan home visit Anda telah berhasil dikirim. Tim kami akan segera menghubungi Anda untuk konfirmasi jadwal kunjungan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i> Terjadi Kesalahan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                <p id="errorMessage">Terjadi kesalahan saat memproses pesanan Anda. Silakan coba lagi.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// Check for pre-selected service from detail page
document.addEventListener('DOMContentLoaded', function() {
    const selectedService = sessionStorage.getItem('selectedService');
    if (selectedService) {
        const service = JSON.parse(selectedService);

        // Set select value
        document.getElementById('serviceSelect').value = service.id;

        // Update price display
        updatePrice();

        // Scroll to form
        document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth' });

        // Highlight selected service
        document.querySelectorAll('.service-card').forEach(card => {
            card.classList.remove('border-primary');
        });
        document.querySelector(`[data-id="${service.id}"]`).closest('.service-card').classList.add('border-primary');

        // Clear sessionStorage
        sessionStorage.removeItem('selectedService');
    }
});

// View detail button
document.querySelectorAll('.view-detail').forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent card click
        const serviceId = this.getAttribute('data-id');
        window.location.href = `home-visit-detail.php?id=${serviceId}`;
    });
});

// Service card click (goes to detail)
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', function() {
        const serviceId = this.querySelector('.view-detail').getAttribute('data-id');
        window.location.href = `home-visit-detail.php?id=${serviceId}`;
    });
});

// Service selection from gallery
document.querySelectorAll('.select-service').forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent card click

        const serviceId = this.getAttribute('data-id');
        const serviceTitle = this.getAttribute('data-title');
        const servicePrice = this.getAttribute('data-price');

        // Set select value
        document.getElementById('serviceSelect').value = serviceId;

        // Update price display
        updatePrice();

        // Scroll to form
        document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth' });

        // Highlight selected service
        document.querySelectorAll('.service-card').forEach(card => {
            card.classList.remove('border-primary');
        });
        this.closest('.service-card').classList.add('border-primary');
    });
});

// Update price when service is selected
document.getElementById('serviceSelect').addEventListener('change', updatePrice);

function updatePrice() {
    const select = document.getElementById('serviceSelect');
    const priceInput = document.getElementById('totalPrice');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption.value) {
        const price = selectedOption.getAttribute('data-price');
        priceInput.value = new Intl.NumberFormat('id-ID').format(price);
    } else {
        priceInput.value = '';
    }
}

// Form submission
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengirim...';

    // Send AJAX request
    fetch('process-home-visit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success modal
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();

            // Reset form
            this.reset();
            document.getElementById('totalPrice').value = '';

            // Remove highlight
            document.querySelectorAll('.service-card').forEach(card => {
                card.classList.remove('border-primary');
            });
        } else {
            // Show error modal
            document.getElementById('errorMessage').textContent = data.message;
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('errorMessage').textContent = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Gallery scroll hint
document.addEventListener('DOMContentLoaded', function() {
    const gallery = document.querySelector('.services-gallery');
    if (gallery && gallery.scrollWidth > gallery.clientWidth) {
        // Add scroll hint
        const hint = document.createElement('div');
        hint.className = 'text-center text-muted mt-2';
        hint.innerHTML = '<i class="fas fa-arrows-alt-h"></i> Geser untuk melihat layanan lainnya';
        gallery.parentNode.appendChild(hint);
    }
});
</script>

<style>
.services-gallery::-webkit-scrollbar {
    height: 8px;
}

.services-gallery::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.services-gallery::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 4px;
}

.services-gallery::-webkit-scrollbar-thumb:hover {
    background: #0b5ed7;
}

.service-card .card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.service-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.service-card.border-primary .card {
    border-color: var(--primary-color) !important;
    border-width: 2px;
}
</style>

<?php require_once '../includes/footer.php'; ?>

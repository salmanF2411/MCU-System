<?php
$page_title = 'Feedback - Sistem MCU Klinik';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/functions.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data feedback (anonymous)
    $rating = (int)$_POST['rating'];
    $kesan = escape($_POST['kesan']);
    $saran = escape($_POST['saran']);

    // Validasi rating
    if ($rating < 1 || $rating > 5) {
        $message = 'Rating harus antara 1-5 bintang.';
        $message_type = 'danger';
    } else {
        // Insert feedback anonymously
        $sql = "INSERT INTO feedback_pasien (rating, kesan, saran) VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iss', $rating, $kesan, $saran);

        if (mysqli_stmt_execute($stmt)) {
            $message = 'Terima kasih atas feedback Anda! Masukan Anda sangat berarti untuk meningkatkan pelayanan kami.';
            $message_type = 'success';
        } else {
            $message = 'Terjadi kesalahan saat mengirim feedback. Silakan coba lagi.';
            $message_type = 'danger';
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 mt-5">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h2 class="mb-0">
                        <i class="fas fa-comments me-2"></i> Berikan Kesan & Saran Anda
                    </h2>
                    <p class="mb-0 mt-2">Feedback Anda membantu kami meningkatkan pelayanan MCU</p>
                </div>
                <div class="card-body p-5">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mb-4">
                        <div class="alert alert-info border-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi:</strong> Feedback ini bersifat anonim. Anda tidak perlu memberikan identitas pribadi.
                        </div>
                    </div>

                    <form method="POST" action="" id="feedbackForm">
                        <!-- Rating Section -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-star text-warning me-2"></i> Bagaimana penilaian Anda terhadap pelayanan MCU kami?
                                <span class="text-danger">*</span>
                            </label>
                            <div class="rating-stars text-center">
                                <div class="stars" id="ratingStars">
                                    <i class="far fa-star" data-rating="1"></i>
                                    <i class="far fa-star" data-rating="2"></i>
                                    <i class="far fa-star" data-rating="3"></i>
                                    <i class="far fa-star" data-rating="4"></i>
                                    <i class="far fa-star" data-rating="5"></i>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" required>
                                <div class="rating-text mt-2" id="ratingText">Pilih rating (1-5 bintang)</div>
                            </div>
                        </div>

                        <!-- Kesan Section -->
                        <div class="mb-4">
                            <label for="kesan" class="form-label fw-bold">
                                <i class="fas fa-smile me-2 text-success"></i> Kesan Anda terhadap pelayanan kami
                            </label>
                            <textarea class="form-control" id="kesan" name="kesan" rows="4"
                                      placeholder="Ceritakan kesan Anda selama menggunakan layanan MCU di klinik kami..."></textarea>
                            <div class="form-text">Opsional - bagikan pengalaman positif Anda</div>
                        </div>

                        <!-- Saran Section -->
                        <div class="mb-4">
                            <label for="saran" class="form-label fw-bold">
                                <i class="fas fa-lightbulb me-2 text-primary"></i> Saran untuk perbaikan
                            </label>
                            <textarea class="form-control" id="saran" name="saran" rows="4"
                                      placeholder="Berikan saran Anda untuk meningkatkan pelayanan MCU kami..."></textarea>
                            <div class="form-text">Opsional - bantu kami menjadi lebih baik</div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2 gap-sm-3">
                            <button type="submit" class="btn btn-primary btn-lg px-4 px-sm-5 w-100 w-sm-auto" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i> Kirim Feedback
                            </button>
                            <button type="reset" class="btn btn-secondary btn-lg px-4 px-sm-4 w-100 w-sm-auto" id="resetBtn">
                                <i class="fas fa-redo me-2"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="card mt-4 border-0 bg-light">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-3">Mengapa Feedback Anda Penting?</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                            <h6>Meningkatkan Kualitas</h6>
                            <p class="small text-muted">Feedback Anda membantu kami meningkatkan standar pelayanan</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-users fa-2x text-success mb-2"></i>
                            <h6>Untuk Semua Pasien</h6>
                            <p class="small text-muted">Masukan Anda bermanfaat untuk semua pengguna layanan MCU</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-shield-alt fa-2x text-info mb-2"></i>
                            <h6>Anonim & Aman</h6>
                            <p class="small text-muted">Privasi Anda terjaga, feedback dikirim secara anonim</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-stars {
    margin: 20px 0;
}

.stars {
    font-size: 2rem;
    cursor: pointer;
}

.stars i {
    margin: 0 5px;
    transition: color 0.2s;
}

.stars i:hover,
.stars i.active {
    color: #ffc107;
}

.rating-text {
    color: #6c757d;
    font-weight: 500;
}

.card {
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.btn {
    border-radius: 25px;
}

.form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>

<script>
// Rating functionality
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('#ratingStars i');
    const ratingInput = document.getElementById('ratingInput');
    const ratingText = document.getElementById('ratingText');
    const submitBtn = document.getElementById('submitBtn');

    const ratingTexts = {
        1: 'Sangat Buruk',
        2: 'Buruk',
        3: 'Cukup',
        4: 'Baik',
        5: 'Sangat Baik'
    };

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            ratingText.textContent = ratingTexts[rating];
            ratingText.style.color = '#28a745';

            // Update star display
            stars.forEach(s => {
                if (parseInt(s.dataset.rating) <= rating) {
                    s.classList.remove('far');
                    s.classList.add('fas', 'active');
                } else {
                    s.classList.remove('fas', 'active');
                    s.classList.add('far');
                }
            });
        });

        star.addEventListener('mouseover', function() {
            const rating = parseInt(this.dataset.rating);
            stars.forEach(s => {
                if (parseInt(s.dataset.rating) <= rating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#6c757d';
                }
            });
        });

        star.addEventListener('mouseout', function() {
            stars.forEach(s => {
                s.style.color = '';
            });
        });
    });

    // Form validation
    document.getElementById('feedbackForm').addEventListener('submit', function(e) {
        if (!ratingInput.value) {
            e.preventDefault();
            alert('Silakan pilih rating terlebih dahulu!');
            return false;
        }

        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengirim...';
    });

    // Reset functionality
    document.getElementById('resetBtn').addEventListener('click', function() {
        // Reset stars
        stars.forEach(star => {
            star.classList.remove('fas', 'active');
            star.classList.add('far');
        });

        // Reset rating
        ratingInput.value = '';
        ratingText.textContent = 'Pilih rating (1-5 bintang)';
        ratingText.style.color = '#6c757d';

        // Reset form
        document.getElementById('feedbackForm').reset();
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

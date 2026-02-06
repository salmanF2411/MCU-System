<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

if (!isset($_GET['id'])) {
    die('ID tidak ditemukan');
}

$id = (int)$_GET['id'];

$query = "SELECT hv.*, hvs.judul_layanan, hvs.deskripsi as deskripsi_layanan, hvs.harga as harga_setting
          FROM home_visit hv
          LEFT JOIN home_visit_setting hvs ON hv.id_setting = hvs.id_setting
          WHERE hv.id_visit = $id";

$result = mysqli_query($conn, $query);
$registration = mysqli_fetch_assoc($result);

if (!$registration) {
    die('Data tidak ditemukan');
}
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="fw-bold">Informasi Pasien</h6>
        <table class="table table-sm">
            <tr>
                <td width="120"><strong>Nama Pasien:</strong></td>
                <td><?php echo htmlspecialchars($registration['nama_pasien']); ?></td>
            </tr>
            <tr>
                <td><strong>Keluhan:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($registration['keluhan'])); ?></td>
            </tr>
            <tr>
                <td><strong>Alamat:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($registration['alamat_visit'])); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal Kunjungan:</strong></td>
                <td><?php echo $registration['tanggal_kunjungan'] ? formatDateIndo($registration['tanggal_kunjungan']) : 'Tidak ditentukan'; ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="fw-bold">Informasi Layanan</h6>
        <table class="table table-sm">
            <tr>
                <td width="120"><strong>Layanan:</strong></td>
                <td><?php echo htmlspecialchars($registration['judul_layanan']); ?></td>
            </tr>
            <tr>
                <td><strong>Deskripsi:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($registration['deskripsi_layanan'])); ?></td>
            </tr>
            <tr>
                <td><strong>Harga:</strong></td>
                <td>Rp <?php echo number_format($registration['harga'], 0, ',', '.'); ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h6 class="fw-bold">Status & Timeline</h6>
        <table class="table table-sm">
            <tr>
                <td width="120"><strong>Status:</strong></td>
                <td>
                    <?php
                    $status_class = '';
                    switch ($registration['status']) {
                        case 'pending': $status_class = 'text-warning'; break;
                        case 'diproses': $status_class = 'text-info'; break;
                        case 'selesai': $status_class = 'text-success'; break;
                        case 'batal': $status_class = 'text-danger'; break;
                    }
                    ?>
                    <span class="fw-bold <?php echo $status_class; ?>">
                        <?php echo ucfirst($registration['status']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Tanggal Daftar:</strong></td>
                <td><?php echo formatDateIndo($registration['created_at'], true); ?></td>
            </tr>
        </table>
    </div>
</div>

<?php
require_once '../config/database.php';

// Set header for JSON response
header('Content-Type: application/json');

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and sanitize input
    $nama_pasien = escape($_POST['nama_pasien'] ?? '');
    $keluhan = escape($_POST['keluhan'] ?? '');
    $alamat_visit = escape($_POST['alamat_visit'] ?? '');
    $id_setting = (int)($_POST['id_setting'] ?? 0);
    $tanggal_kunjungan = escape($_POST['tanggal_kunjungan'] ?? '');

    // Validate required fields
    if (empty($nama_pasien)) {
        throw new Exception('Nama pasien wajib diisi');
    }

    if (empty($keluhan)) {
        throw new Exception('Keluhan wajib diisi');
    }

    if (empty($alamat_visit)) {
        throw new Exception('Alamat wajib diisi');
    }

    if ($id_setting <= 0) {
        throw new Exception('Layanan wajib dipilih');
    }

    // Get service details
    $query = "SELECT harga FROM home_visit_setting WHERE id_setting = $id_setting AND status = 'aktif'";
    $result = mysqli_query($conn, $query);
    $service = mysqli_fetch_assoc($result);

    if (!$service) {
        throw new Exception('Layanan tidak ditemukan atau tidak aktif');
    }

    $harga = $service['harga'];

    // Insert booking
    $query = "INSERT INTO home_visit (nama_pasien, keluhan, alamat_visit, id_setting, harga, tanggal_kunjungan)
              VALUES ('$nama_pasien', '$keluhan', '$alamat_visit', $id_setting, $harga, " .
              ($tanggal_kunjungan ? "'$tanggal_kunjungan'" : "NULL") . ")";

    if (!mysqli_query($conn, $query)) {
        throw new Exception('Gagal menyimpan pesanan: ' . mysqli_error($conn));
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil dikirim'
    ]);

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

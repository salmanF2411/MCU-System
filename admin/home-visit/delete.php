<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Get service ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id == 0) {
    $_SESSION['error'] = "Layanan tidak ditemukan";
    redirect('list.php');
}

// Get service data to delete image
$query = "SELECT gambar FROM home_visit_setting WHERE id_setting = $id";
$result = mysqli_query($conn, $query);
$service = mysqli_fetch_assoc($result);

if (!$service) {
    $_SESSION['error'] = "Layanan tidak ditemukan";
    redirect('list.php');
}

// Delete service
$query = "DELETE FROM home_visit_setting WHERE id_setting = $id";

if (mysqli_query($conn, $query)) {
    // Delete image file if exists
    if ($service['gambar'] && file_exists('../../assets/' . $service['gambar'])) {
        unlink('../../assets/' . $service['gambar']);
    }

    $_SESSION['success'] = "Layanan home visit berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus layanan: " . mysqli_error($conn);
}

redirect('list.php');
?>

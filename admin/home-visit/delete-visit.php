<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Get visit ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id == 0) {
    $_SESSION['error'] = "Pendaftaran home visit tidak ditemukan";
    redirect('process.php');
}

// Get visit data
$query = "SELECT * FROM home_visit WHERE id_visit = $id";
$result = mysqli_query($conn, $query);
$visit = mysqli_fetch_assoc($result);

if (!$visit) {
    $_SESSION['error'] = "Pendaftaran home visit tidak ditemukan";
    redirect('process.php');
}

// Check if status allows deletion (selesai or batal)
if (!in_array($visit['status'], ['selesai', 'batal'])) {
    $_SESSION['error'] = "Hanya pendaftaran yang sudah selesai atau dibatalkan yang dapat dihapus";
    redirect('process.php');
}

// Delete visit
$query = "DELETE FROM home_visit WHERE id_visit = $id";

if (mysqli_query($conn, $query)) {
    $_SESSION['success'] = "Pendaftaran home visit berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus pendaftaran: " . mysqli_error($conn);
}

redirect('process.php');
?>

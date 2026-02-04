<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($id <= 0) {
        throw new Exception('ID tidak valid');
    }

    $valid_statuses = ['pending', 'diproses', 'selesai', 'batal'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Status tidak valid');
    }

    // Check if registration exists
    $query = "SELECT id_visit FROM home_visit WHERE id_visit = $id";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 0) {
        throw new Exception('Pendaftaran tidak ditemukan');
    }

    // Update status
    $query = "UPDATE home_visit SET status = '$status' WHERE id_visit = $id";
    if (!mysqli_query($conn, $query)) {
        throw new Exception('Gagal mengupdate status: ' . mysqli_error($conn));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Status berhasil diupdate'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

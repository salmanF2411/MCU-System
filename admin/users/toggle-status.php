<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id == 0 || !in_array($action, ['activate', 'deactivate'])) {
    $_SESSION['error'] = "Permintaan tidak valid";
    redirect('list.php');
}

// Check if trying to modify self
if ($id == $_SESSION['admin_id']) {
    $_SESSION['error'] = "Tidak dapat mengubah status akun sendiri";
    redirect('list.php');
}

// Toggle status
$new_status = $action == 'activate' ? 1 : 0;
$query = "UPDATE admin_users SET is_active = $new_status WHERE id = $id";

if (mysqli_query($conn, $query)) {
    $status_text = $action == 'activate' ? 'diaktifkan' : 'dinonaktifkan';
    $_SESSION['success'] = "User berhasil $status_text!";
} else {
    $_SESSION['error'] = "Gagal mengubah status user: " . mysqli_error($conn);
}

redirect('list.php');
?>
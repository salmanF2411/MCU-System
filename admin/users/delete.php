<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id > 0) {
        // Check if trying to delete self
        if ($id == $_SESSION['admin_id']) {
            $_SESSION['error'] = "Tidak dapat menghapus akun sendiri";
            redirect('list.php');
        }
        
        // Delete user
        $query = "DELETE FROM admin_users WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "User berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus user: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "ID user tidak valid";
    }
}

redirect('list.php');
?>
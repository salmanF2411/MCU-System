<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id > 0) {
        // Get article data to delete image and video
        $query = "SELECT gambar, video FROM artikel WHERE id = $id";
        $result = mysqli_query($conn, $query);
        $article = mysqli_fetch_assoc($result);

        // Delete image file if exists
        if ($article && $article['gambar'] && file_exists('../../assets/' . $article['gambar'])) {
            unlink('../../assets/' . $article['gambar']);
        }

        // Delete video file if exists
        if ($article && $article['video'] && file_exists('../../assets/' . $article['video'])) {
            unlink('../../assets/' . $article['video']);
        }
        
        // Delete article
        $delete_query = "DELETE FROM artikel WHERE id = $id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['success'] = "Artikel berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus artikel: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "ID artikel tidak valid";
    }
}

redirect('list.php');
?>
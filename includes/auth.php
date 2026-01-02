<?php
// Include database
require_once __DIR__ . '/../config/database.php';

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Login function
 */
function login($username, $password) {
    global $conn;
    
    $username = escape($username);
    $password = md5($password); // Using MD5 for simplicity, consider password_hash() for production
    
    $query = "SELECT * FROM admin_users 
              WHERE username = '$username' 
              AND password = '$password' 
              AND is_active = 1";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['foto'] = $user['foto'];
        
        // Update last login
        $update_query = "UPDATE admin_users SET last_login = NOW() WHERE id = {$user['id']}";
        mysqli_query($conn, $update_query);
        
        return true;
    }
    
    return false;
}

/**
 * Logout function
 */
function logout() {
    session_destroy();
    redirect('../admin/index.php');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Require login middleware
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Silakan login terlebih dahulu";
        redirect('index.php');
    }
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (!isset($_SESSION['role'])) return false;
    if ($_SESSION['role'] == 'super_admin') return true;
    return $_SESSION['role'] == $role;
}

/**
 * Require role middleware
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        $_SESSION['error'] = "Akses ditolak. Anda tidak memiliki izin.";
        redirect('dashboard.php');
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    
    $user_id = $_SESSION['admin_id'];
    $query = "SELECT * FROM admin_users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}
?>
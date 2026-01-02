<?php
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mcu_system');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL
define('BASE_URL', 'http://localhost/mcu-system/frontend');
define('ADMIN_URL', 'http://localhost/mcu-system/admin');
define('ASSETS_URL', 'http://localhost/mcu-system/assets');

// Functions
function escape($data) {
    global $conn;
    return mysqli_real_escape_string($conn, $data);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}


?>
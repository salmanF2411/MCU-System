<?php
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Railway database config
define('DB_HOST', 'yamabiko.proxy.rlwy.net');
define('DB_USER', 'root');
define('DB_PASS', 'XDcdnhBgaRSwNBwcbIHSTrzPWubBIPMy');
define('DB_NAME', 'railway');
define('DB_PORT', '54196');

// Create connection
$conn = mysqli_connect(
    DB_HOST,
    DB_USER,
    DB_PASS,
    DB_NAME,
    DB_PORT
);

// Check connection
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Charset
mysqli_set_charset($conn, "utf8");

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL (ganti dengan domain Railway)
define('BASE_URL', 'https://mcu-system-production.up.railway.app/frontend');
define('ADMIN_URL', 'https://mcu-system-production.up.railway.app/admin');
define('ASSETS_URL', 'https://mcu-system-production.up.railway.app/assets');

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

<?php
// Include database connection
require_once __DIR__ . '/../config/database.php';

/**
 * Generate kode MCU
 */
function generateKodeMCU() {
    $prefix = 'MCU';
    $date = date('Ymd');
    $random = rand(1000, 9999);
    return $prefix . '-' . $date . '-' . $random;
}

/**
 * Get age from birth date
 */
function calculateAge($birth_date) {
    $birth = new DateTime($birth_date);
    $today = new DateTime();
    return $today->diff($birth)->y;
}

/**
 * Format date Indonesian
 */
function formatDateIndo($date, $with_time = false) {
    if (empty($date)) return '-';
    
    $months = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    $formatted = $day . ' ' . $month . ' ' . $year;
    
    if ($with_time) {
        $time = date('H:i', $timestamp);
        $formatted .= ' ' . $time;
    }
    
    return $formatted;
}

/**
 * Get setting value
 */
function getSetting($key) {
    global $conn;
    $query = "SELECT $key FROM pengaturan LIMIT 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row[$key] ?? '';
}

/**
 * Upload file
 */
function uploadFile($file, $folder = 'uploads/') {
    $target_dir = __DIR__ . '/../assets/' . $folder;
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $filename = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $filename;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check file size (max 5MB)
    if ($file['size'] > 5000000) {
        return ['error' => 'File terlalu besar. Maksimal 5MB'];
    }
    
    // Allow certain file formats
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($file_type, $allowed_types)) {
        return ['error' => 'Format file tidak diizinkan'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => $folder . $filename];
    } else {
        return ['error' => 'Gagal mengupload file'];
    }
}

/**
 * Get patient status badge
 */
function getStatusBadge($status) {
    switch($status) {
        case 'menunggu':
            return '<span class="badge bg-warning">Menunggu</span>';
        case 'proses':
            return '<span class="badge bg-info">Proses</span>';
        case 'selesai':
            return '<span class="badge bg-success">Selesai</span>';
        default:
            return '<span class="badge bg-secondary">' . $status . '</span>';
    }
}

/**
 * Get MCU status badge
 */
function getMCUStatusBadge($status) {
    switch($status) {
        case 'FIT':
            return '<span class="badge bg-success">FIT TO WORK</span>';
        case 'UNFIT':
            return '<span class="badge bg-danger">UNFIT</span>';
        case 'FIT WITH NOTE':
            return '<span class="badge bg-warning">FIT WITH NOTE</span>';
        default:
            return '<span class="badge bg-secondary">' . $status . '</span>';
    }
}

/**
 * Check pemeriksaan status
 */
function getPemeriksaanStatus($pasien_id, $role) {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM pemeriksaan 
              WHERE pasien_id = $pasien_id AND pemeriksa_role = '$role'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] > 0;
}

/**
 * Get pemeriksaan status badges
 */
function getPemeriksaanBadges($pasien_id) {
    $pendaftaran = getPemeriksaanStatus($pasien_id, 'pendaftaran') ? 
        '<span class="badge bg-success">✓</span>' : 
        '<span class="badge bg-secondary">○</span>';
    
    $mata = getPemeriksaanStatus($pasien_id, 'dokter_mata') ? 
        '<span class="badge bg-success">✓</span>' : 
        '<span class="badge bg-secondary">○</span>';
    
    $umum = getPemeriksaanStatus($pasien_id, 'dokter_umum') ? 
        '<span class="badge bg-success">✓</span>' : 
        '<span class="badge bg-secondary">○</span>';
    
    return $pendaftaran . ' ' . $mata . ' ' . $umum;
}

/**
 * Get latest pemeriksaan data
 */
function getPemeriksaanData($pasien_id, $role) {
    global $conn;
    $query = "SELECT * FROM pemeriksaan
              WHERE pasien_id = $pasien_id AND pemeriksa_role = '$role'
              ORDER BY tanggal_periksa DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Check if examination value is abnormal
 */
function isAbnormal($parameter, $value) {
    if (empty($value) || $value === '-') {
        return false;
    }

    switch($parameter) {
        case 'suhu':
            // Normal temperature: 36.5 - 37.5 °C
            $temp = floatval($value);
            return $temp > 37.5;
            // return $temp < 36.5 || $temp > 37.5;

        case 'tekanan_darah':
            // Normal blood pressure: systolic 90-140, diastolic 60-90
            if (preg_match('/(\d+)\/(\d+)/', $value, $matches)) {
                $systolic = intval($matches[1]);
                $diastolic = intval($matches[2]);
                return $systolic > 125 || $systolic < 100 || $diastolic > 80 || $diastolic < 70;
                // return $systolic < 90 || $systolic > 140 || $diastolic < 60 || $diastolic > 90;
            }
            return false;

        case 'nadi':
            // Normal pulse: 60-100 bpm
            $pulse = intval($value);
            return $pulse > 100 || $pulse < 60;
            // return $pulse < 60 || $pulse > 100;

        case 'respirasi':
            // Normal respiration: 12-20 breaths/min
            $resp = intval($value);
            return $resp > 25 || $resp < 12;

        case 'visus':
            // Normal vision: 6/6 
            if (preg_match('/(\d+)\/(\d+)/', $value, $matches)) {
                $numerator = intval($matches[1]);
                $denominator = intval($matches[2]);
                // Consider abnormal if worse than 6/6
                return $denominator > 6;
            }
            return false;

        default:
            return false;
    }
}

/**
 * Get CSS class for examination value display
 */
function getValueClass($parameter, $value) {
    return isAbnormal($parameter, $value) ? 'text-danger fw-bold' : '';
}

/**
 * Get CSS class for status display (abnormal = red)
 */
function getStatusClass($status) {
    return (strtolower($status) == 'abnormal') ? 'text-danger fw-bold' : '';
}

/**
 * Get CSS class for description display (if has content = red)
 */
function getDescriptionClass($value) {
    return (!empty($value) && $value !== '-') ? 'text-danger fw-bold' : '';
}
?>
<?php
// Migration script to add feedback table
require_once 'config/database.php';

echo "Starting database migration...\n";

// Create feedback table
$sql = "CREATE TABLE IF NOT EXISTS feedback_pasien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id INT,
    kode_mcu VARCHAR(20),
    nama_pasien VARCHAR(100),
    email VARCHAR(100),
    rating INT CHECK (rating >= 1 AND rating <= 5),
    kesan TEXT,
    saran TEXT,
    tanggal_submit DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unread', 'read') DEFAULT 'unread',
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "✓ Feedback table created successfully!\n";
} else {
    echo "✗ Error creating feedback table: " . mysqli_error($conn) . "\n";
}

echo "Migration completed!\n";
?>

USE mcu_system;
-- Add missing columns to pemeriksaan table (only if they don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'ikterik_keterangan') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN ikterik_keterangan TEXT AFTER anemia;',
    'SELECT "ikterik_keterangan already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'buta_warna_keterangan') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN buta_warna_keterangan TEXT AFTER buta_warna;',
    'SELECT "buta_warna_keterangan already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'lapang_pandang_keterangan') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN lapang_pandang_keterangan TEXT AFTER lapang_pandang;',
    'SELECT "lapang_pandang_keterangan already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'gigi_status') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN gigi_status VARCHAR(20) AFTER tenggorokan_keterangan;',
    'SELECT "gigi_status already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'auskultasi_keterangan') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN auskultasi_keterangan TEXT AFTER paru_auskultasi;',
    'SELECT "auskultasi_keterangan already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'jantung_keterangan') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN jantung_keterangan TEXT AFTER jantung_auskultasi;',
    'SELECT "jantung_keterangan already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'striae') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN striae TEXT AFTER hepar;',
    'SELECT "striae already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'sikatriks') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN sikatriks TEXT AFTER striae;',
    'SELECT "sikatriks already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mcu_system' AND TABLE_NAME = 'pemeriksaan' AND COLUMN_NAME = 'psoas_sign') = 0,
    'ALTER TABLE pemeriksaan ADD COLUMN psoas_sign TEXT AFTER sikatriks;',
    'SELECT "psoas_sign already exists";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

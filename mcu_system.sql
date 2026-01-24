-- Database: mcu_system
CREATE DATABASE IF NOT EXISTS mcu_system;
USE mcu_system;

-- Tabel Pasien
CREATE TABLE pasien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_mcu VARCHAR(20) UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('L', 'P'),
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    usia INT,
    alamat TEXT,
    pendidikan VARCHAR(50),
    agama VARCHAR(20),
    golongan_darah VARCHAR(3),
    no_telp VARCHAR(20),
    email VARCHAR(100),
    perusahaan VARCHAR(100),
    posisi_pekerjaan VARCHAR(100),
    tanggal_mcu DATE,
    status_pendaftaran ENUM('menunggu', 'proses', 'selesai') DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Data Keluarga
CREATE TABLE keluarga_pasien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id INT,
    hubungan ENUM('Ayah', 'Ibu'),
    nama VARCHAR(100),
    usia INT,
    kondisi VARCHAR(50),
    meninggal_tahun INT,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE
);

-- Tabel Riwayat Kesehatan
CREATE TABLE riwayat_kesehatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id INT,
    kategori VARCHAR(50),
    nilai TEXT,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE
);

-- Tabel Kebiasaan
CREATE TABLE kebiasaan_pasien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id INT,
    jenis VARCHAR(50),
    keterangan TEXT,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE
);

-- Tabel Pemeriksaan
CREATE TABLE pemeriksaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id INT,
    pemeriksa_role ENUM('pendaftaran', 'dokter_mata', 'dokter_umum'),
    
    -- Sirkulasi (Pendaftaran)
    tekanan_darah VARCHAR(20),
    nadi INT,
    suhu DECIMAL(4,2),
    respirasi INT,
    tinggi_badan INT,
    berat_badan INT,
    
    -- Mata (Dokter Mata)
    visus_kanan_jauh VARCHAR(10),
    visus_kanan_dekat VARCHAR(10),
    visus_kiri_jauh VARCHAR(10),
    visus_kiri_dekat VARCHAR(10),
    anemia VARCHAR(10),
    buta_warna VARCHAR(20),
    lapang_pandang VARCHAR(20),
    
    -- THT & Gigi (Dokter Umum)
    telinga_status VARCHAR(20),
    telinga_keterangan TEXT,
    hidung_status VARCHAR(20),
    hidung_keterangan TEXT,
    tenggorokan_status VARCHAR(20),
    tenggorokan_keterangan TEXT,
    gigi_keterangan TEXT,
    leher_kgb TEXT,
    
    -- Thorax
    paru_auskultasi TEXT,
    paru_palpasi TEXT,
    paru_perkusi TEXT,
    
    -- Abdominal
    operasi BOOLEAN DEFAULT 0,
    keterangan_operasi TEXT,
    obesitas BOOLEAN DEFAULT 0,
    organomegali BOOLEAN DEFAULT 0,
    hernia BOOLEAN DEFAULT 0,
    nyeri_epigastrium BOOLEAN DEFAULT 0,
    nyeri_abdomen BOOLEAN DEFAULT 0,
    bising_usus BOOLEAN DEFAULT 0,
    hepar BOOLEAN DEFAULT 0,
    hepatomegali TEXT,
    
    -- Refleks
    biceps VARCHAR(10),
    triceps VARCHAR(10),
    patella VARCHAR(10),
    achilles VARCHAR(10),
    plantar_response VARCHAR(10),
    
    -- Kesimpulan
    kesimpulan TEXT,
    saran TEXT,
    status_mcu ENUM('FIT', 'UNFIT', 'FIT WITH NOTE') DEFAULT 'FIT',
    dokter_pemeriksa VARCHAR(100),
    
    pemeriksa_id INT,
    tanggal_periksa DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE
);

-- Tabel Artikel
CREATE TABLE artikel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE,
    konten LONGTEXT,
    gambar VARCHAR(255),
    kategori VARCHAR(50),
    penulis VARCHAR(100),
    tanggal_publish DATE,
    views INT DEFAULT 0,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Admin
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('pendaftaran', 'dokter_mata', 'dokter_umum', 'super_admin') DEFAULT 'pendaftaran',
    foto VARCHAR(255),
    last_login DATETIME,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin default
INSERT INTO admin_users (username, password, nama_lengkap, role) VALUES 
('admin', MD5('admin123'), 'Administrator Utama', 'super_admin'),
('pendaftaran', MD5('pendaftaran123'), 'Staff Pendaftaran', 'pendaftaran'),
('dokter_mata', MD5('doktermata123'), 'Dr. Ahmad Fauzi, Sp.M', 'dokter_mata'),
('dokter_umum', MD5('dokterumum123'), 'Dr. Siti Rahmah, Sp.PD', 'dokter_umum');

-- Tabel Pengaturan
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_klinik VARCHAR(100),
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    whatsapp VARCHAR(20),
    logo VARCHAR(255),
    tentang TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO pengaturan (nama_klinik, alamat, telepon, email, whatsapp) VALUES
('Klinik Sehat Mandiri', 'Jl. Kesehatan No. 123, Jakarta Pusat', '(021) 1234567', 'info@kliniksehat.com', '081234567890');


ALTER TABLE pengaturan ADD COLUMN hero_image VARCHAR(255) DEFAULT NULL;

-- Add missing columns to pemeriksaan table
ALTER TABLE pemeriksaan ADD COLUMN ikterik_keterangan TEXT AFTER anemia;
ALTER TABLE pemeriksaan ADD COLUMN buta_warna_keterangan TEXT AFTER buta_warna;
ALTER TABLE pemeriksaan ADD COLUMN lapang_pandang_keterangan TEXT AFTER lapang_pandang;
ALTER TABLE pemeriksaan ADD COLUMN gigi_status VARCHAR(20) AFTER tenggorokan_keterangan;
ALTER TABLE pemeriksaan ADD COLUMN auskultasi_keterangan TEXT AFTER paru_auskultasi;
ALTER TABLE pemeriksaan ADD COLUMN jantung_keterangan TEXT AFTER jantung_auskultasi;
ALTER TABLE pemeriksaan ADD COLUMN striae TEXT AFTER hepar;
ALTER TABLE pemeriksaan ADD COLUMN sikatriks TEXT AFTER striae;
ALTER TABLE pemeriksaan ADD COLUMN psoas_sign TEXT AFTER sikatriks;

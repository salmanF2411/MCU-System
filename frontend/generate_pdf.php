<?php
require_once __DIR__ . '/../libs/fpdf/fpdf.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// --- Ambil Data ---
$kode_mcu = isset($_GET['kode']) ? $_GET['kode'] : 'MCU-PENDING';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Fallback jika fungsi getSetting belum ada (untuk mencegah error)
if (!function_exists('getSetting')) {
    function getSetting($key) {
        $settings = [
            'alamat' => 'Jl. Kesehatan No. 123, Jakarta Pusat 12125',
            'telepon' => '(021) 12345671122',
            'whatsapp' => '08123456789022'
        ];
        return isset($settings[$key]) ? $settings[$key] : '-';
    }
}
// Fallback formatDateIndo
if (!function_exists('formatDateIndo')) {
    function formatDateIndo($date) { return date('d F Y', strtotime($date)); }
}

class PDF extends FPDF {
    // Fungsi bantuan untuk menggambar lingkaran dengan warna solid
    function Circle($x, $y, $r, $style='D') {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

    function Ellipse($x, $y, $rx, $ry, $style='D') {
        if($style=='F') $op='f'; elseif($style=='FD' || $style=='DF') $op='B'; else $op='S';
        $lx=4/3*(M_SQRT2-1)*$rx;
        $ly=4/3*(M_SQRT2-1)*$ry;
        $k=$this->k;
        $h=$this->h;
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$rx)*$k,($h-$y)*$k,
            ($x+$rx)*$k,($h-($y-$ly))*$k,
            ($x+$lx)*$k,($h-($y-$ry))*$k,
            $x*$k,($h-($y-$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$lx)*$k,($h-($y-$ry))*$k,
            ($x-$rx)*$k,($h-($y-$ly))*$k,
            ($x-$rx)*$k,($h-$y)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$rx)*$k,($h-($y+$ly))*$k,
            ($x-$lx)*$k,($h-($y+$ry))*$k,
            $x*$k,($h-($y+$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x+$lx)*$k,($h-($y+$ry))*$k,
            ($x+$rx)*$k,($h-($y+$ly))*$k,
            ($x+$rx)*$k,($h-$y)*$k,
            $op));
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(128, 128, 128); // Abu-abu
        $this->Cell(0, 10, 'Jika ada pertanyaan, hubungi kami di ' . getSetting('telepon'), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// --- Warna ---
$col_green = [25, 135, 84];   // Bootstrap Success Green
$col_blue  = [13, 110, 253];  // Bootstrap Primary Blue
$col_gray_bg = [248, 249, 250]; // Light Gray Background
$col_dark  = [33, 37, 41];    // Almost Black

// 1. FRAME LUAR (Border Halaman/Card)
$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Rect(10, 10, 190, 200); // Bingkai utama

// 2. HEADER HIJAU
$pdf->SetFillColor($col_green[0], $col_green[1], $col_green[2]);
$pdf->Rect(10, 10, 190, 15, 'F');

// Teks Header "Pendaftaran Berhasil"
$pdf->SetXY(10, 10);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(255, 255, 255);
// Gunakan ZapfDingbats untuk ikon centang kecil di header jika mau, atau teks biasa
$pdf->Cell(190, 15, utf8_decode('Pendaftaran Berhasil!'), 0, 1, 'C');

// 3. IKON CENTANG BESAR (Lingkaran Hijau)
$pdf->Ln(10);
$iconX = 105; // Tengah halaman (210/2)
$iconY = 45;
$radius = 12;

// Gambar Lingkaran Hijau
$pdf->SetFillColor($col_green[0], $col_green[1], $col_green[2]);
$pdf->Circle($iconX, $iconY, $radius, 'F');

// Gambar Centang Putih di dalam lingkaran (Manual Drawing lines)
$pdf->SetDrawColor(255, 255, 255);
$pdf->SetLineWidth(2.5);
// Titik koordinat centang (relatif terhadap pusat lingkaran)
$pdf->Line($iconX - 5, $iconY, $iconX - 1, $iconY + 4); // Garis turun
$pdf->Line($iconX - 1, $iconY + 4, $iconX + 6, $iconY - 5); // Garis naik

// 4. JUDUL UTAMA
$pdf->SetY($iconY + 15);
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Terima kasih telah mendaftar MCU'), 0, 1, 'C');

// 5. KODE MCU
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Kode MCU Anda:', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 32); // Font Besar
$pdf->SetTextColor($col_blue[0], $col_blue[1], $col_blue[2]); // Warna Biru
$pdf->Cell(0, 18, $kode_mcu, 0, 1, 'C');

// Instruksi kecil
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 8, utf8_decode('Harap Tunjukan Kode Ini Pada Saat Akan Melakukan Pemeriksaan.'), 0, 1, 'C');

// 6. KOTAK INFORMASI PENTING
$boxX = 20;
$boxY = $pdf->GetY() + 10;
$boxWidth = 170;
$boxHeightHeader = 10;

// Header Kotak (Abu-abu)
$pdf->SetFillColor($col_gray_bg[0], $col_gray_bg[1], $col_gray_bg[2]);
$pdf->Rect($boxX, $boxY, $boxWidth, $boxHeightHeader, 'F');

// Border Header
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.2);
$pdf->Rect($boxX, $boxY, $boxWidth, $boxHeightHeader);

// Teks Header "Informasi Penting"
$pdf->SetXY($boxX, $boxY);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell($boxWidth, $boxHeightHeader, 'Informasi Penting', 0, 0, 'C');

// Isi Kotak
$contentY = $boxY + $boxHeightHeader;
$contentHeight = 45; // Tinggi area konten
// Border Luar Konten
$pdf->Rect($boxX, $contentY, $boxWidth, $contentHeight);

// List Data
$pdf->SetXY($boxX + 5, $contentY + 5);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(50, 50, 50);

$lineHeight = 9;
$iconSpace = 8; // Spasi untuk simulasi ikon

// Fungsi helper baris
function printInfoRow($pdf, $label, $value, $x, $w) {
    global $lineHeight, $iconSpace;
    $pdf->SetX($x);
    // Jika Anda punya file gambar ikon: $pdf->Image('icon.png', $x, $pdf->GetY()+1, 4);
    // Di sini kita gunakan indentasi saja
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($w, $lineHeight, utf8_decode($label), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, $lineHeight, utf8_decode(': ' . $value), 0, 1, 'L');
}

// Layout kolom manual agar rapi seperti tabel
$labelW = 35; 
$pdf->SetX($boxX + 5);

// Row 1: Tanggal
// Note: Anda bisa menambahkan $pdf->Image('calendar.png', $boxX+5, $pdf->GetY()+2, 5) disini
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($labelW, $lineHeight, 'Tanggal MCU', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, $lineHeight, ': ' . formatDateIndo($tanggal), 0, 1);

// Row 2: Jam
$pdf->SetX($boxX + 5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($labelW, $lineHeight, 'Jam Pelayanan', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, $lineHeight, ': 08:00 - 16:00 WIB', 0, 1);

// Row 3: Lokasi
$pdf->SetX($boxX + 5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($labelW, $lineHeight, 'Lokasi Klinik', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, $lineHeight, ': ' . getSetting('alamat'), 0, 1);

// Row 4: Kontak
$pdf->SetX($boxX + 5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($labelW, $lineHeight, 'Kontak', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, $lineHeight, ': ' . getSetting('telepon') . ' / ' . getSetting('whatsapp'), 0, 1);

// Output PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="pendaftaran-mcu-' . $kode_mcu . '.pdf"');
$pdf->Output('I', 'pendaftaran-mcu-' . $kode_mcu . '.pdf'); // 'I' untuk preview di browser, 'D' untuk download langsung
?>
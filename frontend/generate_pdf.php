<?php
require_once __DIR__ . '/../libs/fpdf/fpdf.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$kode_mcu = isset($_GET['kode']) ? $_GET['kode'] : '';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

if (empty($kode_mcu)) {
    die('Kode MCU tidak valid');
}

class PDF extends FPDF {
    function Header() {
        // No header for this PDF
    }

    function Footer() {
        // Footer with contact info
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Jika ada pertanyaan, hubungi kami di ' . getSetting('telepon'), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Card border
$pdf->SetLineWidth(0.5);
$pdf->Rect(10, 10, 190, 270);

// Header
$pdf->SetFillColor(40, 167, 69); // Green background
$pdf->Rect(10, 10, 190, 15, 'F');
$pdf->SetTextColor(255, 255, 255); // White text
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('✓ Pendaftaran Berhasil!'), 0, 1, 'C');
$pdf->Ln(10);

// Success icon
$pdf->SetTextColor(40, 167, 69); // Green text
$pdf->SetFont('Arial', '', 72);
$pdf->Cell(0, 30, utf8_decode('✓'), 0, 1, 'C');
$pdf->Ln(10);

// Thank you message
$pdf->SetTextColor(0, 0, 0); // Black text
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Terima kasih telah mendaftar MCU'), 0, 1, 'C');
$pdf->Ln(15);

// Kode MCU section
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Kode MCU Anda:', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor(0, 123, 255); // Blue text
$pdf->Cell(0, 15, $kode_mcu, 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0); // Black text
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, utf8_decode('Harap Tunjukan Kode Ini Pada Saat Akan Melakukan Pemeriksaan.'), 0, 1, 'C');
$pdf->Ln(15);

// Information box
$pdf->SetFillColor(248, 249, 250); // Light gray background
$pdf->Rect(15, $pdf->GetY(), 180, 8, 'F');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Informasi Penting', 0, 1, 'L');
$pdf->Ln(5);

// Information list
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, utf8_decode('📅 Tanggal MCU: ') . formatDateIndo($tanggal), 0, 1);
$pdf->Cell(0, 8, utf8_decode('🕐 Jam Pelayanan: 08:00 - 16:00 WIB'), 0, 1);
$pdf->Cell(0, 8, utf8_decode('📍 Lokasi Klinik: ') . getSetting('alamat'), 0, 1);
$pdf->Cell(0, 8, utf8_decode('📞 Kontak: ') . getSetting('telepon') . ' / ' . getSetting('whatsapp'), 0, 1);

// Output PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="pendaftaran-mcu-' . $kode_mcu . '.pdf"');
$pdf->Output('D');
?>

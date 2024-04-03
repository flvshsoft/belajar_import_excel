<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Lokasi file XLS
$xlsFile = 'lokasi.csv';

// Membaca file XLS
$spreadsheet = IOFactory::load($xlsFile);
$worksheet = $spreadsheet->getActiveSheet();

// Mendapatkan jumlah baris dan kolom
$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();

// Iterasi untuk membaca data
$data = [];
for ($row = 1; $row <= $highestRow; ++$row) {
    $rowData = [];
    for ($col = 'A'; $col <= $highestColumn; ++$col) {
        $cellValue = $worksheet->getCell($col . $row)->getValue();
        $rowData[] = $cellValue;
    }
    $data[] = $rowData;
}

// Menampilkan data
print_r($data);
?>

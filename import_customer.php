<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$servername = "localhost"; // Replace with your server name
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$database = "sh_bintang"; // Replace with your MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

    $sql = "INSERT INTO `customer`(`id_customer`, `nama_customer`, `alamat_customer`, `no_hp_customer`, `id_branch`, `created_at`, `updated_at`, `deleted_at`) VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    // Check if the prepare() succeeded
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }
    // Bind parameters
    $stmt->bind_param("isssisss", '', $rowData[1], $rowData[2], $rowData[3], $rowData[4], '', '', '');

    if ($stmt->execute()) {
        // echo "Record updated successfully";
        $hitung['id_sales'] = $conn->insert_id;
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $data[] = $rowData;
}

// Menampilkan data
print_r($data);

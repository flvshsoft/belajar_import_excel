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



// EXCEL

// Lokasi file XLS
$xlsFile = 'format_nota.xlsx';

// Membaca file XLS
$spreadsheet = IOFactory::load($xlsFile);
$worksheet = $spreadsheet->getActiveSheet();

// Mendapatkan jumlah baris dan kolom
$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();

// Iterasi untuk membaca data
$data = [];
$data2 = [];
for ($row = 2; $row <= $highestRow; ++$row) {
    $rowData = [];
    for ($col = 'A'; $col <= $highestColumn; ++$col) {
        $cellValue = $worksheet->getCell($col . $row)->getValue();
        $rowData[] = $cellValue;
    }
    $data[] = $rowData;
    if ($row > 4) {
        $data2[] = $rowData;
    }
}
// print_r($data2);


// Menampilkan data
$hitung = [];
$hitung['area'] = $data[0][0];
$hitung['salesman'] = $data[0][1];

// Start
$sql = "SELECT * FROM `area` WHERE `id_nama_area` LIKE '" . $hitung['area'] . "'"; // Replace with your table name
$result = $conn->query($sql);
$row_sql = $result->fetch_assoc();
// End
$hitung['id_area'] = $row_sql['id_area'];
$hitung['id_branch'] = $row_sql['id_branch'];
// Start
$sql = "SELECT * FROM `partner` WHERE `nama_lengkap` LIKE '" . $data[0][1] . "' AND `id_branch` = '".$hitung['id_branch']."'"; // Replace with your table name
$result = $conn->query($sql);
$row_sql = $result->fetch_assoc();
// End
$hitung['id_partner'] = $row_sql['id_partner'];
$hitung['week'] = $data[0][2];
$hitung['id_asset'] = '50000001';
// tanggal
$unixTimestamp = ($data[0][3] - 25569) * 86400; // Convert Excel date to Unix timestamp
$date = new DateTime("@$unixTimestamp"); // Create DateTime object using Unix timestamp
$formattedDate = $date->format('Y-m-d H:i:s'); // Format the date as desired
$hitung['tgl_do'] = $formattedDate;
// Start
$sql = "SELECT * FROM `sales` WHERE `id_partner` = '" . $hitung['id_partner'] . "' AND `id_area` = '" . $hitung['id_area'] . "' AND `week` = '" . $hitung['week'] . "'"; // Replace with your table name
$result = $conn->query($sql);
$row_sql = $result->fetch_assoc();
// End
if ($row_sql) {
    $hitung['id_sales'] = $row_sql['id_sales'];
} else {
    $sql = "INSERT INTO `sales`(`id_partner`, `id_asset`, `id_area`, `id_branch`, `km`, `week`, `tgl_do`, `keterangan`, `created_at`, `updated_at`, `deleted_at`) VALUES (?,?,?,?,'',?,?,'',?,'','')";
    $stmt = $conn->prepare($sql);
    // Check if the prepare() succeeded
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }
    // Bind parameters
    $stmt->bind_param("iiiiiss", $hitung['id_partner'], $hitung['id_asset'], $hitung['id_area'], $hitung['id_branch'], $hitung['week'], $hitung['tgl_do'], $hitung['tgl_do']);

    if ($stmt->execute()) {
        // echo "Record updated successfully";
        $hitung['id_sales'] = $conn->insert_id;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}


// nota

foreach ($data2 as $key => $value) {
    // tanggal
    $unixTimestamp = ($value[2] - 25569) * 86400; // Convert Excel date to Unix timestamp
    $date = new DateTime("@$unixTimestamp"); // Create DateTime object using Unix timestamp
    $formattedDate = $date->format('Y-m-d H:i:s'); // Format the date as desired
    $value[2] = $formattedDate;
    // konsumen
    // Start
    $sql = "SELECT * FROM `customer` WHERE `nama_customer` LIKE '%" . $value[3] . "%'"; // Replace with your table name
    $result = $conn->query($sql);
    $row_sql = $result->fetch_assoc();
    // End
    if ($row_sql) {
        $value[5] = $row_sql['id_customer'];

        $sql = "INSERT INTO `nota` (`id_nota`, `no_nota`, `id_sales`, `id_partner`, `weeks`, `payment_method`, `total_beli`, `id_customer`, `id_branch`, `id_area`, `id_bank`, `tgl_bayar`, `pay`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (NULL, ?, ?, ?, ?, 'KREDIT', ?, ?, ?, ?, '1', ?, NULL, '', '34563', '2024-04-15 22:01:52.000000', '2024-04-15 22:01:52.000000', '2024-04-15 22:01:52.000000');";
        $stmt = $conn->prepare($sql);
        // Check if the prepare() succeeded
        if (!$stmt) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        // Bind parameters
        $stmt->bind_param("sssssssss", $value['1'], $hitung['id_sales'], $hitung['id_partner'], $hitung['week'], $value[4], $value[5], $hitung['id_branch'],$hitung['id_area'],$value[2]);

        if ($stmt->execute()) {
            // echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
    print_r($value);
}
print_r($hitung);

// Close connection
$conn->close();

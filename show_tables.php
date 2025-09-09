<?php
require_once 'includes/db.php';

$result = mysqli_query($conn, 'SHOW TABLES');
echo "Database tables:\n";
while ($row = mysqli_fetch_row($result)) {
    echo $row[0] . "\n";
}

// Check if jewle_cat table exists and show its structure
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'jewle_cat'");
if (mysqli_num_rows($tableCheck) > 0) {
    echo "\njewle_cat table exists. Structure:\n";
    $structure = mysqli_query($conn, 'DESCRIBE jewle_cat');
    while ($row = mysqli_fetch_assoc($structure)) {
        print_r($row);
    }
    
    // Show some sample data
    echo "\nSample data from jewle_cat:\n";
    $data = mysqli_query($conn, 'SELECT * FROM jewle_cat LIMIT 5');
    while ($row = mysqli_fetch_assoc($data)) {
        print_r($row);
    }
} else {
    echo "\njewle_cat table does not exist in the database.\n";
}
?>
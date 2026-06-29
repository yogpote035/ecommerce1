<?php
mysqli_report(MYSQLI_REPORT_OFF);

function getDbConnection($dbname = 'ecommerce') {
    $configs = [
        ['root', '', $dbname],
        ['root', 'Yogeshpo7@', $dbname],
    ];

    foreach ($configs as $config) {
        $conn = @mysqli_connect('localhost', $config[0], $config[1], $config[2]);
        if ($conn) {
            mysqli_set_charset($conn, 'utf8');
            return $conn;
        }
    }

    return false;
}

$conn = getDbConnection('ecommerce');
if ($conn === false) {
    die('Database connection failed. Please verify MySQL is running and the credentials are correct.');
}
?>
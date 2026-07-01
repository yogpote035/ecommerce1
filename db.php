<?php
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/config/Database.php';

function getDbConnection($dbname = DB_NAME) {
    $config = getDatabaseConfig($dbname);

    $conn = @mysqli_connect($config['host'], $config['user'], $config['pass'], $config['name']);
    if ($conn) {
        mysqli_set_charset($conn, $config['charset']);
        return $conn;
    }

    return false;
}

$conn = getDbConnection(DB_NAME);
if ($conn === false) {
    die('Database connection failed. Please verify MySQL is running and the credentials are correct.');
}
?>
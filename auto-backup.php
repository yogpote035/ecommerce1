<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$sessionPath = __DIR__ . '/logs/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

require_once __DIR__ . '/init.php';

$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0775, true);
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS backup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_file VARCHAR(255) NOT NULL,
    backup_size BIGINT,
    backup_type ENUM('manual', 'automatic') DEFAULT 'automatic',
    status ENUM('success', 'failed') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    notes TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$filename = 'auto_backup_' . date('Ymd_His') . '.sql';
$path = $backupDir . DIRECTORY_SEPARATOR . $filename;
$handle = fopen($path, 'w');

if (!$handle) {
    fwrite(STDERR, "Unable to create backup file\n");
    exit(1);
}

$tables = [];
$result = mysqli_query($conn, 'SHOW TABLES');
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    $create = mysqli_query($conn, 'SHOW CREATE TABLE `' . mysqli_real_escape_string($conn, $table) . '`');
    $createRow = mysqli_fetch_assoc($create);
    fwrite($handle, "\nDROP TABLE IF EXISTS `$table`;\n" . $createRow['Create Table'] . ";\n\n");

    $rows = mysqli_query($conn, 'SELECT * FROM `' . mysqli_real_escape_string($conn, $table) . '`');
    while ($data = mysqli_fetch_assoc($rows)) {
        $columns = array_map(function ($column) {
            return '`' . str_replace('`', '``', $column) . '`';
        }, array_keys($data));
        $values = array_map(function ($value) use ($conn) {
            return $value === null ? 'NULL' : "'" . mysqli_real_escape_string($conn, $value) . "'";
        }, array_values($data));
        fwrite($handle, 'INSERT INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n");
    }
}

fclose($handle);
$size = filesize($path);
$stmt = mysqli_prepare($conn, "INSERT INTO backup_logs (backup_file, backup_size, backup_type, status, notes) VALUES (?, ?, 'automatic', 'success', ?)");
$notes = 'Created by auto-backup.php CLI runner';
mysqli_stmt_bind_param($stmt, 'sis', $filename, $size, $notes);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
SecurityHelper::logActivity($conn, 'automatic_backup', 'backup', null, null, 'admin');

echo "Automatic backup created: $filename\n";

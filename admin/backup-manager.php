<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../config/Database.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ../auth.php?role=admin&mode=login');
    exit;
}

$siteTitle = 'Backup Manager';
$backupDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'backups';
$csrfToken = SecurityHelper::generateCSRFToken();

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

ensureBackupSchema($conn);

function ensureBackupSchema($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS backup_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        backup_file VARCHAR(255) NOT NULL,
        backup_size BIGINT DEFAULT 0,
        backup_type ENUM('manual', 'automatic', 'restore') DEFAULT 'manual',
        status ENUM('success', 'failed') DEFAULT 'success',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_by INT NULL,
        notes TEXT,
        INDEX (created_at, backup_type),
        INDEX (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "ALTER TABLE backup_logs MODIFY COLUMN backup_type ENUM('manual', 'automatic', 'restore') DEFAULT 'manual'");
    ensureBackupColumnExists($conn, 'backup_logs', 'backup_size', "ALTER TABLE backup_logs ADD COLUMN backup_size BIGINT DEFAULT 0");
    ensureBackupColumnExists($conn, 'backup_logs', 'created_by', "ALTER TABLE backup_logs ADD COLUMN created_by INT NULL");
    ensureBackupColumnExists($conn, 'backup_logs', 'notes', "ALTER TABLE backup_logs ADD COLUMN notes TEXT NULL");
}

function ensureBackupColumnExists($conn, $table, $column, $alterSql) {
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ((int)$count === 0) {
        return mysqli_query($conn, $alterSql) !== false;
    }

    return true;
}

function flashAndRedirect($type, $message) {
    $_SESSION['toast'] = ['type' => $type, 'message' => $message];
    header('Location: backup-manager.php');
    exit;
}

function resolveBackupPath($backupDir, $file) {
    $file = basename((string)$file);
    if ($file === '' || !preg_match('/^[a-zA-Z0-9._-]+\.sql$/', $file)) {
        return false;
    }

    $path = $backupDir . DIRECTORY_SEPARATOR . $file;
    $realDir = realpath($backupDir);
    $realPath = file_exists($path) ? realpath($path) : $path;

    if (!$realDir || strpos($realPath, $realDir) !== 0) {
        return false;
    }

    return $path;
}

function logBackupEvent($conn, $file, $size, $type, $status, $notes = '') {
    $adminId = $_SESSION['admin_id'] ?? $_SESSION['aid'] ?? null;
    $stmt = mysqli_prepare($conn, 'INSERT INTO backup_logs (backup_file, backup_size, backup_type, status, created_by, notes) VALUES (?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'sissis', $file, $size, $type, $status, $adminId, $notes);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function sqlValue($conn, $value) {
    if ($value === null) {
        return 'NULL';
    }

    return "'" . mysqli_real_escape_string($conn, (string)$value) . "'";
}

function createDatabaseBackup($conn, $backupDir) {
    $filename = 'backup-' . DB_NAME . '-' . date('Ymd-His') . '.sql';
    $path = $backupDir . DIRECTORY_SEPARATOR . $filename;
    $handle = fopen($path, 'wb');

    if (!$handle) {
        throw new Exception('Unable to create backup file.');
    }

    fwrite($handle, "-- Ecommerce database backup\n");
    fwrite($handle, "-- Database: " . DB_NAME . "\n");
    fwrite($handle, "-- Created: " . date('Y-m-d H:i:s') . "\n\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    $tablesResult = mysqli_query($conn, 'SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
    if (!$tablesResult) {
        fclose($handle);
        throw new Exception('Unable to list database tables.');
    }

    while ($tableRow = mysqli_fetch_array($tablesResult)) {
        $table = $tableRow[0];
        $escapedTable = str_replace('`', '``', $table);

        $createResult = mysqli_query($conn, 'SHOW CREATE TABLE `' . $escapedTable . '`');
        $createRow = $createResult ? mysqli_fetch_assoc($createResult) : null;
        if (!$createRow) {
            continue;
        }

        fwrite($handle, "DROP TABLE IF EXISTS `$escapedTable`;\n");
        fwrite($handle, $createRow['Create Table'] . ";\n\n");

        $dataResult = mysqli_query($conn, 'SELECT * FROM `' . $escapedTable . '`');
        if (!$dataResult || mysqli_num_rows($dataResult) === 0) {
            fwrite($handle, "\n");
            continue;
        }

        $fields = [];
        while ($field = mysqli_fetch_field($dataResult)) {
            $fields[] = '`' . str_replace('`', '``', $field->name) . '`';
        }

        while ($row = mysqli_fetch_assoc($dataResult)) {
            $values = [];
            foreach ($row as $value) {
                $values[] = sqlValue($conn, $value);
            }
            fwrite($handle, 'INSERT INTO `' . $escapedTable . '` (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ");\n");
        }

        fwrite($handle, "\n");
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);

    return [$filename, filesize($path)];
}

function splitSqlStatements($sql) {
    $statements = [];
    $buffer = '';
    $length = strlen($sql);
    $inString = false;
    $quote = '';

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';

        if (!$inString && $char === '-' && $next === '-') {
            while ($i < $length && $sql[$i] !== "\n") {
                $i++;
            }
            continue;
        }

        if (!$inString && $char === '#') {
            while ($i < $length && $sql[$i] !== "\n") {
                $i++;
            }
            continue;
        }

        if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
            if (!$inString) {
                $inString = true;
                $quote = $char;
            } elseif ($quote === $char) {
                $inString = false;
                $quote = '';
            }
        }

        if (!$inString && $char === ';') {
            $statement = trim($buffer);
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    $statement = trim($buffer);
    if ($statement !== '') {
        $statements[] = $statement;
    }

    return $statements;
}

function restoreDatabaseBackup($conn, $path) {
    $sql = file_get_contents($path);
    if ($sql === false || trim($sql) === '') {
        throw new Exception('Backup file is empty or unreadable.');
    }

    $statements = splitSqlStatements($sql);
    mysqli_begin_transaction($conn);
    mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=0');

    try {
        foreach ($statements as $statement) {
            if (!mysqli_query($conn, $statement)) {
                throw new Exception(mysqli_error($conn));
            }
        }
        mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=1');
        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=1');
        mysqli_rollback($conn);
        throw $e;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flashAndRedirect('danger', 'Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        try {
            [$filename, $size] = createDatabaseBackup($conn, $backupDir);
            logBackupEvent($conn, $filename, $size, 'manual', 'success', 'Manual backup created.');
            flashAndRedirect('success', 'Database backup created successfully.');
        } catch (Exception $e) {
            logBackupEvent($conn, 'backup-failed-' . date('Ymd-His') . '.sql', 0, 'manual', 'failed', $e->getMessage());
            flashAndRedirect('danger', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    if ($action === 'restore') {
        $path = resolveBackupPath($backupDir, $_POST['file'] ?? '');
        $confirm = trim($_POST['confirm_restore'] ?? '');
        if (!$path || !file_exists($path)) {
            flashAndRedirect('danger', 'Backup file not found.');
        }
        if ($confirm !== 'RESTORE') {
            flashAndRedirect('danger', 'Type RESTORE to confirm database restore.');
        }

        try {
            restoreDatabaseBackup($conn, $path);
            ensureBackupSchema($conn);
            logBackupEvent($conn, basename($path), filesize($path), 'restore', 'success', 'Database restored from backup.');
            flashAndRedirect('success', 'Database restored successfully.');
        } catch (Exception $e) {
            logBackupEvent($conn, basename($path), file_exists($path) ? filesize($path) : 0, 'restore', 'failed', $e->getMessage());
            flashAndRedirect('danger', 'Restore failed: ' . $e->getMessage());
        }
    }

    if ($action === 'delete') {
        $path = resolveBackupPath($backupDir, $_POST['file'] ?? '');
        if (!$path || !file_exists($path)) {
            flashAndRedirect('danger', 'Backup file not found.');
        }

        $filename = basename($path);
        $size = filesize($path);
        if (unlink($path)) {
            logBackupEvent($conn, $filename, $size, 'manual', 'success', 'Backup file deleted.');
            flashAndRedirect('success', 'Backup deleted successfully.');
        }

        flashAndRedirect('danger', 'Unable to delete backup file.');
    }
}

if (isset($_GET['download'])) {
    $path = resolveBackupPath($backupDir, $_GET['download']);
    if (!$path || !file_exists($path)) {
        http_response_code(404);
        exit('Backup not found.');
    }

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

$backupLogMeta = [];
$loggedBackupResult = mysqli_query($conn, "SELECT backup_file, backup_type, status, created_at FROM backup_logs WHERE backup_file LIKE '%.sql' ORDER BY created_at DESC");
if ($loggedBackupResult) {
    while ($row = mysqli_fetch_assoc($loggedBackupResult)) {
        $name = basename($row['backup_file'] ?? '');
        if ($name !== '' && preg_match('/^[a-zA-Z0-9._-]+\.sql$/', $name) && !isset($backupLogMeta[$name])) {
            $backupLogMeta[$name] = $row;
        }
    }
}

$allBackups = [];
foreach (glob($backupDir . DIRECTORY_SEPARATOR . '*.sql') ?: [] as $filePath) {
    if (!is_file($filePath)) {
        continue;
    }
    $name = basename($filePath);
    $meta = $backupLogMeta[$name] ?? [];
    $loggedTs = !empty($meta['created_at']) ? strtotime($meta['created_at']) : false;
    $modifiedTs = $loggedTs ?: (filemtime($filePath) ?: time());
    $allBackups[] = [
        'name' => $name,
        'size' => filesize($filePath),
        'modified' => !empty($meta['created_at']) ? $meta['created_at'] : date('Y-m-d H:i:s', $modifiedTs),
        'modified_ts' => $modifiedTs,
        'type' => $meta['backup_type'] ?? (strpos($name, 'auto_') === 0 ? 'automatic' : 'manual'),
        'status' => $meta['status'] ?? 'available',
    ];
}
usort($allBackups, function ($a, $b) {
    return ($b['modified_ts'] <=> $a['modified_ts']) ?: strcmp($b['name'], $a['name']);
});

$itemsPerPage = PaginationHelper::PER_PAGE;
$backupPage = PaginationHelper::currentPage('backup_page');
$totalBackups = count($allBackups);
$backupPages = PaginationHelper::totalPages($totalBackups, $itemsPerPage);
$backupPage = min($backupPage, $backupPages);
$backupOffset = PaginationHelper::offset($backupPage, $itemsPerPage);
$backups = array_slice($allBackups, $backupOffset, $itemsPerPage);

$logs = [];
$logPage = PaginationHelper::currentPage('log_page');
$totalLogs = 0;
$logCountResult = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM backup_logs');
if ($logCountResult) {
    $totalLogs = (int)(mysqli_fetch_assoc($logCountResult)['total'] ?? 0);
}
$logPages = PaginationHelper::totalPages($totalLogs, $itemsPerPage);
$logPage = min($logPage, $logPages);
$logOffset = PaginationHelper::offset($logPage, $itemsPerPage);
$logResult = mysqli_query($conn, "SELECT backup_file, backup_size, backup_type, status, created_at, notes FROM backup_logs ORDER BY created_at DESC LIMIT $itemsPerPage OFFSET $logOffset");
if ($logResult) {
    while ($row = mysqli_fetch_assoc($logResult)) {
        $logs[] = $row;
    }
}
include __DIR__ . '/../templates/header.php';
?>
<div class="dashboard-shell mx-auto" style="max-width: 1320px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Database Backup Manager</h1>
            <p class="text-muted mb-0">Create, download, restore, and audit database backups for <?php echo htmlspecialchars(DB_NAME); ?>.</p>
        </div>
        <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5">Create Backup</h2>
            <p class="text-muted">Generates a SQL file using PHP, so it does not depend on shell access or mysqldump availability.</p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="create">
                <button type="submit" class="btn btn-primary">Create New Backup</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Available Backups</h2>
            <?php if (empty($backups)): ?>
                <p class="text-muted mb-0">No backups found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Modified</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($backup['name']); ?></code></td>
                                <td><?php echo htmlspecialchars(ucfirst($backup['type'])); ?></td>
                                <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                                <td><?php echo htmlspecialchars($backup['modified']); ?></td>
                                <td>
                                    <a href="backup-manager.php?download=<?php echo urlencode($backup['name']); ?>" class="btn btn-sm btn-secondary">Download</a>
                                    <form method="post" class="d-inline-block ml-1">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this backup file?')">Delete</button>
                                    </form>
                                    <form method="post" class="mt-2">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="confirm_restore" class="form-control" placeholder="Type RESTORE">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-warning" onclick="return confirm('Restore database from this backup? Current data may be replaced.')">Restore</button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php PaginationHelper::render($backupPage, $backupPages, $totalBackups, $itemsPerPage, 'backups', ['pageKey' => 'backup_page']); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-3">Recent Backup Activity</h2>
            <?php if (empty($logs)): ?>
                <p class="text-muted mb-0">No backup activity logged yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>File</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($log['backup_file']); ?></td>
                                <td><?php echo htmlspecialchars($log['backup_type']); ?></td>
                                <td><span class="badge badge-<?php echo $log['status'] === 'success' ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars($log['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($log['notes'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php PaginationHelper::render($logPage, $logPages, $totalLogs, $itemsPerPage, 'backup activity', ['pageKey' => 'log_page']); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>

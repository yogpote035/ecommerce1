<?php
require_once 'init.php';
if (!empty($_COOKIE['remember_login'])) {
    $parts = explode(':', $_COOKIE['remember_login'], 2);
    if (!empty($parts[0])) {
        ensureRememberTokenTable($conn);
        $stmt = mysqli_prepare($conn, 'DELETE FROM remember_tokens WHERE selector = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $parts[0]);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    setcookie('remember_login', '', time() - 3600, '/', '', false, true);
}
SecurityHelper::destroySession();
header('Location: Home.php');
exit;

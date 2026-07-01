<?php
require_once 'init.php';

function redirectWithToast($type, $message, $location = 'auth.php') {
    $_SESSION['toast'] = ['type' => $type, 'message' => $message];
    header('Location: ' . $location);
    exit();
}

$userType = $_POST['user_type'] ?? 'customer';

ensureOtpSchema($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithToast('danger', 'Invalid request method.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
}

if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirectWithToast('danger', 'Invalid CSRF token.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
}

$action = $_POST['action'] ?? '';
$userType = $_POST['user_type'] ?? $userType;
$email = SecurityHelper::sanitizeEmail($_POST['email'] ?? '');

if (!in_array($userType, ['customer', 'admin'], true)) {
    redirectWithToast('danger', 'Invalid user type.', 'auth.php');
}

if ($action === 'send_otp') {
    if (!SecurityHelper::checkRateLimit('send_otp_' . $userType . '_' . $email, 3, 300)) {
        redirectWithToast('danger', 'Too many OTP requests. Please wait a few minutes before trying again.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
    }

    if (!SecurityHelper::isValidEmail($email)) {
        redirectWithToast('danger', 'Please enter a valid email address.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
    }

    $table = $userType === 'admin' ? 'aregister' : 'cregister';
    $emailField = $userType === 'admin' ? 'email' : 'Cemail';

    $stmt = $conn->prepare("SELECT 1 FROM $table WHERE $emailField = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        redirectWithToast('danger', 'Email not found.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
    }

    $otp = SecurityHelper::generateOTP();
    $expiresAt = date('Y-m-d H:i:s', time() + OTP_EXPIRY);

    $stmt = $conn->prepare(
        "INSERT INTO otp_codes (user_type, email, otp_code, expires_at, attempts, is_used)
         VALUES (?, ?, ?, ?, 0, 0)"
    );
    $stmt->bind_param('ssss', $userType, $email, $otp, $expiresAt);
    $stmt->execute();
    $stmt->close();

    $emailSent = sendOtpEmail($email, $otp);
    if (!$emailSent && APP_DEBUG) {
        logDevelopmentOtp($email, $userType, $otp);
    }

    if (!$emailSent && !APP_DEBUG) {
        redirectWithToast('danger', 'Failed to send OTP email. Please verify server mail settings.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
    }

    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_user_type'] = $userType;

    $message = $emailSent
        ? 'OTP sent to your email address. Please check your inbox.'
        : 'Email is not configured, so the OTP was written to logs/otp-dev.log for local testing.';
    redirectWithToast('success', $message, 'auth.php?role=' . $userType . '&mode=login&otp=1');
}

if ($action === 'verify_otp') {
    $otp = SecurityHelper::sanitize($_POST['otp'] ?? '');
    $email = $_SESSION['otp_email'] ?? $email;
    $userType = $_SESSION['otp_user_type'] ?? $userType;

    if (!SecurityHelper::isValidEmail($email) || !preg_match('/^\d{6}$/', $otp)) {
        redirectWithToast('danger', 'Invalid OTP or email.', 'auth.php?role=' . $userType . '&mode=login');
    }

    if (!SecurityHelper::checkRateLimit('verify_otp_' . $userType . '_' . $email, OTP_MAX_ATTEMPTS, OTP_EXPIRY)) {
        redirectWithToast('danger', 'Too many OTP attempts. Request a new code.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
    }

    $stmt = $conn->prepare(
        "SELECT id, attempts, expires_at FROM otp_codes
         WHERE email = ? AND user_type = ? AND otp_code = ? AND is_used = 0"
    );
    $stmt->bind_param('sss', $email, $userType, $otp);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        $stmt = $conn->prepare('UPDATE otp_codes SET attempts = attempts + 1 WHERE email = ? AND user_type = ?');
        $stmt->bind_param('ss', $email, $userType);
        $stmt->execute();
        $stmt->close();

        redirectWithToast('danger', 'Invalid OTP. Please try again.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
    }

    if (strtotime($result['expires_at']) < time()) {
        redirectWithToast('danger', 'OTP expired. Request a new code.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
    }

    if ($result['attempts'] >= OTP_MAX_ATTEMPTS) {
        redirectWithToast('danger', 'Maximum OTP attempts exceeded. Request a new code.', 'auth.php?role=' . $userType . '&mode=login&otp=1');
    }

    $stmt = $conn->prepare('UPDATE otp_codes SET is_used = 1 WHERE id = ?');
    $stmt->bind_param('i', $result['id']);
    $stmt->execute();
    $stmt->close();
    SecurityHelper::resetRateLimit('verify_otp_' . $userType . '_' . $email);
    unset($_SESSION['otp_email'], $_SESSION['otp_user_type']);

    if ($userType === 'admin') {
        $stmt = $conn->prepare('SELECT aid, aname, email FROM aregister WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $_SESSION['admin_id'] = $admin['aid'];
        $_SESSION['aname'] = $admin['aname'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Admin logged in with OTP successfully.'];
        SecurityHelper::regenerateSessionID();
        header('Location: admin_products.php');
        exit();
    }

    $stmt = $conn->prepare('SELECT Cid, Cemail FROM cregister WHERE Cemail = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $_SESSION['customer_id'] = $customer['Cid'];
    $_SESSION['customer_email'] = $customer['Cemail'];
    $_SESSION['customer_logged_in'] = true;
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Customer logged in with OTP successfully.'];
    SecurityHelper::regenerateSessionID();
    header('Location: index.php?page=1');
    exit();
}

function ensureOtpSchema($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS otp_codes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NULL,
        user_type ENUM('customer', 'admin') NOT NULL,
        email VARCHAR(255) NOT NULL,
        otp_code VARCHAR(6) NOT NULL,
        attempts INT DEFAULT 0,
        is_used TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        INDEX (email, user_type, is_used),
        INDEX (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensureOtpColumnExists($conn, 'otp_codes', 'attempts', "ALTER TABLE otp_codes ADD COLUMN attempts INT DEFAULT 0");
    ensureOtpColumnExists($conn, 'otp_codes', 'is_used', "ALTER TABLE otp_codes ADD COLUMN is_used TINYINT(1) DEFAULT 0");
    ensureOtpColumnExists($conn, 'otp_codes', 'expires_at', "ALTER TABLE otp_codes ADD COLUMN expires_at TIMESTAMP NULL");
    ensureOtpColumnExists($conn, 'otp_codes', 'created_at', "ALTER TABLE otp_codes ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

function ensureOtpColumnExists($conn, $table, $column, $alterSql) {
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

function logDevelopmentOtp($email, $userType, $otp) {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $line = sprintf("[%s] %s OTP for %s: %s\n", date('Y-m-d H:i:s'), $userType, $email, $otp);
    @file_put_contents($logDir . '/otp-dev.log', $line, FILE_APPEND | LOCK_EX);
}

function sendOtpEmail($email, $otp) {
    $fromEmail = getenv('EMAIL_FROM') ?: 'noreply@ecommerce.local';
    $subject = getenv('EMAIL_SUBJECT') ?: 'Your One-Time Password (OTP)';
    $message = "Your OTP is: $otp\n\nThis code is valid for " . (OTP_EXPIRY / 60) . " minutes.\n\nIf you did not request this, please ignore this email.";

    $smtpHost = getenv('SMTP_HOST');
    $smtpPort = getenv('SMTP_PORT') ?: '25';
    $smtpUser = getenv('SMTP_USER');
    $smtpPass = getenv('SMTP_PASS');
    $smtpEncryption = strtolower(getenv('SMTP_ENCRYPTION') ?: '');

    if ($smtpHost) {
        return sendOtpEmailViaSmtp($smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpEncryption, $fromEmail, $email, $subject, $message);
    }

    $headers = [];
    $headers[] = 'From: ' . $fromEmail;
    $headers[] = 'Reply-To: ' . $fromEmail;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';

    return mail($email, $subject, $message, implode("\r\n", $headers));
}

function sendOtpEmailViaSmtp($host, $port, $user, $pass, $encryption, $fromEmail, $toEmail, $subject, $message) {
    $contextOptions = [];
    $transport = ($encryption === 'ssl') ? 'ssl://' : '';
    $stream = stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, stream_context_create($contextOptions));
    if (!$stream) {
        return false;
    }
    stream_set_timeout($stream, 15);

    $response = smtpRead($stream);
    if (strpos($response, '220') !== 0) {
        fclose($stream);
        return false;
    }

    $localhost = 'localhost';
    if (!smtpWrite($stream, "EHLO $localhost\r\n")) return false;
    $response = smtpRead($stream);
    if ($encryption === 'starttls') {
        if (!smtpWrite($stream, "STARTTLS\r\n")) return false;
        $response = smtpRead($stream);
        if (strpos($response, '220') !== 0) {
            fclose($stream);
            return false;
        }
        if (!stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($stream);
            return false;
        }
        if (!smtpWrite($stream, "EHLO $localhost\r\n")) return false;
        $response = smtpRead($stream);
    }

    if ($user && $pass) {
        if (!smtpWrite($stream, "AUTH LOGIN\r\n")) return false;
        $response = smtpRead($stream);
        if (strpos($response, '334') !== 0) {
            fclose($stream);
            return false;
        }
        if (!smtpWrite($stream, base64_encode($user) . "\r\n")) return false;
        $response = smtpRead($stream);
        if (strpos($response, '334') !== 0) {
            fclose($stream);
            return false;
        }
        if (!smtpWrite($stream, base64_encode($pass) . "\r\n")) return false;
        $response = smtpRead($stream);
        if (strpos($response, '235') !== 0) {
            fclose($stream);
            return false;
        }
    }

    if (!smtpWrite($stream, "MAIL FROM:<$fromEmail>\r\n")) return false;
    $response = smtpRead($stream);
    if (strpos($response, '250') !== 0) { fclose($stream); return false; }

    if (!smtpWrite($stream, "RCPT TO:<$toEmail>\r\n")) return false;
    $response = smtpRead($stream);
    if (strpos($response, '250') !== 0 && strpos($response, '251') !== 0) { fclose($stream); return false; }

    if (!smtpWrite($stream, "DATA\r\n")) return false;
    $response = smtpRead($stream);
    if (strpos($response, '354') !== 0) { fclose($stream); return false; }

    $headers = [];
    $headers[] = 'From: ' . $fromEmail;
    $headers[] = 'To: ' . $toEmail;
    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = '';
    $payload = implode("\r\n", $headers) . "\r\n" . wordwrap($message, 70) . "\r\n.\r\n";

    if (!smtpWrite($stream, $payload)) return false;
    $response = smtpRead($stream);
    if (strpos($response, '250') !== 0) { fclose($stream); return false; }

    smtpWrite($stream, "QUIT\r\n");
    fclose($stream);
    return true;
}

function smtpWrite($stream, $command) {
    return fwrite($stream, $command) !== false;
}

function smtpRead($stream) {
    $data = '';
    while (($line = fgets($stream, 515)) !== false) {
        $data .= $line;
        if (substr($line, 3, 1) === ' ') {
            break;
        }
    }
    return $data;
}

redirectWithToast('danger', 'Unknown action.', 'auth.php');

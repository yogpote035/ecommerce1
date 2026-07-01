<?php
require_once 'init.php';

$siteTitle = 'Reset Password';
$csrfToken = SecurityHelper::generateCSRFToken();
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$role = ($_GET['role'] ?? $_POST['role'] ?? 'customer') === 'admin' ? 'admin' : 'customer';

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('customer', 'admin') NOT NULL,
    email VARCHAR(255) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    KEY idx_email_type (email, user_type),
    KEY idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function forgotPasswordColumnExists($conn, $table, $column) {
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return (int)$count > 0;
}

function logPasswordResetLink($email, $link, $status) {
    if (!is_dir(__DIR__ . '/logs')) {
        @mkdir(__DIR__ . '/logs', 0775, true);
    }
    $line = '[' . date('c') . '] ' . $status . ' ' . $email . ' ' . $link . PHP_EOL;
    @file_put_contents(__DIR__ . '/logs/password-reset-dev.log', $line, FILE_APPEND | LOCK_EX);
}

function sendPasswordResetEmail($email, $role, $link) {
    $subject = 'Reset your Ecommerce password';
    $message = "Hello,\n\nWe received a request to reset your Ecommerce " . $role . " account password.\n\nReset your password using this secure link:\n" . $link . "\n\nThis link expires in 30 minutes and can be used only once.\n\nIf you did not request this, you can ignore this email.";
    return MailHelper::sendPlainText($email, $subject, $message);
}

function findPasswordResetByToken($conn, $role, $token) {
    if ($token === '' || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
        return null;
    }

    $hash = hash('sha256', $token);
    $stmt = mysqli_prepare($conn, 'SELECT id, email, is_used, expires_at FROM password_resets WHERE user_type = ? AND token_hash = ? ORDER BY id DESC LIMIT 1');
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'ss', $role, $hash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $reset = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $reset ?: null;
}

function getPasswordResetStatus($reset) {
    if (!$reset) {
        return 'invalid';
    }
    if ((int)($reset['is_used'] ?? 0) === 1) {
        return 'used';
    }
    $expiresAt = strtotime($reset['expires_at'] ?? '');
    if ($expiresAt === false || $expiresAt < time()) {
        return 'expired';
    }
    return 'valid';
}

$resetRecord = $token !== '' ? findPasswordResetByToken($conn, $role, $token) : null;
$resetStatus = $token !== '' ? getPasswordResetStatus($resetRecord) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Invalid form token.'];
        header('Location: forgot_password.php');
        exit;
    }

    $action = $_POST['action'] ?? 'request';
    if ($action === 'request') {
        $email = SecurityHelper::sanitizeEmail($_POST['email'] ?? '');
        if (!SecurityHelper::isValidEmail($email)) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Please enter a valid email address.'];
            header('Location: forgot_password.php?role=' . urlencode($role));
            exit;
        }

        $table = $role === 'admin' ? 'aregister' : 'cregister';
        $emailColumn = $role === 'admin' ? 'email' : 'Cemail';
        $stmt = mysqli_prepare($conn, "SELECT 1 FROM $table WHERE $emailColumn = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($exists) {
            $resetToken = bin2hex(random_bytes(32));
            $hash = hash('sha256', $resetToken);
            $expires = date('Y-m-d H:i:s', time() + 1800);
            $insert = mysqli_prepare($conn, 'INSERT INTO password_resets (user_type, email, token_hash, expires_at) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($insert, 'ssss', $role, $email, $hash, $expires);
            mysqli_stmt_execute($insert);
            mysqli_stmt_close($insert);

            $link = app_absolute_url('forgot_password.php?role=' . urlencode($role) . '&token=' . urlencode($resetToken));
            $sent = sendPasswordResetEmail($email, $role, $link);
            logPasswordResetLink($email, $link, $sent ? 'sent' : 'mail-failed');
        }

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'If the account exists, a reset link has been sent to the registered email address.'];
        header('Location: forgot_password.php?role=' . urlencode($role));
        exit;
    }

    if ($action === 'reset') {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $validation = SecurityHelper::validatePassword($password);
        if ($password !== $confirm) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Passwords do not match';
        }

        if (!$validation['valid']) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => implode('. ', $validation['errors'])];
            header('Location: forgot_password.php?role=' . urlencode($role) . '&token=' . urlencode($token));
            exit;
        }

        $resetRecord = findPasswordResetByToken($conn, $role, $token);
        $resetStatus = getPasswordResetStatus($resetRecord);
        if ($resetStatus !== 'valid') {
            $messages = [
                'used' => 'This reset link has already been used. Please request a new one.',
                'expired' => 'This reset link has expired. Please request a new one.',
                'invalid' => 'Reset link is invalid. Please request a new one.',
            ];
            $_SESSION['toast'] = ['type' => 'danger', 'message' => $messages[$resetStatus] ?? $messages['invalid']];
            header('Location: forgot_password.php?role=' . urlencode($role));
            exit;
        }

        $resetId = (int)$resetRecord['id'];
        $email = $resetRecord['email'];

        $passwordHash = SecurityHelper::hashPassword($password);
        if ($role === 'admin') {
            $update = mysqli_prepare($conn, 'UPDATE aregister SET apass = ? WHERE email = ?');
            mysqli_stmt_bind_param($update, 'ss', $passwordHash, $email);
        } else {
            $hasConfirmColumn = forgotPasswordColumnExists($conn, 'cregister', 'Cconpass');
            if ($hasConfirmColumn) {
                $update = mysqli_prepare($conn, 'UPDATE cregister SET Cpass = ?, Cconpass = ? WHERE Cemail = ?');
                mysqli_stmt_bind_param($update, 'sss', $passwordHash, $passwordHash, $email);
            } else {
                $update = mysqli_prepare($conn, 'UPDATE cregister SET Cpass = ? WHERE Cemail = ?');
                mysqli_stmt_bind_param($update, 'ss', $passwordHash, $email);
            }
        }
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        $used = mysqli_prepare($conn, 'UPDATE password_resets SET is_used = 1 WHERE id = ?');
        mysqli_stmt_bind_param($used, 'i', $resetId);
        mysqli_stmt_execute($used);
        mysqli_stmt_close($used);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Password reset successfully. Please login.'];
        header('Location: auth.php?role=' . urlencode($role) . '&mode=login');
        exit;
    }
}

include 'templates/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card sidebar-card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-2">Reset Password</h1>
        <p class="text-secondary">Choose account type and we will email a secure reset link.</p>

        <?php if ($token !== '' && $resetStatus !== 'valid'): ?>
          <div class="alert alert-warning">
            <?php if ($resetStatus === 'used'): ?>
              This reset link has already been used. Request a new reset link below.
            <?php elseif ($resetStatus === 'expired'): ?>
              This reset link has expired. Request a new reset link below.
            <?php else: ?>
              This reset link is invalid. Request a new reset link below.
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($token === '' || ($token !== '' && $resetStatus !== 'valid')): ?>
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="request">
            <div class="form-group">
              <label for="role">Account type</label>
              <select id="role" name="role" class="form-control">
                <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>Customer</option>
                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
              </select>
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" type="email" name="email" class="form-control" required>
            </div>
            <button class="btn btn-primary">Send reset link</button>
          </form>
        <?php else: ?>
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="reset">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
              <label for="password">New password</label>
              <div class="input-group">
                <input id="password" type="password" name="password" class="form-control" required>
                <div class="input-group-append">
                  <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Show password">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                      <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                      <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="confirm_password">Confirm password</label>
              <div class="input-group">
                <input id="confirm_password" type="password" name="confirm_password" class="form-control" required>
                <div class="input-group-append">
                  <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Show password">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                      <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                      <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <button class="btn btn-primary">Reset password</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
  document.querySelectorAll('.password-toggle').forEach(function (button) {
    button.addEventListener('click', function () {
      var group = button.closest('.input-group');
      var input = group ? group.querySelector('input') : null;
      if (!input) return;
      var shouldShow = input.type === 'password';
      input.type = shouldShow ? 'text' : 'password';
      button.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
    });
  });
</script>
<?php include 'templates/footer.php'; ?>

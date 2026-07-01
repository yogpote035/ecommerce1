<?php
require_once 'init.php';
$siteTitle = 'Retail Login';

if (!empty($_SESSION['rname'])) {
    header('Location: Rmain.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $mode = ($_GET['mode'] ?? '') === 'signup' ? 'signup' : 'login';
    header('Location: auth.php?role=retailer&mode=' . $mode);
    exit;
}

$csrf_token = SecurityHelper::generateCSRFToken();
$messages = [];
$errors = [];
$loginName = '';
$registerName = '';
$registerAddress = '';

$retailerConn = getDbConnection('retailler');
if (!$retailerConn) {
    die('Retailer database connection failed.');
}

function safeInput($value) {
    return trim($value ?? '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityHelper::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        if (isset($_POST['action']) && $_POST['action'] === 'login') {
            $loginName = safeInput($_POST['rname']);
            $password = $_POST['rpass'] ?? '';

            if ($loginName === '' || $password === '') {
                $errors[] = 'Retailer name and password are required.';
            }

            if (empty($errors)) {
                $stmt = mysqli_prepare($retailerConn, 'SELECT rpass FROM rregister WHERE rname = ? LIMIT 1');
                mysqli_stmt_bind_param($stmt, 's', $loginName);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $storedPassword);
                if (mysqli_stmt_fetch($stmt)) {
                    $passwordMatches = false;
                    if (SecurityHelper::isPasswordHash($storedPassword)) {
                        $passwordMatches = SecurityHelper::verifyPassword($password, $storedPassword);
                    } else {
                        $passwordMatches = $storedPassword === $password;
                    }

                    if ($passwordMatches) {
                        if (!SecurityHelper::isPasswordHash($storedPassword)) {
                            $newHash = SecurityHelper::hashPassword($password);
                            $updateStmt = mysqli_prepare($retailerConn, 'UPDATE rregister SET rpass = ? WHERE rname = ?');
                            mysqli_stmt_bind_param($updateStmt, 'ss', $newHash, $loginName);
                            mysqli_stmt_execute($updateStmt);
                            mysqli_stmt_close($updateStmt);
                        }

                        $_SESSION['rname'] = $loginName;
                        $_SESSION['retailer_logged_in'] = true;
                        $_SESSION['retailer_id'] = $loginName;
                        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Retailer logged in successfully.'];
                        mysqli_stmt_close($stmt);
                        mysqli_close($retailerConn);
                        header('Location: Rmain.php');
                        exit;
                    }
                }

                mysqli_stmt_close($stmt);
                $errors[] = 'Invalid retailer name or password.';
            }
        } else {
            $registerName = safeInput($_POST['rname']);
            $registerAddress = safeInput($_POST['radd']);
            $password = $_POST['rpass'] ?? '';
            $confirmPassword = $_POST['rconpass'] ?? '';

            if ($registerName === '') {
                $errors[] = 'Name is required.';
            }
            if ($registerAddress === '') {
                $errors[] = 'Address is required.';
            }
            if ($password === '' || $confirmPassword === '') {
                $errors[] = 'Password and confirm password are required.';
            }
            if ($password !== $confirmPassword) {
                $errors[] = 'Password and confirm password do not match.';
            }
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            }

            if (empty($errors)) {
                $hash = SecurityHelper::hashPassword($password);
                $stmt = mysqli_prepare($retailerConn, 'INSERT INTO rregister (rname, radd, rpass, rconpass) VALUES (?, ?, ?, ?)');
                mysqli_stmt_bind_param($stmt, 'ssss', $registerName, $registerAddress, $hash, $hash);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Retailer registered successfully. Please login.'];
                    mysqli_stmt_close($stmt);
                    mysqli_close($retailerConn);
                    header('Location: auth.php?role=retailer&mode=login');
                    exit;
                }
                $errors[] = 'Registration failed. The retailer name may already exist.';
                mysqli_stmt_close($stmt);
            }
        }
    }
}

if (!empty($errors)) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => implode(' ', $errors)];
    mysqli_close($retailerConn);
    $mode = (($_POST['action'] ?? '') === 'register') ? 'signup' : 'login';
    header('Location: auth.php?role=retailer&mode=' . $mode);
    exit;
}

include 'templates/header.php';
?>
<div class="row justify-content-center">
  <div class="col-xl-10">
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <?php if (!empty($messages)): ?>
      <div class="alert alert-info">
        <ul class="mb-0">
          <?php foreach ($messages as $message): ?>
            <li><?php echo htmlspecialchars($message); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h4 mb-3">Retailer Login</h2>
            <form action="Rlogin.php" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
              <input type="hidden" name="action" value="login">
              <div class="mb-3">
                <label for="rname" class="form-label">Retailer Name</label>
                <input id="rname" type="text" name="rname" class="form-control" value="<?php echo htmlspecialchars($loginName); ?>" required>
              </div>
              <div class="mb-3">
                <label for="rpass" class="form-label">Password</label>
                <input id="rpass" type="password" name="rpass" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary">Login</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h4 mb-3">New Retailer</h2>
            <p class="text-muted">Create your retailer account to manage products.</p>
            <form action="Rlogin.php" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
              <input type="hidden" name="action" value="register">
              <div class="mb-3">
                <label for="register_rname" class="form-label">Name</label>
                <input id="register_rname" type="text" name="rname" class="form-control" value="<?php echo htmlspecialchars($registerName); ?>" required>
              </div>
              <div class="mb-3">
                <label for="register_radd" class="form-label">Address</label>
                <input id="register_radd" type="text" name="radd" class="form-control" value="<?php echo htmlspecialchars($registerAddress); ?>" required>
              </div>
              <div class="mb-3">
                <label for="register_rpass" class="form-label">Password</label>
                <input id="register_rpass" type="password" name="rpass" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="register_rconpass" class="form-label">Confirm Password</label>
                <input id="register_rconpass" type="password" name="rconpass" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-success">Register</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'templates/footer.php';

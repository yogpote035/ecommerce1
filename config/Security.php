<?php
/**
 * Security Configuration
 * Constants and settings for application security
 */

// Session timeout (30 minutes in seconds)
define('SESSION_TIMEOUT', 1800);

// OTP settings
define('OTP_EXPIRY', 300);           // 5 minutes
define('OTP_MAX_ATTEMPTS', 3);       // Max OTP verification attempts

// Password settings
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 12]);

// Rate limiting
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 300);  // 5 minutes

// File upload settings
define('MAX_UPLOAD_SIZE', 5242880);   // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('PRODUCT_IMAGE_MIN_WIDTH', 300);
define('PRODUCT_IMAGE_MIN_HEIGHT', 300);

// Security headers
define('SECURITY_HEADERS', [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'Content-Security-Policy' => "default-src * 'unsafe-inline' 'unsafe-eval'; script-src * 'unsafe-inline' 'unsafe-eval'; style-src * 'unsafe-inline'; font-src * data: https:; img-src * data: https:; connect-src *; frame-src *; child-src *;"
]);

// Environment
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', APP_ENV === 'development');

/**
 * Set security headers
 */
function setSecurityHeaders() {
    foreach (SECURITY_HEADERS as $header => $value) {
        header("$header: $value");
    }
}

/**
 * Initialize security
 */
function initSecurity() {
    // Set security headers
    setSecurityHeaders();
    
    // Secure session configuration
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Set default error handler
    set_error_handler('customErrorHandler');
    set_exception_handler('customExceptionHandler');
}

/**
 * Custom error handler
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (APP_DEBUG) {
        echo "<div style='border: 1px solid red; padding: 10px; margin: 10px; background: #fee;'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline<br>";
        echo "</div>";
    } else {
        // In production, log error silently
        error_log("[{$errno}] {$errstr} in {$errfile}:{$errline}");
    }
    
    return true;
}

/**
 * Custom exception handler
 */
function customExceptionHandler($exception) {
    if (APP_DEBUG) {
        echo "<div style='border: 1px solid red; padding: 10px; margin: 10px; background: #fee;'>";
        echo "<strong>Exception:</strong> " . $exception->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        error_log("Exception: " . $exception->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        include 'errors/500.php';
    }
}

// Auto-initialize on include
initSecurity();
?>

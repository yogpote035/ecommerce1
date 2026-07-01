<?php
/**
 * SecurityHelper - Centralized security functions
 * Password hashing, CSRF tokens, OTP generation, input sanitization
 */

class SecurityHelper {
    
    // ====================================
    // PASSWORD MANAGEMENT
    // ====================================
    
    /**
     * Hash password using BCRYPT
     * @param string $password - Plain text password
     * @return string - Hashed password
     */
    public static function hashPassword($password) {
        $algo = defined('PASSWORD_HASH_ALGO') ? PASSWORD_HASH_ALGO : PASSWORD_BCRYPT;
        $options = defined('PASSWORD_HASH_OPTIONS') ? PASSWORD_HASH_OPTIONS : ['cost' => 12];
        return password_hash($password, $algo, $options);
    }
    
    /**
     * Verify plain password against hash
     * @param string $password - Plain text password
     * @param string $hash - Stored hash
     * @return bool - True if password matches
     */
    public static function verifyPassword($password, $hash) {
        if (empty($hash) || !self::isPasswordHash($hash)) {
            return false;
        }
        return password_verify($password, $hash);
    }
    
    /**
     * Detect whether a stored value is a password hash
     * @param string $hash
     * @return bool
     */
    public static function isPasswordHash($hash) {
        if (empty($hash)) {
            return false;
        }

        $info = password_get_info($hash);
        return $info['algo'] !== 0;
    }
    
    /**
     * Check if password hash needs rehashing
     * @param string $hash - Stored hash
     * @return bool - True if rehashing needed
     */
    public static function passwordNeedsRehash($hash) {
        $algo = defined('PASSWORD_HASH_ALGO') ? PASSWORD_HASH_ALGO : PASSWORD_BCRYPT;
        $options = defined('PASSWORD_HASH_OPTIONS') ? PASSWORD_HASH_OPTIONS : ['cost' => 12];
        return password_needs_rehash($hash, $algo, $options);
    }
    
    // ====================================
    // CSRF PROTECTION
    // ====================================
    
    /**
     * Generate or retrieve CSRF token
     * @return string - CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token from form submission
     * @param string $token - Token from form
     * @return bool - True if token valid
     */
    public static function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // ====================================
    // OTP GENERATION & VALIDATION
    // ====================================
    
    /**
     * Generate 6-digit OTP
     * @return string - 6-digit OTP code
     */
    public static function generateOTP() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate secure random token
     * @param int $length - Token length in bytes
     * @return string - Hex encoded random token
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    // ====================================
    // INPUT SANITIZATION
    // ====================================
    
    /**
     * Sanitize user input
     * @param mixed $input - Input to sanitize
     * @return string - Sanitized input
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        return htmlspecialchars(
            trim($input),
            ENT_QUOTES,
            'UTF-8'
        );
    }
    
    /**
     * Sanitize email
     * @param string $email - Email to sanitize
     * @return string - Sanitized email
     */
    public static function sanitizeEmail($email) {
        return strtolower(trim(filter_var($email, FILTER_SANITIZE_EMAIL)));
    }
    
    /**
     * Validate email format
     * @param string $email - Email to validate
     * @return bool - True if valid email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     * @param string $password - Password to validate
     * @return array - ['valid' => bool, 'errors' => array]
     */
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = 'Password must contain at least one special character (!@#$%^&*)';
        }
        
        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
    
    // ====================================
    // SESSION MANAGEMENT
    // ====================================
    
    /**
     * Regenerate session ID for security
     * @return bool - True if successful
     */
    public static function regenerateSessionID() {
        return session_regenerate_id(true);
    }
    
    /**
     * Check if session is valid and not expired
     * @param int $timeout - Session timeout in seconds (default: 1800 = 30 min)
     * @return bool - True if session valid
     */
    public static function isSessionValid($timeout = 1800) {
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $timeout) {
                session_destroy();
                return false;
            }
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Destroy session completely
     * @return bool - True if successful
     */
    public static function destroySession() {
        $_SESSION = [];
        
        if (ini_get('session.use_cookies') || ini_get('session.use_only_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        return session_destroy();
    }
    
    // ====================================
    // RATE LIMITING
    // ====================================
    
    /**
     * Check if action exceeds rate limit
     * @param string $action - Action identifier (e.g., 'login_attempt')
     * @param int $maxAttempts - Max attempts allowed
     * @param int $timeWindow - Time window in seconds
     * @return bool - True if within limit
     */
    public static function checkRateLimit($action, $maxAttempts = 5, $timeWindow = 300) {
        $key = 'ratelimit_' . $action . '_' . $_SERVER['REMOTE_ADDR'];
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'timestamp' => time()];
            return true;
        }
        
        $current = $_SESSION[$key];
        $elapsed = time() - $current['timestamp'];
        
        if ($elapsed > $timeWindow) {
            $_SESSION[$key] = ['count' => 1, 'timestamp' => time()];
            return true;
        }
        
        if ($current['count'] >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key]['count']++;
        return true;
    }
    
    /**
     * Reset rate limit
     * @param string $action - Action identifier
     */
    public static function resetRateLimit($action) {
        $key = 'ratelimit_' . $action . '_' . $_SERVER['REMOTE_ADDR'];
        unset($_SESSION[$key]);
    }
    
    // ====================================
    // IP & BROWSER VALIDATION
    // ====================================
    
    /**
     * Get client IP address
     * @return string - Client IP
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }
    }
    
    /**
     * Get user agent
     * @return string - User agent string
     */
    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    }
    
    // ====================================
    // FILE UPLOAD SECURITY
    // ====================================
    
    /**
     * Validate file upload
     * @param array $file - $_FILES array element
     * @param array $allowedTypes - Allowed MIME types
     * @param int $maxSize - Max file size in bytes
     * @return array - ['valid' => bool, 'error' => string]
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'error' => self::getUploadErrorMessage($file['error'])
            ];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return [
                'valid' => false,
                'error' => 'File size exceeds maximum allowed (' . round($maxSize / 1024 / 1024) . ' MB)'
            ];
        }
        
        // Check MIME type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                return [
                    'valid' => false,
                    'error' => 'Invalid file type: ' . $mimeType
                ];
            }
        }
        
        // Verify it's actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            return [
                'valid' => false,
                'error' => 'File is not a valid upload'
            ];
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    /**
     * Get upload error message
     * @param int $errorCode - PHP upload error code
     * @return string - Error message
     */
    private static function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
    
    // ====================================
    // LOGGING
    // ====================================
    
    /**
     * Log activity
     * @param mixed $conn - Database connection
     * @param string $action - Action performed
     * @param string $entityType - Type of entity
     * @param int $entityId - Entity ID
     * @param int|null $userId - User ID
     * @param string $userType - 'customer', 'admin', or 'retailer'
     */
    public static function logActivity($conn, $action, $entityType, $entityId = null, $userId = null, $userType = 'customer') {
        try {
            self::ensureActivityLogTable($conn);
            $ip = self::getClientIP();
            $userAgent = self::getUserAgent();
            $userId = $userId !== null ? (int)$userId : null;
            $entityId = $entityId !== null ? (int)$entityId : null;
            $userType = in_array($userType, ['customer', 'admin', 'retailer'], true) ? $userType : 'customer';
            
            $stmt = $conn->prepare("
                INSERT INTO activity_logs (user_id, user_type, action, entity_type, entity_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$stmt) {
                error_log('Activity log prepare failed: ' . $conn->error);
                return false;
            }
            
            $stmt->bind_param(
                "isssiss",
                $userId,
                $userType,
                $action,
                $entityType,
                $entityId,
                $ip,
                $userAgent
            );
            
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        } catch (Throwable $e) {
            // Silent fail for logging
            error_log('Activity log failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log error
     * @param mixed $conn - Database connection
     * @param string $message - Error message
     * @param string $code - Error code
     * @param string $file - File path
     * @param int $line - Line number
     * @param string $trace - Stack trace
     * @param string $severity - 'info', 'warning', 'error', 'critical'
     */
    public static function logError($conn, $message, $code, $file, $line, $trace = '', $severity = 'error') {
        try {
            self::ensureErrorLogTable($conn);
            $ip = self::getClientIP();
            $line = (int)$line;
            $severity = in_array($severity, ['info', 'warning', 'error', 'critical'], true) ? $severity : 'error';
            
            $stmt = $conn->prepare("
                INSERT INTO error_logs (error_message, error_code, file_path, line_number, trace, severity, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$stmt) {
                error_log('Error log prepare failed: ' . $conn->error);
                return false;
            }
            
            $stmt->bind_param(
                "sssisss",
                $message,
                $code,
                $file,
                $line,
                $trace,
                $severity,
                $ip
            );
            
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        } catch (Throwable $e) {
            // Silent fail for logging
            error_log('Error log failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function ensureActivityLogTable($conn) {
        $conn->query("CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            user_type ENUM('customer', 'admin', 'retailer') DEFAULT 'customer',
            action VARCHAR(255),
            entity_type VARCHAR(100),
            entity_id INT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_user_created (user_id, created_at),
            KEY idx_action (user_type, action)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $conn->query("ALTER TABLE activity_logs MODIFY user_type ENUM('customer', 'admin', 'retailer') DEFAULT 'customer'");

        $columns = [
            'user_id' => 'ALTER TABLE activity_logs ADD COLUMN user_id INT NULL AFTER id',
            'user_type' => "ALTER TABLE activity_logs ADD COLUMN user_type ENUM('customer', 'admin', 'retailer') DEFAULT 'customer' AFTER user_id",
            'action' => 'ALTER TABLE activity_logs ADD COLUMN action VARCHAR(255) NULL AFTER user_type',
            'entity_type' => 'ALTER TABLE activity_logs ADD COLUMN entity_type VARCHAR(100) NULL AFTER action',
            'entity_id' => 'ALTER TABLE activity_logs ADD COLUMN entity_id INT NULL AFTER entity_type',
            'ip_address' => 'ALTER TABLE activity_logs ADD COLUMN ip_address VARCHAR(45) NULL AFTER entity_id',
            'user_agent' => 'ALTER TABLE activity_logs ADD COLUMN user_agent TEXT NULL AFTER ip_address',
            'created_at' => 'ALTER TABLE activity_logs ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER user_agent',
        ];
        foreach ($columns as $column => $sql) {
            if (!self::columnExists($conn, 'activity_logs', $column)) {
                $conn->query($sql);
            }
        }
    }

    private static function ensureErrorLogTable($conn) {
        $conn->query("CREATE TABLE IF NOT EXISTS error_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            error_message TEXT,
            error_code VARCHAR(50),
            file_path VARCHAR(255),
            line_number INT,
            trace TEXT,
            severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'error',
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_severity_created (severity, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $conn->query("ALTER TABLE error_logs MODIFY severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'error'");

        $columns = [
            'error_message' => 'ALTER TABLE error_logs ADD COLUMN error_message TEXT NULL AFTER id',
            'error_code' => 'ALTER TABLE error_logs ADD COLUMN error_code VARCHAR(50) NULL AFTER error_message',
            'file_path' => 'ALTER TABLE error_logs ADD COLUMN file_path VARCHAR(255) NULL AFTER error_code',
            'line_number' => 'ALTER TABLE error_logs ADD COLUMN line_number INT NULL AFTER file_path',
            'trace' => 'ALTER TABLE error_logs ADD COLUMN trace TEXT NULL AFTER line_number',
            'severity' => "ALTER TABLE error_logs ADD COLUMN severity ENUM('info', 'warning', 'error', 'critical') DEFAULT 'error' AFTER trace",
            'ip_address' => 'ALTER TABLE error_logs ADD COLUMN ip_address VARCHAR(45) NULL AFTER severity',
            'created_at' => 'ALTER TABLE error_logs ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER ip_address',
        ];
        foreach ($columns as $column => $sql) {
            if (!self::columnExists($conn, 'error_logs', $column)) {
                $conn->query($sql);
            }
        }
    }

    private static function columnExists($conn, $table, $column) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return (int)$count > 0;
    }
}
?>

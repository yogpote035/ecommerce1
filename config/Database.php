<?php
/**
 * Database configuration for the e-commerce application.
 * Values may be overridden by environment variables.
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'Yogeshpo7@');
define('DB_NAME', getenv('DB_NAME') ?: 'ecommerce');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

/**
 * Get database connection settings array.
 *
 * @param string $dbname
 * @return array
 */
function getDatabaseConfig($dbname = DB_NAME) {
    return [
        'host' => DB_HOST,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'name' => $dbname,
        'charset' => DB_CHARSET,
    ];
}

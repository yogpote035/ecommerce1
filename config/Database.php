<?php
/**
 * Database configuration for the e-commerce application.
 * Values may be overridden by environment variables.
 */

define('DB_HOST', getenv('DB_HOST') ?: 'db.fr-roub1.bengt.wasmernet.com');
define('DB_PORT', getenv('DB_PORT') ?: 20184);
define('DB_USER', getenv('DB_USER') ?: 'user_78e7febe');
define('DB_PASS', getenv('DB_PASS') ?: 'pw_0JwxdIy7a64G0CEFQ0wxq4pcI6Yo1VE3');
define('DB_NAME', getenv('DB_NAME') ?: 'ecommerce_app1');
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
        'port' => DB_PORT,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'name' => $dbname,
        'charset' => DB_CHARSET,
    ];
}

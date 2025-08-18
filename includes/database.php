<?php
/**
 * Database configuration and connection for OUTSINC
 * Secure MySQL connection using MySQLi
 */

// Database configuration - Update these values for your environment
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'outsinc_user');
define('DB_PASSWORD', 'outsinc_password');
define('DB_NAME', 'outsinc_db');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * @return mysqli Database connection object
 * @throws Exception If connection fails
 */
function getDatabase() {
    static $connection = null;
    
    if ($connection === null) {
        try {
            // Create connection with error reporting
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            // Set charset
            $connection->set_charset(DB_CHARSET);
            
            // Ensure proper timezone
            $connection->query("SET time_zone = '+00:00'");
            
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    return $connection;
}

/**
 * Escape and prepare user input for database queries
 * @param string $input Raw user input
 * @return string Escaped input
 */
function escapeInput($input) {
    $db = getDatabase();
    return $db->real_escape_string(trim($input));
}

/**
 * Execute a prepared statement safely
 * @param string $query SQL query with placeholders
 * @param array $params Parameters to bind
 * @param string $types Parameter types (i, d, s, b)
 * @return mysqli_result|bool Query result
 */
function executeQuery($query, $params = [], $types = '') {
    $db = getDatabase();
    
    try {
        $stmt = $db->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result !== false ? $result : $stmt;
        
    } catch (mysqli_sql_exception $e) {
        error_log("Query execution failed: " . $e->getMessage());
        error_log("Query: " . $query);
        throw new Exception("Database operation failed. Please try again later.");
    }
}

/**
 * Get the last inserted ID
 * @return int Last insert ID
 */
function getLastInsertId() {
    $db = getDatabase();
    return $db->insert_id;
}

/**
 * Start a database transaction
 */
function beginTransaction() {
    $db = getDatabase();
    $db->autocommit(false);
}

/**
 * Commit a database transaction
 */
function commitTransaction() {
    $db = getDatabase();
    $db->commit();
    $db->autocommit(true);
}

/**
 * Rollback a database transaction
 */
function rollbackTransaction() {
    $db = getDatabase();
    $db->rollback();
    $db->autocommit(true);
}

/**
 * Close database connection
 */
function closeDatabase() {
    $db = getDatabase();
    if ($db) {
        $db->close();
    }
}

// Register shutdown function to close connection
register_shutdown_function('closeDatabase');
?>
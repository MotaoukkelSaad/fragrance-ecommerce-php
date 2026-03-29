<?php
// =====================================================
// FRAGRANCE BOUTIQUE - DATABASE CONFIGURATION
// =====================================================

// Display errors for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// =====================================================
// DATABASE CREDENTIALS FOR LOCAL DEVELOPMENT
// =====================================================

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_fragrance';

// =====================================================
// CREATE CONNECTION
// =====================================================

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// =====================================================
// CHECK CONNECTION
// =====================================================

if ($conn->connect_error) {
    die("❌ DATABASE CONNECTION FAILED!<br>" . $conn->connect_error);
}

// =====================================================
// SET CHARACTER SET
// =====================================================

if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
}

// =====================================================
// DEFINE SHIPPING COSTS
// =====================================================

define('SHIPPING_FREE', 0);
define('SHIPPING_EXPRESS', 35.00);

?>
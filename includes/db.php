<?php
// ------------------------------------------------------------
// Database connection file
// ------------------------------------------------------------
// This file creates a PDO database connection that can be used
// anywhere in the project by including this file.
// ------------------------------------------------------------

require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Show DB errors as exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,    // Fetch rows as associative arrays
            PDO::ATTR_EMULATE_PREPARES => false,                 // Use native prepared statements
        ]
    );
} catch (PDOException $e) {
    // Stop the application if database connection fails
    die('Database connection failed: ' . $e->getMessage());
}

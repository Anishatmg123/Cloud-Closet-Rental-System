<?php
/**
 * Database Connection Module
 * Cloud Closet Rental System
 *
 * This file establishes a connection to the MySQL database using PHP Data Objects (PDO).
 * It is designed to be reusable across all pages of the Cloud Closet Rental System.
 */

// 1. Database Configuration Parameters
$host = 'localhost';          // The server where the database is hosted
$db_name = 'rentaldb';        // Name of the database in phpMyAdmin
$username = 'root';           // Default XAMPP MySQL username
$password = '';               // Default XAMPP MySQL password (empty by default)
$charset = 'utf8mb4';         // Character set for handling special characters safely

// 2. Data Source Name (DSN)
// The DSN specifies the database driver (mysql), host, database name, and charset
$dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";

// 3. PDO Connection Options
// These options customize how PDO behaves:
// - ATTR_ERRMODE => ERRMODE_EXCEPTION: Tells PDO to throw exceptions when SQL errors occur.
// - ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC: Automatically fetches database results as associative arrays.
// - ATTR_EMULATE_PREPARES => false: Disables emulation of prepared statements to use real prepared statements (safer against SQL injection).
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 4. Establish the connection
    // We instantiate a new PDO object and store it in the $pdo variable.
    // This $pdo object will be used to execute queries on other pages.
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // To test the connection in your browser, you can temporarily uncomment the line below:
    // echo "Connected successfully to the Cloud Closet database!";
} catch (PDOException $e) {
    // 5. Error Handling
    // If the connection fails, PDO throws a PDOException.
    // We catch it and stop script execution using die() to display a user-friendly message.
    die("Database connection failed: " . $e->getMessage());
}

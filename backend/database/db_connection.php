<?php
// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    if ($env !== false) {
        foreach ($env as $key => $value) {
            putenv("{$key}={$value}");
        }
    }
}

// Database Configuration from environment variables
// Database Configuration
$servername = "77.37.35.50"; // Use "localhost" for Hostinger
$username = "u153805899_careers_db"; // Your database username
$password = "Sofindexcareer213."; // Your database password
$dbname =   "u153805899_careers_db"; // Your database name


if (!$servername || !$username || !$dbname) {
    die("Database connection failed: One or more required environment variables (servername, username, dbname) are missing. Please check your .env file.");
}

$conn = @new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

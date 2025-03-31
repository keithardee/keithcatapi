<?php
$host = "localhost"; // Change if using a remote database
$dbname = "purrfectpaircat_db"; // Your database name
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log the error (recommended for production)
    error_log("Database connection failed: " . $e->getMessage());

    // Send a clean JSON response
    echo json_encode([
        'error' => true,
        'message' => 'Database connection failed. Please try again later.'
    ]);
    exit;
}
?>

<?php
require 'config.php';
header("Content-Type: application/json");

$response = [];

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method!");
    $response['error'] = true;
    $response['message'] = "Invalid request method!";
    echo json_encode($response);
    exit;
}

// Read JSON input
$json = file_get_contents("php://input");
if ($json === false) {
    error_log("Failed to read JSON input.");
    $response['error'] = true;
    $response['message'] = "Invalid JSON input!";
    echo json_encode($response);
    exit;
}

// Decode JSON
$data = json_decode($json, true);
if ($data === null) {
    error_log("JSON decode failed: " . json_last_error_msg());
    $response['error'] = true;
    $response['message'] = "Invalid JSON format!";
    echo json_encode($response);
    exit;
}

// Log received data
error_log("Received data: " . print_r($data, true));

// Check required fields
$requiredFields = ['user_id', 'name', 'breed', 'gender', 'age', 'adopt_status', 'vaccination', 'adddate', 'imageUri'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        error_log("Missing required field: $field");
        $response['error'] = true;
        $response['message'] = "Missing required field: $field";
        echo json_encode($response);
        exit;
    }
}

try {
    // Check if user exists
    $userCheck = $conn->prepare("SELECT id FROM users WHERE id = :user_id LIMIT 1");
    $userCheck->bindParam(":user_id", $data['user_id'], PDO::PARAM_INT);
    $userCheck->execute();

    if ($userCheck->rowCount() == 0) {
        error_log("User not found: " . $data['user_id']);
        $response['error'] = true;
        $response['message'] = "User not found!";
        echo json_encode($response);
        exit;
    }

    // Insert pet data
    $stmt = $conn->prepare("INSERT INTO posts (user_id, name, breed, gender, age, adopt_status, vaccination, adddate, imageUri)
    VALUES (:user_id, :name, :breed, :gender, :age, :adopt_status, :vaccination, :adddate, :imageUri)");
    
    $stmt->execute([
        ':user_id' => $data['user_id'],
        ':name' => $data['name'],
        ':breed' => $data['breed'],
        ':gender' => $data['gender'],
        ':age' => $data['age'],
        ':adopt_status' => $data['adopt_status'],
        ':vaccination' => $data['vaccination'],
        ':adddate' => $data['adddate'],
        ':imageUri' => $data['imageUri']
    ]);

    error_log("Pet added successfully for user_id: " . $data['user_id']);

    $response['error'] = false;
    $response['message'] = "Cat added successfully!";
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $response['error'] = true;
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>

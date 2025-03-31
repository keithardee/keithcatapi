<?php
require 'config.php';
header("Content-Type: application/json");

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read raw JSON input
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // Debugging: Check received JSON data
    file_put_contents("debug.log", "Received JSON: " . $json . PHP_EOL, FILE_APPEND);

    if (
        isset($data['fullname'], $data['email'], $data['contactNumber'],
        $data['facebookName'], $data['homeAddress'], $data['password'])
    ) {
        // Sanitize input
        $fullname = htmlspecialchars(strip_tags($data['fullname']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $contactNumber = htmlspecialchars(strip_tags($data['contactNumber']));
        $facebookName = htmlspecialchars(strip_tags($data['facebookName']));
        $homeAddress = htmlspecialchars(strip_tags($data['homeAddress']));
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        try {
            // Check if email already exists
            $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkEmail->execute([$email]);

            if ($checkEmail->rowCount() > 0) {
                $response['error'] = true;
                $response['message'] = "Email already exists!";
            } else {
                // Insert into users table
                $query = $conn->prepare("INSERT INTO users (fullname, email, contactNumber, facebookName, homeAddress, password) VALUES (?, ?, ?, ?, ?, ?)");

                if ($query->execute([$fullname, $email, $contactNumber, $facebookName, $homeAddress, $password])) {
                    $response['error'] = false;
                    $response['message'] = "Registration successful!";
                } else {
                    $response['error'] = true;
                    $response['message'] = "Failed to register user.";
                }
            }
        } catch (PDOException $e) {
            $response['error'] = true;
            $response['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Missing required fields!";
    }
} else {
    $response['error'] = true;
    $response['message'] = "Invalid request method!";
}

echo json_encode($response);
?>

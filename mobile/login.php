<?php
require 'config.php';
require 'authMiddleware.php'; // Import AuthMiddleware for generateToken
header("Content-Type: application/json");

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (isset($data['email'], $data['password'])) {
        $email = htmlspecialchars(strip_tags($data['email']));
        $password = $data['password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['error'] = true;
            $response['message'] = "Invalid email format!";
            echo json_encode($response);
            exit();
        }

        try {
            // Fetch user details from database
            $query = $conn->prepare("SELECT id, fullname, email, contactNumber, facebookName, homeAddress, password FROM users WHERE email = ?");
            $query->execute([$email]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    // Generate JWT token using AuthMiddleware's generateToken function
                    $token = AuthMiddleware::generateToken($user['id']); // Pass the user ID
                    
                    $response['error'] = false;
                    $response['message'] = "Login successful!";
                    $response['token'] = $token; // Include the JWT token in the response
                    $response['user'] = [
                        "id" => $user['id'],
                        "fullname" => $user['fullname'],
                        "email" => $user['email'],
                        "contactNumber" => $user['contactNumber'],
                        "facebookName" => $user['facebookName'],
                        "homeAddress" => $user['homeAddress']
                    ];
                } else {
                    http_response_code(401); // Unauthorized
                    $response['error'] = true;
                    $response['message'] = "Incorrect password!";
                }
            } else {
                http_response_code(404); // Not Found
                $response['error'] = true;
                $response['message'] = "Email not found!";
            }
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            error_log($e->getMessage(), 0); // Log the error for debugging
            $response['error'] = true;
            $response['message'] = "Internal server error.";
        }
    } else {
        http_response_code(400); // Bad Request
        $response['error'] = true;
        $response['message'] = "Missing email or password!";
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response['error'] = true;
    $response['message'] = "Invalid request method!";
}

echo json_encode($response);
?>
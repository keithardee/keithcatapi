<?php
require 'config.php';
header("Content-Type: application/json");

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (isset($data['email'], $data['password'])) {
        $email = htmlspecialchars(strip_tags($data['email']));
        $password = $data['password']; // No need to hash

        try {
            // Fetch user details from database
            $query = $conn->prepare("SELECT id, fullname, email, contactNumber, facebookName, homeAddress, password FROM users WHERE email = ?");
            $query->execute([$email]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $response['error'] = false;
                $response['message'] = "Login successful!";
                $response['user'] = [
                    "id" => $user['id'],
                    "fullname" => $user['fullname'],
                    "email" => $user['email'],
                    "contactNumber" => $user['contactNumber'],
                    "facebookName" => $user['facebookName'],
                    "homeAddress" => $user['homeAddress']
                ];
            } else {
                $response['error'] = true;
                $response['message'] = "Invalid email or password!";
            }
        } catch (PDOException $e) {
            $response['error'] = true;
            $response['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Missing email or password!";
    }
} else {
    $response['error'] = true;
    $response['message'] = "Invalid request method!";
}

echo json_encode($response);
?>

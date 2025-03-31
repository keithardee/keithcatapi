<?php
require 'config.php';
header("Content-Type: application/json");

$response = [];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['error'] = true;
        $response['message'] = "Invalid request method!";
        echo json_encode($response);
        exit;
    }

    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    $requiredFields = ['user_id', 'name', 'breed', 'gender', 'age', 'adopt_status', 'vaccination', 'adddate', 'imageUri'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            $response['error'] = true;
            $response['message'] = "Missing required field: $field";
            echo json_encode($response);
            exit;
        }
    }

    try {
        $userCheck = $conn->prepare("SELECT id FROM users WHERE id = :user_id LIMIT 1");
        $userCheck->bindParam(":user_id", $data['user_id'], PDO::PARAM_INT);
        $userCheck->execute();

        if ($userCheck->rowCount() == 0) {
            $response['error'] = true;
            $response['message'] = "User not found!";
            echo json_encode($response);
            exit;
        }

        // Insert into the posts table
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

        $response['error'] = false;
        $response['message'] = "Cat added successfully!";
    } catch (PDOException $e) {
        $response['error'] = true;
        $response['message'] = "Database error: " . $e->getMessage();
    }

    echo json_encode($response);

?>
<?php
require 'config.php';
header("Content-Type: application/json");

$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        if (isset($data['id'])) {
            try {
                $stmt = $conn->prepare("DELETE FROM posts WHERE id = :id");
                $stmt->execute([':id' => $data['id']]);

                $response['error'] = false;
                $response['message'] = "Cat deleted successfully!";
            } catch (PDOException $e) {
                $response['error'] = true;
                $response['message'] = "Database error: " . $e->getMessage();
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Missing cat ID!";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid request method!";
    }

    echo json_encode($response);

?>
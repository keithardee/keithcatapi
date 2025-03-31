<?php
require 'config.php';
header("Content-Type: application/json");

$response = [];

    $status = $_GET['status'] ?? 'approved'; // Default to 'approved'
    $user_id = $_GET['user_id'] ?? null; // Get user ID (for filtering pending pets)

    try {
        if ($status === 'pending' && $user_id) {
            // Fetch only the pending posts of the logged-in user
            $stmt = $conn->prepare("SELECT * FROM posts WHERE status = :status AND user_id = :user_id ORDER BY created_at DESC");
            $stmt->bindParam(":status", $status, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        } else {
            // Fetch approved posts for HomeFragment
            $stmt = $conn->prepare("SELECT * FROM posts WHERE status = :status ORDER BY created_at DESC");
            $stmt->bindParam(":status", $status, PDO::PARAM_STR);
        }

        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'error' => false,
            'message' => 'Posts retrieved successfully',
            'data' => $posts
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'error' => true,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }

?>
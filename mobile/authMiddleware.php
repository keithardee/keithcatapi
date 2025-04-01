<?php
require __DIR__ . '/../vendor/autoload.php'; // Include Composer autoloader
use Firebase\JWT\JWT; // Import the JWT class

class AuthMiddleware {
    // Secure key for encoding/decoding
    private static $key = "purrfect"; // Replace with a strong, secret key

    // Generate a JWT Token
    public static function generateToken($userId) {
        $issuedAt = time(); // Current time
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour
        $payload = [
            'iat' => $issuedAt, // Issued at
            'exp' => $expirationTime, // Expiration time
            'id' => $userId // Custom claim: User ID
        ];

        return JWT::encode($payload, self::$key, 'HS256'); // Generate and return the token
    }

    // Verify a JWT Token
    public static function verifyToken($headers) {
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization']; // Retrieve the Authorization header
            $arr = explode(" ", $authHeader); // Split "Bearer <token>"
            $jwt = $arr[1]; // Get the token part

            if ($jwt) {
                try {
                    $decoded = JWT::decode($jwt, self::$key, ['HS256']); // Decode the token
                    return $decoded; // Token is valid, return decoded payload
                } catch (Exception $e) {
                    // Handle invalid token
                    http_response_code(401);
                    echo json_encode(["message" => "Access denied. Invalid token."]);
                    exit();
                }
            }
        }
        // Handle missing token
        http_response_code(401);
        echo json_encode(["message" => "Access denied. Token not provided."]);
        exit();
    }
}
?>
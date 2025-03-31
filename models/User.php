<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($fullname, $email, $contactNumber, $facebookName, $homeAddress, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (fullname, email, contactNumber, facebookName, homeAddress, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$fullname, $email, $contactNumber, $facebookName, $homeAddress, $hashedPassword]);
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($email, $newPassword) {
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$newPassword, $email]);
    }
}
?>

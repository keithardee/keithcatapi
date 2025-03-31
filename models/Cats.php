<?php
require_once __DIR__ . '/../config/Database.php';

class Cats {
    private $conn;
    private $table = "posts";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllCats() {
        $sql = "SELECT * FROM " . $this->table;
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addCat($name, $breed, $gender, $age, $adopt, $vaccination, $adddate, $imageUri) {
        $sql = "INSERT INTO " . $this->table . " (user_id, name, breed, gender, age, adopt, vaccination, adddate, imageUri)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssss", $name, $breed, $gender, $age, $adopt, $vaccination, $adddate, $imageUri);
        return $stmt->execute();
    }

    public function updateCat($id, $name, $breed, $gender, $age, $adopt, $vaccination, $adddate, $imageUri) {
        $sql = "UPDATE " . $this->table . " SET
                user_id =?, name = ?, breed = ?, gender = ?, age = ?, adopt = ?, vaccination = ?, adddate = ?, imageUri = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $name, $breed, $gender, $age, $adopt, $vaccination, $adddate, $imageUri, $id);
        return $stmt->execute();
    }

    public function deleteCat($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>

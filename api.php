<?php
require_once __DIR__ . '/mobile/config.php';
header("Content-Type: application/json");

$response = [];

$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$endpoint = trim(str_replace($scriptName, '', $requestUri), '/');

if (empty($endpoint)) {
    $response['error'] = true;
    $response['message'] = "Request parameter is missing!";
    echo json_encode($response);
    exit;
}

file_put_contents("debug.log", "Extracted endpoint: " . $endpoint . PHP_EOL, FILE_APPEND);
file_put_contents("debug.log", "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . PHP_EOL, FILE_APPEND);

switch ($endpoint) {
    case 'login':
        login($conn);
        break;
    case 'register':
        register($conn);
        break;
    case 'getPosts':
        getPosts($conn);
        break;
    case 'addCat':
        addCat($conn);
        break;
    case 'updateCat':
        updateCat($conn);
        break;
    case 'deleteCat':
        deleteCat($conn);
        break;
    default:
        $response['error'] = true;
        $response['message'] = "Invalid API request: $endpoint";
        echo json_encode($response);
        break;
}


function login($conn) {
    global $response;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        if (isset($data['email'], $data['password'])) {
            $email = htmlspecialchars(strip_tags($data['email']));
            $password = $data['password'];

            try {
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
}

function register($conn) {
    global $response;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        if (isset($data['fullname'], $data['email'], $data['contactNumber'], $data['facebookName'], $data['homeAddress'], $data['password'])) {
            $fullname = htmlspecialchars(strip_tags($data['fullname']));
            $email = htmlspecialchars(strip_tags($data['email']));
            $contactNumber = htmlspecialchars(strip_tags($data['contactNumber']));
            $facebookName = htmlspecialchars(strip_tags($data['facebookName']));
            $homeAddress = htmlspecialchars(strip_tags($data['homeAddress']));
            $password = password_hash($data['password'], PASSWORD_BCRYPT);

            try {
                $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $checkEmail->execute([$email]);

                if ($checkEmail->rowCount() > 0) {
                    $response['error'] = true;
                    $response['message'] = "Email already exists!";
                } else {
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
}

// Function to add a cat
function addCat($conn) {
    global $response;

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
}


// Function to update a cat
function updateCat($conn) {
    global $response;

    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        if (isset($data['id'], $data['name'], $data['breed'], $data['gender'], $data['age'], $data['adopt_status'], $data['vaccination'], $data['adddate'], $data['imageUri'])) {
            try {
                $stmt = $conn->prepare("UPDATE posts SET name = :name, breed = :breed, gender = :gender, age = :age, adopt_status = :adopt_status, vaccination = :vaccination, adddate = :adddate, imageUri = :imageUri WHERE id = :id");

                $stmt->execute([
                    ':id' => $data['id'],
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
                $response['message'] = "Cat updated successfully!";
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
}

// Function to delete a cat
function deleteCat($conn) {
    global $response;

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
}

function getPosts($conn) {
    global $response;

    $status = $_GET['status'] ?? 'approved'; // Default to 'approved'

    try {
        // Prepare SQL query with correct named parameter
        $stmt = $conn->prepare("SELECT * FROM posts WHERE status = :status ORDER BY created_at DESC");
        $stmt->bindParam(":status", $status, PDO::PARAM_STR); // Bind parameter
        $stmt->execute();

        // Fetch all posts
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
}

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/Database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get single user
            $stmt = $user->getById($_GET['id']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $user_item = array(
                    "id" => $row['id'],
                    "email" => $row['email'],
                    "phone" => $row['phone'],
                    "first_name" => $row['first_name'],
                    "last_name" => $row['last_name'],
                    "full_name" => $row['first_name'] . ' ' . $row['last_name'],
                    "date_of_birth" => $row['date_of_birth'],
                    "gender" => $row['gender'],
                    "address" => $row['address'],
                    "emergency_contact_name" => $row['emergency_contact_name'],
                    "emergency_contact_phone" => $row['emergency_contact_phone'],
                    "profile_image" => $row['profile_image'],
                    "is_active" => $row['is_active'],
                    "created_at" => $row['created_at'],
                    "updated_at" => $row['updated_at']
                );

                http_response_code(200);
                echo json_encode($user_item);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "User not found"));
            }
        } elseif (isset($_GET['email'])) {
            // Get user by email
            $stmt = $user->getByEmail($_GET['email']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $user_item = array(
                    "id" => $row['id'],
                    "email" => $row['email'],
                    "phone" => $row['phone'],
                    "first_name" => $row['first_name'],
                    "last_name" => $row['last_name'],
                    "full_name" => $row['first_name'] . ' ' . $row['last_name'],
                    "date_of_birth" => $row['date_of_birth'],
                    "gender" => $row['gender'],
                    "address" => $row['address'],
                    "emergency_contact_name" => $row['emergency_contact_name'],
                    "emergency_contact_phone" => $row['emergency_contact_phone'],
                    "profile_image" => $row['profile_image'],
                    "is_active" => $row['is_active'],
                    "created_at" => $row['created_at'],
                    "updated_at" => $row['updated_at']
                );

                http_response_code(200);
                echo json_encode($user_item);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "User not found"));
            }
        } else {
            // Get all users
            $stmt = $user->getAll();
            $users = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user_item = array(
                    "id" => $row['id'],
                    "email" => $row['email'],
                    "phone" => $row['phone'],
                    "first_name" => $row['first_name'],
                    "last_name" => $row['last_name'],
                    "full_name" => $row['first_name'] . ' ' . $row['last_name'],
                    "date_of_birth" => $row['date_of_birth'],
                    "gender" => $row['gender'],
                    "address" => $row['address'],
                    "emergency_contact_name" => $row['emergency_contact_name'],
                    "emergency_contact_phone" => $row['emergency_contact_phone'],
                    "profile_image" => $row['profile_image'],
                    "is_active" => $row['is_active'],
                    "created_at" => $row['created_at'],
                    "updated_at" => $row['updated_at']
                );
                array_push($users, $user_item);
            }

            http_response_code(200);
            echo json_encode($users);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Server error: " . $e->getMessage()));
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create new user
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->email) && !empty($data->first_name) && !empty($data->last_name)) {
        $user->email = $data->email;
        $user->password_hash = isset($data->password) ? password_hash($data->password, PASSWORD_DEFAULT) : null;
        $user->phone = $data->phone ?? null;
        $user->first_name = $data->first_name;
        $user->last_name = $data->last_name;
        $user->date_of_birth = $data->date_of_birth ?? null;
        $user->gender = $data->gender ?? null;
        $user->address = $data->address ?? null;
        $user->emergency_contact_name = $data->emergency_contact_name ?? null;
        $user->emergency_contact_phone = $data->emergency_contact_phone ?? null;
        $user->profile_image = $data->profile_image ?? null;

        if ($user->create()) {
            // Get the created user
            $stmt = $user->getById($user->id);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $user_item = array(
                "id" => $row['id'],
                "email" => $row['email'],
                "phone" => $row['phone'],
                "first_name" => $row['first_name'],
                "last_name" => $row['last_name'],
                "full_name" => $row['first_name'] . ' ' . $row['last_name'],
                "date_of_birth" => $row['date_of_birth'],
                "gender" => $row['gender'],
                "address" => $row['address'],
                "emergency_contact_name" => $row['emergency_contact_name'],
                "emergency_contact_phone" => $row['emergency_contact_phone'],
                "profile_image" => $row['profile_image'],
                "is_active" => $row['is_active'],
                "created_at" => $row['created_at'],
                "updated_at" => $row['updated_at']
            );

            http_response_code(201);
            echo json_encode($user_item);
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create user"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Incomplete data. Email, first name, and last name are required."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed"));
}
?>
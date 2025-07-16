<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/Database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->connect();

if($db === null) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Database connection failed."
    ));
    exit();
}

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(empty($data->user_id)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "User ID is required."
    ));
    exit();
}

if(!empty($data->email) && !$user->isValidEmail($data->email)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid email format."
    ));
    exit();
}

if(!empty($data->phone) && !$user->isValidPhone($data->phone)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid phone number format."
    ));
    exit();
}

$valid_genders = ['male', 'female', 'other'];
if(!empty($data->gender) && !in_array($data->gender, $valid_genders)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid gender value."
    ));
    exit();
}

if(!empty($data->email)) {
    $user->email = $data->email;
    if($user->emailExistsForOtherUser($data->user_id)) {
        http_response_code(409);
        echo json_encode(array(
            "success" => false,
            "message" => "Email already exists for another user."
        ));
        exit();
    }
}

$user->id = $data->user_id;
$user->email = !empty($data->email) ? $data->email : null;
$user->phone = !empty($data->phone) ? $data->phone : null;
$user->first_name = !empty($data->first_name) ? $data->first_name : null;
$user->last_name = !empty($data->last_name) ? $data->last_name : null;
$user->date_of_birth = !empty($data->date_of_birth) ? $data->date_of_birth : null;
$user->gender = !empty($data->gender) ? $data->gender : null;
$user->address = !empty($data->address) ? $data->address : null;
$user->emergency_contact_name = !empty($data->emergency_contact_name) ? $data->emergency_contact_name : null;
$user->emergency_contact_phone = !empty($data->emergency_contact_phone) ? $data->emergency_contact_phone : null;
$user->profile_image = !empty($data->profile_image) ? $data->profile_image : null;

if($user->updateProfile()) {
    $updated_user = $user->getUserById($data->user_id);
    
    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "message" => "Profile updated successfully.",
        "data" => $updated_user
    ));
} else {
    http_response_code(503);
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to update profile. Please try again."
    ));
}
?>
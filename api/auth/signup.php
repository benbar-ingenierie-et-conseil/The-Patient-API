<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php';
include_once '../../models/User.php';

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

$required_fields = ['email', 'password', 'first_name', 'last_name', 'phone', 'emergency_contact_name', 'emergency_contact_phone'];
$missing_fields = [];

foreach($required_fields as $field) {
    if(empty($data->$field)) {
        $missing_fields[] = $field;
    }
}

if(!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Missing required fields: " . implode(', ', $missing_fields)
    ));
    exit();
}

if(!$user->isValidEmail($data->email)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid email format."
    ));
    exit();
}

if(!$user->isValidPhone($data->phone)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid phone number format."
    ));
    exit();
}

if(strlen($data->password) < 6) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Password must be at least 6 characters long."
    ));
    exit();
}

$valid_genders = ['male', 'female'];
if(!empty($data->gender) && !in_array($data->gender, $valid_genders)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid gender value."
    ));
    exit();
}

$user->email = $data->email;
if($user->emailExists()) {
    http_response_code(409);
    echo json_encode(array(
        "success" => false,
        "message" => "Email already exists. Please use a different email."
    ));
    exit();
}

$user->email = $data->email;
$user->password_hash = password_hash($data->password, PASSWORD_DEFAULT);
$user->phone = $data->phone;
$user->first_name = $data->first_name;
$user->last_name = $data->last_name;
$user->date_of_birth = !empty($data->date_of_birth) ? $data->date_of_birth : null;
$user->gender = !empty($data->gender) ? $data->gender : null;
$user->address = !empty($data->address) ? $data->address : null;
$user->emergency_contact_name = $data->emergency_contact_name;
$user->emergency_contact_phone = $data->emergency_contact_phone;

if($user->create()) {
    http_response_code(201);
    echo json_encode(array(
        "success" => true,
        "message" => "User account created successfully.",
        "data" => array(
            "user_id" => $user->id,
            "email" => $user->email,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "phone" => $user->phone
        )
    ));
} else {
    http_response_code(503);
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to create user account. Please try again."
    ));
}
?>
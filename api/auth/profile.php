<?php
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

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

if(empty($user_id)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "User ID is required."
    ));
    exit();
}

$user_data = $user->getUserById($user_id);

if($user_data) {
    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "message" => "User profile retrieved successfully.",
        "data" => $user_data
    ));
} else {
    http_response_code(404);
    echo json_encode(array(
        "success" => false,
        "message" => "User not found."
    ));
}
?>
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

if(empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Email and password are required."
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

$user->email = $data->email;

if($user->emailExists()) {
    if(password_verify($data->password, $user->password_hash)) {
        
        $session_token = bin2hex(random_bytes(32));
        
        $user_data = $user->getUserById($user->id);
        
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Login successful.",
            "data" => array(
                "user_id" => $user->id,
                "email" => $user->email,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "phone" => $user->phone,
                "session_token" => $session_token,
                "user_profile" => $user_data
            )
        ));
    } else {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid email or password."
        ));
    }
} else {
    http_response_code(401);
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid email or password."
    ));
}
?>
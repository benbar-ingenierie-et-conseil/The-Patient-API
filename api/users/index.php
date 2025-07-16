<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/Database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing user ID"]);
    exit();
}

$user->id = $data->id;
$user->email = $data->email ?? null;
$user->phone = $data->phone ?? null;
$user->first_name = $data->first_name ?? null;
$user->last_name = $data->last_name ?? null;
$user->date_of_birth = $data->date_of_birth ?? null;
$user->gender = $data->gender ?? null;
$user->address = $data->address ?? null;
$user->emergency_contact_name = $data->emergency_contact_name ?? null;
$user->emergency_contact_phone = $data->emergency_contact_phone ?? null;
$user->profile_image = $data->profile_image ?? null;

if ($user->update()) {
    $stmt = $user->getById($user->id);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    http_response_code(200);
    echo json_encode(["data" => $row]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to update user"]);
}
?>

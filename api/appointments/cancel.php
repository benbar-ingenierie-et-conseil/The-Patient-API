<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/Database.php';
include_once '../models/Appointment.php';

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id) && !empty($data->cancellation_reason)) {
        $appointment->id = $data->id;

        if ($appointment->cancel($data->cancellation_reason)) {
            http_response_code(200);
            echo json_encode(array("message" => "Appointment cancelled successfully"));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to cancel appointment"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Appointment ID and cancellation reason required"));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed"));
}
?>
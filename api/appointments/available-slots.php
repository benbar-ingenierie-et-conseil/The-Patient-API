<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['doctor_id']) && isset($_GET['date'])) {
        $available_slots = $appointment->getAvailableTimeSlots($_GET['doctor_id'], $_GET['date']);
        
        http_response_code(200);
        echo json_encode(array_values($available_slots));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Doctor ID and date required"));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed"));
}
?>
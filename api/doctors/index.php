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
include_once '../models/Doctor.php';

$database = new Database();
$db = $database->getConnection();
$doctor = new Doctor($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get single doctor
            $stmt = $doctor->getById($_GET['id']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $doctor_item = array(
                    "id" => $row['id'],
                    "first_name" => $row['first_name'],
                    "last_name" => $row['last_name'],
                    "name" => $row['first_name'] . ' ' . $row['last_name'],
                    "specialization" => $row['specialization'],
                    "specialty" => $row['specialization'], 
                    "email" => $row['email'],
                    "phone" => $row['phone'],
                    "license_number" => $row['license_number'],
                    "office_address" => $row['office_address'],
                    "biography" => $row['biography'],
                    "profile_image" => $row['profile_image'],
                    "consultation_fee" => $row['consultation_fee'],
                    "is_active" => $row['is_active'],
                    "created_at" => $row['created_at'],
                    "updated_at" => $row['updated_at']
                );

                http_response_code(200);
                echo json_encode($doctor_item);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Doctor not found"));
            }
        } elseif (isset($_GET['specialization']) || isset($_GET['specialty'])) {
            // Get doctors by specialization (support both parameter names)
            $specialization = $_GET['specialization'] ?? $_GET['specialty'];
            $stmt = $doctor->getBySpecialization($specialization);
            $doctors = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $doctor_item = array(
                    "id" => $row['id'],
                    "first_name" => $row['first_name'],
                    "last_name" => $row['last_name'],
                    "name" => $row['first_name'] . ' ' . $row['last_name'],
                    "specialization" => $row['specialization'],
                    "specialty" => $row['specialization'],
                    "email" => $row['email'],
                    "phone" => $row['phone'],
                    "license_number" => $row['license_number'],
                    "office_address" => $row['office_address'],
                    "biography" => $row['biography'],
                    "profile_image" => $row['profile_image'],
                    "consultation_fee" => $row['consultation_fee'],
                    "is_active" => $row['is_active'],
                    "created_at" => $row['created_at'],
                    "updated_at" => $row['updated_at']
                );
                array_push($doctors, $doctor_item);
            }

            http_response_code(200);
            echo json_encode($doctors);
        } else {
            // Get all doctors
            $stmt = $doctor->getAll();
            $doctors = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $doctor_item = array(
                    "id" => $row['id'],
                    "first_name" => $row['first_name'],
                    "last_name" => $row['last_name'],
                    "name" => $row['first_name'] . ' ' . $row['last_name'],
                    "specialization" => $row['specialization'],
                    "specialty" => $row['specialization'],
                    "email" => $row['email'],
                    "phone" => $row['phone'],
                    "license_number" => $row['license_number'],
                    "office_address" => $row['office_address'],
                    "biography" => $row['biography'],
                    "profile_image" => $row['profile_image'],
                    "consultation_fee" => $row['consultation_fee'],
                    "is_active" => $row['is_active'],
                    "created_at" => $row['created_at'],
                    "updated_at" => $row['updated_at']
                );
                array_push($doctors, $doctor_item);
            }

            http_response_code(200);
            echo json_encode($doctors);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Server error: " . $e->getMessage()));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed"));
}
?>
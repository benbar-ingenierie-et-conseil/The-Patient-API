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
include_once '../models/Consultation.php';

$database = new Database();
$db = $database->getConnection();
$consultation = new Consultation($db);

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['patient_id'])) {
                // Get consultations for a specific patient
                $stmt = $consultation->getByPatientId($_GET['patient_id']);
                $consultations = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $consultation_item = array(
                        "id" => $row['id'],
                        "appointment_id" => $row['appointment_id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "consultation_date" => $row['consultation_date'],
                        "consultation_time" => $row['consultation_time'],
                        "symptoms" => $row['symptoms'],
                        "diagnosis" => $row['diagnosis'],
                        "treatment_plan" => $row['treatment_plan'],
                        "follow_up_instructions" => $row['follow_up_instructions'],
                        "next_appointment_recommended" => $row['next_appointment_recommended'],
                        "consultation_fee" => $row['consultation_fee'],
                        "status" => $row['status'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "doctor_email" => $row['doctor_email'],
                        "doctor_phone" => $row['doctor_phone'],
                        "patient_name" => $row['patient_name'],
                        "patient_email" => $row['patient_email'],
                        "patient_phone" => $row['patient_phone']
                    );
                    array_push($consultations, $consultation_item);
                }

                http_response_code(200);
                echo json_encode($consultations);
            } elseif (isset($_GET['doctor_id'])) {
                // Get consultations for a specific doctor
                $stmt = $consultation->getByDoctorId($_GET['doctor_id']);
                $consultations = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $consultation_item = array(
                        "id" => $row['id'],
                        "appointment_id" => $row['appointment_id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "consultation_date" => $row['consultation_date'],
                        "consultation_time" => $row['consultation_time'],
                        "symptoms" => $row['symptoms'],
                        "diagnosis" => $row['diagnosis'],
                        "treatment_plan" => $row['treatment_plan'],
                        "follow_up_instructions" => $row['follow_up_instructions'],
                        "next_appointment_recommended" => $row['next_appointment_recommended'],
                        "consultation_fee" => $row['consultation_fee'],
                        "status" => $row['status'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "patient_name" => $row['patient_name'],
                        "patient_email" => $row['patient_email'],
                        "patient_phone" => $row['patient_phone']
                    );
                    array_push($consultations, $consultation_item);
                }

                http_response_code(200);
                echo json_encode($consultations);
            } elseif (isset($_GET['id'])) {
                // Get single consultation
                $stmt = $consultation->getById($_GET['id']);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $consultation_item = array(
                        "id" => $row['id'],
                        "appointment_id" => $row['appointment_id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "consultation_date" => $row['consultation_date'],
                        "consultation_time" => $row['consultation_time'],
                        "symptoms" => $row['symptoms'],
                        "diagnosis" => $row['diagnosis'],
                        "treatment_plan" => $row['treatment_plan'],
                        "follow_up_instructions" => $row['follow_up_instructions'],
                        "next_appointment_recommended" => $row['next_appointment_recommended'],
                        "consultation_fee" => $row['consultation_fee'],
                        "status" => $row['status'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "doctor_email" => $row['doctor_email'],
                        "doctor_phone" => $row['doctor_phone'],
                        "doctor_image" => $row['doctor_image'],
                        "patient_name" => $row['patient_name'],
                        "patient_email" => $row['patient_email'],
                        "patient_phone" => $row['patient_phone']
                    );

                    http_response_code(200);
                    echo json_encode($consultation_item);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Consultation not found"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Patient ID, Doctor ID, or Consultation ID required"));
            }
            break;

        case 'POST':
            // Create new consultation
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->patient_id) && !empty($data->doctor_id) && 
                !empty($data->consultation_date) && !empty($data->consultation_time)) {

                $consultation->appointment_id = $data->appointment_id ?? null;
                $consultation->patient_id = $data->patient_id;
                $consultation->doctor_id = $data->doctor_id;
                $consultation->consultation_date = $data->consultation_date;
                $consultation->consultation_time = $data->consultation_time;
                $consultation->symptoms = $data->symptoms ?? null;
                $consultation->diagnosis = $data->diagnosis ?? null;
                $consultation->treatment_plan = $data->treatment_plan ?? null;
                $consultation->follow_up_instructions = $data->follow_up_instructions ?? null;
                $consultation->next_appointment_recommended = $data->next_appointment_recommended ?? false;
                $consultation->consultation_fee = $data->consultation_fee ?? 0.00;
                $consultation->status = $data->status ?? 'in_progress';

                if ($consultation->create()) {
                    // Get the created consultation with full details
                    $stmt = $consultation->getById($consultation->id);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    $consultation_item = array(
                        "id" => $row['id'],
                        "appointment_id" => $row['appointment_id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "consultation_date" => $row['consultation_date'],
                        "consultation_time" => $row['consultation_time'],
                        "symptoms" => $row['symptoms'],
                        "diagnosis" => $row['diagnosis'],
                        "treatment_plan" => $row['treatment_plan'],
                        "follow_up_instructions" => $row['follow_up_instructions'],
                        "next_appointment_recommended" => $row['next_appointment_recommended'],
                        "consultation_fee" => $row['consultation_fee'],
                        "status" => $row['status'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "patient_name" => $row['patient_name']
                    );

                    http_response_code(201);
                    echo json_encode($consultation_item);
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to create consultation"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Incomplete data"));
            }
            break;

        case 'PUT':
            // Update consultation
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->id)) {
                $consultation->id = $data->id;
                $consultation->symptoms = $data->symptoms ?? null;
                $consultation->diagnosis = $data->diagnosis ?? null;
                $consultation->treatment_plan = $data->treatment_plan ?? null;
                $consultation->follow_up_instructions = $data->follow_up_instructions ?? null;
                $consultation->next_appointment_recommended = $data->next_appointment_recommended ?? false;
                $consultation->consultation_fee = $data->consultation_fee ?? 0.00;
                $consultation->status = $data->status ?? 'in_progress';

                if ($consultation->update()) {
                    // Get the updated consultation with full details
                    $stmt = $consultation->getById($consultation->id);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    $consultation_item = array(
                        "id" => $row['id'],
                        "appointment_id" => $row['appointment_id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "consultation_date" => $row['consultation_date'],
                        "consultation_time" => $row['consultation_time'],
                        "symptoms" => $row['symptoms'],
                        "diagnosis" => $row['diagnosis'],
                        "treatment_plan" => $row['treatment_plan'],
                        "follow_up_instructions" => $row['follow_up_instructions'],
                        "next_appointment_recommended" => $row['next_appointment_recommended'],
                        "consultation_fee" => $row['consultation_fee'],
                        "status" => $row['status'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty']
                    );

                    http_response_code(200);
                    echo json_encode($consultation_item);
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to update consultation"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Consultation ID required"));
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Method not allowed"));
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Server error: " . $e->getMessage()));
}
?>
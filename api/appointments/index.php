<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
file_put_contents('debug.log', "Method: " . $_SERVER['REQUEST_METHOD'] . " | Data: " . file_get_contents("php://input") . "\n", FILE_APPEND);
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

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['patient_id'])) {
                // Get appointments for a specific patient
                $stmt = $appointment->getByPatientId($_GET['patient_id']);
                $appointments = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $appointment_item = array(
                        "id" => $row['id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "appointment_date" => $row['appointment_date'],
                        "appointment_time" => $row['appointment_time'],
                        "duration" => $row['duration'],
                        "status" => $row['status'],
                        "appointment_type" => $row['appointment_type'],
                        "reason" => $row['reason'],
                        "patient_notes" => $row['patient_notes'],
                        "doctor_notes" => $row['doctor_notes'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "cancelled_at" => $row['cancelled_at'],
                        "cancellation_reason" => $row['cancellation_reason'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "doctor_email" => $row['doctor_email'],
                        "doctor_phone" => $row['doctor_phone'],
                        "consultation_fee" => floatval($row['consultation_fee']),
                        "patient_name" => $row['patient_name'],
                        "patient_email" => $row['patient_email'],
                        "patient_phone" => $row['patient_phone']
                    );
                    array_push($appointments, $appointment_item);
                }

                http_response_code(200);
                echo json_encode($appointments);
            } elseif (isset($_GET['id'])) {
                // Get single appointment
                $stmt = $appointment->getById($_GET['id']);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $appointment_item = array(
                        "id" => $row['id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "appointment_date" => $row['appointment_date'],
                        "appointment_time" => $row['appointment_time'],
                        "duration" => $row['duration'],
                        "status" => $row['status'],
                        "appointment_type" => $row['appointment_type'],
                        "reason" => $row['reason'],
                        "patient_notes" => $row['patient_notes'],
                        "doctor_notes" => $row['doctor_notes'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "cancelled_at" => $row['cancelled_at'],
                        "cancellation_reason" => $row['cancellation_reason'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "consultation_fee" => $row['consultation_fee'],
                        "patient_name" => $row['patient_name'],
                        "patient_email" => $row['patient_email'],
                        "patient_phone" => $row['patient_phone']
                    );

                    http_response_code(200);
                    echo json_encode($appointment_item);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Appointment not found"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Patient ID or Appointment ID required"));
            }
            break;

        case 'POST':
            // Create new appointment
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->patient_id) && !empty($data->doctor_id) && 
                !empty($data->appointment_date) && !empty($data->appointment_time)) {

                // Check if time slot is available
                if (!$appointment->isTimeSlotAvailable($data->doctor_id, $data->appointment_date, $data->appointment_time)) {
                    http_response_code(409);
                    echo json_encode(array("message" => "Time slot is not available"));
                    break;
                }

                $appointment->patient_id = $data->patient_id;
                $appointment->doctor_id = $data->doctor_id;
                $appointment->appointment_date = $data->appointment_date;
                $appointment->appointment_time = $data->appointment_time;
                $appointment->duration = $data->duration ?? 30;
                $appointment->status = $data->status ?? 'pending';
                $appointment->appointment_type = $data->appointment_type ?? 'consultation';
                $appointment->reason = $data->reason ?? null;
                $appointment->patient_notes = $data->patient_notes ?? null;

                if ($appointment->create()) {
                    // Get the created appointment with doctor info
                    $stmt = $appointment->getById($appointment->id);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    $appointment_item = array(
                        "id" => $row['id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "appointment_date" => $row['appointment_date'],
                        "appointment_time" => $row['appointment_time'],
                        "duration" => $row['duration'],
                        "status" => $row['status'],
                        "appointment_type" => $row['appointment_type'],
                        "reason" => $row['reason'],
                        "patient_notes" => $row['patient_notes'],
                        "doctor_notes" => $row['doctor_notes'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "cancelled_at" => $row['cancelled_at'],
                        "cancellation_reason" => $row['cancellation_reason'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "consultation_fee" => $row['consultation_fee']
                    );

                    http_response_code(201);
                    echo json_encode($appointment_item);
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to create appointment"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Incomplete data"));
            }
            break;

        case 'PUT':
            // Update appointment
            $data = json_decode(file_get_contents("php://input"));
            file_put_contents('debug.log', "Update id: " . ($data->id ?? 'no id') . "\n", FILE_APPEND);


            if (!empty($data->id)) {
                $appointment->id = $data->id;

                // Check if time slot is available (excluding current appointment)
                if (isset($data->doctor_id) && isset($data->appointment_date) && isset($data->appointment_time)) {
                    if (!$appointment->isTimeSlotAvailable($data->doctor_id, $data->appointment_date, $data->appointment_time, $data->id)) {
                        http_response_code(409);
                        echo json_encode(array("message" => "Time slot is not available"));
                        break;
                    }
                }

                $appointment->doctor_id = $data->doctor_id;
                $appointment->appointment_date = $data->appointment_date;
                $appointment->appointment_time = $data->appointment_time;
                $appointment->duration = $data->duration ?? 30;
                $appointment->status = $data->status ?? 'pending';
                $appointment->appointment_type = $data->appointment_type ?? 'consultation';
                $appointment->reason = $data->reason ?? null;
                $appointment->patient_notes = $data->patient_notes ?? null;
                $appointment->doctor_notes = $data->doctor_notes ?? null;

                if ($appointment->update()) {
                $stmt = $appointment->getById($appointment->id);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$row) {
                        http_response_code(404);
                        echo json_encode(array("message" => "Appointment not found after update"));
                        break;
                    }

                    

                    $appointment_item = array(
                        "id" => $row['id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "appointment_date" => $row['appointment_date'],
                        "appointment_time" => $row['appointment_time'],
                        "duration" => $row['duration'],
                        "status" => $row['status'],
                        "appointment_type" => $row['appointment_type'],
                        "reason" => $row['reason'],
                        "patient_notes" => $row['patient_notes'],
                        "doctor_notes" => $row['doctor_notes'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "cancelled_at" => $row['cancelled_at'],
                        "cancellation_reason" => $row['cancellation_reason'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "consultation_fee" => $row['consultation_fee']
                    );

                    http_response_code(200);
                    echo json_encode($appointment_item);
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to update appointment"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Appointment ID required"));
            }
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->id)) {
                $appointment->id = $data->id;

                if ($appointment->delete()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Appointment deleted"));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to delete appointment"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Appointment ID required"));
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
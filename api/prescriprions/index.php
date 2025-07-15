<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/Database.php';
include_once '../models/Prescription.php';

$database = new Database();
$db = $database->getConnection();
$prescription = new Prescription($db);

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['patient_id'])) {
                // Get prescriptions for a patient
                $stmt = $prescription->getByPatientId($_GET['patient_id']);
                $prescriptions = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Get medications for this prescription
                    $medications_stmt = $prescription->getMedications($row['id']);
                    $medications = array();
                    while ($med_row = $medications_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $medications[] = array(
                            "id" => $med_row['id'],
                            "medication_name" => $med_row['medication_name'],
                            "dosage" => $med_row['dosage'],
                            "frequency" => $med_row['frequency'],
                            "duration" => $med_row['duration'],
                            "instructions" => $med_row['instructions']
                        );
                    }

                    $prescription_item = array(
                        "id" => $row['id'],
                        "consultation_id" => $row['consultation_id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "prescription_date" => $row['prescription_date'],
                        "instructions" => $row['instructions'],
                        "is_active" => $row['is_active'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "consultation_date" => $row['consultation_date'],
                        "diagnosis" => $row['diagnosis'],
                        "medications" => $medications
                    );
                    array_push($prescriptions, $prescription_item);
                }

                http_response_code(200);
                echo json_encode($prescriptions);
            } elseif (isset($_GET['consultation_id'])) {
                // Get prescriptions for a consultation
                $stmt = $prescription->getByConsultationId($_GET['consultation_id']);
                $prescriptions = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Get medications for this prescription
                    $medications_stmt = $prescription->getMedications($row['id']);
                    $medications = array();
                    while ($med_row = $medications_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $medications[] = array(
                            "id" => $med_row['id'],
                            "medication_name" => $med_row['medication_name'],
                            "dosage" => $med_row['dosage'],
                            "frequency" => $med_row['frequency'],
                            "duration" => $med_row['duration'],
                            "instructions" => $med_row['instructions']
                        );
                    }

                    $prescription_item = array(
                        "id" => $row['id'],
                        "consultation_id" => $row['consultation_id'],
                        "patient_id" => $row['patient_id'],
                        "doctor_id" => $row['doctor_id'],
                        "prescription_date" => $row['prescription_date'],
                        "instructions" => $row['instructions'],
                        "is_active" => $row['is_active'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "medications" => $medications
                    );
                    array_push($prescriptions, $prescription_item);
                }

                http_response_code(200);
                echo json_encode($prescriptions);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Patient ID or Consultation ID required"));
            }
            break;

        case 'POST':
            // Create new prescription
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->consultation_id) && !empty($data->patient_id) && 
                !empty($data->doctor_id) && !empty($data->prescription_date)) {

                $prescription->consultation_id = $data->consultation_id;
                $prescription->patient_id = $data->patient_id;
                $prescription->doctor_id = $data->doctor_id;
                $prescription->prescription_date = $data->prescription_date;
                $prescription->instructions = $data->instructions ?? null;
                $prescription->is_active = $data->is_active ?? true;

                if ($prescription->create()) {
                    // Add medications if provided
                    if (isset($data->medications) && is_array($data->medications)) {
                        foreach ($data->medications as $medication) {
                            $prescription->addMedication((array)$medication);
                        }
                    }

                    http_response_code(201);
                    echo json_encode(array(
                        "id" => $prescription->id,
                        "message" => "Prescription created successfully"
                    ));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to create prescription"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Incomplete data"));
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
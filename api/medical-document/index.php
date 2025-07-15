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
include_once '../models/MedicalDocument.php';

$database = new Database();
$db = $database->getConnection();
$document = new MedicalDocument($db);

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['patient_id'])) {
                // Get documents for a patient
                $stmt = $document->getByPatientId($_GET['patient_id']);
                $documents = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $document_item = array(
                        "id" => $row['id'],
                        "patient_id" => $row['patient_id'],
                        "consultation_id" => $row['consultation_id'],
                        "doctor_id" => $row['doctor_id'],
                        "document_type" => $row['document_type'],
                        "file_name" => $row['file_name'],
                        "file_path" => $row['file_path'],
                        "file_size" => $row['file_size'],
                        "mime_type" => $row['mime_type'],
                        "upload_date" => $row['upload_date'],
                        "description" => $row['description'],
                        "is_downloadable" => $row['is_downloadable'],
                        "created_at" => $row['created_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty'],
                        "consultation_date" => $row['consultation_date'],
                        "diagnosis" => $row['diagnosis']
                    );
                    array_push($documents, $document_item);
                }

                http_response_code(200);
                echo json_encode($documents);
            } elseif (isset($_GET['consultation_id'])) {
                // Get documents for a consultation
                $stmt = $document->getByConsultationId($_GET['consultation_id']);
                $documents = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $document_item = array(
                        "id" => $row['id'],
                        "patient_id" => $row['patient_id'],
                        "consultation_id" => $row['consultation_id'],
                        "doctor_id" => $row['doctor_id'],
                        "document_type" => $row['document_type'],
                        "file_name" => $row['file_name'],
                        "file_path" => $row['file_path'],
                        "file_size" => $row['file_size'],
                        "mime_type" => $row['mime_type'],
                        "upload_date" => $row['upload_date'],
                        "description" => $row['description'],
                        "is_downloadable" => $row['is_downloadable'],
                        "created_at" => $row['created_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty']
                    );
                    array_push($documents, $document_item);
                }

                http_response_code(200);
                echo json_encode($documents);
            } elseif (isset($_GET['id'])) {
                // Get single document
                $stmt = $document->getById($_GET['id']);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $document_item = array(
                        "id" => $row['id'],
                        "patient_id" => $row['patient_id'],
                        "consultation_id" => $row['consultation_id'],
                        "doctor_id" => $row['doctor_id'],
                        "document_type" => $row['document_type'],
                        "file_name" => $row['file_name'],
                        "file_path" => $row['file_path'],
                        "file_size" => $row['file_size'],
                        "mime_type" => $row['mime_type'],
                        "upload_date" => $row['upload_date'],
                        "description" => $row['description'],
                        "is_downloadable" => $row['is_downloadable'],
                        "created_at" => $row['created_at'],
                        "doctor_name" => $row['doctor_name'],
                        "doctor_specialty" => $row['doctor_specialty']
                    );

                    http_response_code(200);
                    echo json_encode($document_item);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Document not found"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Patient ID, Consultation ID, or Document ID required"));
            }
            break;

        case 'POST':
            // Handle file upload and create document record
            if (isset($_FILES['file']) && isset($_POST['patient_id']) && isset($_POST['doctor_id'])) {
                $upload_dir = '../uploads/medical_documents/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file = $_FILES['file'];
                $file_name = $file['name'];
                $file_tmp = $file['tmp_name'];
                $file_size = $file['size'];
                $file_type = $file['type'];
                
                // Generate unique filename
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $unique_filename;

                // Validate file type
                $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                if (!in_array(strtolower($file_extension), $allowed_types)) {
                    http_response_code(400);
                    echo json_encode(array("message" => "File type not allowed"));
                    break;
                }

                // Validate file size (max 10MB)
                if ($file_size > 10 * 1024 * 1024) {
                    http_response_code(400);
                    echo json_encode(array("message" => "File size too large"));
                    break;
                }

                if (move_uploaded_file($file_tmp, $file_path)) {
                    $document->patient_id = $_POST['patient_id'];
                    $document->consultation_id = $_POST['consultation_id'] ?? null;
                    $document->doctor_id = $_POST['doctor_id'];
                    $document->document_type = $_POST['document_type'] ?? 'other';
                    $document->file_name = $file_name;
                    $document->file_path = $file_path;
                    $document->file_size = $file_size;
                    $document->mime_type = $file_type;
                    $document->upload_date = date('Y-m-d');
                    $document->description = $_POST['description'] ?? null;
                    $document->is_downloadable = isset($_POST['is_downloadable']) ? $_POST['is_downloadable'] : true;

                    if ($document->create()) {
                        http_response_code(201);
                        echo json_encode(array(
                            "id" => $document->id,
                            "message" => "Document uploaded successfully",
                            "file_name" => $file_name
                        ));
                    } else {
                        // Delete uploaded file if database insert fails
                        unlink($file_path);
                        http_response_code(503);
                        echo json_encode(array("message" => "Unable to save document record"));
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => "File upload failed"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Missing required fields or file"));
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
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/Database.php';
include_once '../models/MedicalDocument.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    $document = new MedicalDocument($db);

    try {
        $stmt = $document->getById($_GET['id']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['is_downloadable']) {
            $file_path = $row['file_path'];
            
            if (file_exists($file_path)) {
                // Set appropriate headers for file download
                header('Content-Type: ' . $row['mime_type']);
                header('Content-Disposition: attachment; filename="' . $row['file_name'] . '"');
                header('Content-Length: ' . filesize($file_path));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                
                // Output file content
                readfile($file_path);
                exit();
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "File not found on server"));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Document not found or not downloadable"));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Server error: " . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Document ID required"));
}
?>
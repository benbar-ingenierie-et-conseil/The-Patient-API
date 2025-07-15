
<?php

class MedicalDocument {
    private $conn;
    private $table_name = "medical_documents";

    public $id;
    public $patient_id;
    public $consultation_id;
    public $doctor_id;
    public $document_type;
    public $file_name;
    public $file_path;
    public $file_size;
    public $mime_type;
    public $upload_date;
    public $description;
    public $is_downloadable;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get documents by patient
    public function getByPatientId($patient_id) {
        $query = "SELECT 
                    md.id, md.patient_id, md.consultation_id, md.doctor_id,
                    md.document_type, md.file_name, md.file_path, md.file_size,
                    md.mime_type, md.upload_date, md.description, md.is_downloadable,
                    md.created_at,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    d.specialization as doctor_specialty,
                    c.consultation_date, c.diagnosis
                  FROM " . $this->table_name . " md
                  LEFT JOIN doctors d ON md.doctor_id = d.id
                  LEFT JOIN consultations c ON md.consultation_id = c.id
                  WHERE md.patient_id = :patient_id
                  ORDER BY md.upload_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();

        return $stmt;
    }

    // Get documents by consultation
    public function getByConsultationId($consultation_id) {
        $query = "SELECT 
                    md.id, md.patient_id, md.consultation_id, md.doctor_id,
                    md.document_type, md.file_name, md.file_path, md.file_size,
                    md.mime_type, md.upload_date, md.description, md.is_downloadable,
                    md.created_at,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    d.specialization as doctor_specialty
                  FROM " . $this->table_name . " md
                  LEFT JOIN doctors d ON md.doctor_id = d.id
                  WHERE md.consultation_id = :consultation_id
                  ORDER BY md.upload_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':consultation_id', $consultation_id);
        $stmt->execute();

        return $stmt;
    }

    // Get single document
    public function getById($id) {
        $query = "SELECT 
                    md.id, md.patient_id, md.consultation_id, md.doctor_id,
                    md.document_type, md.file_name, md.file_path, md.file_size,
                    md.mime_type, md.upload_date, md.description, md.is_downloadable,
                    md.created_at,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    d.specialization as doctor_specialty
                  FROM " . $this->table_name . " md
                  LEFT JOIN doctors d ON md.doctor_id = d.id
                  WHERE md.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt;
    }

    // Create document record
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET id = UUID(),
                      patient_id = :patient_id,
                      consultation_id = :consultation_id,
                      doctor_id = :doctor_id,
                      document_type = :document_type,
                      file_name = :file_name,
                      file_path = :file_path,
                      file_size = :file_size,
                      mime_type = :mime_type,
                      upload_date = :upload_date,
                      description = :description,
                      is_downloadable = :is_downloadable,
                      created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->consultation_id = htmlspecialchars(strip_tags($this->consultation_id));
        $this->doctor_id = htmlspecialchars(strip_tags($this->doctor_id));
        $this->document_type = htmlspecialchars(strip_tags($this->document_type));
        $this->file_name = htmlspecialchars(strip_tags($this->file_name));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->file_size = htmlspecialchars(strip_tags($this->file_size));
        $this->mime_type = htmlspecialchars(strip_tags($this->mime_type));
        $this->upload_date = htmlspecialchars(strip_tags($this->upload_date));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->is_downloadable = $this->is_downloadable ? 1 : 0;

        // Bind parameters
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':consultation_id', $this->consultation_id);
        $stmt->bindParam(':doctor_id', $this->doctor_id);
        $stmt->bindParam(':document_type', $this->document_type);
        $stmt->bindParam(':file_name', $this->file_name);
        $stmt->bindParam(':file_path', $this->file_path);
        $stmt->bindParam(':file_size', $this->file_size);
        $stmt->bindParam(':mime_type', $this->mime_type);
        $stmt->bindParam(':upload_date', $this->upload_date);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':is_downloadable', $this->is_downloadable);

        if ($stmt->execute()) {
            // Get the created document ID
            $query = "SELECT id FROM " . $this->table_name . " 
                      WHERE patient_id = :patient_id AND file_name = :file_name
                      ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $this->patient_id);
            $stmt->bindParam(':file_name', $this->file_name);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return true;
        }
        return false;
    }
}
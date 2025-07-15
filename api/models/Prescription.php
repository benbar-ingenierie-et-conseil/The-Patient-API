<?php

class Prescription {
    private $conn;
    private $table_name = "prescriptions";

    public $id;
    public $consultation_id;
    public $patient_id;
    public $doctor_id;
    public $prescription_date;
    public $instructions;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get prescriptions by patient
    public function getByPatientId($patient_id) {
        $query = "SELECT 
                    p.id, p.consultation_id, p.patient_id, p.doctor_id,
                    p.prescription_date, p.instructions, p.is_active,
                    p.created_at, p.updated_at,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    d.specialization as doctor_specialty,
                    c.consultation_date, c.diagnosis
                  FROM " . $this->table_name . " p
                  LEFT JOIN doctors d ON p.doctor_id = d.id
                  LEFT JOIN consultations c ON p.consultation_id = c.id
                  WHERE p.patient_id = :patient_id
                  ORDER BY p.prescription_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();

        return $stmt;
    }

    // Get prescriptions by consultation
    public function getByConsultationId($consultation_id) {
        $query = "SELECT 
                    p.id, p.consultation_id, p.patient_id, p.doctor_id,
                    p.prescription_date, p.instructions, p.is_active,
                    p.created_at, p.updated_at,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    d.specialization as doctor_specialty
                  FROM " . $this->table_name . " p
                  LEFT JOIN doctors d ON p.doctor_id = d.id
                  WHERE p.consultation_id = :consultation_id
                  ORDER BY p.prescription_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':consultation_id', $consultation_id);
        $stmt->execute();

        return $stmt;
    }

    // Get prescription medications
    public function getMedications($prescription_id) {
        $query = "SELECT 
                    pm.id, pm.prescription_id, pm.medication_name,
                    pm.dosage, pm.frequency, pm.duration, pm.instructions,
                    pm.created_at
                  FROM prescription_medications pm
                  WHERE pm.prescription_id = :prescription_id
                  ORDER BY pm.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':prescription_id', $prescription_id);
        $stmt->execute();

        return $stmt;
    }

    // Create prescription
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET id = UUID(),
                      consultation_id = :consultation_id,
                      patient_id = :patient_id,
                      doctor_id = :doctor_id,
                      prescription_date = :prescription_date,
                      instructions = :instructions,
                      is_active = :is_active,
                      created_at = NOW(),
                      updated_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->consultation_id = htmlspecialchars(strip_tags($this->consultation_id));
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->doctor_id = htmlspecialchars(strip_tags($this->doctor_id));
        $this->prescription_date = htmlspecialchars(strip_tags($this->prescription_date));
        $this->instructions = htmlspecialchars(strip_tags($this->instructions));
        $this->is_active = $this->is_active ? 1 : 0;

        // Bind parameters
        $stmt->bindParam(':consultation_id', $this->consultation_id);
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':doctor_id', $this->doctor_id);
        $stmt->bindParam(':prescription_date', $this->prescription_date);
        $stmt->bindParam(':instructions', $this->instructions);
        $stmt->bindParam(':is_active', $this->is_active);

        if ($stmt->execute()) {
            // Get the created prescription ID
            $query = "SELECT id FROM " . $this->table_name . " 
                      WHERE consultation_id = :consultation_id AND patient_id = :patient_id
                      ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':consultation_id', $this->consultation_id);
            $stmt->bindParam(':patient_id', $this->patient_id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return true;
        }
        return false;
    }

    // Add medication to prescription
    public function addMedication($medication_data) {
        $query = "INSERT INTO prescription_medications
                  SET id = UUID(),
                      prescription_id = :prescription_id,
                      medication_name = :medication_name,
                      dosage = :dosage,
                      frequency = :frequency,
                      duration = :duration,
                      instructions = :instructions,
                      created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':prescription_id', $this->id);
        $stmt->bindParam(':medication_name', $medication_data['medication_name']);
        $stmt->bindParam(':dosage', $medication_data['dosage']);
        $stmt->bindParam(':frequency', $medication_data['frequency']);
        $stmt->bindParam(':duration', $medication_data['duration']);
        $stmt->bindParam(':instructions', $medication_data['instructions']);

        return $stmt->execute();
    }
}
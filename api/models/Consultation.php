<?php

class Consultation {
    private $conn;
    private $table_name = "consultations";

    public $id;
    public $appointment_id;
    public $patient_id;
    public $doctor_id;
    public $consultation_date;
    public $consultation_time;
    public $symptoms;
    public $diagnosis;
    public $treatment_plan;
    public $follow_up_instructions;
    public $next_appointment_recommended;
    public $consultation_fee;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all consultations for a patient
    public function getByPatientId($patient_id) {
        $query = "SELECT 
                    c.id, c.appointment_id, c.patient_id, c.doctor_id, 
                    c.consultation_date, c.consultation_time, c.symptoms, 
                    c.diagnosis, c.treatment_plan, c.follow_up_instructions,
                    c.next_appointment_recommended, c.consultation_fee, c.status,
                    c.created_at, c.updated_at,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    d.specialization as doctor_specialty,
                    d.email as doctor_email, d.phone as doctor_phone,
                    CONCAT(u.first_name, ' ', u.last_name) as patient_name,
                    u.email as patient_email, u.phone as patient_phone
                  FROM " . $this->table_name . " c
                  LEFT JOIN doctors d ON c.doctor_id = d.id
                  LEFT JOIN users u ON c.patient_id = u.id
                  WHERE c.patient_id = :patient_id
                  ORDER BY c.consultation_date DESC, c.consultation_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();

        return $stmt;
    }

    // Get single consultation with full details
    public function getById($id) {
        $query = "SELECT 
                    c.id, c.appointment_id, c.patient_id, c.doctor_id, 
                    c.consultation_date, c.consultation_time, c.symptoms, 
                    c.diagnosis, c.treatment_plan, c.follow_up_instructions,
                    c.next_appointment_recommended, c.consultation_fee, c.status,
                    c.created_at, c.updated_at,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    d.specialization as doctor_specialty,
                    d.email as doctor_email, d.phone as doctor_phone,
                    d.profile_image as doctor_image,
                    CONCAT(u.first_name, ' ', u.last_name) as patient_name,
                    u.email as patient_email, u.phone as patient_phone
                  FROM " . $this->table_name . " c
                  LEFT JOIN doctors d ON c.doctor_id = d.id
                  LEFT JOIN users u ON c.patient_id = u.id
                  WHERE c.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt;
    }

    // Get consultations by doctor
    public function getByDoctorId($doctor_id) {
        $query = "SELECT 
                    c.id, c.appointment_id, c.patient_id, c.doctor_id, 
                    c.consultation_date, c.consultation_time, c.symptoms, 
                    c.diagnosis, c.treatment_plan, c.follow_up_instructions,
                    c.next_appointment_recommended, c.consultation_fee, c.status,
                    c.created_at, c.updated_at,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    d.specialization as doctor_specialty,
                    CONCAT(u.first_name, ' ', u.last_name) as patient_name,
                    u.email as patient_email, u.phone as patient_phone
                  FROM " . $this->table_name . " c
                  LEFT JOIN doctors d ON c.doctor_id = d.id
                  LEFT JOIN users u ON c.patient_id = u.id
                  WHERE c.doctor_id = :doctor_id
                  ORDER BY c.consultation_date DESC, c.consultation_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->execute();

        return $stmt;
    }

    // Create consultation
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET id = UUID(),
                      appointment_id = :appointment_id,
                      patient_id = :patient_id,
                      doctor_id = :doctor_id,
                      consultation_date = :consultation_date,
                      consultation_time = :consultation_time,
                      symptoms = :symptoms,
                      diagnosis = :diagnosis,
                      treatment_plan = :treatment_plan,
                      follow_up_instructions = :follow_up_instructions,
                      next_appointment_recommended = :next_appointment_recommended,
                      consultation_fee = :consultation_fee,
                      status = :status,
                      created_at = NOW(),
                      updated_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->appointment_id = htmlspecialchars(strip_tags($this->appointment_id));
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->doctor_id = htmlspecialchars(strip_tags($this->doctor_id));
        $this->consultation_date = htmlspecialchars(strip_tags($this->consultation_date));
        $this->consultation_time = htmlspecialchars(strip_tags($this->consultation_time));
        $this->symptoms = htmlspecialchars(strip_tags($this->symptoms));
        $this->diagnosis = htmlspecialchars(strip_tags($this->diagnosis));
        $this->treatment_plan = htmlspecialchars(strip_tags($this->treatment_plan));
        $this->follow_up_instructions = htmlspecialchars(strip_tags($this->follow_up_instructions));
        $this->next_appointment_recommended = $this->next_appointment_recommended ? 1 : 0;
        $this->consultation_fee = htmlspecialchars(strip_tags($this->consultation_fee));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind parameters
        $stmt->bindParam(':appointment_id', $this->appointment_id);
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':doctor_id', $this->doctor_id);
        $stmt->bindParam(':consultation_date', $this->consultation_date);
        $stmt->bindParam(':consultation_time', $this->consultation_time);
        $stmt->bindParam(':symptoms', $this->symptoms);
        $stmt->bindParam(':diagnosis', $this->diagnosis);
        $stmt->bindParam(':treatment_plan', $this->treatment_plan);
        $stmt->bindParam(':follow_up_instructions', $this->follow_up_instructions);
        $stmt->bindParam(':next_appointment_recommended', $this->next_appointment_recommended);
        $stmt->bindParam(':consultation_fee', $this->consultation_fee);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            // Get the created consultation ID
            $query = "SELECT id FROM " . $this->table_name . " 
                      WHERE patient_id = :patient_id AND doctor_id = :doctor_id 
                      AND consultation_date = :consultation_date AND consultation_time = :consultation_time
                      ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $this->patient_id);
            $stmt->bindParam(':doctor_id', $this->doctor_id);
            $stmt->bindParam(':consultation_date', $this->consultation_date);
            $stmt->bindParam(':consultation_time', $this->consultation_time);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return true;
        }
        return false;
    }

    // Update consultation
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET symptoms = :symptoms,
                      diagnosis = :diagnosis,
                      treatment_plan = :treatment_plan,
                      follow_up_instructions = :follow_up_instructions,
                      next_appointment_recommended = :next_appointment_recommended,
                      consultation_fee = :consultation_fee,
                      status = :status,
                      updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->symptoms = htmlspecialchars(strip_tags($this->symptoms));
        $this->diagnosis = htmlspecialchars(strip_tags($this->diagnosis));
        $this->treatment_plan = htmlspecialchars(strip_tags($this->treatment_plan));
        $this->follow_up_instructions = htmlspecialchars(strip_tags($this->follow_up_instructions));
        $this->next_appointment_recommended = $this->next_appointment_recommended ? 1 : 0;
        $this->consultation_fee = htmlspecialchars(strip_tags($this->consultation_fee));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind parameters
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':symptoms', $this->symptoms);
        $stmt->bindParam(':diagnosis', $this->diagnosis);
        $stmt->bindParam(':treatment_plan', $this->treatment_plan);
        $stmt->bindParam(':follow_up_instructions', $this->follow_up_instructions);
        $stmt->bindParam(':next_appointment_recommended', $this->next_appointment_recommended);
        $stmt->bindParam(':consultation_fee', $this->consultation_fee);
        $stmt->bindParam(':status', $this->status);

        return $stmt->execute();
    }
}
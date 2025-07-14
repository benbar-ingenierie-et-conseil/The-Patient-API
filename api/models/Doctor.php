<?php

class Doctor {
    private $conn;
    private $table_name = "doctors";

    public $id;
    public $first_name;
    public $last_name;
    public $specialization;
    public $email;
    public $phone;
    public $license_number;
    public $office_address;
    public $biography;
    public $profile_image;
    public $consultation_fee;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all doctors
    public function getAll() {
        $query = "SELECT id, first_name, last_name, specialization, email, phone, 
                         license_number, office_address, biography, profile_image,
                         consultation_fee, is_active, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE is_active = 1
                  ORDER BY first_name ASC, last_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Get single doctor
    public function getById($id) {
        $query = "SELECT id, first_name, last_name, specialization, email, phone,
                         license_number, office_address, biography, profile_image,
                         consultation_fee, is_active, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE id = :id AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt;
    }

    // Get doctors by specialization
    public function getBySpecialization($specialization) {
        $query = "SELECT id, first_name, last_name, specialization, email, phone,
                         license_number, office_address, biography, profile_image,
                         consultation_fee, is_active, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE specialization = :specialization AND is_active = 1
                  ORDER BY first_name ASC, last_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':specialization', $specialization);
        $stmt->execute();

        return $stmt;
    }

    // Get doctor availability
    public function getAvailability($doctor_id) {
        $query = "SELECT day_of_week, start_time, end_time, is_available
                  FROM doctor_availability
                  WHERE doctor_id = :doctor_id AND is_available = 1
                  ORDER BY day_of_week ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->execute();

        return $stmt;
    }
}
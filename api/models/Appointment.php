<?php

class Appointment {
    private $conn;
    private $table_name = "appointments";

    public $id;
    public $patient_id;
    public $doctor_id;
    public $appointment_date;
    public $appointment_time;
    public $duration;
    public $status;
    public $appointment_type;
    public $reason;
    public $patient_notes;
    public $doctor_notes;
    public $created_at;
    public $updated_at;
    public $cancelled_at;
    public $cancellation_reason;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all appointments for a patient
    public function getByPatientId($patient_id) {
        $query = "SELECT 
                    a.id, a.patient_id, a.doctor_id, a.appointment_date, 
                    a.appointment_time, a.duration, a.status, a.appointment_type,
                    a.reason, a.patient_notes, a.doctor_notes, a.created_at,
                    a.updated_at, a.cancelled_at, a.cancellation_reason,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name, 
                    d.specialization as doctor_specialty,
                    d.email as doctor_email, d.phone as doctor_phone,
                    d.consultation_fee,
                    CONCAT(u.first_name, ' ', u.last_name) as patient_name,
                    u.email as patient_email, u.phone as patient_phone
                  FROM " . $this->table_name . " a
                  LEFT JOIN doctors d ON a.doctor_id = d.id
                  LEFT JOIN users u ON a.patient_id = u.id
                  WHERE a.patient_id = :patient_id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();

        return $stmt;
    }

    // Get single appointment
    public function getById($id) {
        $query = "SELECT 
                    a.id, a.patient_id, a.doctor_id, a.appointment_date, 
                    a.appointment_time, a.duration, a.status, a.appointment_type,
                    a.reason, a.patient_notes, a.doctor_notes, a.created_at,
                    a.updated_at, a.cancelled_at, a.cancellation_reason,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name, 
                    d.specialization as doctor_specialty,
                    d.consultation_fee,
                    CONCAT(u.first_name, ' ', u.last_name) as patient_name,
                    u.email as patient_email, u.phone as patient_phone
                  FROM " . $this->table_name . " a
                  LEFT JOIN doctors d ON a.doctor_id = d.id
                  LEFT JOIN users u ON a.patient_id = u.id
                  WHERE a.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt;
    }

    // Create appointment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET id = UUID(),
                      patient_id = :patient_id,
                      doctor_id = :doctor_id,
                      appointment_date = :appointment_date,
                      appointment_time = :appointment_time,
                      duration = :duration,
                      status = :status,
                      appointment_type = :appointment_type,
                      reason = :reason,
                      patient_notes = :patient_notes,
                      created_at = NOW(),
                      updated_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->doctor_id = htmlspecialchars(strip_tags($this->doctor_id));
        $this->appointment_date = htmlspecialchars(strip_tags($this->appointment_date));
        $this->appointment_time = htmlspecialchars(strip_tags($this->appointment_time));
        $this->duration = htmlspecialchars(strip_tags($this->duration));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->appointment_type = htmlspecialchars(strip_tags($this->appointment_type));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->patient_notes = htmlspecialchars(strip_tags($this->patient_notes));

        // Bind parameters
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':doctor_id', $this->doctor_id);
        $stmt->bindParam(':appointment_date', $this->appointment_date);
        $stmt->bindParam(':appointment_time', $this->appointment_time);
        $stmt->bindParam(':duration', $this->duration);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':appointment_type', $this->appointment_type);
        $stmt->bindParam(':reason', $this->reason);
        $stmt->bindParam(':patient_notes', $this->patient_notes);

        if ($stmt->execute()) {
            // Get the created appointment ID
            $query = "SELECT id FROM " . $this->table_name . " 
                      WHERE patient_id = :patient_id AND doctor_id = :doctor_id 
                      AND appointment_date = :appointment_date AND appointment_time = :appointment_time
                      ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patient_id', $this->patient_id);
            $stmt->bindParam(':doctor_id', $this->doctor_id);
            $stmt->bindParam(':appointment_date', $this->appointment_date);
            $stmt->bindParam(':appointment_time', $this->appointment_time);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return true;
        }
        return false;
    }

    // Update appointment
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET doctor_id = :doctor_id,
                      appointment_date = :appointment_date,
                      appointment_time = :appointment_time,
                      duration = :duration,
                      status = :status,
                      appointment_type = :appointment_type,
                      reason = :reason,
                      patient_notes = :patient_notes,
                      doctor_notes = :doctor_notes,
                      updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->doctor_id = htmlspecialchars(strip_tags($this->doctor_id));
        $this->appointment_date = htmlspecialchars(strip_tags($this->appointment_date));
        $this->appointment_time = htmlspecialchars(strip_tags($this->appointment_time));
        $this->duration = htmlspecialchars(strip_tags($this->duration));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->appointment_type = htmlspecialchars(strip_tags($this->appointment_type));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->patient_notes = htmlspecialchars(strip_tags($this->patient_notes));
        $this->doctor_notes = htmlspecialchars(strip_tags($this->doctor_notes));

        // Bind parameters
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':doctor_id', $this->doctor_id);
        $stmt->bindParam(':appointment_date', $this->appointment_date);
        $stmt->bindParam(':appointment_time', $this->appointment_time);
        $stmt->bindParam(':duration', $this->duration);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':appointment_type', $this->appointment_type);
        $stmt->bindParam(':reason', $this->reason);
        $stmt->bindParam(':patient_notes', $this->patient_notes);
        $stmt->bindParam(':doctor_notes', $this->doctor_notes);

        return $stmt->execute();
    }

    // Cancel appointment
    public function cancel($cancellation_reason) {
        $query = "UPDATE " . $this->table_name . "
                  SET status = 'cancelled',
                      cancellation_reason = :cancellation_reason,
                      cancelled_at = NOW(),
                      updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $cancellation_reason = htmlspecialchars(strip_tags($cancellation_reason));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':cancellation_reason', $cancellation_reason);

        return $stmt->execute();
    }

    // Check if time slot is available
    public function isTimeSlotAvailable($doctor_id, $appointment_date, $appointment_time, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                  WHERE doctor_id = :doctor_id 
                  AND appointment_date = :appointment_date 
                  AND appointment_time = :appointment_time
                  AND status NOT IN ('cancelled', 'no_show')";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->bindParam(':appointment_time', $appointment_time);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'] == 0;
    }

    // Get available time slots for a doctor on a specific date
    public function getAvailableTimeSlots($doctor_id, $appointment_date) {
        // Get doctor's availability for the day of week
        $day_of_week = date('w', strtotime($appointment_date)); // 0 = Sunday, 1 = Monday, etc.
        
        $availability_query = "SELECT start_time, end_time FROM doctor_availability 
                              WHERE doctor_id = :doctor_id AND day_of_week = :day_of_week 
                              AND is_available = 1";
        
        $stmt = $this->conn->prepare($availability_query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':day_of_week', $day_of_week);
        $stmt->execute();
        
        $availability = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$availability) {
            return []; // Doctor not available on this day
        }
        
        // Generate time slots based on doctor's availability
        $start_time = $availability['start_time'];
        $end_time = $availability['end_time'];
        $slots = [];
        
        $current_time = strtotime($start_time);
        $end_timestamp = strtotime($end_time);
        
        while ($current_time < $end_timestamp) {
            $slots[] = date('H:i', $current_time);
            $current_time += 30 * 60; // 30 minutes intervals
        }

        // Get booked slots
        $query = "SELECT appointment_time FROM " . $this->table_name . "
                  WHERE doctor_id = :doctor_id 
                  AND appointment_date = :appointment_date
                  AND status NOT IN ('cancelled', 'no_show')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->execute();

        $booked_slots = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $booked_slots[] = substr($row['appointment_time'], 0, 5); // Format HH:MM
        }

        // Return available slots
        return array_diff($slots, $booked_slots);
    }
}
<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $password_hash;
    public $phone;
    public $first_name;
    public $last_name;
    public $date_of_birth;
    public $gender;
    public $address;
    public $emergency_contact_name;
    public $emergency_contact_phone;
    public $profile_image;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET email=:email, 
                      password_hash=:password_hash, 
                      phone=:phone, 
                      first_name=:first_name, 
                      last_name=:last_name, 
                      date_of_birth=:date_of_birth, 
                      gender=:gender, 
                      address=:address, 
                      emergency_contact_name=:emergency_contact_name, 
                      emergency_contact_phone=:emergency_contact_phone,
                      created_at=NOW(),
                      updated_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->emergency_contact_name = htmlspecialchars(strip_tags($this->emergency_contact_name));
        $this->emergency_contact_phone = htmlspecialchars(strip_tags($this->emergency_contact_phone));

        // Bind values
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":emergency_contact_name", $this->emergency_contact_name);
        $stmt->bindParam(":emergency_contact_phone", $this->emergency_contact_phone);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function emailExists() {
        $query = "SELECT id, email, password_hash, first_name, last_name, phone, is_active 
                  FROM " . $this->table_name . " 
                  WHERE email = :email AND is_active = 1 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->phone = $row['phone'];
            $this->is_active = $row['is_active'];
            
            return true;
        }

        return false;
    }

    // Get user by ID
    public function getUserById($user_id) {
        $query = "SELECT id, email, first_name, last_name, phone, date_of_birth, 
                         gender, address, emergency_contact_name, emergency_contact_phone,
                         profile_image, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id AND is_active = 1 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    public function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isValidPhone($phone) {
        return preg_match('/^[\+]?[0-9\s\-\(\)]{10,20}$/', $phone);
    }
}
?>
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

    public function getAll() {
        $query = "SELECT id, email, phone, first_name, last_name, date_of_birth, 
                         gender, address, emergency_contact_name, emergency_contact_phone,
                         profile_image, is_active, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE is_active = 1
                  ORDER BY first_name ASC, last_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT id, email, phone, first_name, last_name, date_of_birth,
                        gender, address, emergency_contact_name, emergency_contact_phone,
                        profile_image, is_active, created_at, updated_at
                FROM " . $this->table_name . "
                WHERE id = :id AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
    
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

    public function getByEmail($email) {
        $query = "SELECT id, email, password_hash, phone, first_name, last_name, 
                         date_of_birth, gender, address, emergency_contact_name, 
                         emergency_contact_phone, profile_image, is_active, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE email = :email AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt;
    }

    public function emailExists() {
    $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email AND is_active = 1 LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $this->email);
    $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->password_hash = $row['password_hash'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->phone = $row['phone'];
            $this->email = $row['email'];
            return true;
        }

        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET id = UUID(),
                      email = :email,
                      password_hash = :password_hash,
                      phone = :phone,
                      first_name = :first_name,
                      last_name = :last_name,
                      date_of_birth = :date_of_birth,
                      gender = :gender,
                      address = :address,
                      emergency_contact_name = :emergency_contact_name,
                      emergency_contact_phone = :emergency_contact_phone,
                      profile_image = :profile_image,
                      is_active = 1,
                      created_at = NOW(),
                      updated_at = NOW()";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->date_of_birth = htmlspecialchars(strip_tags($this->date_of_birth));
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->emergency_contact_name = htmlspecialchars(strip_tags($this->emergency_contact_name));
        $this->emergency_contact_phone = htmlspecialchars(strip_tags($this->emergency_contact_phone));
        $this->profile_image = htmlspecialchars(strip_tags($this->profile_image));

        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $this->password_hash);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':gender', $this->gender);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':emergency_contact_name', $this->emergency_contact_name);
        $stmt->bindParam(':emergency_contact_phone', $this->emergency_contact_phone);
        $stmt->bindParam(':profile_image', $this->profile_image);

        if ($stmt->execute()) {
            $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return true;
        }
        return false;
    }

    public function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isValidPhone($phone) {
        return preg_match('/^\+?[0-9]{10,15}$/', $phone);
    }

    public function emailExistsForOtherUser($current_user_id) {
        $query = "SELECT id FROM " . $this->table_name . " 
                WHERE email = :email AND id != :current_user_id AND is_active = 1 
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':current_user_id', $current_user_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . " 
                SET email = COALESCE(:email, email),
                    phone = COALESCE(:phone, phone),
                    first_name = COALESCE(:first_name, first_name),
                    last_name = COALESCE(:last_name, last_name),
                    date_of_birth = COALESCE(:date_of_birth, date_of_birth),
                    gender = COALESCE(:gender, gender),
                    address = COALESCE(:address, address),
                    emergency_contact_name = COALESCE(:emergency_contact_name, emergency_contact_name),
                    emergency_contact_phone = COALESCE(:emergency_contact_phone, emergency_contact_phone),
                    profile_image = COALESCE(:profile_image, profile_image),
                    updated_at = NOW()
                WHERE id = :id AND is_active = 1";

        $stmt = $this->conn->prepare($query);

        if ($this->email) $this->email = htmlspecialchars(strip_tags($this->email));
        if ($this->phone) $this->phone = htmlspecialchars(strip_tags($this->phone));
        if ($this->first_name) $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        if ($this->last_name) $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        if ($this->gender) $this->gender = htmlspecialchars(strip_tags($this->gender));
        if ($this->address) $this->address = htmlspecialchars(strip_tags($this->address));
        if ($this->emergency_contact_name) $this->emergency_contact_name = htmlspecialchars(strip_tags($this->emergency_contact_name));
        if ($this->emergency_contact_phone) $this->emergency_contact_phone = htmlspecialchars(strip_tags($this->emergency_contact_phone));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":emergency_contact_name", $this->emergency_contact_name);
        $stmt->bindParam(":emergency_contact_phone", $this->emergency_contact_phone);
        $stmt->bindParam(":profile_image", $this->profile_image);

        return $stmt->execute();
    }


}
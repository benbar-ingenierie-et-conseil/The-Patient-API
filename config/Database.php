<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $env = $this->loadEnv();
        $this->host = $env['DB_HOST'] ?? 'localhost';
        $this->db_name = $env['DB_NAME'] ?? '';
        $this->username = $env['DB_USER'] ?? '';
        $this->password = $env['DB_PASS'] ?? '';
    }

    public function connect() {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Connection Failed: " . $e->getMessage()]);
            exit;
        }
        return $this->conn;
    }

    private function loadEnv() {
        $envPath = __DIR__ . '/../.env';
        if (!file_exists($envPath)) {
            echo json_encode(["error" => ".env file not found"]);
            exit;
        }
        return parse_ini_file($envPath);
    }
}

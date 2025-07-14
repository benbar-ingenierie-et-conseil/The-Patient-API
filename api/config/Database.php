<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host     = $this->getEnvVariable('DB_HOST', 'localhost');
        $this->db_name  = $this->getEnvVariable('DB_NAME', '');
        $this->username = $this->getEnvVariable('DB_USER', '');
        $this->password = $this->getEnvVariable('DB_PASS', '');
    }

    public function connect() {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Connection Failed: " . $e->getMessage()]);
            exit;
        }
        return $this->conn;
    }

    private function getEnvVariable($key, $default = null) {
        $envPath = __DIR__ . '/../../.env';
        if (!file_exists($envPath)) return $default;

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (!str_contains($line, '=')) continue;
            list($envKey, $envValue) = explode('=', $line, 2);
            if (trim($envKey) === $key) {
                return trim($envValue);
            }
        }
        return $default;
    }

    public function getConnection() {
        return $this->connect();
    }
}

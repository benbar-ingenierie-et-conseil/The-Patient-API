<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'api/config/Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo json_encode(["message" => "pong"]);
} else {
    echo json_encode(["message" => "pang"]);
}

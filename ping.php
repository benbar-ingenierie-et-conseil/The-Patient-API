<?php
require_once 'config/Database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->connect();

if ($conn) {
    echo json_encode(["message" => "pong"]);
} else {
    echo json_encode(["message" => "pang"]);
}

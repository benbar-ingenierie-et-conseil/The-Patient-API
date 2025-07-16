<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/Database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "Missing user ID"]);
            exit();
        }

        $userId = $_GET['id'];
        $row = $user->getById($userId);

        if ($row) {
            http_response_code(200);
            echo json_encode(["data" => $row]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found"]);
        }
        exit();
    }
?>
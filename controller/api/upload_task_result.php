<?php
// TODO: Authentication
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["task_id"]) || !isset($data["recognized_known_entity_id"]) || !isset($data["location_id"])) {
    echo json_encode(["message" => "Not all required data received (task_id, recognized_known_entity_id, location_id)"]);
    exit;
}

// Upload the task result to the DB
require '../includes/classes/Database.php';

$db = new Database();

$db->updateAnalyzerTaskResult($data["task_id"], $data["recognized_known_entity_id"], $data["location_id"]);

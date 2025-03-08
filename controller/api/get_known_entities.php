<?php
// TODO: Authentication
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../includes/classes/Database.php';

$db = new Database();

$knownEntities = $db->getKnownEntities();

if (!$knownEntities) {
    echo json_encode(["error" => "Known entities is null"]);
    exit;
} else {
    echo json_encode($knownEntities);
}

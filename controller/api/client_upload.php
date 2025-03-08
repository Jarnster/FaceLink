<?php
// TODO: Authentication
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["image"])) {
    echo json_encode(["message" => "No image data received"]);
    exit;
}

$image = str_replace("data:image/png;base64,", "", $data["image"]);
$image = base64_decode($image);
$file = uniqid() . ".jpg";
$file_path_runcontext = "uploads/" . $file;

if (file_put_contents($file_path_runcontext, $image)) {
    echo json_encode(["message" => "Image uploaded successfully", "file" => $file]);


    // Register the new analyzer task
    require '../includes/classes/Database.php';

    $db = new Database();

    $analyzerTasks = $db->registerAnalyzerTask($file);
} else {
    echo json_encode(["message" => "Image upload failed"]);
}

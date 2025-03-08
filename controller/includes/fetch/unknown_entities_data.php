<?php
// START OF BASE REQUIRE
$rootPath = __DIR__;
$docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$maxDepth = 10;
$depth = 0;

while (!is_dir($rootPath . '/includes') && $rootPath !== $docRoot && $depth < $maxDepth) {
    $rootPath = dirname($rootPath);
    $depth++;
}

if (!is_dir($rootPath . '/includes')) {
    die(json_encode(['error' => 'Includes directory not found']));
}

// require $rootPath . '/includes/auth.php'; // MHMM...
// END OF BASE REQUIRE

session_start();
if (!isset($_SESSION['user_id'])) {
    exit;
}

require $rootPath . '/includes/utils.php';
require $rootPath . '/includes/classes/Database.php';

$db = new Database();

$pdo = $db->getPDO();
$stmt = $pdo->prepare("SELECT * FROM analyzer_results WHERE recognized_known_entity_id = 0 ORDER BY datetime DESC");
$stmt->execute();
$entities = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

foreach ($entities as &$result_row) {
    $id = $result_row["id"];
    $task_id = $result_row["task_id"];
    $result_row["datetime"] = timeAgo($result_row["datetime"]) ?: "Never detected";

    $stmt_capture = $pdo->prepare("SELECT filename FROM analyzer_tasks WHERE id = ?");
    $stmt_capture->execute([$task_id]);
    $task_row = $stmt_capture->fetch(PDO::FETCH_ASSOC) ?: [];

    $result_row["capture_filename"] = $task_row["filename"];
}

echo json_encode($entities);
exit;

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
$stmt = $pdo->prepare("SELECT * FROM known_entities");
$stmt->execute();
$entities = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

foreach ($entities as &$entity) {
    $id = $entity["id"];
    $entity["last_activity"] = timeAgo(get_entity_last_activity($id, "timestamp")) ?: "Never detected";
    $entity["location_id"] = get_entity_last_activity($id, "location_id") ?? "";
    $entity["permissions_overview"] = "";
    $entity["last_capture_filename"] = get_entity_last_activity($id, "last_capture_src");
}

echo json_encode($entities);
exit;

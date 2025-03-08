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

$taskCount = sizeof($db->getAnalyzerTasks(false));
$knownEntityCount = sizeof($db->getKnownEntities());
$unknownEntityCount = sizeof($db->getUnknownEntities());

$taskColor = match (true) {
    $taskCount <= 2 => '#3fd934',
    $taskCount <= 5 => '#ff6e26',
    $taskCount <= 10 => '#f53d18',
    default => '#ff0000',
};

header('Content-Type: application/json');
echo json_encode([
    'taskCount' => $taskCount,
    'taskColor' => $taskColor,
    'knownEntityCount' => $knownEntityCount,
    'unknownEntityCount' => $unknownEntityCount
]);

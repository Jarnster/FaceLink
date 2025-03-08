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

// END OF BASE REQUIRE

require $rootPath . '/includes/utils.php';

if (!is_controller_user_valid()) {
    header('Location: /login.php');
    exit("Not signed in.");
}

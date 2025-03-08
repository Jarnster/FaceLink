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

require $rootPath . '/includes/auth.php';
// END OF BASE REQUIRE

require $rootPath . '/includes/utils.php';

if (!isset($_GET["id"])) {
    die(json_encode(['error' => 'No entity id provided']));
}

$entity_id = intval($_GET["id"]);

$entity = get_entity_data_from_id($entity_id);

if (!$entity) {
    die(json_encode(['error' => 'Invalid entity']));
}

// Entity Data
$name = $entity["name"];

$last_activity_datetime = get_entity_last_activity($entity_id, "timestamp");

$last_activity_location_id = get_entity_last_activity($entity_id, "location_id");

$last_activity_capture_src = get_entity_last_activity($entity_id, "last_capture_src");
?>

<h2><i class="fa fa-eye"></i> Entity Details: <?= $name ?></h2>
<div class="dashboard-widgets">
    <div class="widget">
        <h2><i class="fa fa-id-card"></i> Information</h2>
        <p>Known: <b>yes</b></p>
        <p>Name: <b><?php echo $name; ?></b></p>
    </div>
    <div class="widget">
        <h2><i class="fa fa-history"></i> Activity History</h2>
        <p>Last activity timestamp:
        <p><?php echo $last_activity_datetime . " <i style='color:lightgrey;'>" . timeAgo($last_activity_datetime) . "</i>"; ?></p>
        <hr>
        <p>Last capture - <?php echo $last_activity_datetime ?> <a class='link' href='?tab=locations'>@ <?php echo $last_activity_location_id ?></a>:</p>
        <img src="/api/uploads/<?php echo $last_activity_capture_src; ?>" alt="Last capture image" width='350px'>
        <hr>
        <h3>All captures:</h3>
        <?php
        require_once 'includes/classes/Database.php';
        $pdo = $db->getPDO();
        $stmt_result = $pdo->prepare("SELECT * FROM analyzer_results WHERE recognized_known_entity_id = ? ORDER BY datetime DESC");
        $stmt_result->execute([$entity_id]);
        if ($stmt_result->rowCount() > 0) {
            echo $stmt_result->rowCount() . " total captures found<hr>";
            while ($result_row = $stmt_result->fetch(PDO::FETCH_ASSOC)) {
                $result_id = $result_row["id"];
                $stmt_task = $pdo->prepare("SELECT * FROM analyzer_tasks WHERE result_id = ? AND is_done = 1");
                $stmt_task->execute([$result_id]);
                $task_rows = $stmt_task->fetchAll(PDO::FETCH_ASSOC);
                foreach ($task_rows as $task_row) {
                    $task_id = $task_row["id"];
                    $datetime = $result_row["datetime"];
                    $filename = $task_row["filename"];
                    $filepath = "/api/uploads/$filename";
                    $location_id = $result_row["location_id"];
                    echo "<i>$datetime <a class='link' href='?tab=locations'>@ $location_id</a></i><br><img src='$filepath' width='150px'><hr>";
                }
            }
        }
        ?>
    </div>
</div>
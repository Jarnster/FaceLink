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

// Get the number of waiting analyzer tasks
$taskCount = sizeof($db->getAnalyzerTasks(false));

// Determine the color based on pending tasks count
if ($taskCount <= 15) {
    $taskColor = '#3fd934';
} elseif ($taskCount <= 35) {
    $taskColor = '#ff6e26';
} elseif ($taskCount <= 100) {
    $taskColor = '#f53d18';
} else {
    $taskColor = '#ff0000';
}
?>

<h2><i class="fa fa-laptop"></i> Dashboard</h2>
<div class="dashboard-widgets">
    <div class="widget">
        <h2><i class="fa fa-eye"></i> Quick Overview</h2>
        <p><b id="knownEntityCount" style="color: grey;"><?php echo sizeof($db->getKnownEntities()); ?></b> Registered
            <a href="?tab=entities" class="link">entities</a>
        </p>
        <p><b id="unknownEntityCount" style="color: grey;"><?php echo sizeof($db->getKnownEntities()); ?></b> Unidentified
            <a href="?tab=unknown_entities" class="link">captures</a>
        </p>
        <p><b id="taskCount" style="color: <?php echo $taskColor; ?>;"><?php echo $taskCount; ?></b> Tasks waiting for analyzer processing</p>
        <i style="color:lightgrey;font-size:12px;">Hint: tasks waiting must be as low as possible to avoid long facial scanning delays</i>
    </div>
</div>

<script>
    function updateDashboard() {
        fetch('includes/fetch/dashboard_data.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('knownEntityCount').textContent = data.knownEntityCount;
                document.getElementById('unknownEntityCount').textContent = data.unknownEntityCount;
                document.getElementById('taskCount').textContent = data.taskCount;
                document.getElementById('taskCount').style.color = data.taskColor;
            })
            .catch(error => console.error('Error fetching dashboard data:', error));
    }

    // Initially load dashboard data and set interval
    updateDashboard();
    setInterval(updateDashboard, 1000);
</script>
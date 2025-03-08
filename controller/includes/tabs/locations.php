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

if (isset($_POST['save'])) {
    $entity_id = intval($_POST['entity_id']);
    $name = htmlspecialchars($_POST['name']) ?? '';

    $metadata = json_encode([]);

    $data = [
        "id" => $entity_id,
        "name" => $name,
    ];

    $entity_id = $db->updateEntity($entity_id, $data);

    // Image upload
    if (!empty($_FILES['reference_image']['name'])) {
        $uploadDir = $rootPath . "\api\uploads";
        $uploadFile = $uploadDir . '\face_' . $entity_id . '.jpg';

        if (move_uploaded_file($_FILES['reference_image']['tmp_name'], $uploadFile)) {
            chmod($uploadFile, 0644);
        } else {
            echo "<p style='color: red;'>Error uploading reference image.</p>";
        }
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
}
?>

<h2><i class="fa fa-hotel"></i> Locations</h2>
<i class='fa fa-check' style='color:lightgreen;'></i> <b>Known Entity</b>
<br>
<i class='fa fa-close' style='color:red;'></i> <b>Unidentified Entity</b>
<hr>

<table class="table">
    <thead>
        <tr>
            <th><i class="fa fa-hashtag"></i> Name</th>
            <th><i class="fa fa-history"></i> Last Capture</th>
            <th><i class="fa fa-area-chart"></i> Amount Last 24h</th>
            <th><i class="fa fa-calendar-week"></i> Amount Last week</th>
            <th><i class="fa fa-calendar"></i> Amount Last month</th>
            <th><i class="fa fa-image"></i> Last Capture Image</th>
            <th><i class="fa fa-bell"></i> Active Alerts</th>
            <th><i class="fa fa-bell"></i> Recent Alerts</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $locations = $db->fetchLocations();
        foreach ($locations as $location_id => $location_data) {
            $last_capture_filename = $location_data["last_capture"];
            $reference_image = "<img src='/api/uploads/$last_capture_filename' width='150px'>"; // HTML element
            $last_detection_timestamp = $location_data["last_detection_timestamp"];
            $amount_24h = $location_data["amount_24h"];
            $amount_7d = $location_data["amount_7d"];
            $amount_30d = $location_data["amount_30d"];

            echo "<tr>";
            echo "<td>$location_id</td>";
            echo "<td>" . timeAgo($last_detection_timestamp) . "</td>";
            echo "<td>$amount_24h</td>";
            echo "<td>$amount_7d</td>";
            echo "<td>$amount_30d</td>";
            echo "<td>$reference_image</td>";

            // Active alerts
            echo "<td>";
            // ... foreach() ...
            echo "</td>";

            // Recent alerts
            echo "<td>";
            // ... foreach() ...
            echo "</td>";

            echo "</tr>";
        }
        ?>
    </tbody>
</table>
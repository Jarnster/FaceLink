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

<h2><i class="fa fa-list-check"></i> Registered Entities</h2>
<?php
$name = "Enter name";

$json = htmlspecialchars(json_encode([
    "id" => 0,
    "name" => $name,
]));

echo "<button class='open-modal button' data-entity='$json'><i class='fa fa-plus'></i> Add new</button>";
echo "<hr>";
?>

<!-- Entity Configuration Modal -->
<div id="entityModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Entity Configuration: <b id="entityIdDisplay" name="entityIdDisplay"></b></h2>
        <form method="post" id="entityForm" name="entityForm" autocomplete="off" enctype="multipart/form-data">
            <label for="description">Entity Id (read-only):</label>
            <input type="text" id="entity_id" name="entity_id" readonly>

            <br>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <br>

            <label for="reference_image">Reference Image:</label>
            <input type="file" id="reference_image" name="reference_image">

            <br><br>

            <button type="submit" name="save" class="button" style="background:green"><i class="fa fa-upload"></i> Save Changes</button>
        </form>
    </div>
</div>


<table class="table">
    <thead>
        <tr>
            <th><i class="fa fa-hashtag"></i> ID</th>
            <th><i class="fa fa-user"></i> Name</th>
            <th><i class="fa fa-history"></i> Last Captured</th>
            <th><i class="fa fa-shield"></i> Permissions Overview</th>
            <th><i class="fa fa-image"></i> Last Capture</th>
            <th><i class="fa fa-image"></i> Reference Image</th>
            <th><i class="fa fa-cogs"></i> Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Empty for dynamic Ajax content -->
    </tbody>
</table>

<script>
    // LOAD ENTITIES
    function loadEntities() {
        fetch('includes/fetch/entities_data.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector(".table tbody");
                tbody.innerHTML = "";
                data.forEach(entity => {
                    let lastActivity = entity.last_activity ? `${entity.last_activity} <a class='link' href='?tab=locations'>@ ${entity.location_id}</a>` : "Never detected";
                    let referenceImage = `<img src='/api/uploads/face_${entity.id}.jpg' width='150px'>`;
                    let lastCaptureImage = `<img src='/api/uploads/${entity.last_capture_filename}' width='150px'>`;

                    let jsonData = JSON.stringify({
                        id: entity.id,
                        name: entity.name
                    });

                    let row = `<tr>
                    <td>${entity.id}</td>
                    <td>${entity.name}</td>
                    <td>${lastActivity}</td>
                    <td>${entity.permissions_overview || ""}</td>
                    <td>${lastCaptureImage}</td>
                    <td>${referenceImage}</td>
                    <td>
                     <a href='?tab=view_entity&id=${entity.id}' class='button'><i class='fa fa-eye'></i> View more</a>
                     <button class='open-modal button' data-entity='${jsonData}'><i class='fa fa-wrench'></i> Configure</button>
                    </td>
                </tr>`;
                    tbody.innerHTML += row;
                });

                // **HERLAAD DE MODAL EVENTS OPNIEUW NA ELKE FETCH**
                attachModalEvents();
            })
            .catch(error => console.error('Error loading entities:', error));
    }

    // **Event listener to open entity modal**
    function attachModalEvents() {
        document.querySelectorAll(".open-modal").forEach(button => {
            button.addEventListener("click", function() {
                const entityData = JSON.parse(this.dataset.entity);
                openModal(entityData);
            });
        });
    }

    // **ENTITY MODAL HANDLER**
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById("entityModal");
        const closeButton = document.querySelector(".close");

        window.openModal = function(entityData) {
            document.getElementById("entity_id").value = entityData.id;
            document.getElementById("entityIdDisplay").innerText = entityData.id;
            document.getElementById("name").value = entityData.name;

            modal.style.display = "block";
        }

        closeButton.addEventListener("click", function() {
            modal.style.display = "none";
        });

        window.addEventListener("click", function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });

        // **Initially load entities and set interval and register modal events**
        loadEntities();
        setInterval(loadEntities, 4000);
    });
</script>
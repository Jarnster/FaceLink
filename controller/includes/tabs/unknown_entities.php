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
?>

<h2><i class="fa fa-user-secret"></i> Unidentified Entities</h2>
<?php
$name = "Enter name";

$json = htmlspecialchars(json_encode([
    "id" => 0,
    "name" => $name,
]));
?>
<hr>

<table class="table">
    <thead>
        <tr>
            <th><i class="fa fa-hashtag"></i> ID</th>
            <th><i class="fa fa-shield"></i> Timestamp & Location</th>
            <th><i class="fa fa-image"></i> Capture</th>
        </tr>
    </thead>
    <tbody>
        <!-- Empty for dynamic Ajax content -->
    </tbody>
</table>

<script>
    // LOAD ENTITIES
    function loadEntities() {
        fetch('includes/fetch/unknown_entities_data.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector(".table tbody");
                tbody.innerHTML = "";
                data.forEach(entity => {
                    let lastActivity = entity.datetime ? `${entity.datetime} <a class='link' href='?tab=locations'>@ ${entity.location_id}</a>` : "Never detected";
                    let referenceImage = `<img src='/api/uploads/${entity.capture_filename}' width='150px'>`;
                    let jsonData = JSON.stringify({
                        id: entity.id,
                        name: entity.name
                    });

                    let row = `<tr>
                    <td>${entity.id}</td>
                    <td>${lastActivity}</td>
                    <td>${referenceImage}</td>
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

        // **Initially load entities and register modal events**
        loadEntities();
        setInterval(loadEntities, 4000);
    });
</script>
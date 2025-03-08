<?php

class Database
{
    private $pdo = null;

    public function __construct()
    {
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

        $DB_HOST = get_config_value("DB_HOST") or "127.0.0.1";
        $DB_USERNAME = get_config_value("DB_USERNAME") or "root";
        $DB_PASSWORD = get_config_value("DB_PASSWORD") or "root";
        $DB_DATABASE = get_config_value("DB_DATABASE") or "facelink";
        $DB_DSN = "mysql:host=" . $DB_HOST . ";dbname=" . $DB_DATABASE . ";charset=utf8mb4";

        try {
            $this->pdo = new PDO($DB_DSN, $DB_USERNAME, $DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    public function getUserRow(string $username)
    {
        $stmt = $this->getPDO()->prepare("SELECT * FROM controller_users WHERE LOWER(username) = LOWER(?) LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function getAnalyzerTasks(bool $markAsInProgress = true)
    {
        $maxClockAnalyzerTasks = get_config_value("MAX_THREAD_ANALYZER_TASKS") or 5; // Max amount of analyzer tasks to give to the analyzer at a time, for each analyzer check tick
        if ($markAsInProgress == false) {
            $maxClockAnalyzerTasks = 9999; // When markAsInProgress is false, this means it will be for monitoring, so don't set a limit to make it possible to view all
        }
        $stmt = $this->getPDO()->prepare("SELECT * FROM analyzer_tasks WHERE is_done = 0 AND in_progress = 0 LIMIT " . $maxClockAnalyzerTasks);
        $stmt->execute([]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if ($markAsInProgress == true && !empty($tasks)) {
            // Extract IDs from fetched tasks
            $taskIds = array_column($tasks, 'id');
            $placeholders = implode(',', array_fill(0, count($taskIds), '?'));

            // SET IN PROGRESS TRUE for selected tasks
            $stmt = $this->getPDO()->prepare("UPDATE analyzer_tasks SET in_progress = 1 WHERE id IN ($placeholders)");
            $stmt->execute($taskIds);
        }

        return $tasks;
    }

    public function registerAnalyzerTask($file)
    {
        $stmt = $this->getPDO()->prepare("INSERT INTO analyzer_tasks (filename)
        VALUES(?)");
        $stmt->execute([$file]);
    }

    public function updateAnalyzerTaskResult(int $task_id, int $recognized_known_entity_id, string $location_id)
    {
        $pdo = $this->getPDO();

        // Insert new result
        $stmt_result = $pdo->prepare("INSERT INTO analyzer_results (recognized_known_entity_id, task_id, location_id)
            VALUES(?, ?, ?)");
        $stmt_result->execute([$recognized_known_entity_id, $task_id, $location_id]);

        // Get the last inserted ID
        $result_id = $pdo->lastInsertId();

        // Update task status
        $stmt_progress = $pdo->prepare("UPDATE analyzer_tasks SET is_done = 1, in_progress = 0, result_id = ? WHERE id = ?");
        $stmt_progress->execute([$result_id, $task_id]);
    }

    public function getKnownEntities()
    {
        $stmt = $this->getPDO()->prepare("SELECT * FROM known_entities");
        $stmt->execute();
        $stmt_result = $stmt->fetchAll() ?: [];

        return $stmt_result;
    }

    public function getUnknownEntities()
    {
        $stmt = $this->getPDO()->prepare("SELECT * FROM analyzer_results WHERE recognized_known_entity_id = 0");
        $stmt->execute();
        $stmt_result = $stmt->fetchAll() ?: [];

        return $stmt_result;
    }

    public function updateEntity(int $entity_id, array $data)
    {
        if ($entity_id == 0) {
            $stmt = $this->getPDO()->prepare("INSERT INTO known_entities (name) VALUES (?)");
            $stmt->execute([$data["name"]]);
            $entity_id = $this->getPDO()->lastInsertId();
            return $entity_id;
        } else {
            $stmt = $this->getPDO()->prepare("UPDATE known_entities SET name = ?, zone_configuration = ?, metadata = ? WHERE id = ?");
            $stmt->execute([
                $data["name"],
                $data["zone_configuration"] ?? json_encode([]),
                $data["metadata"] ?? json_encode([]),
                $entity_id
            ]);
        }

        return $entity_id;
    }

    public function fetchLocations()
    {
        $locations = array();
        $stmt = $this->getPDO()->prepare("SELECT * FROM analyzer_results ORDER BY datetime DESC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $location_id = $row["location_id"];
            $datetime = $row["datetime"];
            if (!isset($locations[$location_id])) {
                // Get last detection timestamp
                $last_detection_timestamp = $datetime;

                // Get the amount of detections within the last 24 hours

                // KNOWN
                $stmt_24h_k = $this->getPDO()->prepare("SELECT * FROM analyzer_results WHERE location_id = ? AND recognized_known_entity_id != 0 AND datetime >= NOW() - INTERVAL 24 HOUR ORDER BY datetime DESC");
                $stmt_24h_k->execute(
                    [$location_id]
                );

                // UNKNOWN
                $stmt_24h_u = $this->getPDO()->prepare("SELECT * FROM analyzer_results WHERE location_id = ? AND recognized_known_entity_id = 0 AND datetime >= NOW() - INTERVAL 24 HOUR ORDER BY datetime DESC");
                $stmt_24h_u->execute(
                    [$location_id]
                );

                $amount_24h = $stmt_24h_k->rowCount() . " <i class='fa fa-check' style='color:lightgreen;'></i> <br> " . $stmt_24h_u->rowCount() . " <i class='fa fa-close' style='color:red;'></i>";

                // Get the amount of detections within the last week

                // KNOWN
                $stmt_7d_k = $this->getPDO()->prepare("SELECT * FROM analyzer_results WHERE location_id = ? AND recognized_known_entity_id != 0 AND datetime >= NOW() - INTERVAL 1 WEEK ORDER BY datetime DESC");
                $stmt_7d_k->execute(
                    [$location_id]
                );

                // UNKNOWN
                $stmt_7d_u = $this->getPDO()->prepare("SELECT * FROM analyzer_results WHERE location_id = ? AND recognized_known_entity_id = 0 AND datetime >= NOW() - INTERVAL 1 WEEK ORDER BY datetime DESC");
                $stmt_7d_u->execute(
                    [$location_id]
                );

                $amount_7d = $stmt_7d_k->rowCount() . " <i class='fa fa-check' style='color:lightgreen;'></i> <br> " . $stmt_7d_u->rowCount() . " <i class='fa fa-close' style='color:red;'></i>";

                // Get the amount of detections within the last month

                // KNOWN
                $stmt_30d_k = $this->getPDO()->prepare("SELECT * FROM analyzer_results WHERE location_id = ? AND recognized_known_entity_id != 0 AND datetime >= NOW() - INTERVAL 1 MONTH ORDER BY datetime DESC");
                $stmt_30d_k->execute(
                    [$location_id]
                );

                // UNKNOWN
                $stmt_30d_u = $this->getPDO()->prepare("SELECT * FROM analyzer_results WHERE location_id = ? AND recognized_known_entity_id = 0 AND datetime >= NOW() - INTERVAL 1 MONTH ORDER BY datetime DESC");
                $stmt_30d_u->execute(
                    [$location_id]
                );

                $amount_30d = $stmt_30d_k->rowCount() . " <i class='fa fa-check' style='color:lightgreen;'></i> <br> " . $stmt_30d_u->rowCount() . " <i class='fa fa-close' style='color:red;'></i>";

                // Get the last capture
                $pdo = $this->getPDO();
                $stmt_last_capture = $pdo->prepare("SELECT * FROM analyzer_tasks WHERE is_done = 1 ORDER BY result_id DESC LIMIT 1");
                $stmt_last_capture->execute([]);
                if ($stmt_last_capture->rowCount() > 0) {
                    $row = $stmt_last_capture->fetch(PDO::FETCH_ASSOC);
                    $filename = $row["filename"];
                }
                $last_capture = $filename or "none";

                // Push values to the array
                $locations[$location_id] = array("last_detection_timestamp" => $last_detection_timestamp, "amount_24h" => $amount_24h, "amount_7d" => $amount_7d, "amount_30d" => $amount_30d, "last_capture" => $last_capture);
            }
        }
        return $locations;
    }
}

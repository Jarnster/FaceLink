<?php

if (!function_exists("is_controller_user_valid")) {
    function is_controller_user_valid()
    {
        require_once 'classes/Database.php';
        $db = new Database();

        session_start();
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $pdo = $db->getPDO();
        $stmt = $pdo->prepare("SELECT id FROM controller_users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() !== false;
    }
}

if (!function_exists("get_entity_last_activity")) {
    function get_entity_last_activity(int $entityId, string $field = "timestamp")
    {
        require_once 'classes/Database.php';
        $db = new Database();

        $pdo = $db->getPDO();
        $stmt = $pdo->prepare("SELECT * FROM analyzer_results WHERE recognized_known_entity_id = ? ORDER BY datetime DESC LIMIT 1");
        $stmt->execute([$entityId]);
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($field == "timestamp") {
                $datetime = $row["datetime"];
                return $datetime ?? "No activity reported";
            } else if ($field == "location_id") {
                $pdo = $db->getPDO();
                $stmt = $pdo->prepare("SELECT * FROM analyzer_tasks WHERE result_id = ? AND is_done = 1 LIMIT 1");
                $stmt->execute([$row["id"]]);
                $location_id = $row["location_id"];
                return $location_id ?? "Unknown location_id";
            } else if ($field == "last_capture_src") {
                $pdo = $db->getPDO();
                $stmt = $pdo->prepare("SELECT * FROM analyzer_tasks WHERE result_id = ? AND is_done = 1 LIMIT 1");
                $stmt->execute([$row["id"]]);
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $filename = $row["filename"];
                    return $filename;
                }
            }
        }
    }
}

if (!function_exists("get_entity_data_from_id")) {
    function get_entity_data_from_id(int $entityId)
    {
        require_once 'classes/Database.php';
        $db = new Database();

        $pdo = $db->getPDO();
        $stmt = $pdo->prepare("SELECT * FROM known_entities WHERE id = ? LIMIT 1");
        $stmt->execute([$entityId]);
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        }
    }
}

if (!function_exists('get_config_value')) {
    function get_config_value(string $key)
    {
        static $CONFIG = null;

        if ($CONFIG === null) {
            $configPath = realpath(__DIR__ . '/config.php');
            if (!$configPath || !file_exists($configPath)) {
                throw new RuntimeException("Config file not found: " . $configPath);
            }

            $CONFIG = require $configPath;

            if (!is_array($CONFIG)) {
                throw new RuntimeException("Config file did not return an array.");
            }
        }

        return $CONFIG[$key] ?? null;
    }
}

if (!function_exists("timeAgo")) {
    function timeAgo($datetime)
    {
        $TZ = get_config_value("TZ") or "America/New_York";
        date_default_timezone_set($TZ);

        $timestamp = strtotime($datetime);
        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 0) {
            return "In the future";
        }

        $units = [
            "year" => 31536000,
            "month" => 2592000,
            "day" => 86400,
            "hour" => 3600,
            "minute" => 60,
            "second" => 1
        ];

        $result = [];
        foreach ($units as $unit => $value) {
            if ($diff >= $value) {
                $count = floor($diff / $value);
                $result[] = "$count $unit" . ($count > 1 ? "s" : "");
                $diff %= $value;
            }
            if (count($result) == 2) break; // Max 2 time units
        }

        return implode(" and ", $result) . " ago";
    }
}

<?php
// logging_functions.php

/**
 * Log daily summary for a station.
 * Updates or creates a file per day and keeps one line per station.
 *
 * @param string $station Station name
 * @param array $skipped Array of skipped timestamps
 * @param array $failed Array of failed timestamps
 */
function logDailySummary($station, $skipped = [], $failed = []) {
    $logDir = __DIR__ . "/logs/daily";
    if (!file_exists($logDir)) mkdir($logDir, 0777, true);

    $dateStr = date("Y-m-d"); // daily file
    $logFile = "$logDir/summary_$dateStr.txt";

    $summary = [];

    // load existing summaries if file exists
    if (file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (preg_match("/^\[([^\]]+)\]\s+(.*)$/", $line, $matches)) {
                $summary[$matches[1]] = $matches[2];
            }
        }
    }

    // get previous unique sets for station if they exist
    $prevData = ["success" => [], "skipped" => [], "failed" => []];
    if (isset($summary[$station])) {
        if (preg_match_all("/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/", $summary[$station], $matches)) {
            // assume timestamps were saved in previous line (optional)
        }
    }

    // merge new skipped/failed
    $failedUnique  = array_unique($failed);
    $skippedUnique = array_unique($skipped);

    // successes = attempted - skipped - failed
    $allUnique = array_unique(array_merge($failedUnique, $skippedUnique));
    $successCount = count($allUnique) ? count($allUnique) - count($failedUnique) - count($skippedUnique) : 0;

    $line = sprintf(
        "%d transmissions attempted: %d successful, %d skipped, %d failed",
        count($allUnique),
        $successCount,
        count($skippedUnique),
        count($failedUnique)
    );

    $summary[$station] = $line;

    // write all lines back to file
    $outputLines = [];
    foreach ($summary as $stn => $txt) {
        $outputLines[] = "[$stn] $txt";
    }

    file_put_contents($logFile, implode("\n", $outputLines) . "\n");
}

/**
 * Log weekly summary for a station.
 * Works like daily but accumulates over a week.
 *
 * @param string $station Station name
 * @param array $skipped Array of skipped timestamps
 * @param array $failed Array of failed timestamps
 */
function logWeeklySummary($station, $skipped = [], $failed = []) {
    $logDir = __DIR__ . "/logs/weekly";
    if (!file_exists($logDir)) mkdir($logDir, 0777, true);

    // calculate ISO week
    $weekStart = date("Y-m-d", strtotime("monday this week"));
    $weekEnd   = date("Y-m-d", strtotime("sunday this week"));
    $logFile   = "$logDir/summary_{$weekStart}_to_{$weekEnd}.txt";

    $summary = [];

    // load existing summaries if file exists
    if (file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (preg_match("/^\[([^\]]+)\]\s+(.*)$/", $line, $matches)) {
                $summary[$matches[1]] = $matches[2];
            }
        }
    }

    // merge new data with previous counts
    $prevFailed  = $summary[$station]['failed'] ?? [];
    $prevSkipped = $summary[$station]['skipped'] ?? [];

    $failedUnique  = array_unique(array_merge($prevFailed, $failed));
    $skippedUnique = array_unique(array_merge($prevSkipped, $skipped));

    $allUnique = array_unique(array_merge($failedUnique, $skippedUnique));
    $successCount = count($allUnique) ? count($allUnique) - count($failedUnique) - count($skippedUnique) : 0;

    $line = sprintf(
        "%d transmissions attempted: %d successful, %d skipped, %d failed",
        count($allUnique),
        $successCount,
        count($skippedUnique),
        count($failedUnique)
    );

    $summary[$station] = $line;

    // write all lines back to file
    $outputLines = [];
    foreach ($summary as $stn => $txt) {
        $outputLines[] = "[$stn] $txt";
    }

    file_put_contents($logFile, implode("\n", $outputLines) . "\n");
}
?>

<?php
// logging_functions.php

/**
 * Path to logs folder (adjust if needed)
 */
define('LOG_DIR', __DIR__ . '/logs');
define('DAILY_TRACKING_DIR', LOG_DIR . '/daily_tracking');
define('WEEKLY_TRACKING_DIR', LOG_DIR . '/weekly_tracking');
define('DAILY_SUMMARY_DIR', LOG_DIR . '/daily');
define('WEEKLY_SUMMARY_DIR', LOG_DIR . '/weekly');

// Ensure directories exist
foreach ([DAILY_TRACKING_DIR, WEEKLY_TRACKING_DIR, DAILY_SUMMARY_DIR, WEEKLY_SUMMARY_DIR] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

/**
 * Update the daily summary log for a station
 *
 * @param string $station Station name
 * @param array $transmissions Array of ['timestamp' => status] for this run
 */
function update_daily_summary($station, $transmissions) {
    $date = date("Y-m-d");
    $trackingFile = DAILY_TRACKING_DIR . "/{$station}_{$date}.json";
    $summaryFile  = DAILY_SUMMARY_DIR . "/summary_{$date}.txt";

    // Load existing tracking info
    $tracking = file_exists($trackingFile) ? json_decode(file_get_contents($trackingFile), true) : [];

    // Update tracking with new transmissions
    foreach ($transmissions as $ts => $status) {
        if (!isset($tracking[$ts]) || $tracking[$ts] !== $status) {
            $tracking[$ts] = $status;
        }
    }

    // Save tracking
    file_put_contents($trackingFile, json_encode($tracking, JSON_PRETTY_PRINT));

    // Aggregate counts
    $counts = ['success' => 0, 'skipped' => 0, 'failed' => 0];
    foreach ($tracking as $s) {
        if (isset($counts[$s])) $counts[$s]++;
    }

    // Write single-line summary
    $line = sprintf("[%s] %d transmissions attempted: %d successful, %d skipped, %d failed\n",
        $station,
        array_sum($counts),
        $counts['success'],
        $counts['skipped'],
        $counts['failed']
    );

    // Append/update daily summary file
    $allLines = file_exists($summaryFile) ? file($summaryFile, FILE_IGNORE_NEW_LINES) : [];
    $found = false;
    foreach ($allLines as &$l) {
        if (strpos($l, "[$station]") === 0) {
            $l = rtrim($line);
            $found = true;
            break;
        }
    }
    if (!$found) $allLines[] = rtrim($line);

    file_put_contents($summaryFile, implode("\n", $allLines) . "\n");
}

/**
 * Update the weekly summary log for a station
 *
 * @param string $station Station name
 * @param array $transmissions Array of ['timestamp' => status] for this run
 */
function update_weekly_summary($station, $transmissions) {
    // Determine ISO week and year
    $weekNum = date("oW"); // e.g., 202538 = 2025, week 38
    $trackingFile = WEEKLY_TRACKING_DIR . "/{$station}_week{$weekNum}.json";
    $summaryFile  = WEEKLY_SUMMARY_DIR . "/summary_{$weekNum}.txt";

    // Load existing tracking info
    $tracking = file_exists($trackingFile) ? json_decode(file_get_contents($trackingFile), true) : [];

    // Update tracking
    foreach ($transmissions as $ts => $status) {
        if (!isset($tracking[$ts]) || $tracking[$ts] !== $status) {
            $tracking[$ts] = $status;
        }
    }

    // Save tracking
    file_put_contents($trackingFile, json_encode($tracking, JSON_PRETTY_PRINT));

    // Aggregate counts
    $counts = ['success' => 0, 'skipped' => 0, 'failed' => 0];
    foreach ($tracking as $s) {
        if (isset($counts[$s])) $counts[$s]++;
    }

    // Write single-line summary
    $line = sprintf("[%s] %d transmissions attempted: %d successful, %d skipped, %d failed\n",
        $station,
        array_sum($counts),
        $counts['success'],
        $counts['skipped'],
        $counts['failed']
    );

    // Append/update weekly summary file
    $allLines = file_exists($summaryFile) ? file($summaryFile, FILE_IGNORE_NEW_LINES) : [];
    $found = false;
    foreach ($allLines as &$l) {
        if (strpos($l, "[$station]") === 0) {
            $l = rtrim($line);
            $found = true;
            break;
        }
    }
    if (!$found) $allLines[] = rtrim($line);

    file_put_contents($summaryFile, implode("\n", $allLines) . "\n");
}
?>

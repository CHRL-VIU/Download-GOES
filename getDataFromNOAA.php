<?php
// NOAA data retrieval and table update script
// -------------------------------------------

// Show all errors and warnings in CLI
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the creds
require 'config.php';

// Get the station field definitions
require 'tbl_defs.php';

// Get functions
require 'noaa_functions.php';

// Get logging functions
require 'logging_functions.php';

// ------------------------------------------------------------
// RAW TABLE UPDATES
// ------------------------------------------------------------
foreach ($nesids as $stnName => $nesid) {

    // update search criteria file based on provided NESID 
    // file format is: 
    // DRS_SINCE: now - 180 minutes 
    // DRS_UNTIL: now 
    // DCP_ADDRESS: 49A0216E 
    // located at /LRGS/MessageBrowser.sc
    // IMPORTANT: Retrieval timeframe is controlled by user setting DRS_SINCE in /LRGS/MessageBrowser_steady.sc

    updateNesid(LRGS_QUERY_IN, LRGS_QUERY_OUT, $nesid);
    echo "Starting LRGS data request for station: $stnName ($nesid)\n";

    $output = shell_exec(CMD);

    // parseDataFromNOAA returns all timestamps, skipped, errors
    $stationSummary = parseDataFromNOAA($output, $stnName, $stations);

    $allTimestamps = $stationSummary['all_timestamps'] ?? [];
    $skipped       = $stationSummary['skipped'] ?? [];
    $failed        = $stationSummary['errors'] ?? [];

    $successes = array_diff($allTimestamps, array_merge($skipped, $failed));

    // Optional logging
    // logDailySummary($stnName, $successes, $skipped, $failed);
    // logWeeklySummary($stnName, $successes, $skipped, $failed);
}

echo "Finished raw table updates. Starting clean table updates...\n";

// ------------------------------------------------------------
// CLEAN TABLE UPDATES
// ------------------------------------------------------------
$numRowsToClean = 240;
$stnToKpa      = ['lowercain', 'cainridgerun'];

foreach ($nesids as $stnName => $nesid) {

    // Open ONE connection per station
    $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error() . "\n";
        exit;
    }

    $rawRows = getMySQLRows("raw_$stnName", $numRowsToClean);

    foreach ($rawRows as $line) {

        // Select only process_fields from raw row
        $processKeys  = array_flip($stations[$stnName]['process_fields']);
        $processArray = array_intersect_key($line, $processKeys);

        // Common filtering
        if (isset($processArray['PC']) && abs($processArray['PC']) > 9999) {
            $processArray['PC'] = NULL;
        }

        // Station-specific adjustments
        switch ($stnName) {
            case 'uppercruickshank':
                $processArray['SDepth'] = ($processArray['SDepth'] - (609.7 - 572.1))
                                         * sqrt(($processArray['Temp'] + 273.15) / 273.15);
                $processArray['PC'] *= 1000;
                break;

            case 'tetrahedron':
                $processArray['Rn_1'] *= 2;
                break;

            case 'plummerhut':
                $processArray['SDepth'] += 630;
                break;

            case 'lowercain':
                $processArray['SW'] -= 116;
                break;

            case 'upperskeena':
                $processArray['PC'] = ($processArray['PC'] - 11.255) * 1000;
                break;

            case 'mountmaya':
                $processArray['BP'] += 18.63203478;
                $processArray['Pcp1hr'] *= 1000;
                break;

            case 'placeglacier':
                $processArray['SDepth'] -= 122.3;
                break;

            case 'mountarrowsmith':
                $processArray['SW'] += 488;
                break;
        }

        // Adjust BP if needed
        if (!in_array($stnName, $stnToKpa) && isset($processArray['BP'])) {
            $processArray['BP'] /= 10;
        }

        // Add derived water year
        $curDateTime = $line['DateTime'];
        $curWatYr    = wtr_yr($curDateTime, 10);

        // ----------------------------------------------------
        // Build final array using pairwise mapping
        // ----------------------------------------------------
        $finalArray     = [];
        $processFields  = $stations[$stnName]['process_fields'];
        $cleanFields    = $stations[$stnName]['clean_fields'];

        for ($i = 0; $i < count($cleanFields); $i++) {

            $cleanCol = $cleanFields[$i];
            $procCol  = $processFields[$i] ?? null;

            // If this process column is 'WatYr', compute it; otherwise use processArray
            if ($procCol === 'WatYr') {
                $val = wtr_yr($line['DateTime'], 10);
            } else {
                $val = $procCol ? ($processArray[$procCol] ?? NULL) : NULL;
            }

            $finalArray[] = $val;
        }

        // ----------------------------------------------------
        // Build SQL values safely
        // ----------------------------------------------------
        $values = [];
        foreach ($finalArray as $val) {
            if (is_null($val)) {
                $values[] = "NULL";
            } elseif (is_numeric($val)) {
                $values[] = $val;
            } else {
                $values[] = "'" . mysqli_real_escape_string($conn, $val) . "'";
            }
        }

        $valueString = implode(",", $values);
        $cleanNames  = implode(",", $cleanFields);

        $query = "INSERT IGNORE INTO `clean_$stnName` ($cleanNames) VALUES ($valueString)";
        if (!mysqli_query($conn, $query)) {
            echo "Error updating clean_$stnName: " . mysqli_error($conn) . "\n";
        }
    }

    // Close connection once per station
    mysqli_close($conn);
}

echo "Finished clean table updates for all stations.\n";
?>

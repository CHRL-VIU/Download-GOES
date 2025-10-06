<?php
// this script queries the NOAA server using LRGS scripts available here https://dcs1.noaa.gov/Account/Login

// get the creds
require 'config.php';

// get the tbl field definitions
require 'tbl_defs.php';

// get the functions
require 'noaa_functions.php';

// get logging functions
require 'logging_functions.php';

// update search criteria file based on provided NESID 
// file format is: 
// DRS_SINCE: now - 180 minutes
// DRS_UNTIL: now
// DCP_ADDRESS: 49A0216E
// located at /LRGS/MessageBrowser.sc

// -------------------------------
// Start raw table update and logging
// -------------------------------
foreach ($nesids as $name => $id){
    try {
        updateNesid(LRGS_QUERY_IN, LRGS_QUERY_OUT, $id);
        echo "Starting the LRGS data request for station: $id\n";

        $output = shell_exec(CMD);

        // parseDataFromNOAA now returns all timestamps, skipped timestamps, and errors
        $stationSummary = parseDataFromNOAA($output, $name, $fields);

        $allTimestamps = $stationSummary['all_timestamps'] ?? [];
        $skipped       = $stationSummary['skipped'] ?? [];
        $failed        = $stationSummary['errors'] ?? [];

        // calculate successes
        $successes = array_diff($allTimestamps, array_merge($skipped, $failed));

        // update auxiliary daily/weekly logs
        logDailySummary($name, $successes, $skipped, $failed);
        logWeeklySummary($name, $successes, $skipped, $failed);

    } catch (Exception $e) {
        $failed = [$e->getMessage()];
        logDailySummary($name, [], [], $failed);
        logWeeklySummary($name, [], [], $failed);
        continue;
    }
}


echo "Finished the raw table update... starting the clean tables now...\n";
// -------------------------------
// Start Clean Table update
// -------------------------------
$numRowsToClean = 240;
$stnToKpa = array("lowercain", "cainridgerun");

foreach ($nesids as $curStation => $nesid) {
    $rawRows = getMySQLRows("raw_$curStation", $numRowsToClean);

    foreach ($rawRows as $line) {
        $filterKeys = array_flip($filterFields[$curStation]);
        $filterArray = array_intersect_key($line, $filterKeys);

        // filter invalid PC values
        if(array_key_exists('PC', $filterArray) && abs($filterArray['PC']) > 9999){
            $filterArray['PC'] = NULL;
        }

        // station-specific offsets and adjustments
        if($curStation == "uppercruickshank"){
            $filterArray['SDepth'] = $filterArray['SDepth'] - (609.7-572.1);
            $filterArray['SDepth'] = $filterArray['SDepth']*(sqrt(($filterArray['Temp'] + 273.15)/273.15));
            $filterArray['PC'] = $filterArray['PC'] * 1000;
        }
        if($curStation == "tetrahedron"){
            $filterArray['Rn_1'] = $filterArray['Rn_1'] * 2;
        }
        if($curStation == "plummerhut"){
            $filterArray['SDepth'] = $filterArray['SDepth'] + 630;
        }
        if($curStation == "lowercain"){
            $filterArray['SW'] = $filterArray['SW'] - 116;
        }
        if(!in_array($curStation, $stnToKpa)){
            $filterArray['BP'] = $filterArray['BP'] / 10;
        }
        if($curStation == "upperskeena"){
            $filterArray['PC'] = ($filterArray['PC'] - 11.255) * 1000;
        }
        if($curStation == "mountmaya"){
            $filterArray['BP'] = ($filterArray['BP'] + 18.63203478);
            $filterArray['Pcp1hr'] = $filterArray['Pcp1hr'] * 1000;
        }
        if($curStation == "placeglacier"){
            $filterArray['SDepth'] = $filterArray['SDepth'] - 122.3;
        }
        if($curStation == "mountarrowsmith"){
            $filterArray['SW'] = $filterArray['SW'] + 488;
        }

        $curDateTime = $line["DateTime"];
        $curWatYr = wtr_yr($curDateTime, 10);

        $finalArray = array_slice($filterArray, 0, 1, TRUE) + array("WatYr" => $curWatYr) + array_slice($filterArray, 1, 20, TRUE);

        // convert clean array to string for SQL insert
        $string = implode("','", $finalArray);
        $cleanNames = implode(",", $cleanFields[$curStation]);

        $query = "INSERT IGNORE into `clean_$curStation` ($cleanNames) values('$string')";

        $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);

        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit;
        }

        if (!mysqli_query($conn, $query)) {
            echo "Update ".$curStation." Clean Table Error description: " . mysqli_error($conn);
            continue;
        }
    }
}

// free result set
mysqli_close($conn); 

echo "Finished table updates for all stations.\n";
?>

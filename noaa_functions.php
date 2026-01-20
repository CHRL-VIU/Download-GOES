<?php
// define functions for NOAA query

function wtr_yr ($DATETIME, $START_MONTH=10) {
    $datetime = strtotime($DATETIME);
    $curYear = date("Y", $datetime);
    $curMonth= date("m",$datetime);
    $offset = ($curMonth >= $START_MONTH) ? 1 : 0;
    return $curYear + $offset;
} 

function updateNesid ($SEARCHFILE_IN, $SEARCHFILE_OUT, $NESID) {
    $searchCritRaw = file_get_contents($SEARCHFILE_IN);
    $searchCritNew = substr_replace($searchCritRaw, $NESID, -10, 8);
    file_put_contents($SEARCHFILE_OUT, $searchCritNew);
}

// Updated parseDataFromNOAA: safe mapping from msg_fields → raw_fields
function parseDataFromNOAA($rawOutput, $stnname, $fieldsArray) {
    $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        return ['all_timestamps' => [], 'skipped' => [], 'errors' => ["DB connection failed"]];
    }

    $msg_fields = $fieldsArray[$stnname]['msg_fields'];   // full message fields
    $raw_fields = $fieldsArray[$stnname]['raw_fields'];   // subset for raw table
    $field_length = count($msg_fields);

    // Precompute indices of raw_fields within msg_fields
    $rawIndexes = [];
    foreach ($raw_fields as $rf) {
        $idx = array_search($rf, $msg_fields);
        if ($idx !== false) {
            $rawIndexes[] = $idx;
        } else {
            echo "[WARN] raw_field '$rf' not found in msg_fields for $stnname\n";
        }
    }

    $all_timestamps = [];
    $skipped = [];
    $errors = [];

    $array = explode("@", $rawOutput); // split raw string into transmissions
    foreach ($array as $line) {
        if (trim($line) == "") continue;

        $lineArray = preg_split('/[\s]+/', $line);

        // grab elements for datetime
        $hr = substr($lineArray[0], 13, 2);
        $yr = "20" . substr($lineArray[0], 8, 2);
        $jday = substr($lineArray[0], 10, 3);

        $datetime = date("Y-m-d H:00:00", strtotime('+' . $jday . ' days', mktime($hr, 0, 0, 1, 0, $yr)) - (8*60*60));
        $all_timestamps[] = $datetime;

        // remove raw NOAA timestamp / NESID string and add datetime at front
        array_shift($lineArray);
        array_unshift($lineArray, $datetime);
        array_pop($lineArray); // remove empty last element if exists

        // Safety: skip if the message does not match expected msg_fields length
        if (count($lineArray) != $field_length) {
            $skipped[] = $datetime;
            continue;
        }

        // Build values for raw table using precomputed indices
        $insertValues = [];
        foreach ($rawIndexes as $idx) {
            $insertValues[] = $lineArray[$idx] ?? null;
        }

        // Escape values for SQL
        $values = [];
        foreach ($insertValues as $val) {
            if (is_null($val)) {
                $values[] = "NULL";
            } elseif (is_numeric($val)) {
                $values[] = $val;
            } else {
                $values[] = "'" . mysqli_real_escape_string($conn, $val) . "'";
            }
        }

        $fieldsStr = implode(",", $raw_fields);
        $valuesStr = implode(",", $values);

        $query = "INSERT IGNORE INTO `raw_$stnname` ($fieldsStr) VALUES ($valuesStr)";
        if (!mysqli_query($conn, $query)) {
            $errors[] = "Failed insert at $datetime: " . mysqli_error($conn);
            $skipped[] = $datetime;
        }
    }

    mysqli_close($conn);
    return ['all_timestamps' => $all_timestamps, 'skipped' => $skipped, 'errors' => $errors];
}

function getMySQLRows($stationName, $numRows) {
    $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit;
    }

    $sql = "(SELECT * FROM `$stationName` ORDER BY DateTime desc LIMIT $numRows) order by DateTime";
    $result = mysqli_query($conn,$sql);

    if(!$result) exit("Select Query Error Description: ". mysqli_error($conn));

    $raw_array = [];
    while($row = mysqli_fetch_assoc($result)) {
        $raw_array[] = $row;
    }

    mysqli_close($conn);
    return $raw_array;
}
?>

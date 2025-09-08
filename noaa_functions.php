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

// Updated parseDataFromNOAA to track all timestamps for success calculation
function parseDataFromNOAA ($rawOutput, $stnname, $fieldsArray){
    $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        return ['all_timestamps'=>[], 'skipped'=>[], 'errors'=>["DB connection failed"]];
    }

    $fields = $fieldsArray[$stnname];
    $field_length = count(explode(", ", $fields));
    $array = explode("@", $rawOutput); // split raw string into transmissions

    $all_timestamps = [];
    $skipped = [];
    $errors = [];

    foreach ($array as $line) {
        if(trim($line) == "") continue;

        $lineArray = preg_split('/[\s]+/', $line);

        // grab elements for datetime
        $hr = substr($lineArray[0], 13, 2);
        $yr = "20" . substr($lineArray[0], 8, 2);
        $jday = substr($lineArray[0], 10, 3);

        $datetime = date("Y-m-d H:00:00", strtotime('+'.$jday.' days', mktime($hr, 0, 0, 1, 0, $yr)) - (8*60*60));
        $all_timestamps[] = $datetime;

        // remove raw NOAA timestamp / nesid string
        array_shift($lineArray);
        array_unshift($lineArray, $datetime);
        array_pop($lineArray); // remove empty last element

        // skip transmission if number of fields mismatch
        if(count($lineArray) != $field_length){
            $skipped[] = $datetime;
            continue;
        }

        // escape single quotes in data
        $lineArray = array_map(function($v){ return str_replace("'", "\\'", $v); }, $lineArray);
        $datString = implode("','", $lineArray);
        $query = "INSERT IGNORE INTO `raw_$stnname` ($fields) VALUES('$datString')";

        if (!mysqli_query($conn, $query)) {
            $errors[] = "Failed insert at $datetime: ".mysqli_error($conn);
            $skipped[] = $datetime;
            continue;
        }
    }

    mysqli_close($conn);

    return [
        'all_timestamps' => $all_timestamps,  // NEW: return all timestamps seen
        'skipped'        => $skipped,
        'errors'         => $errors
    ];
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

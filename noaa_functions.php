<?php
// define functions for NOAA query

// update search criteria file based on provided NESID 
// file format is: 
// DRS_SINCE: now - 180 minutes
// DRS_UNTIL: now
// DCP_ADDRESS: 49A0216E
// located at /LRGS/MessageBrowser.sc

function wtr_yr ($DATETIME, $START_MONTH=10) {
  # Convert dates into POSIXlt
  $datetime = strtotime($DATETIME);
  $curYear = date("Y", $datetime);
  $curMonth= date("m",$datetime);
  # Year offset
  if($curMonth >= $START_MONTH){
    $offset = 1;
  }
  else{$offset = 0;}

  # water year
  $adjYear = $curYear+$offset;

  # return water year
  return $adjYear;
} 

function updateNesid ($SEARCHFILE_IN, $SEARCHFILE_OUT, $NESID) {
        $searchCritRaw = file_get_contents($SEARCHFILE_IN); // reads an array of lines
        $searchCritNew = (substr_replace($searchCritRaw, $NESID, -10, 8));
        file_put_contents($SEARCHFILE_OUT, $searchCritNew);
    }

// function to parse raw NOAA output and insert a tx into our db
function parseDataFromNOAA ($rawOutput, $stnname, $fieldsArray){
      $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);

      if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            return ['skipped'=>[], 'errors'=>["DB connection failed"]];
      }

      $fields = $fieldsArray[$stnname];
      $field_length = count(explode(", ", $fields));
      $array = explode("@", $rawOutput); // split raw string into transmissions

      $skipped = [];
      $errors = [];

      foreach ($array as $line) {
            if(trim($line) == ""){
                  continue; // skip empty transmissions
            }

            $lineArray = preg_split('/[\s]+/', $line);

            // grab elements for datetime
            $hr = substr($lineArray[0], 13, 2);
            $yr = "20" . substr($lineArray[0], 8, 2);
            $jday = substr($lineArray[0], 10, 3);

            $datetime = date("Y-m-d H:00:00", strtotime('+'.$jday.' days', mktime($hr, 0, 0, 1, 0, $yr)) - (8*60*60));

            // remove raw NOAA timestamp / nesid string
            $removedItem = array_shift($lineArray);
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

      return ['skipped'=>$skipped, 'errors'=>$errors];
}
function getMySQLRows($stationName, $numRows) {
    $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);

    if (mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
      exit;
    }
    $sql = "(SELECT * FROM `$stationName` ORDER BY DateTime desc LIMIT $numRows) order by DateTime";

    $result = mysqli_query($conn,$sql);

    if(!$result){
        exit("Select Query Error Description: ". mysqli_error($conn));
    }

    // put query in assoc array 
    $raw_array = array();

    while($row = mysqli_fetch_assoc($result)) {
        $raw_array[] = $row;
        }

    return $raw_array;
}
?>
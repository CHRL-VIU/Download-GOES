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

function updateNesid ($SEARCHFILE, $NESID) {
        $searchCritRaw = file_get_contents($SEARCHFILE); // reads an array of lines
        $searchCritNew = (substr_replace($searchCritRaw, $NESID, -10, 8));
        file_put_contents($SEARCHFILE, $searchCritNew);
    }

// function to parse raw NOAA output and insert a tx into our db
function parseDataFromNOAA ($rawOutput, $stnname, $fieldsArray){
  $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);

  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
  }
  
  $fields = $fieldsArray[$stnname];

  $field_length = count(explode(", ", $fields));

  $array = explode("@", $rawOutput); // turn raw string into array for each timestamped transmission which are separated by "@" defined in the config. 

  // loop through each tx and return a line of data compatible with our db 
  foreach ($array as $line) {
      if($line == ""){
          continue; // skip empty tranmissions
      }

      $lineArray = preg_split('/[\s]+/', $line); // split up the string by line breaks and creates an array with each tx variable in each array element

      // grab elements needed for date
      $hr = substr($lineArray[0], 13, 2);
      $yr = "20" . substr($lineArray[0], 8, 2);
      $jday = substr($lineArray[0], 10, 3);

      $datetime = date("Y-m-d H:00:00", strtotime('+'.$jday.' days', mktime($hr, 0, 0, 1, 0, $yr)) - (8*60*60));  // LHS is jdays to add to reference time, RHS is reference time to add jdays to. And need to subtract 8 hours to get to PST to match viu DB

      // remove raw NOAA timestamp / nesid string ofromf array
      $removedItem = array_shift($lineArray);
      // add datetime to start of array
      array_unshift($lineArray, $datetime);
      // remove empty line at end of array 
      array_pop($lineArray);

      if(count($lineArray) < $field_length){
          continue; // skip incomplete transmissions
      }

      // create string from array elements in current line
      $datString = implode("','", $lineArray);

      $query = "insert ignore into `raw_$stnname` ($fields) values('$datString')";

      if (!mysqli_query($conn, $query)) {
      exit("Update ".$stnname." Raw Tbl Error description: " . mysqli_error($conn));
      }
  }

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
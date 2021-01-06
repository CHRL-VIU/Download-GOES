<?php
// this script queries the NOAA server using LRGS scripts available here https://dcs1.noaa.gov/Account/Login

// get the creds
require 'config.php';

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

      // create string from array elements in current line
      $datString = implode("','", $lineArray);

      $query = "insert ignore into `raw_$stnname` ($fields) values('$datString')";

      if (!mysqli_query($conn, $query)) {
      exit("Insert Query Error description: " . mysqli_error($conn));
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

$nesids = array(
    "uppercruickshank" => "49008912",
    "cainridgerun" => "49A0216E",
    "apelake" => "BCF680B2",
    "claytonfalls" => "BCF070F8",
    // "homathko" => "434BC438",
    // "klinaklini" => "4900B25A",
    "lowercain" => "49004C0C",
    // //"machmell" => "",
    // "machmellkliniklini" => "BCF05614",
    "mountarrowsmith" => "490051A8"
    // "mountcayley" => "BCF00668", 
    // "perseverance" => "49006AE0",
    // "tetrahedron" => "4900A12C"
);

// these are the fields as they come in directly from noaa and will be uploaded to the raw_ tables
$fields = array(
  "uppercruickshank" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, BP, Telem, Vtx, TCase, SM, ST, SWUavg15m, SWLavg15m, LWUavg15m, LWLavg15m, ALBavg15m, TA, SW, SD, PC, VB, Ib, Vs, I_S, YB",
  "cainridgerun" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, Telem, Vtx, TCase, TA, SD, VB, Ib, Vs, I_S, YB",
  "lowercain" => "DateTime, RH, Temp, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, Telem, Vtx, TCase, Pcp1hr, Pcp_raw, Pcp_temp, SW_SSG, TA, SW, SD, PC, VB, Ib, Vs, I_S, YB",
  "mountarrowsmith" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, BP, Telem, Vtx, TCase, SM, ST, SWUavg15m, SWLavg15m, LWUavg15m, LWLavg15m, CNR_T_15m, TA, SW, SD, PC, VB, Ib, Vs, I_S, YB",
  "claytonfalls" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, BP, Telem, Vtx, TCase, Pcp1hr, Pcp_raw, Pcp_temp, SW_SSG",
  "apelake" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, BP, Telem, Vtx, TCase, Pcp1hr, Pcp_raw, Pcp_temp, SW_SSG"
);

// this is the list of raw_ fields that we care about and will publish to the clean tables note that the names here do not match the clean_ tables
// for repeating station definitions
$commonFilterFields = array(
    'DateTime', 
    'RH', 
    'Temp', 
    'Mx_Spd', 
    'Mx_Dir', 
    'Wspd', 
    'Dir', 
    'Rn_1', 
    'RnTotal', 
    'SDepth', 
    'BP', 
    'SM', 
    'ST', 
    'SWUavg15m', 
    'SWLavg15m', 
    'LWUavg15m', 
    'LWLavg15m', 
    'SW', 
    'PC', 
    'VB');

$filterFields = array(
    "uppercruickshank" => $commonFilterFields,

    "cainridgerun" => array(
    'DateTime', 
    'RH', 
    'Temp', 
    'Mx_Spd', 
    'Mx_Dir', 
    'Wspd', 
    'Dir', 
    'Rn_1', 
    'RnTotal', 
    'SDepth', 
    'PYR', 
    'VB'
    ),

    "lowercain" => array(
    'DateTime', 
    'RH', 
    'Temp', 
    'Rn_1', 
    'RnTotal', 
    'SDepth', 
    'PYR', 
    'SW', 
    'PC', 
    'VB' 
    ),
    
    "mountarrowsmith" => $commonFilterFields,

    "claytonfalls" => array(
    'DateTime', 
    'RH', 
    'Temp', 
    'Mx_Spd', 
    'Mx_Dir', 
    'Wspd', 
    'Dir', 
    'Rn_1', 
    'RnTotal', 
    'SDepth', 
    'BP', 
    'PYR', 
    'SW_SSG', 
    'Pcp1hr',
    'Pcp_raw', 
    'Vtx' // no batt volt so take with grain of salt
    ),

    "apelake" => array( // same as clayton falls
    'DateTime', 
    'RH', 
    'Temp', 
    'Mx_Spd', 
    'Mx_Dir', 
    'Wspd', 
    'Dir', 
    'Rn_1', 
    'RnTotal', 
    'SDepth', 
    'BP', 
    'PYR', 
    'SW_SSG', 
    'Pcp1hr',
    'Pcp_raw', 
    'Vtx' 
    ),   
);

// list of fields that match the clean_ tables
$commonCleanFields = array(    
    "DateTime",
    "WatYr",
    "RH",
    "Air_Temp",
    "Pk_Wind_Speed",
    "Pk_Wind_Dir",
    "Wind_Speed",
    "Wind_Dir",
    "PP_Tipper",
    "PC_Tipper",
    "Snow_Depth",
    "BP",
    "Soil_Moisture",
    "Soil_Temperature",
    "SWU",
    "SWL",
    "LWU",
    "LWL",
    "SWE",
    "PC_Raw_Pipe",
    "Batt");

$cleanFields = array(
  "uppercruickshank" => $commonCleanFields,

  "cainridgerun" => array(
    "DateTime",
    "WatYr",
    "RH",
    "Air_Temp",
    "Pk_Wind_Speed",
    "Pk_Wind_Dir",
    "Wind_Speed",
    "Wind_Dir",
    "PP_Tipper",
    "PC_Tipper",
    "Snow_Depth",
    "Solar_Rad",
    "Batt"
  ),
  "lowercain" => array(
    "DateTime",
    "WatYr",
    "RH",
    "Air_Temp",
    "PP_Tipper",
    "PC_Tipper",
    "Snow_Depth",
    "Solar_Rad",
    "SWE",
    "PC_Raw_Pipe",
    "Batt"
  ),

  "mountarrowsmith" => $commonCleanFields,

  "claytonfalls" => array(
  'DateTime', 
  "WatYr",
  'RH', 
  'Air_Temp', 
  "Pk_Wind_Speed",
  "Pk_Wind_Dir",
  "Wind_Speed",
  "Wind_Dir",
  "PP_Tipper",
  "PC_Tipper",
  'Snow_Depth', 
  'BP', 
  'Solar_Rad', 
  'SWE', 
  'PP_Pipe',
  'PC_Raw_Pipe', 
  'Batt' // no batt volt so take with grain of salt
  ),

  "apelake" => array( // same as clayton falls
  'DateTime', 
  "WatYr",
  'RH', 
  'Air_Temp', 
  "Pk_Wind_Speed",
  "Pk_Wind_Dir",
  "Wind_Speed",
  "Wind_Dir",
  "PP_Tipper",
  "PC_Tipper",
  'Snow_Depth', 
  'BP', 
  'Solar_Rad', 
  'SWE', 
  'PP_Pipe',
  'PC_Raw_Pipe', 
  'Batt' // no batt volt so take with grain of salt
  )
);

// update search criteria file based on provided NESID 
// file format is: 
// DRS_SINCE: now - 180 minutes
// DRS_UNTIL: now
// DCP_ADDRESS: 49A0216E
// located at /LRGS/MessageBrowser.sc

$fileName = '/LRGS/MessageBrowser.sc';

//loop through NESIDs to query
foreach ($nesids as $name => $id){
        updateNesid($fileName, $id); // update search crit file to query current file
        $output = shell_exec(CMD); // CMD is defined in the config and runs this line "C:/LRGSClient/bin/getDcpMessages -h \"cdadata.wcda.noaa.gov\" -u \"".NOAAUSER."\" -P \"".NOAAPASS."\" -f \"C:/LRGSClient/MessageBrowser.sc\" -b \"@\""
        parseDataFromNOAA($output, $name, $fields);
}

// Start Clean Table update

$numRowsToClean = 24; 

// stns to convert kpa 

$stnToKpa = array("lowercain", "cainridgerun");

foreach ($nesids as $curStation => $nesid) {
    //select from bottom of table and skip the first row of the query defined in the first line under the calcs section
    $rawRows = getMySQLRows("raw_$curStation", $numRowsToClean);

    foreach ($rawRows as $line) {
      $filterKeys = array_flip($filterFields[$curStation]);
      $filterArray = array_intersect_key($line, $filterKeys);

      // offset cruickshank snow depth + adj BP
      if($curStation == "uppercruickshank"){
        $filterArray['SDepth'] = $filterArray['SDepth'] + 572.1; // offset eyeballed by alex from raw data
        $filterArray['BP'] = $filterArray['BP'] / 10;   // convert BP from hpa to kpa 
        $filterArray['PC'] = $filterArray['PC'] * 1000; // convert PT from m to mm
      }

      // convert air pressure but not at cain
      if(!in_array($curStation, $stnToKpa)){
        $filterArray['BP'] = $filterArray['BP'] / 10;   // convert BP from hpa to kpa 
      }

      $curDateTime = $line["DateTime"];
      $curWatYr = wtr_yr($curDateTime, 10); // calc wat yr

      $finalArray = array_slice($filterArray, 0, 1, TRUE) + array("WatYr" => $curWatYr) + array_slice($filterArray, 1, 20, TRUE); // add in water year

      // convert clean array to a string                    
      $string = implode("','", $finalArray);

      // grab list of fields with the clean_ names 
      $cleanNames = implode(",", $cleanFields[$curStation]);

      //$query = "UPDATE `clean_$curStation` SET WatYr = $curWatYr WHERE DateTime = '$curDateTime'";
      $query = "INSERT IGNORE into `clean_$curStation` ($cleanNames) values('$string')";

      $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);

      if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit;
      }

      // import to clean tbl 
      if (!mysqli_query($conn, $query)) {
          exit("Insert Query Error description: " . mysqli_error($conn));
      }
    }
}

// free result set   
mysqli_close($conn);
?>
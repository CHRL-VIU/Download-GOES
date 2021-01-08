<?php
// this script queries the NOAA server using LRGS scripts available here https://dcs1.noaa.gov/Account/Login

// get the creds
require 'config.php';

// get the tbl field definitons
require 'tbl_defs.php';

// get the functions
require 'noaa_functions.php';

// update search criteria file based on provided NESID 
// file format is: 
// DRS_SINCE: now - 180 minutes
// DRS_UNTIL: now
// DCP_ADDRESS: 49A0216E
// located at /LRGS/MessageBrowser.sc

// start raw tbl update
//loop through NESIDs to query
// foreach ($nesids as $name => $id){
//         updateNesid(LRGS_FILENAME, $id); // update search crit file to query current file
//         $output = shell_exec(CMD); // CMD is defined in the config and runs this line "C:/LRGSClient/bin/getDcpMessages -h \"cdadata.wcda.noaa.gov\" -u \"".NOAAUSER."\" -P \"".NOAAPASS."\" -f \"C:/LRGSClient/MessageBrowser.sc\" -b \"@\""
//         parseDataFromNOAA($output, $name, $fields);
// }

// Start Clean Table update

$numRowsToClean = 900; 

// stns to convert kpa 

$stnToKpa = array("lowercain", "cainridgerun");

foreach ($nesids as $curStation => $nesid) {
    //select from bottom of table and skip the first row of the query defined in the first line under the calcs section
    $rawRows = getMySQLRows("raw_$curStation", $numRowsToClean);

    foreach ($rawRows as $line) {
      $filterKeys = array_flip($filterFields[$curStation]);
      $filterArray = array_intersect_key($line, $filterKeys); // note the resulting array here will match the ordering of the raw_tbls not the order of the filterFields Array 

      // offset cruickshank snow depth + adj BP
      if($curStation == "uppercruickshank"){
        $filterArray['SDepth'] = $filterArray['SDepth'] + 572.1; // offset eyeballed by alex from raw data
        $filterArray['PC'] = $filterArray['PC'] * 1000; // convert PT from m to mm
      }

      // offset plummer snow depth + adj BP
      if($curStation == "plummerhut"){
        $filterArray['SDepth'] = $filterArray['SDepth'] + 630; // offset eyeballed by alex from raw data
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
      $query = "REPLACE into `clean_$curStation` ($cleanNames) values('$string')";

      $conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);

      if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit;
      }

      // import to clean tbl 
      if (!mysqli_query($conn, $query)) {
          exit("Update ".$curStation." Clean Table Error description: " . mysqli_error($conn));
      }
    }
}

// free result set   
mysqli_close($conn);
?>
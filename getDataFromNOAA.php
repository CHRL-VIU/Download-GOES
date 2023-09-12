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
foreach ($nesids as $name => $id){
        updateNesid(LRGS_QUERY_IN, LRGS_QUERY_OUT, $id); // update search crit file to query current file
        echo "Starting the LRGS data request for station: " . $id. "\n";
        $output = shell_exec(CMD); // CMD is defined in the config and runs this line "C:/LRGSClient/bin/getDcpMessages -h \"cdadata.wcda.noaa.gov\" -u \"".NOAAUSER."\" -P \"".NOAAPASS."\" -f \"C:/LRGSClient/MessageBrowser.sc\" -b \"@\""
        parseDataFromNOAA($output, $name, $fields);
}

echo "Finished the raw table update... starting the clean tables now...\n";

// Start Clean Table update

$numRowsToClean = 24; 

// stns to convert kpa 

$stnToKpa = array("lowercain", "cainridgerun");

foreach ($nesids as $curStation => $nesid) {
    //select from bottom of table and skip the first row of the query defined in the first line under the calcs section
    $rawRows = getMySQLRows("raw_$curStation", $numRowsToClean);

    foreach ($rawRows as $line) {
      $filterKeys = array_flip($filterFields[$curStation]);
      $filterArray = array_intersect_key($line, $filterKeys); // note the resulting array here will match the ordering of the raw_tbls not the order of the filterFields Array 
     // print("<pre>".print_r($filterArray,true)."</pre>");

      // for all stations PC should not be greater or less than 9999, other vars behave much better and must have some filtering at the logger level but if this changes could copy this filtering for each possible variable in filterArray.
      if(array_key_exists('PC', $filterArray) && abs($filterArray['PC']) > 9999){
       $filterArray['PC'] = NULL;
      }

      // offset cruickshank snow depth + adj BP
      // someone added a logger side offset of +609.7 cm starting 2023-05-19 00:00:00
      // now we need to subtract (609.7-572.1) to get to our original tare
      if($curStation == "uppercruickshank"){
        // $filterArray['SDepth'] = $filterArray['SDepth'] + 572.1; // offset eyeballed by alex from raw data
        $filterArray['SDepth'] = $filterArray['SDepth'] - (609.7-572.1); // offset to counteract logger side attack 
        $filterArray['PC'] = $filterArray['PC'] * 1000; // convert PT from m to mm
      }
      
      // adjust tipping bucket at Tet + snow depth
      if($curStation == "tetrahedron"){
        $filterArray['Rn_1'] = $filterArray['Rn_1'] * 2; // offset eyeballed by alex from raw data
        $filterArray['SDepth'] = $filterArray['SDepth'] - 610.9; // offset from summer height from raw data (Julien)
      }

      // offset plummer snow depth + adj BP
      if($curStation == "plummerhut"){
        $filterArray['SDepth'] = $filterArray['SDepth'] + 630; // offset eyeballed by alex from raw data
      }

      // convert air pressure but not at cain
      if(!in_array($curStation, $stnToKpa)){
        $filterArray['BP'] = $filterArray['BP'] / 10;   // convert BP from hpa to kpa 
      }

      // correct offset upperskeena snow depth + convert m to mm
      // if($curStation == "upperskeena"){
      //   $filterArray['SDepth'] = ($filterArray['SDepth'] - 11.255) * 1000; // 
      // }

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
          echo "Update ".$curStation." Clean Table Error description: " . mysqli_error($conn);
          continue;
      }
    }
}

// free result set   
mysqli_close($conn); 

echo "Finished table updates for all stations.\n";
?>

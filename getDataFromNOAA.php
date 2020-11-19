<?php
// this script queries the NOAA server using LRGS scripts available here https://dcs1.noaa.gov/Account/Login

// get the creds
require 'config.php';

$fields = "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, BP, Telem, Vtx, TCase, SM, ST, SWUavg15m, SWLavg15m, LWUavg15m, LWLavg15m, ALBavg15m, TA, SW, SD, PC, VB, Ib, Vs, I_S, YB";

// excecute NOAA LRGS Script
$output = shell_exec(CMD);

$array = explode("@", $output);

$conn = mysqli_connect(MYSQLHOST, MYSQLUSER, MYSQLPASS, MYSQLDB);

if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit;
}

foreach ($array as $line) {
    if($line == ""){
        continue;
    }
    //$lineArray = preg_split("/\r\n|\n|\r/", $line);
    $lineArray = preg_split('/[\s]+/', $line);

    // grab elements needed for date
    $hr = substr($lineArray[0], 13, 2);
    $yr = "20" . substr($lineArray[0], 8, 2);
    $jday = substr($lineArray[0], 10, 3);
    // $date = jdtogregorian(gregoriantojd(01, 01, $yr)+($jday-1));
    // $datetime = date("Y-m-d H:00:00", strtotime($date . $hr)); 
    $datetime = date("Y-m-d H:00:00", strtotime('+'.$jday.' days', mktime($hr, 0, 0, 1, 0, $yr)));  // LHS is jdays to add, RHS is reference time to add jdays to.

    // remove first element of array
    $removedItem = array_shift($lineArray);
    // add datetime to start of array
    array_unshift($lineArray, $datetime);
    // remove empty line at end of array 
    array_pop($lineArray);

    // create string from array elements in current line
    $datString = implode("','", $lineArray);

    $query = "insert ignore into `raw_cruickshank` ($fields) values('$datString')";

    if (!mysqli_query($conn, $query)) {
    exit("Insert Query Error description: " . mysqli_error($conn));
    }
}

?>
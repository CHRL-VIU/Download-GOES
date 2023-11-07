<?php
// tbl_defs.php define each level of tbl fields for each station

// nesid list
$nesids = array(
    "upperskeena" => "49A02FBC",
    "uppercruickshank" => "49008912",
    "cainridgerun" => "49A0216E",
    "apelake" => "BCF680B2",
    "claytonfalls" => "BCF070F8",
    "homathko" => "434BC438",
    "klinaklini" => "4900B25A",
    "lowercain" => "49004C0C",
    // "machmell" => "BCF05614",
    "machmellkliniklini" => "BCF05614",
    "mountarrowsmith" => "490051A8",
    "mountcayley" => "BCF00668",
    "perseverance" => "49006AE0",
    "tetrahedron" => "4900A12C",
    "plummerhut" => "49007996"
);

// these are the fields as they come in directly from noaa and will be uploaded to the raw_ tables

// common fields for older wx stns (i.e. clayton falls, ape lake, machmell klini) 
$firstGenFtsRawFeilds = "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, BP, Telem, Vtx, TCase, Pcp1hr, Pcp_raw, Pcp_temp, SW_SSG";

// common fields for newer wx stations (i.e. arrowsmith, cruickshank, klini, homathko)
$secGenFtsRawFeilds = "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, BP, Telem, Vtx, TCase, SM, ST, SWUavg15m, SWLavg15m, LWUavg15m, LWLavg15m, ALBavg15m, TA, SW, SD, PC, VB, Ib, Vs, I_S, YB";

// put all raw stn fields together into array
$fields = array(
  "claytonfalls" => $firstGenFtsRawFeilds,
  "apelake" => $firstGenFtsRawFeilds,
  "machmellkliniklini" => $firstGenFtsRawFeilds,
  // "machmell" => $firstGenFtsRawFeilds,
  
  "cainridgerun" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, Telem, Vtx, TCase, TA, SD, VB, Ib, Vs, I_S, Ttherm, Rt, YB",
  "lowercain" => "DateTime, RH, Temp, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, Telem, Vtx, TCase, Pcp1hr, Pcp_raw, Pcp_temp, SW_SSG, TA, SW, SD, PC, VB, Ib, Vs, I_S, YB, Ttherm, Rt",
  
  "mountarrowsmith" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, BP, Telem, Vtx, TCase, SM, ST, SWUavg15m, SWLavg15m, LWUavg15m, LWLavg15m, ALBavg15m, TA, TA2, SW, SD, PC, VB, Ib, Vs, I_S, YB",
  "homathko" => $secGenFtsRawFeilds,
  "klinaklini" => $secGenFtsRawFeilds,
  "uppercruickshank" => $secGenFtsRawFeilds,

  "mountcayley" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, BP, Telem, Vtx, TCase, SM, ST, Pcp1hr, Pcp_raw",
  "perseverance" => $secGenFtsRawFeilds,
  "tetrahedron" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, PYR, PYRSR, BP, Telem, Vtx, TCase, SDepth2, SDcomp2, SDist_Q2, SW, SM, ST, TA, SD, PC, VB, Ib, Vs, I_S, YB, SD2",
  "plummerhut" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, BP, Telem, Vtx, TCase, SM, ST, SWUavg15m, SWLavg15m, LWUavg15m, LWLavg15m, ALBavg15m, TA, SD, VB, Ib, Vs, I_S, YB",
  "upperskeena" => "DateTime, RH, Temp, Mx_Spd, Mx_Dir, WSK10mMax, WDD10mMax, Wspd, Dir, Rn_1, RnTotal, SDepth, SDcomp, SDist_Q, BP, Telem, Vtx, TCase, SM, ST, SWUavg15m, SWLavg15m, LWUavg15m, LWLavg15m, ALBavg15m, SD_raw, SW_ssg, PCm, TA, SW, SD, PC, VB, Ib, Vs, I_S, YB"
);

// this is the list of raw_ fields that we care about and will publish to the clean tables note that the names here do not match the clean_ tables. We need this additional step bc the names of the raw tbls != the clean tabes probably a more elegant solution with a named array or something.. 
// for common older station defs
$firstGenFilterFields = array(
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
);

// for repeating newer station definitions
$secGenFilterFields = array(
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
    'VB'
  );

    // put together
$filterFields = array(
    "uppercruickshank" => $secGenFilterFields,

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
    
    "mountarrowsmith" => $secGenFilterFields,

    "claytonfalls" => $firstGenFilterFields,

    "machmellkliniklini" => $firstGenFilterFields,

    // "machmell" => $firstGenFilterFields,

    "apelake" => $firstGenFilterFields,   

    "homathko" => $secGenFilterFields,

    "klinaklini" => $secGenFilterFields,

    "mountcayley" => array(
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
    'SM', 
    'ST', 
    'Pcp1hr',
    'Pcp_raw', 
    'Vtx' 
    ),

    "perseverance" => $secGenFilterFields,

    "tetrahedron" => array(
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
    'SM', 
    'ST', 
    'SW', 
    'PC', 
    'Vtx' 
    ),

    "plummerhut" => array(
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
    'VB'
    ),

    'upperskeena' => $secGenFilterFields

);

// list of fields that match the clean_ tables need to match ordering of raw_tbls

// common clean table defs for older wx stns
$firstGenCleanFields = array(
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
  'Solar_Rad', 
  'BP', 
  'Batt',
  'PP_Pipe',
  'PC_Raw_Pipe', 
  'SWE'
);

// common clean table defs for newer wx stns
$secGenCleanFields = array(    
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
    "Batt"
  );

// we need this third step because not all stations transmit data for each of our clean table fields we could fill these in as NaNs on the data retrival script but this was just as easy
$cleanFields = array(
  "uppercruickshank" => $secGenCleanFields,

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

  "mountarrowsmith" => $secGenCleanFields,

  "claytonfalls" => $firstGenCleanFields,

  "apelake" => $firstGenCleanFields,

  "homathko" => $secGenCleanFields,

  "klinaklini" => $secGenCleanFields,

  "machmellkliniklini" => $firstGenCleanFields,

  // "machmell" => $firstGenCleanFields,

  "mountcayley" => array(
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
  'Solar_Rad', 
  'BP', 
  'Batt',
  "Soil_Moisture",
  "Soil_Temperature",
  'PP_Pipe',
  'PC_Raw_Pipe'
  ),

  "perseverance" => $secGenCleanFields,

  "tetrahedron" => array(
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
  'Solar_Rad', 
  'BP',
  'Batt',
  'SWE',  
  "Soil_Moisture",
  "Soil_Temperature",
  'PC_Raw_Pipe'
  ),

  "plummerhut" => array(
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
    "Batt"
  ),

  "upperskeena" => $secGenCleanFields

);
?>

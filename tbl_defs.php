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

    "plummerhut" => "49007996",
    "mountmaya" => "49a09c32",
    "placeglacier" => "4344e446"
);

// Common 1st/2nd Gen Fields for FTS msg/process/clean tables used to auto-populate some stations below
$firstGenFtsMsgFields  = ['DateTime', 'RH', 'Temp', 'Mx_Spd', 'Mx_Dir', 'WSK10mMax', 'WDD10mMax', 'Wspd', 'Dir', 'Rn_1', 'RnTotal', 'SDepth', 'SDcomp', 'SDist_Q', 'PYR', 'PYRSR', 'BP', 'Telem', 'Vtx', 'TCase', 'Pcp1hr', 'Pcp_raw', 'Pcp_temp', 'SW_SSG'];
$firstGenProcessFields = ['DateTime', 'WatYr', 'RH', 'Temp', 'Mx_Spd', 'Mx_Dir', 'Wspd', 'Dir', 'Rn_1', 'RnTotal', 'SDepth', 'BP', 'PYR', 'Pcp1hr', 'Pcp_raw', 'SW_SSG', 'Vtx'];
$firstGenCleanFields   = ['DateTime', 'WatYr', 'RH', 'Air_Temp', 'Pk_Wind_Speed', 'Pk_Wind_Dir', 'Wind_Speed', 'Wind_Dir', 'PP_Tipper', 'PC_Tipper', 'Snow_Depth', 'BP', 'Solar_Rad', 'PP_Pipe', 'PC_Raw_Pipe', 'SWE', 'Batt'];

$secGenFtsMsgFields    = ['DateTime', 'RH', 'Temp', 'Mx_Spd', 'Mx_Dir', 'WSK10mMax', 'WDD10mMax', 'Wspd', 'Dir', 'Rn_1', 'RnTotal', 'SDepth', 'SDcomp', 'SDist_Q', 'BP', 'Telem', 'Vtx', 'TCase', 'SM', 'ST', 'SWUavg15m', 'SWLavg15m', 'LWUavg15m', 'LWLavg15m', 'ALBavg15m', 'TA', 'SW', 'SD', 'PC', 'VB', 'Ib', 'Vs', 'I_S', 'YB'];
$secGenProcessFields   = ['DateTime', 'WatYr', 'RH', 'Temp', 'Mx_Spd', 'Mx_Dir', 'Wspd', 'Dir', 'Rn_1', 'RnTotal', 'SDepth', 'BP', 'SM', 'ST', 'SWUavg15m', 'SWLavg15m', 'LWUavg15m', 'LWLavg15m', 'SW', 'PC', 'VB'];
$secGenCleanFields     = ['DateTime', 'WatYr', 'RH', 'Air_Temp', 'Pk_Wind_Speed', 'Pk_Wind_Dir', 'Wind_Speed', 'Wind_Dir', 'PP_Tipper', 'PC_Tipper', 'Snow_Depth', 'BP', 'Soil_Moisture', 'Soil_Temperature', 'SWU', 'SWL', 'LWU', 'LWL', 'SWE', 'PC_Raw_Pipe', 'Batt'];

// ------------------------------------------------------------
// Station table definitions
// ------------------------------------------------------------
// $stations defines how GOES messages are parsed and mapped
// into raw and clean database tables for each station.
//
// Required keys per station:
//
//   msg_fields
//     - Ordered list of all fields in the incoming GOES message used to parse raw transmissions
//     - MUST exactly match the GOES message field order and length
//     - Fields MUST correspond to the field names in the raw_<station> SQL table (for those in raw_fields, anyhow)
//
//   raw_fields
//     - Subset of msg_fields to be written to the raw_<station> table
//     - Fields MUST exist in msg_fields
//     - Fields names MUST correspond to the field names in the raw_<station> SQL table
//
//   process_fields
//     - Ordered list of fields to be pulled from raw table and inserted into clean table
//     - May also include computed fields not present in the raw table (e.g. WatYr)
//     - Fields names MUST correspond to the field names in raw_fields and the raw_<station> table
//     - MUST be positionally aligned with clean_fields
//
//   clean_fields
//     - Ordered list of fields used to map and insert fields into the clean_<station> SQL table
//     - Fields names MUST correspond to the field names in the clean_<station> SQL table
//     - MUST be positionally aligned with process_fields
//
// IMPORTANT:
//   - process_fields and clean_fields must be the same length and positionally aligned
//   - Any computed field (e.g. WatYr) must appear in process_fields
//     and is handled explicitly in the processing script

$stations = [
    "claytonfalls" => [
        "msg_fields"     => $firstGenFtsMsgFields,
        "raw_fields"     => [],
        "process_fields" => $firstGenProcessFields,
        "clean_fields"   => $firstGenCleanFields
    ],

    "apelake" => [
        "msg_fields"     => $firstGenFtsMsgFields,
        "raw_fields"     => [],
        "process_fields" => $firstGenProcessFields,
        "clean_fields"   => $firstGenCleanFields
    ],

    "machmellkliniklini" => [
        "msg_fields"     => $firstGenFtsMsgFields,
        "raw_fields"     => [],
        "process_fields" => $firstGenProcessFields,
        "clean_fields"   => $firstGenCleanFields
    ],

    "cainridgerun" => [
        "msg_fields"     => ['DateTime','RH','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','PYR','PYRSR','Telem','Vtx','TCase','TA','SD','VB','Ib','Vs','I_S','YB','Ttherm','Rt'],
        "raw_fields"     => [],
        "process_fields" => ['DateTime','WatYr','RH','Temp','Mx_Spd','Mx_Dir','Wspd','Dir','Rn_1','RnTotal','SDepth','PYR','VB'],
        "clean_fields"   => ['DateTime','WatYr','RH','Air_Temp','Pk_Wind_Speed','Pk_Wind_Dir','Wind_Speed','Wind_Dir','PP_Tipper','PC_Tipper','Snow_Depth','Solar_Rad','Batt']
    ],

    "lowercain" => [
        "msg_fields"     => ['DateTime','RH','Temp','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','PYR','PYRSR','Telem','Vtx','TCase','Pcp1hr','Pcp_raw','Pcp_temp','SW_SSG','TA','SW','SD','PC','VB','Ib','Vs','I_S','YB','Ttherm','Rt'],
        "raw_fields"     => [],
        "process_fields" => ['DateTime','WatYr','RH','Temp','Rn_1','RnTotal','SDepth','PYR','SW','PC','VB'],
        "clean_fields"   => ['DateTime','WatYr','RH','Air_Temp','PP_Tipper','PC_Tipper','Snow_Depth','Solar_Rad','SWE','PC_Raw_Pipe','Batt']
    ],

    "mountarrowsmith" => [
        "msg_fields"     => ['DateTime','Rh','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','PYR','PYRSR','BP','Telem','Vtx','TCase','SDepth2','SDcomp2','SDist_Q2','SW','SM','ST','TA','TA2','SD','PC','VB','Ib','Vs','I_S','YB','PCTW','SWTW'],
        "raw_fields"     => ['DateTime','Rh','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','BP','Telem','Vtx','TCase','SW','SM','ST','TA','TA2','SD','PC','VB','Ib','Vs','I_S','YB'],
        "process_fields" => ['DateTime','WatYr','RH','Mx_Spd','Mx_Dir','Wspd','Dir','Rn_1','RnTotal','SDepth','BP','SM','ST','TA2','SW','PC','VB'],
        "clean_fields"   => ['DateTime','WatYr','RH','Pk_Wind_Speed','Pk_Wind_Dir','Wind_Speed','Wind_Dir','PP_Tipper','PC_Tipper','Snow_Depth','BP','Soil_Moisture','Soil_Temperature','Air_Temp','SWE','PC_Raw_Pipe','Batt']
    ],

    "homathko" => [
        "msg_fields"     => $secGenFtsMsgFields,
        "raw_fields"     => [],
        "process_fields" => $secGenProcessFields,
        "clean_fields"   => $secGenCleanFields
    ],

    "klinaklini" => [
        "msg_fields"     => $secGenFtsMsgFields,
        "raw_fields"     => [],
        "process_fields" => $secGenProcessFields,
        "clean_fields"   => $secGenCleanFields
    ],

    "uppercruickshank" => [
        "msg_fields"     => $secGenFtsMsgFields,
        "raw_fields"     => [],
        "process_fields" => $secGenProcessFields,
        "clean_fields"   => $secGenCleanFields
    ],

    "mountcayley" => [
        "msg_fields"     => ['DateTime','RH','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','PYR','PYRSR','BP','Telem','Vtx','TCase','SM','ST','Pcp1hr','Pcp_raw'],
        "raw_fields"     => [],
        "process_fields" => ['DateTime','WatYr','RH','Temp','Mx_Spd','Mx_Dir','Wspd','Dir','Rn_1','RnTotal','SDepth','BP','PYR','SM','ST','Pcp1hr','Pcp_raw','Vtx'],
        "clean_fields"   => ['DateTime','WatYr','RH','Air_Temp','Pk_Wind_Speed','Pk_Wind_Dir','Wind_Speed','Wind_Dir','PP_Tipper','PC_Tipper','Snow_Depth','BP','Solar_Rad','Soil_Moisture','Soil_Temperature','PP_Pipe','PC_Raw_Pipe','Batt']
    ],

    "perseverance" => [
        "msg_fields"     => $secGenFtsMsgFields,
        "raw_fields"     => [],
        "process_fields" => $secGenProcessFields,
        "clean_fields"   => $secGenCleanFields
    ],

    "tetrahedron" => [
        "msg_fields"     => ['DateTime','RH','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','PYR','PYRSR','BP','Telem','Vtx','TCase','SDepth2','SDcomp2','SDist_Q2','SW','SM','ST','TA','SD','PC','VB','Ib','Vs','I_S','YB','SD2','PCTW','SWTW'],
        "raw_fields"     => [],
        "process_fields" => ['DateTime','WatYr','RH','Temp','Mx_Spd','Mx_Dir','Wspd','Dir','Rn_1','RnTotal','SDepth','BP','PYR','SM','ST','SW','PC','Vtx'],
        "clean_fields"   => ['DateTime','WatYr','RH','Air_Temp','Pk_Wind_Speed','Pk_Wind_Dir','Wind_Speed','Wind_Dir','PP_Tipper','PC_Tipper','Snow_Depth','BP','Solar_Rad','Soil_Moisture','Soil_Temperature','SWE','PC_Raw_Pipe','Batt']
    ],

    "plummerhut" => [
        "msg_fields"     => ['DateTime','RH','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','BP','Telem','Vtx','TCase','SM','ST','SWUavg15m','SWLavg15m','LWUavg15m','LWLavg15m','ALBavg15m','TA','SD','VB','Ib','Vs','I_S','YB','SR_temp_lo','SDepth_lo','SDcomp_lo','SD_raw_lo','SDist_Q_lo'],
        "raw_fields"     => [],
        "process_fields" => ['DateTime','WatYr','RH','Temp','Mx_Spd','Mx_Dir','Wspd','Dir','Rn_1','RnTotal','SDepth','BP','SM','ST','SWUavg15m','SWLavg15m','LWUavg15m','LWLavg15m','VB','SR_temp_lo','SDepth_lo'],
        "clean_fields"   => ['DateTime','WatYr','RH','Air_Temp','Pk_Wind_Speed','Pk_Wind_Dir','Wind_Speed','Wind_Dir','PP_Tipper','PC_Tipper','Snow_Depth','BP','Soil_Moisture','Soil_Temperature','SWU','SWL','LWU','LWL','Batt','Air_Temp_2','Snow_Depth_2']
    ],

    "upperskeena" => [
        "msg_fields"     => ['DateTime','RH','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','BP','Telem','Vtx','TCase','SM','ST','SWUavg15m','SWLavg15m','LWUavg15m','LWLavg15m','ALBavg15m','SD_raw','SW_ssg','PCm','TA','SW','SD','PC','VB','Ib','Vs','I_S','YB'],
        "raw_fields"     => [],
        "process_fields" => $secGenProcessFields,
        "clean_fields"   => $secGenCleanFields
    ],

    "mountmaya" => [
        "msg_fields"     => ['DateTime','Rh','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','PYR','PYRSR','BP','Telem','Vtx','TCase','Pcp1hr','TA','SD','VB','Ib','Vs','I_S','YB'],
        "raw_fields"     => [],
        "process_fields" => ['DateTime','WatYr','RH','Temp','Mx_Spd','Mx_Dir','Wspd','Dir','Rn_1','RnTotal','SDepth','PYR','BP','Vtx','Pcp1hr'],
        "clean_fields"   => ['DateTime','WatYr','RH','Air_Temp','Pk_Wind_Speed','Pk_Wind_Dir','Wind_Speed','Wind_Dir','PP_Tipper','PC_Tipper','Snow_Depth','Solar_Rad','BP','Batt','PC_Raw_Pipe']
    ],

    "placeglacier" => [
        "msg_fields"     => ['DateTime','Rh','Temp','Mx_Spd','Mx_Dir','WSK10mMax','WDD10mMax','Wspd','Dir','Rn_1','RnTotal','SDepth','SDcomp','SDist_Q','BP','Telem','Vtx','TCase','SWUavg15m','SWLavg15m','LWUavg15m','LWLavg15m','ALBavg15m','SD','VB','Ib','Vs','I_S','YB','SR_temp_lo','SDepth_lo','SDcomp_lo','SD_raw_lo','SDist_Q_lo'],
        "raw_fields"     => [],
        "process_fields" => ['DateTime','WatYr','RH','Temp','Mx_Spd','Mx_Dir','Wspd','Dir','Rn_1','RnTotal','SDepth','BP','Vtx','SWUavg15m','SWLavg15m','LWUavg15m','LWLavg15m','SR_temp_lo','SDepth_lo'],
        "clean_fields"   => ['DateTime','WatYr','RH','Air_Temp','Pk_Wind_Speed','Pk_Wind_Dir','Wind_Speed','Wind_Dir','PP_Tipper','PC_Tipper','Snow_Depth','BP','Batt','SWU','SWL','LWU','LWL','Air_Temp_2','Snow_Depth_2']
    ]
];

// Ensure raw_fields defaults to msg_fields if empty
foreach ($stations as $stationName => &$station) {
    if (empty($station['raw_fields'])) {
        $station['raw_fields'] = $station['msg_fields'];
    }
}
unset($station); // break the reference
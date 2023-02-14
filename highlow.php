<?php
// This API endpoint outputs a CSV of the next weeks high and low tides
// Columns:
// UnixTimestamp<Long>, ISO8601-Date<String>, ISO8601-Time<String>, TideHeight<Float>, HighOrLow<String>

// Command line for xtide explained:
// Location:Llandudno, ExcludeMetadata:SunriseSunsetMoonriseMoonsetMoonPhase, PredictionLength:7Days, Mode:PlainHighLows, Format:CSV

$dateYesterday = date('Y-m-d',strtotime("-1 days"));

$cmd = 'tide -l "Llandudno, Gwynedd, Wales" -b "' . $dateYesterday . ' 00:00" -em pSsMm -pi 8 -m p -f c';

$descriptorspec = array(
   0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
   2 => array("pipe", "w")    // stderr is a pipe that the child will write to
);
flush();
$process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
$rows = array();
if (is_resource($process)) {
    while ($line = fgets($pipes[1])) {

	$myArr = explode(',', $line);

	// Replace location with ISO8601 timestamp converted to unix timestamp
	$myArr[0] =  date("U",strtotime($myArr[1] . ' ' . $myArr[2]));

	// Trim units off height
	$myArr[3] = floatval($myArr[3]);

	// Back to CSV again
        // print implode(",", $myArr);
	$rows[]=array($myArr[0] + 0, $myArr[3]);
    }
}
echo json_encode($rows);
flush();

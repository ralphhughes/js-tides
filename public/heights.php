<?php
error_reporting(E_STRICT);
// This API endpoint outputs a CSV of the tide height every 15 minutes for the given location and day
// Example: heights.php?location=Llandudno,%20Gwynedd,%20Wales&timestamp=2023-03-08&days=2
// Columns:
// UnixTimestamp<Long>, TideHeight<Float>


function array2csv($data, $delimiter = ',', $enclosure = '"', $escape_char = "\\") {
	$f = fopen('php://memory', 'r+');
	foreach ($data as $item) {
		fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
	}
	rewind($f);
	return stream_get_contents($f);
}
function run_xtide_highlows($location, $timestamp, $interval, $numDays) {
	// Command line for xtide
	// Location:Llandudno, ExcludeMetadata:PhaseSunriseSunsetMoonriseMoonset, StepInterval:15min, PeriodToGenerateLength:1Day, Mode:PlainHighLow, Format:CSV
	$cmd = 'tide -l ' . $location . ' -b ' . $timestamp . ' -em pSsMm -s "' . $interval . '" -pi ' . $numDays . ' -m p -f c';
	
	// Debug
	// print($cmd . "\n");
	
	$descriptorspec = array(
		0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
		1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
		2 => array("pipe", "w")    // stderr is a pipe that the child will write to
	);
	
	$process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
	
	// $rows[] = [];
	if (is_resource($process)) {
		while ($line = fgets($pipes[1])) {
			$myArr = explode(',', $line);	// 0=Location, 1=ISO_Date, 2=H:MM AM TZ, 3=height in metres, 4=High or low
			
			// Debug
			// print $myArr[0] . ',' . $myArr[1] . ',' . $myArr[2]. ',' . $myArr[3]. ',' . $myArr[4]; 

			// Replace location with ISO8601 timestamp converted to unix timestamp
			$row_unixtime = date("U",strtotime($myArr[1] . ' ' . $myArr[2]));
			
			// Trim units off height
			$myArr[3] = floatval($myArr[3]);			
			
			// Trim control chars off high/low tide text
			$myArr[4] = preg_replace('/[\x00-\x1F\x7F]/u', '', $myArr[4]);

			$myArr[4] = preg_replace('/ Tide/', '', $myArr[4]); // Trim the " tide" text off each entry

			$rows[]=array($row_unixtime + 0, $myArr[3], $myArr[4]);
			
		}	
	}
	// print "\n\n";
	return $rows;
}
function run_xtide_heights($location, $timestamp, $interval, $numDays) {
	
	// Command line for xtide
	// Location:Llandudno, ExcludeMetadata:PhaseSunriseSunsetMoonriseMoonset, StepInterval:15min, PeriodToGenerateLength:1Day, Mode:RawUnixTimestamps, Format:CSV
	$cmd = 'tide -l ' . $location . ' -b ' . $timestamp . ' -em pSsMm -s "' . $interval . '" -pi ' . $numDays . ' -m r -f c';
	
	// Debug
	// print($cmd . "\n");
	
	$descriptorspec = array(
		0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
		1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
		2 => array("pipe", "w")    // stderr is a pipe that the child will write to
	);
	
	$process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
	
	// $rows[] = [];
	if (is_resource($process)) {
		while ($line = fgets($pipes[1])) {
			$myArr = explode(',', $line);	// 0 is location, 1 is unix timestamp, 2 is tide height
			
			// Debug
			// print $myArr[1] . ',' . $myArr[2]; 
			$myArr[1] = $myArr[1] + 0; // convert to number
			
			$myArr[2] = preg_replace('/[\x00-\x1F\x7F]/u', '', $myArr[2]) + 0; // replace any control characters from the string and convert to number
			
			$myArr[2] = number_format((float)$myArr[2], 2, '.', ''); // Format to 2 decimal places always
			
			// Add to rows
			$rows[]=array($myArr[1], $myArr[2], "");
		}	
	}
	return $rows;
}

// Read the parameters off the querystring

// Strip any control characters from location then escape it (will single quote it)
$location = escapeshellarg(preg_replace('/[\x00-\x1F\x7F]/u', '', $_GET['location']));

// Strip everything except numbers,dashes, colons and spaces from timestamp (ISO8601 date) and escape it
$timestamp = escapeshellarg(preg_replace("/[^\:\ \d-]/i", "", $_GET['timestamp'] . " 00:00"));

// Convert days to integer greater than zero
$numDays = (int) $_GET['days'];
if ($numDays < 1 || $numDays > 366) {
	$numDays = 1;
}

// More detail if less days, less detail if more days, just to keep the execution time down
if ($numDays == 1) {
	$interval = "00:15";
} else if ($numDays > 1 && $numDays <= 180) {
	$interval = "00:30";
} else {
	$interval = "01:00";
}

if (strcasecmp($_GET['format'], "csv") == 0) {
	$format = "csv";
} else {
	$format = "json";
}


$highlow_rows = run_xtide_highlows($location, $timestamp, $interval, $numDays);
$height_rows = run_xtide_heights($location, $timestamp, $interval, $numDays);

$rows = array_merge($highlow_rows, $height_rows);
array_multisort($rows, SORT_ASC, SORT_REGULAR);

// Turn the rows array into csv or json for returning to client
if ($format == "csv") {
	echo "unix_time,height,type\n"; // Add header row first
	echo array2csv($rows);
} else if ($format == "json") {
	echo json_encode($rows);
}
flush();

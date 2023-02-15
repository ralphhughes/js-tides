<?php
	error_reporting(E_STRICT);
	// This API endpoint outputs a CSV of the tide height every 15 minutes for the given location and day
	// Columns:
	// UnixTimestamp<Long>, TideHeight<Float>
	
	// Strip any control characters from location then escape it (will single quote it)
	$location = escapeshellarg(preg_replace('/[\x00-\x1F\x7F]/u', '', $_GET['location']));
	
	// Strip everything except numbers and dashes from timestamp (date)
	$timestamp = escapeshellarg(preg_replace("/[^\:\ \d-]/i", "", $_GET['timestamp'] . " 00:00"));
	
	
	// Command line for xtide
	// Location:Llandudno, ExcludeMetadata:PhaseSunriseSunsetMoonriseMoonset, StepInterval:15min, PeriodToGenerateLength:1Day, Mode:RawUnixTimestamps, Format:CSV
	$cmd = 'tide -l ' . $location . ' -b ' . $timestamp . ' -em pSsMm -s "00:15" -pi 1.5 -m r -f c';
	
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
			$rows[]=array($myArr[1], $myArr[2]);
		}	
	}
	
	// Turn the rows variable into json
	echo json_encode($rows);
	flush();

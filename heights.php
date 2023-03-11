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
	
	// Strip any control characters from location then escape it (will single quote it)
	$location = escapeshellarg(preg_replace('/[\x00-\x1F\x7F]/u', '', $_GET['location']));
	
	// Strip everything except numbers,dashes, colons and spaces from timestamp (ISO8601 date) and escape it
	$timestamp = escapeshellarg(preg_replace("/[^\:\ \d-]/i", "", $_GET['timestamp'] . " 00:00"));
	
	// Convert days to integer greater than zero
	$numDays = (int) $_GET['days'];
	if ($numDays < 1 || $numDays > 366) {
		$numDays = 1;
	}
	
	if ($numDays == 1) {
		$interval = "00:15";
	} else {
		$interval = "00:30";
	}
	
	if (strcasecmp($_GET['format'], "csv") == 0) {
		$format = "csv";
	} else {
		$format = "json";
	}
	
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
			$rows[]=array($myArr[1], $myArr[2]);
		}	
	}
	
	// Turn the rows array into csv or json for returning to client
	if ($format == "csv") {
		echo "unix_time,height\n";
		echo array2csv($rows);
	} else if ($format == "json") {
		echo json_encode($rows);
	}
	flush();

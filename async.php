<?php
	/*
	Sever Side PHP processing for gamesalad tables
	written by Jonathan Mulcahy
	http://www.icebergapps.com
	version 1.0.1
	1/27/2013
	*/
	
	// connect to SQL
	include_once 'php/functions.php';

	$port = '3306';
	$link = @mysql_connect(':/Applications/MAMP/tmp/mysql/mysql.sock', 'root', 'root');

	// database connection strings. change these to your DB and Table names.
	$dbName = "gsTest";
	$tableName = "gsTest";

	if (!$link) {
		exit('Error: Could not connect to MySQL server!');
	}

	// connect to the table
	mysql_select_db($dbName)or die("cannot select DB");

	// lets prepare some files to capture what is going on.
	$incomingJson = 'json.txt';
	//$fullArray = 'fullArray.txt';  // needed if you enable the debugging secton below
	$sqlErrorLog = "sqlErrors.txt";

	// initialize the string with a blank value
	$string = "";

	// start SEND data
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		//capture incoming data
		error_reporting(1);
		$sig = $_POST["sig"];
		$jsondata = $_POST["params"];

		// this line captures the sent data so you can figure out what you need to send back.
		file_put_contents($incomingJson,$jsondata);

		// this line tells the application that the data send was successful.
		echo '{"Status":"Success"}';

		// convert JSON to an array
		$array = json_decode($jsondata, TRUE);

		/*
		// formats the array to view it easier
		$results = print_r($array,true);
		file_put_contents($fullArray,$results);
		*/

		//get the total number of objects in the array
		$arrlength = count($array['Children']['1']['Properties']);

		// set while loop index
		$i = 0;
		
		//loop through array node and get row values
		while ($i < $arrlength ) {

			// get row value
			$value = $array['Children']['1']['Properties'][$i]['Value']."\n";

			// convert delimited string to an array
			$arrayPieces = explode("|", $value);

			// get array values. This section would have to be modified to capture each value you are interested in.
			$rowName = $arrayPieces[0];  // this variable will be blank if you don't name your rows. 
			$playerID = $arrayPieces[1];
			$playerName =$arrayPieces[2];
			$playerStats = $arrayPieces[3];

			// construct SQL statement
			$sql="INSERT INTO ".$tableName."(playerID, playerName, playerStats)VALUES('$playerID', '$playerName', '$playerStats')";

			// insert SQL statement
			$result=mysql_query($sql);

			// catch any errors
			if($result){
				// if successful do nothing for now.
			}

			else {

				// if failure, write to custom log
				$sqlError = "Error writing to database\n";
				file_put_contents($sqlErrorLog, $sqlError, FILE_APPEND);
			}

			$i++;
		}
	} // end of POST

	// start GET data
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {

		// initialize the JSON body variable
		$jsonBody="";

		// get table contents
		$query = mysql_query("SELECT * FROM ".$tableName); 

		// construct an array to hold the data we pull from mySQL
		$rows = array();

		// loop through the table and drop the data into the array
		while($row = mysql_fetch_assoc($query)) {
		    $rows[] = $row;	   
		}

		// get the number of rows in the array. We need this in the JSON return
		$arrlength = count($rows);

		// set while loop index
		$i = 0;

		//loop through array node and get row values
		while ($i < $arrlength ) {
			
			// tables we are capturing
			$playerID = $rows[$i]['playerID'];
			$playerName =$rows[$i]['playerName'];
			$playerStats = $rows[$i]['playerStats'];

			// table row numbers. our index starts at 0, so we want to increment it by 1 to get valid row numbers.
			$tableRow = $i+1;

			// construct the JSON return from our data
			$jsonString = '{"Name":"'.$tableRow .'","Value":"|'.$playerID.'|'.$playerName.'|'.$playerStats.'|"},';

			// append the JSON return with the new data
			$jsonBody=$jsonBody.$jsonString;

			// increase index and loop again if not at end of array.
			$i++;			
		}

		// construct the JSON response

		// this is the header of the JSON return. It will have to be adjusted to match whatever your app is expecting. We have to define this here to get the row count above.
		$jsonHeadher='{"Properties":[],"Name":"","Children":[{"Properties":[{"Name":"rowCount","Value":'.$arrlength.'},{"Name":"columnCount","Value":3},{"Name":"0-1-name","Value":"playerID"},{"Name":"0-1-type","Value":1},{"Name":"0-2-name","Value":"playerName"},{"Name":"0-2-type","Value":1},{"Name":"0-3-name","Value":"playerstats"},{"Name":"0-3-type","Value":2}],"Name":"id911451_headers","Children":[]},{"Properties":[';
		
		// this is the footer of the JSON return. Again it will have to be adjusted to match whatever your app is expecting.
		$jsonFooter='],"Name":"id911451","Children":[]}]}';

		// removes an extra comma that the loop above leaves behind
		$jsonBody=rtrim($jsonBody, ",");

		// constructing the full JSON return
		$returnedJson=$jsonHeadher.$jsonBody.$jsonFooter;
		
		// write the JSON data so the app can read it.
		echo $returnedJson;	


	} // end of get

	// close the SQL connection
	mysql_close($link);

?>

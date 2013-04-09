<?php
//fix for max execution time to 600 seconds (default is after 30 seconds script will stop)
ini_set('max_execution_time', 600);
//building names to query from 
$buildings = array("ATB","CC","CLB","COM","CSB","GEB","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");

//db info
$server = "10.99.3.219";
$user = "sqledash";
$pass = "dunt_890";
$db = "ES_JCCC";


$con = mssql_connect($server, $user, $pass) 
or die("Couldn't connect to SQL Server on Error: " . mssql_get_last_message());
//set database
mssql_select_db($db) 
    or die('Could not select a database.');



//select n# of random room temps for the cc builidng 
//object_id is a number with around 10 digites and just looks for a random number  from 1-99  anywhere in the ID number
$queryStr = "SELECT top(3)  name
FROM sys.Tables
where object_id like '%".rand( 1 , 99 )."%' and name like '%RMTMP%' and name like '%CC%' and name not like '%JCCC%'";

//holds the name of the tables found
$tables = array();
//run query store in result 
$result = mssql_query($queryStr ) or die('A error occured: ' . mysql_error());
//to loop through each row use:
while ($row = mssql_fetch_array($result)){
	//push each table name to the array
	 array_push($tables, $row["name"] );	
}

	//to loop through 
	// Set timezone
	date_default_timezone_set('US/Central');
	// loop Start date for updateing we will neet to run a mysql query to get lastest date
	$date = '2012-09-05 04:00:00';
	// loop End date for updateing we will use current date 
	$end_date = '2012-12-31 01:00:00';
 	
 	$dateArr = array();
	$valArray = array();
	//loop by date
	while (strtotime($date) <= strtotime($end_date)) {
		//split datetime to an array of date[0] and time[1]
		$dateArry = explode(" ", $date);
		//$dateYMD array is year = [0], Month = [1], Day = [2]	
		$dateYMD = explode("-", $dateArry [0]);
		
		//get hour
		$hour = explode(":", $dateArry[1]);
		$hour = $hour[0];
		
		//to get the advrage (sum of temps/number of temps)
		$sum = 0;
		$count = 0;
		
		//loop each table name
		foreach ($tables as $tbl) {
			
		//get the first tempiture from each table by date and hour
			$queryStr = "SELECT top(1)".$tbl.".value 
							FROM 
								".$tbl."
							WHERE 
								DATEPART(hh, tstamp) = ".$hour." and
								DATEPART(dd, tstamp) = ".$dateYMD[2]." and
								DATEPART(mm, tstamp) = ".$dateYMD[1]." and
								DATEPART(yy, tstamp) = ".$dateYMD[0];
			//run query
			$result = mssql_query($queryStr );
			//get first row
				$row = mssql_fetch_array($result);
				//ignore rows with no value
				if((double)$row[0] > 0){
				//add to sum
				$sum = $sum+(double)$row[0];
				//add one to count
				$count++;
				//for right now this just prints the temp and table name to html
				echo($row[0]." -- $tbl</br>");
				}//end if ignore rows
		}//end each table name loop
		//if count not 0 print degree on date 
		if($count > 0)
		echo (" ---- AVG Degree:<b>".round(($sum/$count),2)."&deg</b> --- On Date:<b>$date -</b>--- <hr>");
		//add an hour to current date for while date loop
		$date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date)));
	}//end of while date loop

?>
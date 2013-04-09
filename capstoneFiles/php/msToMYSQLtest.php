<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <title>Progress Bar</title>
</head>
<body>
<!-- Progress bar holder -->
<div id="progress" style="width:500px;height:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>


<?php
//fix for max execution time to 600 seconds (default is after 30 seconds script will stop)
ini_set('max_execution_time', 6000);

//building names to query from 
$buildings = array("ATB","CC","CLB","CSB","GEB__COM","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");

//MYSQL INFO and connection
$MYserver = "localhost";
$MYuser = "root";
$MYpass = "";
$MYdb = "mstomytest";
$MYtable = "test2";
$MYcon = mysqli_connect($MYserver ,$MYuser ,$MYpass ,$MYdb);
// Check connection
if (mysqli_connect_errno($MYcon))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

//MSSQL INFO and connection
$MSserver = "10.99.3.219";
$MSuser = "sqledash";
$MSpass = "dunt_890";
$MSdb = "ES_JCCC";

$MScon = mssql_connect($MSserver, $MSuser, $MSpass) 
or die("Couldn't connect to SQL Server on Error: " . mssql_get_last_message());
//set database
mssql_select_db($MSdb) 
    or die('Could not select a database.');





// Create MYSQL table if it doesnt exists and mkTable is true (error if table is already made)
$mkTable = FALSE;
if($mkTable)
{	//create table query
	$sql="CREATE TABLE ".$MYtable." 
	(
		ID INT NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(ID),
		Time DATETIME NOT NULL, ";
	//add temp and kwh col for each building	
	foreach($buildings as $b){
		$sql .= $b."_temp DECIMAL, ".$b."_kwh DECIMAL, ";
	}
	//replace the last two chars (',' and ' ') with sql end bracket ')'
	$sql = substr_replace($sql, ")", -2);
	
	// Execute query
	if (mysqli_query($MYcon,$sql)){
	 echo("succsess!"); 		
	}
}//end of makeing table
	

//get last time database was updated if database was just made the date will be Sept 7 2012 
date_default_timezone_set('US/Central');
if($mkTable){
	$date = '2012-09-07 00:00:00';
}
else{
//get last date updated
$MYresult = mysqli_query($MYcon,"SELECT MAX(Time) as maxTime FROM ".$MYtable);
//if row is returned insert to database 
$row = mysqli_fetch_array($MYresult);
//add new date +1 hour
$date = (string)$row[0];
$date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date)));
}

 	
	//set end_date to current date and hour
	$end_date = date ("Y-m-d H:00:00");
	
 	
	//loop date if last updated date +1 hour is less than current datetime
	while (strtotime($date) <= strtotime($end_date )) {
		//insert each datetime from $date to current datetime
		mysqli_query($MYcon,"INSERT INTO ".$MYtable." (Time)
						  VALUES ('".$date."')");
		//add an hour to current date for while date loop
		$date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date)));
	}//end of while date loop

	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	//Start of CONVERTING MSsql to MYsql
//
//curent builngs with no RMTMP: ATB,...
//store some building names for possable forloop
///$bld = array("CC","CLB","CSB","COM_CSB","GYM","HCDC",);
//holds an key pair array of where statements for each building
/*
	$where = array();
	$where["CC"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%TMP%' and name like 'CC%' ";
	$where["CLB"] = "name like '%RMTMP%' and name like 'CLB%' ";
	$where["CSB"] = "name like '%TMP%' and name like 'CSB%' ";
	$where["GEB__COM"] = "name like '%RMTMP%' and name like 'GEB__COM%' ";
	$where["GYM"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%RMTMP%' and name like 'GYM%' ";
	$where["HCDC"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%RMTMP%' and name like 'HCDC%' ";
	$where["LIB"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%LIB%'and name like'%RMTMP%'";
	$where["NMOCA"] = "name like 'NERMAN%' and name like '%TEMP%'";
	$where["OCB"] = "name like 'OCB%' and name like '%TEMP%'";*/
	//$where["PA"] = "name like '%RPA%' and name like '%TEMP%'";
	//$where["SC"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%TMP%' and name like 'SC%' ";
	$where["SCI"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%SCI%' and name like '%tmp%' ";
	$where["WH"] = "name like 'WH%' and name like '%tmp%'";
	
// loop Start date for updateing we will neet to run a mysql query to get lastest date
		$date = '2012-12-31 00:00:00';
		// loop End date for updateing we will use current date 
		$end_date = date ("Y-m-d H:00:00");
$DateDiff = abs(strtotime($date ) - strtotime($end_date))/3600;

//for loop here right now its just one building at a time
//if(FALSE)
foreach($where as $buildingName => $whereStatment){
	
	
	
//get n# of of table names from top(#)
	$queryStr = "SELECT top(5)  name
	FROM sys.Tables
	where ".$whereStatment;
	
	//holds the name of the tables found
	$tables = array();
	//run query store in result 
	$TABLEresult = mssql_query($queryStr ) or die('A error occured: ' . mysql_error());
	//loop each row found and store in an array of table names
	while ($row = mssql_fetch_array($TABLEresult)){
		//push each table name to the array
		 array_push($tables, $row["name"] );	
	}
	
		while (strtotime($date) <= strtotime($end_date)) {
			//split datetime to an array of date [0] and time [1]
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
				
			//get the first tempiture from each table for a single date and hour
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
					}//end if ignore rows
			}//end each table name loop
			
			
			//if count not 0 print degree on date 
			if($count > 0){
				//print the update statement to html
				//echo("<hr>UPDATE ".$MYtable." SET ".$buildingName."_TEMP=".round(($sum/$count),2)."where time like '".$date."'");
				//run update statement
				mysqli_query($MYcon,"UPDATE ".$MYtable." 
									SET ".$buildingName."_TEMP=".round(($sum/$count),2)."
							  		where time like '".$date."'");
							  		
				//add an hour to current date for while date loop
				$date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date)));
			}
			else {
			//print to html if not found
			//echo("<hr>No mach fround for $buildingName _TEMP at $date");
			//skip 6 hours becuase there was no data
			$date = date ("Y-m-d H:i:s", strtotime("+6 hour", strtotime($date)));
			}
			
			
		}//end of while date loop
		//break;
	}//end of for loop	
//close dbs
mysqli_close($MYcon);
mssql_close($MScon);
// Tell user that the process is completed
echo '<script language="javascript">document.getElementById("information").innerHTML="Process completed"</script>';
?>
</body>
</html>
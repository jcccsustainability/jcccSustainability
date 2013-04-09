<?php
//fix for max execution time to 600 seconds (default is after 30 seconds script will stop)
ini_set('max_execution_time', 600);
//building names to query from 
$buildings = array("ATB","CC","CLB","COM","CSB","GEB","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");
foreach ($buildings as $b) {
	
	}
$server = "localhost";
$user = "root";
$pass = "";
$db = "mstomytest";
$table = "test1";


$con=mysqli_connect($server ,$user ,$pass ,$db);

// Check connection
if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }


// Create table
/*
$sql="CREATE TABLE ".$table." 
(
	ID INT NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(ID),
	Time DATETIME NOT NULL, ";
	
foreach($buildings as $b){
	$sql .= $b."_temp DECIMAL, ".$b."_kwh DECIMAL, ";
}
//replace the last two chars (',' and ' ') with sql end bracket ')'
$sql = substr_replace($sql, ")", -2);

// Execute query
if (mysqli_query($con,$sql)){
 echo("succsess!"); 		
}
*/

//insert datetimes to database

/*
 

date_default_timezone_set('US/Central');
//$result = mysqli_query($con,"SELECT MAX(Time) as maxTime FROM ".$table);

//get last time database was updated
//while($row = mysqli_fetch_array($result))
//{
//$date = date ("Y-m-d H:i:s", $row['maxTime']);
//}
 

	// loop Start date for updateing we will neet to run a mysql query to get lastest date
	//$date = '2012-09-05 04:00:00';
	//set end_date to current date time
	$end_date = date ("Y-m-d H:i:s");
 	
	//loop date
	while (strtotime($date) <= strtotime($end_date)) {
		mysqli_query($con,"INSERT INTO ".$table." (Time)
						  VALUES ('".$date."')");
		//add an hour to current date for while date loop
		$date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date)));
	}//end of while date loop

	
 */	
	
$result = mysqli_query($con,"SELECT MAX(Time) as mTime FROM ".$table);
while($row = mysqli_fetch_array($result))
  {
 	 echo $row['mTime'];
  }
mysqli_close($con);



?>
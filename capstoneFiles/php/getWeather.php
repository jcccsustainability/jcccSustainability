<?php

ini_set('max_execution_time', 600);

//MYSQL INFO and connection
$MYserver = "localhost";
$MYuser = "root";
$MYpass = "";
$MYdb = "mstomytest";
$MYtable = "Weather3";
$MYcon = mysqli_connect($MYserver ,$MYuser ,$MYpass ,$MYdb);
// Check connection
if (mysqli_connect_errno($MYcon))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$sql="CREATE TABLE $MYtable
	(
		 
		Time DATETIME NOT NULL, 
		PRIMARY KEY(Time ),
		Temp  DECIMAL(4,1)
	)";
		

	// Execute query
	if (mysqli_query($MYcon,$sql)){
	 echo("succsess!"); 		
	}






//variabls for csv array 
$TimeCDT = 0;
$TemperatureF = 1;
$DewPointF = 2;
$Humidity = 3;
$SeaLevelPressureIn = 4;
$VisibilityMPH = 5;
$WindDirection = 6;
$WindSpeedMPH = 7;
$GustSpeedMPH = 8;
$PrecipitationIn = 9;
$Events = 10;
$Conditions = 11;
$WindDirDegrees = 12;
$DateUTC= 13;

$date = date ("Y-m-d",  strtotime("2012-09-01"));
$end_date = date ("Y-m-d");

//get last date script ran
$MYresult = mysqli_query($MYcon,"SELECT MAX(Time) as maxTime FROM ".$MYtable);
//if row is returned insert to database 
if($MYresult && FALSE){
$row = mysqli_fetch_array($MYresult);
//add new date +1 hour
$date = date ("Y-m-d H:i:s", strtotime((string)$row[0]));
$date = explode(" " , $date);
$date = $date[0];
}

while(strtotime($date) <= strtotime($end_date) )
{
	echo $date."<br>";
	$dateArray = explode("-", $date);
	if (($handle = fopen("http://www.wunderground.com/history/airport/KOJC/".$dateArray[0]."/".((int)$dateArray[1])."/".((int)$dateArray[2])."/DailyHistory.html?req_city=Overland+Park&req_state=KS&req_statename=Kansas&theprefset=SHOWMETAR&theprefvalue=1&format=1", "r")) !== FALSE) 
	{
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    	
		    if(isset($data[$TemperatureF]) && $data[$TemperatureF] != "TemperatureF" && $data[$TemperatureF] != "" && (int)$data[$TemperatureF] > -90){
				$dateTime = date ("Y-m-d H:00:00", strtotime("-5 hour", strtotime( substr_replace($data[$DateUTC],'',-6) )));
				$sql = "INSERT INTO ".$MYtable." (TIME, TEMP)
						  VALUES ('".$dateTime ."',".$data[$TemperatureF].")";
		     	echo $sql."<br>";
				mysqli_query($MYcon,$sql);
		    }
		}
	   fclose($handle);
	   $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
	}
}
?>

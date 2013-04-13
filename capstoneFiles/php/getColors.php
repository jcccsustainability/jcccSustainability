<?php
//MYSQL INFO and connection
$buildings =  array("ATB","CC","CLB","GEB__COM","CSB","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");

$MYserver = "localhost";
$MYuser = "root";
$MYpass = "";
$MYdb = "mstomytest";
$MYtable = "test7";
$MYcon = mysqli_connect($MYserver ,$MYuser ,$MYpass ,$MYdb);

$type = $_GET['type'] ;
$date1 = $_GET['date1'] ;
$date2 = $_GET['date2'] ;

//$type = 'temp' ;
//$date1 = "2012-09-07";
//$date2 = "2012-09-14";

// Check connection
if (mysqli_connect_errno($MYcon))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
if($type == "temp")
{
	//get advrage outside
		$sql = "SELECT avg(weather) as avg
		FROM buildings where TIME between '".$date1."' and '".$date2."'";
		
		$MYresult = mysqli_query($MYcon,$sql);
		if($MYresult)
		{
		$row = mysqli_fetch_array($MYresult);
		$weatherAvg = (double)$row["avg"];
		}
		else{
			$weatherAvg = 0 ;
		}
		
		
		
		
	foreach ($buildings as $bld) {

		//get andverg temp for a building
		$sql = "SELECT avg(".$bld."_temp) as avg
		FROM buildings
		WHERE TIME between '".$date1."' and '".$date2."'  
		and ".$bld."_temp is not null";
		$MYresult = mysqli_query($MYcon,$sql);
		if($MYresult)
		{
		$row = mysqli_fetch_array($MYresult);
		$buildingTemp = (double)$row["avg"];
		}
		else{
			$buildingTemp = 0;
		}
		
		
		
		//if there is a temp set building temps
		if($weatherAvg > 0 && $buildingTemp > 0){
			echo abs($buildingTemp-$weatherAvg)*2 ." ";	
		}
		else {
			echo "gray ";
		}
	}//end for ea. building
}
else {

	foreach ($buildings as $bld) {
		//get advrage kwh
		$sql = "SELECT avg(".$bld."_kwh) as avg
		FROM buildings where ".$bld."_kwh is not null";
		
		$MYresult = mysqli_query($MYcon,$sql);
		if($MYresult)
		{
		$row = mysqli_fetch_array($MYresult);
		$kwhAvg = (double)$row["avg"];
		}
		else{
			$kwhAvg = 0;
		}
	
	
		//get andverg temp for a building
		$sql = "SELECT avg(".$bld."_kwh) as avg
		FROM buildings
		WHERE TIME between '".$date1."' and '".$date2."'  
		and ".$bld."_kwh is not null";
		$MYresult = mysqli_query($MYcon,$sql) or die(mysqli_error($MYcon));;
		if($MYresult)
		{
		$row = mysqli_fetch_array($MYresult);
		$buildingKwh = (double)$row["avg"];
		}
		else{
			$buildingKwh = 0;
		}
		
		//if($kwhAvg > 0 && $buildingKwh > 0)
		//echo "avg= $kwhAvg , building= $buildingKwh , on $bld total: ".abs($buildingKwh-$kwhAvg). "returned=";
		
		//if there is a temp set building temps
		if($kwhAvg > 0 && $buildingKwh > 0){
				
			$val =  abs($buildingKwh-$kwhAvg);	
			if($val < 100){
				echo $val." ";
			}
			else {
				echo "100 ";
			}
		}
		else {
			echo "gray ";
		}
		//echo "<br>";
	}//end for ea. building

}//end else
?>
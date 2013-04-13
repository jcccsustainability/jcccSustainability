<?php
//MYSQL INFO and connection
$buildings = array("ATB","CC","CLB","CSB","GEB__COM","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");
$buildingTemps = array();


$MYserver = "localhost";
$MYuser = "root";
$MYpass = "";
$MYdb = "mstomytest";
$MYtable = "test7";
$MYcon = mysqli_connect($MYserver ,$MYuser ,$MYpass ,$MYdb);

$type = $_GET['type'] ;
$date1 = $_GET['date1'] ;
$date2 = $_GET['date2'] ;

// Check connection
if (mysqli_connect_errno($MYcon))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
if($type = "temp")
{
	$sum = 0;
	//get advrage outside weather
	$sql = "SELECT avg(weather) as avg
	FROM buildings
	WHERE TIME between '".$date1."' and '".$date2."'  ";
	$MYresult = mysqli_query($MYcon,$sql) or die(mysqli_error($MYcon));;
	$row = mysqli_fetch_array($MYresult);
	$weatherAvg = (double)$row["avg"];
	
	
	foreach ($buildings as $bld) {
	//get andverg temp for a building
	$sql = "SELECT avg(".$bld."_".$type.") as avg
	FROM buildings
	WHERE TIME between '".$date1."' and '".$date2."'  
	and ".$bld."_".$type." is not null";
	$MYresult = mysqli_query($MYcon,$sql) or die(mysqli_error($MYcon));;
	$row = mysqli_fetch_array($MYresult);
	//store temp
	$tempAvg = (double)$row["avg"];
	//echo $tempAvg."<br>";
	//if there is a temp set building temps
	if($tempAvg ){
		//echo "$bld - avg:$tempAvg  min:$tempMin<br>";
		$buildingTemps[$bld] = abs((double)$tempAvg-$weatherAvg);
		//$sum +=	(double)$tempAvg-$tempMin;
	}
	else {
		$buildingTemps[$bld] = -1;
	}
	}
	foreach ($buildings as $bld){
		if ($buildingTemps[$bld] > 0)
			echo ($buildingTemps[$bld]/72*150) ." ";
		else 
			echo "gray ";
	}
}
else {

	foreach ($buildings as $bld) {
	//get advrage kwh
	$sql = "SELECT avg(".$bld."_kwh) as avg
	FROM buildings
	WHERE TIME between '".$date1."' and '".$date2."'  
	and ".$bld."_kwh is not null";
	$MYresult = mysqli_query($MYcon,$sql) or die(mysqli_error($MYcon));;
	$row = mysqli_fetch_array($MYresult);
	$kwhAvg = (double)$row["avg"];
		
	
					
				
			
		
	//get andverg temp for a building
	$sql = "SELECT avg(".$bld."_kwh) as avg
	FROM buildings
	WHERE TIME between '".$date1."' and '".$date2."'  
	and ".$bld."_".$type." is not null";
	$MYresult = mysqli_query($MYcon,$sql) or die(mysqli_error($MYcon));;
	$row = mysqli_fetch_array($MYresult);
	//store temp
	$Kwh = (double)$row["avg"];
	//echo $tempAvg."<br>";
	//if there is a temp set building temps
	if($kwhAvg > 0 && $kwh > 0){
		
		echo abs($Kwh-$kwhAvg)." ";
		
	}
	else {
		echo "gray ";
	}
	}
	
	

}
?>
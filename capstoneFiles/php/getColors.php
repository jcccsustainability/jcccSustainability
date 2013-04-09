<?php
//MYSQL INFO and connection
$buildings = array("ATB","CC","CLB","CSB","GEB__COM","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");
$buildingTemps = array();
$jason = array();
$MYserver = "localhost";
$MYuser = "root";
$MYpass = "";
$MYdb = "mstomytest";
$MYtable = "test7";
$MYcon = mysqli_connect($MYserver ,$MYuser ,$MYpass ,$MYdb);
// Check connection
if (mysqli_connect_errno($MYcon))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$sum = 0;
foreach ($buildings as $bld) {
	

$sql = "SELECT avg(".$bld."_TEMP) as avg, min(".$bld."_TEMP) as min
FROM test5
WHERE TIME between '2012-09-08' and '2012-09-09'  ";
$MYresult = mysqli_query($MYcon,$sql) or die(mysqli_error($MYcon));;
$row = mysqli_fetch_array($MYresult);
$tempAvg = (double)$row["avg"];
$tempMin = (double)$row["min"];
if($tempAvg){
	//echo "$bld - avg:$tempAvg  min:$tempMin<br>";
	$buildingTemps[$bld] = (double)$tempAvg-$tempMin;
	$sum +=	(double)$tempAvg-$tempMin;
}
else {
	$buildingTemps[$bld] = -1;
}
}
foreach ($buildings as $bld){
	if ($buildingTemps[$bld] != -1)
		echo (round($buildingTemps[$bld]/$sum*200))." ";
	else
		echo "gray ";
}
?>
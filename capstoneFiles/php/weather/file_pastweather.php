<?php



//MYSQL INFO and connection
$MYserver = "localhost";
$MYuser = "root";
$MYpass = "";
$MYdb = "mstomytest";
$MYtable = "testFinal";
$MYcon = mysqli_connect($MYserver ,$MYuser ,$MYpass ,$MYdb);
// Check connection
if (mysqli_connect_errno($MYcon))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
/*
$sql="CREATE TABLE weather 
	(
		 
		Time DATETIME NOT NULL, 
		PRIMARY KEY(Time ),
		Temp INT)";
		

	// Execute query
	if (mysqli_query($MYcon,$sql)){
	 echo("succsess!"); 		
	}
*/






//~ http://api.worldweatheronline.com/premium/v1/past-weather.ashx?key=xxxxxxxxxxxxxxxxx&q=SW1&date=2009-07-20&format=xml

//Minimum request
//Can be city,state,country, zip/postal code, IP address, longtitude/latitude. If long/lat are 2 elements, they will be assembled. IP address is one element.
$loc_array= Array("66210");		//data validated in foreach. 
$api_key="uybaa2fv63qfmjjhbkrkvxfp";		//should be embedded in your code, so no data validation necessary, otherwise if(strlen($api_key)!=24)
$date = date ("Y-m-d", strtotime("2012-07-20"));
$dateTime = date ("Y-m-d H:i:s", strtotime("2012-07-20 00:00:00"));					//validated as $date_safe
$enddate="2013-04-01";				//optional included in example, validated as $enddate_safe

$loc_safe=Array();
foreach($loc_array as $loc){
	$loc_safe[]= urlencode($loc);
}


$loc_string=implode(",", $loc_safe);
$date_safe=$date;		//this SHOULD return the same value, but if malformed this will correct
$enddate_safe=urlencode($enddate);		//this SHOULD return the same value, but if malformed this will correct

while (strtotime($date) <= strtotime($enddate )) {
//To add more conditions to the query, just lengthen the url string
$premiumurl=sprintf('http://api.worldweatheronline.com/premium/v1/past-weather.ashx?q=66212&tp=3&format=xml&date='.$date.'&enddate=2013-04-01&key=qy2dmfvfft2team38bhfgtc8', 
	$api_key, $loc_string, $date_safe);

//print $premiumurl . "<br />";

$xml_response = file_get_contents($premiumurl);
$xml = simplexml_load_string($xml_response);
printf("<p>On %s the wind speed was %s mph blowing TEMP %s</p>", 
	$xml->weather->date, $xml->weather->windspeedMiles, $xml->weather->maxtempF );
	
	foreach ($xml->weather as $day) {
		echo $day->date."<br>";
		foreach ($day->hourly as $hr) {
			echo $hr->tempF."<br>";
			mysqli_query($MYcon,"INSERT INTO weather (Time, temp)
						  VALUES ('".$dateTime."' , '".$hr->tempF."')");
			$dateTime = date ("Y-m-d H:i:s", strtotime("+3 hour", strtotime($dateTime)));
		}
		$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));	
	}
}	
print "<pre>";
print_r($xml);
print "</pre>";
?>

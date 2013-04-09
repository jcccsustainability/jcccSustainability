<?php
//~ http://api.worldweatheronline.com/free/v1/marine.ashx?key=xxxxxxxxxxxxxxxxx&q=45,-2&format=xml

//Minimum request
//Can be longtitude/latitude. If long/lat are 2 elements, they will be assembled. 
$loc_array= Array("45","-2");		//data validated in foreach. 
$api_key="xkq544hkar4m69qujdgujn7w";		//should be embedded in your code, so no data validation necessary, otherwise if(strlen($api_key)!=24)

$loc_safe=Array();
foreach($loc_array as $loc){
	$loc_safe[]= urlencode($loc);
}
$loc_string=implode(",", $loc_safe);

//To add more conditions to the query, just lengthen the url string
$basicurl=sprintf('http://api.worldweatheronline.com/free/v1/marine.ashx?key=%s&q=%s&format=xml', 
	$api_key, $loc_string);

print $basicurl . "<br />";

//Premium API
$premiumurl=sprintf('http://api.worldweatheronline.com/premium/v1/marine.ashx?key=%s&q=format=xml', 
	$api_key, $loc_string);

$xml_response = file_get_contents($basicurl);
$xml = simplexml_load_string($xml_response);
printf("<p>Current wind speed is %s mph blowing to %s</p>", 
	$xml->weather->hourly->windspeedMiles, $xml->weather->hourly->winddir16Point );

print "<pre>";
print_r($xml);
print "</pre>";
?>

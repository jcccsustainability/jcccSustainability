<?php
//~ http://api.worldweatheronline.com/premium/v1/past-weather.ashx?key=xxxxxxxxxxxxxxxxx&q=SW1&date=2009-07-20&format=xml
//This cURL example requires php_curl. To verify installion,  phpinfo();
//Failure to support cURL results in:   PHP Fatal error:  Call to undefined function curl_init() 

//Minimum request
//Can be city,state,country, zip/postal code, IP address, longtitude/latitude. If long/lat are 2 elements, they will be assembled. IP address is one element.
$loc_array= Array("New York","ny");		//data validated in foreach. 
$api_key="xkq544hkar4m69qujdgujn7w";		//should be embedded in your code, so no data validation necessary, otherwise if(strlen($api_key)!=24)
$date="2009-07-20";					//validated as $date_safe
$enddate="2009-07-27";				//optional included in example, validated as $enddate_safe

$loc_safe=Array();
foreach($loc_array as $loc){
	$loc_safe[]= urlencode($loc);
}
$loc_string=implode(",", $loc_safe);
$date_safe=urlencode($date);		//this SHOULD return the same value, but if malformed this will correct
$enddate_safe=urlencode($enddate);		//this SHOULD return the same value, but if malformed this will correct

//To add more conditions to the query, just lengthen the url string
$premiumurl=sprintf('http://api.worldweatheronline.com/free/v1/weather.ashx?key=%s&q=%s&date=%s&format=xml', 
	$api_key, $loc_string, $date_safe);

print $premiumurl . "<br />";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $basicurl);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1); 
$xml_response =curl_exec($ch);
curl_close($ch);

$xml = simplexml_load_string($xml_response);
printf("<p>On %s the wind speed was %s mph blowing to %s</p>", 
	$xml->weather->date, $xml->weather->windspeedMiles, $xml->weather->winddir16Point );

print "<pre>";
print_r($xml);
print "</pre>";
?>

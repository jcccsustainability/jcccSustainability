<?php
//~ http://api.worldweatheronline.com/free/v1/weather.ashx?key=xxxxxxxxxxxxxxxxx&q=SW1&num_of_days=3&format=xml
//This cURL example requires php_curl. To verify installion,  phpinfo();
//Failure to support cURL results in:   PHP Fatal error:  Call to undefined function curl_init() 

//Minimum request
//Can be city,state,country, zip/postal code, IP address, longtitude/latitude. If long/lat are 2 elements, they will be assembled. IP address is one element.
$loc_array= Array("New York","ny");		//data validated in foreach. 
$api_key="xkq544hkar4m69qujdgujn7w";		//should be embedded in your code, so no data validation necessary, otherwise if(strlen($api_key)!=24)
$num_of_days=2;					//data validated in sprintf

$loc_safe=Array();
foreach($loc_array as $loc){
	$loc_safe[]= urlencode($loc);
}
$loc_string=implode(",", $loc_safe);

//To add more conditions to the query, just lengthen the url string
$basicurl=sprintf('http://api.worldweatheronline.com/free/v1/weather.ashx?key=%s&q=%s&num_of_days=%s', 
	$api_key, $loc_string, intval($num_of_days));

print $basicurl . "<br />";

//Premium API
$premiumurl=sprintf('http://api.worldweatheronline.com/premium/v1/premium-weather-V2.ashx?key=%s&q=%s&num_of_days=%s', 
	$api_key, $loc_string, intval($num_of_days));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $basicurl);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1); 
$xml_response =curl_exec($ch);
curl_close($ch);

$xml = simplexml_load_string($xml_response);
printf("<p>Current wind speed is %s mph blowing to %s</p>", 
	$xml->current_condition->windspeedMiles, $xml->current_condition->winddir16Point );

print "<pre>";
print_r($xml);
print "</pre>";
?>

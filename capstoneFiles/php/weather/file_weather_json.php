<?php
//~ http://api.worldweatheronline.com/free/v1/weather.ashx?key=xxxxxxxxxxxxxxxxx&q=SW1&num_of_days=3&format=json

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
$basicurl=sprintf('http://api.worldweatheronline.com/free/v1/weather.ashx?key=%s&q=%s&num_of_days=%s&format=json', 
	$api_key, $loc_string, intval($num_of_days));

print $basicurl . "<br />\n";

//Premium API
$premiumurl=sprintf('http://api.worldweatheronline.com/premium/v1/premium-weather-V2.ashx?key=%s&q=%s&num_of_days=%s&format=json', 
	$api_key, $loc_string, intval($num_of_days));

$json_reply = file_get_contents($basicurl);
$json=json_decode($json_reply);
printf("<p>Current wind speed is %s mph blowing to %s</p>", 
	//~ $json->{'data'}->{'current_condition'}->{'windspeedMiles'}, $json->{'data'}->{'current_condition'}->{'winddir16Point'} );
	$json->{'data'}->{'current_condition'}['0']->{'windspeedMiles'}, 
	$json->{'data'}->{'current_condition'}['0']->{'winddir16Point'} );
//~ print "<script>var weather= JSON.parse(";
//~ print $json_reply;
//~ print ");</script>";

print "<pre>";
print_r($json);
print "</pre>";
?>

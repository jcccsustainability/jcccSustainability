<?php

$myServer = "10.99.3.219";
$myUser = "sqledash";
$myPass = "dunt_890";
$myDB = "ES_JCCC";


// Server in the this format: <computer>\<instance name> or 
// <server>,<port> when using a non default port number
$server = 'bas-sql.jccc.edu';

// Connect to MSSQL
$con = mssql_connect($myServer, $myUser, $myPass) 
or die("Couldn't connect to SQL Server on Error: " . mssql_get_last_message());

mssql_select_db($myDB) 
    or die('Could not select a database.');
	
$SQL = "SELECT top(10)  name
FROM sys.Tables
where name like '%rmtmp%' and name like '%cc%'

";
$tables = array();


// Execute query:
$result = mssql_query($SQL) 
    or die('A error occured: ' . mysql_error());
 
// Get result count:
$count = mssql_num_rows($result);
print "Showing $count rows:<hr/>\n\n";
 
// Fetch rows:
while($row = mssql_fetch_array($result))
{
  array_push($tables, $row["name"]);
  
  
	
	
}

mssql_close($con);

$sum = 0;
foreach ($tables as $tbl) {
    
	
	$con = mssql_connect($myServer, $myUser, $myPass) 
	or die("Couldn't connect to SQL Server on Error: " . mssql_get_last_message());
	mssql_select_db($myDB) 
    or die('Could not select a database.');
	
	$Q = "select AVG (CASE WHEN Value <> 0 THEN Value ELSE NULL END) as val
			from dbo.".$tbl;
	// Execute query:
$r = mssql_query($Q) 
    or die('A error occured: ' . mysql_error());

while($rs = mssql_fetch_array($r))
{
if($rs["val"] != NULL){
echo("<br>".$tbl);
echo("   |   ".$rs["val"]);

$sum += (float)$rs["val"];
}
else {
	$count --;
}
break;
}

mssql_close($con);
	
}
echo("<br/> Avg tmp = ". $sum/$count);	

?>
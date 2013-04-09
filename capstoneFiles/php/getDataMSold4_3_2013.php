<?php 
//Michael Read
//mySql to json for google chart's datatable
// https://developers.google.com/chart/interactive/docs/php_example


//connect to database
$server = "10.99.3.219";
$user = "sqledash";
$pass = "dunt_890";
$db = "ES_JCCC";
//db used in try needs to be global
$con = mssql_connect($server, $user, $pass) 
or die("Couldn't connect to SQL Server on Error: " . mssql_get_last_message());
//set database
mssql_select_db($db) 
    or die('Could not select a database.');
	
	


//gets query sting from veriable by url peramiters
if (!empty($_GET['q']))
//holds a query
$queryStr = $_GET['q'];
else//if no pramiter it will pick a test one 
$queryStr = "SELECT top(".rand ( 5 , 15 ).") value, tstamp
FROM dbo.CC__ASD_IBSASDNETWORK_MNVAV__MNFLO__111_COMPOSITE_RMTMP__LOG
ORDER BY tstamp DESC
";



//run query
$result = mssql_query($queryStr ) 
    or die('A error occured: ' . mysql_error());


// Get result count:
$count = mssql_num_rows($result);



//make google chart dataTable
$table = array();

//add array of cols to the data table
$table['cols'] = array();

//add each col to the data table as $table['cols']

	//get and hold all the meta data for this col as an array
	
	
	//add the google's dataTable label name and data type
	$table['cols'][] = array('label' => 'timeStamp', 
		                      'type' => 'string');
		                      
	$table['cols'][] = array('label' => 'Room Temp', 
		                      'type' => 'number');	                    
	//go to next column						  


//make google's dataTable rows
$table['rows'] = array();

//add each row to the datatable
while ($row = mssql_fetch_array($result)) {
//creat a array to hold each cell in a single row
  	$cells = array();  
	$cells[] = array('v' => $row["tstamp"]);
	$cells[] = array('v' => (double)$row["value"]);
				
				
			
		
	
	
	//add the array of ceslls to the rows array in the table
	$table['rows'][] = array('c' => $cells);
}

//encode to a jason and printit out for javascript
echo( json_encode($table) );



 /*
$query->execute();
$results = $query->fetchAll(PDO::FETCH_ASSOC);


$flag = true;
$table = array();
$table['cols'] = array(
    array('label' => 'power', 'type' => 'string'),
	array('label' => 'date', 'type' => 'datetime')
);
$table['rows'] = array();

foreach ($results as $row) {
    $temp = array();
    $dateTimeArr = explode(' ', $row['datetime']);
    $dateArr = explode('-', $dateTimeArr[0]);
    $timeArr = explode(':', $dateTimeArr[1]);
    $year = $dateArr[0];
    $month = $dateArr[1] - 1;  // months are zero-indexed
    $day = $dateArr[2];
    $hour = $timeArr[0];
    $min = $timeArr[1];
    $sec = $timeArr[2];
    $temp[] = array('v' => "Date($year, $month, $day, $hour, $min, $sec)");
    $temp[] = array('v' => (int) $row['power']);
    $table['rows'][] = array('c' => $temp);
}
echo(json_encode($table));
*/
?>
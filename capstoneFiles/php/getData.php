<?php 
//Michael Read
//mySql to json for google chart's datatable
// https://developers.google.com/chart/interactive/docs/php_example


//connect to database
$username = "root" ;
$password = '';
//db used in try needs to be global
$db;

//gets data base name from veriable by url peramiters
if (!empty($_GET['dbs']))
$dbName = $_GET['dbs'] ;
else//if no pramiter it will pick a test one 
$dbName = "information_schema";

//gets query sting from veriable by url peramiters
if (!empty($_GET['q']))
//holds a query
$queryStr = $_GET['q'];
else//if no pramiter it will pick a test one 
$queryStr = "SELECT TABLE_NAME, AVG_ROW_LENGTH  FROM TABLES where AVG_ROW_LENGTH > 0 LIMIT 0 , 5";


//try to connect to database/////////////////////////////////////////////////////////////
try {
	$db = new PDO('mysql:host=localhost;dbname='.$dbName , $username, $password);
}
catch (PDOException $e) {
     echo 'ERROR: ' . $e->getMessage();
}
//set some atturabutes
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//run query
$query = $db->prepare($queryStr);
$query->execute();
//get an array or results
$results = $query->fetchAll(PDO::FETCH_ASSOC);
////End of db connect////////////////////////////////////////////////////


//converts mySql data types to google chart data types 
//$sqlTypes["VAR_STRING"]; will return "string"
$sqlTypes = array (
"VAR_STRING" => "string",
"LONGLONG" => "number",
"DATETIME" => "date"
);


//get number of cols retuned
$colCount = $query->columnCount();


//make google chart dataTable
$table = array();

//add array of cols to the data table
$table['cols'] = array();

//add each col to the data table as $table['cols']
for($i = 0; $i < $colCount; $i++){
	//get and hold all the meta data for this col as an array
	$table_fields  = $query->getColumnMeta($i);
	
	//add the google's dataTable label name and data type
	$table['cols'][] = array('label' => $table_fields["name"], 
		                      'type' => $sqlTypes[$table_fields["native_type"]]);
	//go to next column						  
}

//make google's dataTable rows
$table['rows'] = array();

//add each row to the datatable
foreach ($results as $row) {
//creat a array to hold each cell in a single row
  	$cells = array();  
	//get each cell by row(foreach above) and column (foreach below)
	foreach($table['cols'] as $col)
	{
		//get the col's type so the cell can be cassed for google charts 
		switch($col["type"]){
			//if its a number cast to a double to be on the safe side
			case "number":
				 $cells[] = array('v' => (double)$row[$col["label"]]);
				break;
			//if its a string no cast will be done (string by default)
			case "string":
				$cells[] = array('v' => $row[$col["label"]]);
				break;
			//case "dateTime" in the format of YYYY-MM-DD HH:MM:SS
			case "date":
				//split date and time up (find the space )
				$dateTimeArr = explode(' ', $row[$col["label"]]);
				//split month up into Year,Month,Day 
    			$dateArr = explode('-', $dateTimeArr[0]);
				//split date up into hour,min,sec 
    			$timeArr = explode(':', $dateTimeArr[1]);
				//set each veriable
    			$year = $dateArr[0];
    			$month = $dateArr[1] - 1;  // months are zero-indexed
    			$day = $dateArr[2];
    			$hour = $timeArr[0];
    			$min = $timeArr[1];
    			$sec = $timeArr[2];
				//make the cell
				$cells[] = array('v' => "Date($year, $month, $day, $hour, $min, $sec)");
				break;
			
		}
	}
	
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
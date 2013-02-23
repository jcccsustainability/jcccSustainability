<?php 
//Michael Read
//mySql to json for google chart's datatable
// https://developers.google.com/chart/interactive/docs/php_example


//connect to database
$username = "root" ;
$password = '';
$db;
//sets data base for veriable by url peramiters
if (!empty($_GET['dbs']))
$dbName = $_GET['dbs'] ;
else//for testing
$dbName = "information_schema";

if (!empty($_GET['q']))
//holds a query
$queryStr = $_GET['q'];
else
$queryStr = "SELECT TABLE_NAME, AVG_ROW_LENGTH  FROM TABLES where AVG_ROW_LENGTH > 0 LIMIT 0 , 5";
//echo $queryStr;
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


//converts mySql datat yples to google chart data types 
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
//add the datTabe array of cols
$table['cols'] = array();

//add each col to $table['cols']
for($i = 0; $i < $colCount; $i++){
	//holds all the meta data as an array
	$table_fields  = $query->getColumnMeta($i);
	//add the dataTable's column name and datatype
	$table['cols'][] = array('label' => $table_fields["name"], 'type' => $sqlTypes[$table_fields["native_type"]]);
}

//make dataTable rows
$table['rows'] = array();

//add each row to the datatable
foreach ($results as $row) {
//creat a array to hold each cell in the row
  	$cells = array();  
	//get each cell by row(foreach above) and column (foreach below)
	foreach($table['cols'] as $col)
	{
		//get the col's type so the cell can be cassed for google charts 
		switch($col["type"]){
			//if its a number cast to a double
			case "number":
				 $cells[] = array('v' => (double)$row[$col["label"]]);
				break;
			//if its a string no cast will be done (string by default)
			case "string":
				$cells[] = array('v' => $row[$col["label"]]);
				break;
			//case "date" is a bit tricky and im not sure if it works 
			case "date":
				$dateTimeArr = explode(' ', $row[$col["label"]]);
    			$dateArr = explode('-', $dateTimeArr[0]);
    			$timeArr = explode(':', $dateTimeArr[1]);
    			$year = $dateArr[0];
    			$month = $dateArr[1] - 1;  // months are zero-indexed
    			$day = $dateArr[2];
    			$hour = $timeArr[0];
    			$min = $timeArr[1];
    			$sec = $timeArr[2];
				$cells[] = array('v' => "Date($year, $month, $day, $hour, $min, $sec)");
				break;
			
		}
	}
	
	//add the array of cess to the rows array in the table
	$table['rows'][] = array('c' => $cells);
}

//send to javascript
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
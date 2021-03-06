<?php 
//Michael Read
//just prints the column names and the data types of each column in a simple html table


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
$queryStr = "SELECT * FROM TABLES";
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

//sql data types to google data types
$sqlTypes = array (
"VAR_STRING" => "string",
"LONGLONG" => "number",
"DATETIME" => "date"
);

//get number of cols retuned
$colCount = $query->columnCount();
//set up a html table for easy reading
echo "number cols retruned: ".$colCount;
echo "<table border='1'>";
echo "<tr>";
echo '<td>Name</td>';
echo '<td>SQL Type</td>';
echo '<td>Google Chart Type</td>';
echo "</tr>";
//add each col to $table['cols']
for($i = 0; $i < $colCount; $i++){
	
	//holds all the meta data of a single col as an array
		$met = $query->getColumnMeta($i);
		//html row added with name and sql type and google type (if they mach else it will print !not set!)
		echo "<tr>";
		echo '<td>'.$met["name"]."</td>";
		echo '<td>'.$met["native_type"]."</td>";
		if(array_key_exists($met["native_type"], $sqlTypes))
			echo '<td>'.$sqlTypes[$met["native_type"]].'</td>';
		else 
			echo '<td>!NOT SET!</td>';
	
		echo "</tr>";
		}
//close the html talbe
echo "</table >";

?>
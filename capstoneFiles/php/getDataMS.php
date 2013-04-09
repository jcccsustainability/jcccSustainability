<?php 


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




$queryStr = "SELECT top(".rand ( 25 , 35 ).") value, tstamp
FROM dbo.CC__ASD_IBSASDNETWORK_MNVAV__MNFLO__111_COMPOSITE_RMTMP__LOG
ORDER BY tstamp DESC
";



//run query store in result 
$result = mssql_query($queryStr ) or die('A error occured: ' . mysql_error());
//to loop through each row use:
//while ($row = mssql_fetch_array($result)){ $variable = $row["col name"]  }
	
	
// Get result size (number of rows)
$count = mssql_num_rows($result);



//make google chart dataTable
$table = array();

//add array of cols to the data table
$table['cols'] = array();
	//the query has two columns we add them to the google chart (order matters for some charts)							  						  
	$table['cols'][] = array('label' => 'timeStamp', 
		                      'type' => 'date');						  
		                      
	$table['cols'][] = array('label' => 'Room Temp', 
		                      'type' => 'number');
							  
	                				
//make google's dataTable rows
$table['rows'] = array();

// get each row from the database and and store it to the google data table rows array
while ($row = mssql_fetch_array($result)) {
//creat a array to hold each cell in a single row
  	$cells = array();  
  	
  	//split date and time up (explode will convert a string to an array of strngs by a spacer char (space for this)
				$dateTimeArr = explode(' ', $row["tstamp"]);
				//first part is the month (like JAN)
    			$monthAB = $dateTimeArr[0];
				//convert the Date string (looks "JAN") and converts to a 0 based int  JAN = 0 MAY = 4 
				// the "1 2011" has no effect just geting the month
				$month = date('m', strtotime("$monthAB 1 2011"));
				//day number is next
				$day = $dateTimeArr[1];
				//then year
				$year = $dateTimeArr[2];
				//explode the last string ("10:00AM")
				$timeArr = explode(':', $dateTimeArr[count($dateTimeArr)-1]);
				//hour
				$hour = $timeArr[0];
				//first two chars are the mins
				$min = substr($timeArr[1],0,2);
				//next to chars is AM or PM
    			$amPm = substr($timeArr[1],2,2);
				
				//convert to military time
				if($amPm == "PM"){
					$hour += 12;
					if($hour >= 24)
						$hour = 0;
				}
					
			
				
  	//add the date cell to row array
	$cells[] = array('v' => "Date($year, $month, $day, $hour, 0, 0, 0)");
	//add the "value" to row array
	$cells[] = array('v' => (double)$row["value"]);

	
	
	//add the array of cells to the rows array in the table
	$table['rows'][] = array('c' => $cells);
}
 mssql_close($con);

//encode the dataTable jason and printit out for javascript
echo( json_encode($table) );
/*the google data table looks like this
 * Table array {
 * 		"cols" array {
 * 			//each col has 
 * 			{
 * 			String lable
 * 			String Type [number, string, date, ...few other data types]
 * 			}
 * 		}
 * 		"rows" array {
 * 			//each cell
 * 			"c" array{
 * 				{
 * 					//value
 * 					 v = value of a cell
 * 				}
 * 			}
 * 			
 * 		}
 * }
 * 
 * example of what table array looks like when it is a JASON string
 
  {
  "cols": [//two columns
        {"label":"Topping","type":"string"},
        {"label":"Slices","type":"number"}
      ],
  "rows": [
        { //each cell (2 cols = 2 cells)
  		"c":[
 			//Topping
			{"v":"Mushrooms"},
 			//Slices
  			{"v":3}
  			]
  		},
  		//next row
        {
  		"c":[
  			{"v":"Onions","f":null},
  			{"v":1,"f":null}
  			]
  		},
  		//next row ... ect
        {"c":[{"v":"Olives","f":null},{"v":1,"f":null}]},
        {"c":[{"v":"Zucchini","f":null},{"v":1,"f":null}]},
        {"c":[{"v":"Pepperoni","f":null},{"v":2,"f":null}]}
      ]
}
 * 
*/

?>
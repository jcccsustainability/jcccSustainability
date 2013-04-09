
<?php
/* mike read
 * msToMySQL
 * in production this program gets the last time this script ran by a mysql to get the most recent date on the mysql database
 * then adds any dates that are missing from the database to the time column
 * then run a few queries
 * 1) Get a few mssql table names for a single building
 * 2) For each of the table names Get a single temp at a maching a date and hour
 * 3) Advrage the temps from the building and update it to mysql  database
 * 	-repeat 2 and 3 for each date range in the tables with 
 * 	-then repeate 1-3 with new building
 * this script can also make the table if $mkTable is set to true (error if table already made)
 */
//fix for max execution time to 600 seconds (default is after 30 seconds script will stop)
ini_set('max_execution_time', 600);
//for sending this serverside script to client mid stream to update progress bar
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
@ob_end_clean();
set_time_limit(0);

//building names $buildings[#]."_TEMP" is tempituer column and $buildings."_KWH" is kwh column 
$buildings = array("ATB","CC","CLB","CSB","GEB__COM","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");

//MYSQL INFO and connection
$MYserver = "localhost";
$MYuser = "root";
$MYpass = "";
$MYdb = "mstomytest";
$MYtable = "testFinal";
$MYcon = mysqli_connect($MYserver ,$MYuser ,$MYpass ,$MYdb);
// Check connection
if (mysqli_connect_errno($MYcon))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

//MSSQL INFO and connection
$MSserver = "10.99.3.219";
$MSuser = "sqledash";
$MSpass = "dunt_890";
$MSdb = "ES_JCCC";

$MScon = mssql_connect($MSserver, $MSuser, $MSpass) 
or die("Couldn't connect to SQL Server on Error: " . mssql_get_last_message());
//set database
mssql_select_db($MSdb) 
    or die('Could not select a database.');


//to make a table for the first time FALSE by defualt 
$mkTable = TRUE;
//for update tables with new times, queries mysql to get last time db was updated
$updateTables = TRUE;

//Create MYSQL table if  mkTable is true (error if table is already made)
if($mkTable)
{	//create table query
	$sql="CREATE TABLE ".$MYtable." 
	(
		 
		Time DATETIME NOT NULL, 
		PRIMARY KEY(Time ),";
		
	//add temp and kwh col for each building	
	foreach($buildings as $b){
		$sql .= $b."_temp DECIMAL(4,2), ".$b."_kwh DECIMAL, ";
	}
	
	//replace the last two chars (',' and ' ') with sql end bracket ')'
	$sql = substr_replace($sql, ")", -2);
	
	// Execute query
	if (mysqli_query($MYcon,$sql)){
	 echo("succsess!"); 		
	}
}//end of makeing table

//set time zone for php date object
date_default_timezone_set('US/Central');
//get last time database was updated if database was just made the date will be Sept 7 2012 

//set start date when the first data in the mssql db is
if($mkTable){
	$start_date = '2012-09-07 00:00:00';
}
else if($updateTables){ //get last date updated
$MYresult = mysqli_query($MYcon,"SELECT MAX(Time) as maxTime FROM ".$MYtable);
//if row is returned insert to database 
$row = mysqli_fetch_array($MYresult);
//add new date +1 hour
$start_date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime((string)$row[0])));
}
else { //testing
		$start_date = '2012-09-07 00:00:00';
}	
//for looping through dates make a new date that will change in the loop
$date = date ("Y-m-d H:i:s",  strtotime($start_date));	
//current date and hour 
$end_date = date ("Y-m-d H:00:00"); 
	
//loop date if last updated date +1 hour is less than current datetime
if($updateTables){
	//insert all the dates between date and end_date into time column in the MYSQL table (all the data for buildings is null)
	while (strtotime($date) <= strtotime($end_date )) {
		//insert each datetime from $date to current datetime
		mysqli_query($MYcon,"INSERT INTO ".$MYtable." (Time)
						  VALUES ('".$date."')");
		//add an hour to current date for while date loop
		$date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date)));
	}//end of while date loop
}	
//reset date to start date for use later
$date = date ("Y-m-d H:i:s",  strtotime($start_date));	

//print loading bars to html	
echo'
<!-- Progress bar holder -->
<div id="Total" style="width:500px;border:1px solid #ccc;"></div>
<div id="ALLprogress" style="width:500px;border:1px solid #ccc;"></div>
<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>
<div id="updated" style="width"></div>';
	
	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	//Start of CONVERTING MSsql to MYsql

//holds an key pair array of where statements for each building
	$where = array();
	$where["CC"] = "  name like '%TMP%' and name like 'CC%' ";
	$where["CLB"] = "name like '%RMTMP%' and name like 'CLB%' ";
	$where["CSB"] = "name like '%TMP%' and name like 'CSB%' ";
	$where["GEB__COM"] = "name like '%RMTMP%' and name like 'GEB__COM%' ";
	$where["GYM"] = "  name like '%RMTMP%' and name like 'GYM%' ";
	$where["HCDC"] = "  name like '%RMTMP%' and name like 'HCDC%' ";
	$where["LIB"] = "  name like '%LIB%'and name like'%RMTMP%'";
	$where["NMOCA"] = "name like 'NERMAN%' and name like '%TEMP%'";
	$where["OCB"] = "name like 'OCB%' and name like '%TEMP%'";
	$where["PA"] = "name like '%RPA%' and name like '%TEMP%'";
	$where["SC"] = "  name like '%TMP%' and name like 'SC%' ";
	$where["SCI"] = "  name like '%SCI%' and name like '%tmp%' ";
	$where["WH"] = "name like 'WH%' and name like '%tmp%'";
	
//get the differents from the start and end date in hours
//for getting current progress percent for loading bar
$hourDiff	= abs(strtotime($end_date) - strtotime($start_date))/(60*60);

//current part of the progress of a sub proggress bar $i/total = % done
$i = 0;
//current progress of the overall progress $aI/total = % all done
$aI = 0;

//a temp int to not print code to update progrss bar every time loop runs (unless needed)
$updated = 0;
//loop each where statement to get tables with a building name
foreach($where as $buildingName => $whereStatment){
	
	//get an int of the current overall percent done for progress bar
	$ALLpercent = intval(($aI/(sizeof($where)))*100);
	//incrament progress bar for next time loop runs
	$aI++;
	
	//get n# of of table names from top(#) found with an where array
	$queryStr = "SELECT top(5)  name
	FROM sys.Tables
	where ".$whereStatment;
	
	//holds the name of the tables found
	$tables = array();

	//run query to get table names
	$TABLEresult = mssql_query($queryStr ) or die('A error occured: ' . mysql_error());
	//loop each row found and store in an array of table names
	while ($row = mssql_fetch_array($TABLEresult)){
		//push each table name to the array
		 array_push($tables, $row["name"] );	
	}
		//set the sub-progress bar's percent
		$percent = intval($i/(sizeof($tables)*$hourDiff+1)+1);
		
		//loop dates
		while (strtotime($date) <= strtotime($end_date)) {
			//if the percent number changed print javascript to change progress bar
			if($percent >= $updated)
			{
				//$percentUpdate = $percent +1;
				 echo '<script language="javascript">
   				 document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'%;background-color:#ddd;\">&nbsp;</div>";
				 document.getElementById("ALLprogress").innerHTML="<div style=\"width:'.$ALLpercent.'%;background-color:#ddd;\">&nbsp;</div>";
   				 document.getElementById("information").innerHTML="'.$i.' of '.(sizeof($tables)*$hourDiff).' rows  processed.";
   				 document.getElementById("Total").innerHTML="Processing '.$aI.' of '.(sizeof($where)).' tables.";
   				 
   				 </script>';
 
   
   				 // Send output to browser immediately
   				 flush();
				 //for the if statement to run again above
				 $updated = $percent+2;
			}
			
			
			//split datetime to an array of date [0] and time [1]
			$dateArry = explode(" ", $date);
			//$dateYMD array is year = [0], Month = [1], Day = [2]	
			$dateYMD = explode("-", $dateArry [0]);
			
			//get hour
			$hour = explode(":", $dateArry[1]);
			$hour = $hour[0];
			
			//to get the advrage (sum of temps/number of temps)
			$sum = 0;
			$count = 0;
		
			
			
			//loop each table name
			foreach ($tables as $tbl) {
				
			//update the precent for sub loading bar
			$percent = intval($i/((sizeof($tables)*$hourDiff)+1)*100);
			$i++;
			
			//get the first tempiture from each table for a single date and hour
				$queryStr = "SELECT top(1)".$tbl.".value 
								FROM 
									".$tbl."
								WHERE 
									DATEPART(hh, tstamp) = ".$hour." and
									DATEPART(dd, tstamp) = ".$dateYMD[2]." and
									DATEPART(mm, tstamp) = ".$dateYMD[1]." and
									DATEPART(yy, tstamp) = ".$dateYMD[0];
				//run query on the MSSQL database
				$result = mssql_query($queryStr );
					//get first row
					if($result)
					$row = mssql_fetch_array($result);
					
					//ignore rows with no value
					if((double)$row[0] > 0){
					//add to sum
					$sum = $sum+(double)$row[0];
					//add one to count
					$count++;
					}//end if ignore rows
			}//end each table name loop
			
			
			//if count not 0 store advrage degree to MYSQL
			if($count > 0){
				//mysql update to change the null cell (becuase the time row is already made)
				$updated = ("UPDATE ".$MYtable." SET ".$buildingName."_TEMP=".round(($sum/$count),2).
							" where time like '".$date."'");
				//print the update statement to html for progress bar
				echo '<script language="javascript">
   				  document.getElementById("updated").innerHTML="'.$updated.'";
   				 </script>';
				
				//run update statement
				mysqli_query($MYcon,"UPDATE ".$MYtable." 
									SET ".$buildingName."_TEMP=".round(($sum/$count),2)."
							  		where time like '".$date."'");
							  		
				//add an hour to current date for while date loop
				$date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date)));
			}
			else {
			//print to html if not found
		echo '<script language="javascript">
   				  document.getElementById("updated").innerHTML="No mach fround for '.$buildingName.' at '.$date.'";
   				 </script>';
			//skip 12 hours becuase there was no data and sensor was probably off for a while
			$date = date ("Y-m-d H:i:s", strtotime("+12 hour", strtotime($date)));
			}
			
			
		}//end of while date loop
		//reset i for next sub progress bar
		$i = 0;
		//reset date for next building
		$date = $start_date;
	}//end of for loop
	// Tell user that the process is completed
		echo '<script language="javascript">
   				 document.getElementById("progress").innerHTML="<div style=\"width:100%;background-color:#ddd;\">&nbsp;</div>";
				 document.getElementById("ALLprogress").innerHTML="<div style=\"width:100%;background-color:#ddd;\">&nbsp;</div>";
   				 document.getElementById("information").innerHTML="done!";
				 document.getElementById("updated").innerHTML="";
   				 </script>';
//close dbs
mysqli_close($MYcon);
mssql_close($MScon);

?>


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
//////some php settings//////////////////////////////////////////////////////////////////////////////////////
//fix for max execution time to 600 seconds (default is after 30 seconds script will stop)
ini_set('max_execution_time', 600);
//for sending this serverside script to client mid stream to update progress bar
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
@ob_end_clean();
set_time_limit(0);

//building names $buildings[#]."_TEMP" is tempituer column and $buildings."_KWH" is kwh column 
$buildings = array("ATB","CC","CLB","CSB","GEB__COM","GYM","HCDC","HSC","ITC","LIB","NMOCA","OCB","PA","RC","SC","SCI","WH","WLB");

//CONNECT TO DATABASEs///////////////////////////////////////////////////////////////////////////////////////
//MYSQL INFO and connection
$MYserver = "localhost";
$MYuser = "root";
$MYpass = "";
$MYdb = "mstomytest";
$MYtable = "buildings";
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


//print loading bars to html	
echo'
<!-- Progress bar holder -->
<div id="Total" style="width:500px;border:1px solid #ccc;"></div>
<div id="ALLprogress" style="width:500px;border:1px solid #ccc;"></div>
<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>
<div id="updated" style="width"></div>';

//to make a table for the first time FALSE by defualt 
$mkTable = FALSE;
//for update tables with new times, queries mysql to get last time db was updated
$updateTables = TRUE;

//MAKE TABLE IF NEEDED//////////////////////////////////////////////////////////////////////////////////////////
//Create MYSQL table if  mkTable is true (error if table is already made)
if($mkTable)
{	//create table query
	$sql="CREATE TABLE ".$MYtable." 
	(
		 
		Time DATETIME NOT NULL, 
		PRIMARY KEY(Time ),
		weather  DECIMAL(4,1), ";
		
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

///GET DATE TO START QUERYIES (2012-09-07 or last time database was updated)/////////////////////////////////////////////////////////////////////////////////////////
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
	

//SET start and enddates //////////////////////////////////////////////////////////
//for looping through dates make a new date that will change in the loop
$date = date ("Y-m-d H:i:s",  strtotime($start_date));	
//current date and hour 
$end_date = date ("Y-m-d H:00:00"); 
	
//loop date and add time id to database /////////////////////////////////////////////
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

//ADD OUTSIDE WEATHER TO WEATHER COL///////////////
$TemperatureF = 1;
$DateUTC= 13;
while(strtotime($date) <= strtotime($end_date) )
{
	//echo $date."<br>";
	$dateArray = explode("-", $date);
	if (($handle = fopen("http://www.wunderground.com/history/airport/KOJC/".$dateArray[0]."/".((int)$dateArray[1])."/".((int)$dateArray[2])."/DailyHistory.html?req_city=Overland+Park&req_state=KS&req_statename=Kansas&theprefset=SHOWMETAR&theprefvalue=1&format=1", "r")) !== FALSE) 
	{
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    	
		    if(isset($data[$TemperatureF]) && $data[$TemperatureF] != "TemperatureF" && $data[$TemperatureF] != "" && (int)$data[$TemperatureF] > -90){
				$dateTime = date ("Y-m-d H:00:00", strtotime("-5 hour", strtotime( substr_replace($data[$DateUTC],'',-6) )));
				
						  
			   $sql = ("UPDATE ".$MYtable." SET weather=".$data[$TemperatureF].
							" where time like '".$dateTime."'");
							
		     	//echo $sql."<br>";
				mysqli_query($MYcon,$sql);
		    }
		}
	   fclose($handle);
	   $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
	}
}

//reset date to startdate
$date = date ("Y-m-d H:i:s",  strtotime($start_date));	
setTemp();
$date = date ("Y-m-d H:i:s",  strtotime($start_date));	
setKwh();
function setTemp()
{	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	//Start of CONVERTING MSsql to MYsql

//holds an key pair array of where statements for each building
	$where = array();
	$where["CC"] = "  name like '%RMTMP%' and name like 'CC%' ";
	$where["CLB"] = " name like '%RMTMP%' and name like 'CLB%' ";
	$where["CSB"] = " name like '%SPACETMP%' and name like 'CSB%' and name not like '%RT2SPACETMP%' ";
	$where["GEB__COM"] = " name like '%RMTMP%' and name like 'GEB__COM%' ";
	$where["GYM"] = "  name like '%RMTMP%' and name like 'GYM%' ";
	$where["HCDC"] = "  name like '%SPCTMP%' and name like 'HCDC%' ";
	$where["LIB"] = "  name like '%LIB%'and name like'%RMTMP%' ";
	$where["NMOCA"] = " name like 'NERMAN%' and name like '%TEMP%'";
	$where["OCB"] = " name like 'OCB%' and name like '%TEMP%' ";
	$where["PA"] =  " name like '%RPA%' and name like '%MTEMP%' ";
	$where["SC"] = "  name like '%TMP%' and name like '%COMPOSITE%' and name like 'SC%' and name not like '%RM211TMP%' ";
	$where["SCI"] = "  name like '%SCI%' and name like '%rmtmp%' ";
	$where["WH"] = " name like 'WH%' and name like '%spacetmp%' ";


//LOADING BAR VARS/////////////////////////////////////////////////////////	
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
	
	//get n# of of table names from top(#) found with a where[#] array
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
		
		//loop start date to end date +1 hour each loop
		while (strtotime($date) <= strtotime($end_date)) {
				
			//if the loading percent number changed print javascript to change progress bar
			if($percent > $updated)
			{
				//update loading bar
				 echo '<script language="javascript">
   				 document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'%;background-color:#ddd;\">&nbsp;</div>";
				 document.getElementById("ALLprogress").innerHTML="<div style=\"width:'.$ALLpercent.'%;background-color:#ddd;\">&nbsp;</div>";
   				 document.getElementById("information").innerHTML="'.$i.' of '.(sizeof($tables)*$hourDiff).' rows  processed.";
   				 document.getElementById("Total").innerHTML="Processing '.$aI.' of '.(sizeof($where)).' tables.";   				 
   				 </script>';
   				 // Send output to browser immediately
   				 flush();
				 //for the if statement to run again above
				 $updated = $percent+1;
			}
			
			//GET YEAR MONTH DAY HOUR as and array
			//split datetime to an array of date [0] and time [1]
			$dateArry = explode(" ", $date);
			//$dateYMD array is year = [0], Month = [1], Day = [2]	
			$dateYMD = explode("-", $dateArry [0]);
			//get hour
			$hour = explode(":", $dateArry[1]);
			$hour = $hour[0];
			
			//TO GET THE ADVRAGE EACH TEMP IN MSSQL DB
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
			
			
			//if count not 0 store advrage degree  to MYSQL where the time (key) = the date of temps
			if($count > 0){
				//mysql update to change the null cell (becuase the time row is already made)
				$updated = ("UPDATE ".$MYtable." SET ".$buildingName."_TEMP=".round(($sum/$count),2).
							" where time like '".$date."'");
				//print the sql update statement to html for progress bar
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
				 
			//skip 6 hours becuase there was no data and sensor was probably off for a while
			$date = date ("Y-m-d H:i:s", strtotime("+6 hour", strtotime($date)));
			}
			
			
		}//end of while date loop
		
		//reset date for next building
		$date = $start_date;
		
		//reset i for next sub progress bar
		$i = 0;
		
		
	}//end of for loop of where statements
	
	// Tell user that the process is completed
		echo '<script language="javascript">
   				 document.getElementById("progress").innerHTML="<div style=\"width:100%;background-color:#ddd;\">&nbsp;</div>";
				 document.getElementById("ALLprogress").innerHTML="<div style=\"width:100%;background-color:#ddd;\">&nbsp;</div>";
   				 document.getElementById("information").innerHTML="done!";
				 document.getElementById("updated").innerHTML="";
   				 </script>';
}//end of set temp fuction



function setKwh()
{	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	//Start of CONVERTING MSsql to MYsql

//holds an key pair array of where statements for each building
	$where = array();
	$where["ATB"] = " name like '%AVGKW%' and name like '%ATB%' ";
	$where["CC"] = "  name like '%AVGKW%' and name like '%CARLSEN%' ";
	$where["CLB"] = " name like '%AVGKW%' and name like '%CLB%' ";
	$where["CSB"] = " name like '%AVGKW%' and name like '%CSB%' ";
	$where["GEB__COM"] = "  name like '%AVGKW%' and (name like '%GEB%' or name like '%COM%') ";
	$where["HSC"] = " name like '%AVGKW%' and name like '%HSC%'  ";
	$where["ITC"] = "  name like '%AVGKW%' and name like '%ITC%'  ";
	$where["LIB"] = " name like '%AVGKW%' and name like '%LIB%' ";
	$where["OCB"] = " name like '%AVGKW%' and name like '%OCB%'   ";
	$where["PA"] = " name like '%AVGKW%' and name like '%RPA%'  ";
	$where["SC"] = " name like '%AVGKW%' and name like 'PMTR_SC_PM%'   ";
	$where["SCI"] = " name like '%AVGKW%' and name like '%SCI%'  ";
	$where["WH"] = "  name like '%AVGKW%' and name like '%WH%'  ";
	$where["WLB"] = " name like '%AVGKW%' and name like '%WLB%'   ";

	


//LOADING BAR VARS/////////////////////////////////////////////////////////	
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
	
	//get n# of of table names from top(#) found with a where[#] array
	$queryStr = "SELECT name
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
		
		//loop start date to end date +1 hour each loop
		while (strtotime($date) <= strtotime($end_date)) {
				
			//if the loading percent number changed print javascript to change progress bar
			if($percent > $updated)
			{
				//update loading bar
				 echo '<script language="javascript">
   				 document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.'%;background-color:#ddd;\">&nbsp;</div>";
				 document.getElementById("ALLprogress").innerHTML="<div style=\"width:'.$ALLpercent.'%;background-color:#ddd;\">&nbsp;</div>";
   				 document.getElementById("information").innerHTML="'.$i.' of '.(sizeof($tables)*$hourDiff).' rows  processed.";
   				 document.getElementById("Total").innerHTML="Processing '.$aI.' of '.(sizeof($where)).' tables.";   				 
   				 </script>';
   				 // Send output to browser immediately
   				 flush();
				 //for the if statement to run again above
				 $updated = $percent+1;
			}
			
			//GET YEAR MONTH DAY HOUR as and array
			//split datetime to an array of date [0] and time [1]
			$dateArry = explode(" ", $date);
			//$dateYMD array is year = [0], Month = [1], Day = [2]	
			$dateYMD = explode("-", $dateArry [0]);
			//get hour
			$hour = explode(":", $dateArry[1]);
			$hour = $hour[0];
			
			//TO GET THE ADVRAGE EACH TEMP IN MSSQL DB
			//to get the advrage (sum of temps/number of temps)
			$sum = 0;
			$count = 0;
		
			
			
			//loop each table name
			foreach ($tables as $tbl) {
				
			//update the precent for sub loading bar
			$percent = intval($i/((sizeof($tables)*$hourDiff)+1)*100);
			$i++;
			
			//get the first tempiture from each table for a single date and hour
				$queryStr = "SELECT sum(".$tbl.".value)
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
			
			
			//if count not 0 store advrage degree  to MYSQL where the time (key) = the date of temps
			if($count > 0){
				//mysql update to change the null cell (becuase the time row is already made)
				$updated = ("UPDATE ".$MYtable." SET ".$buildingName."_kwh=".round(($sum),2).
							" where time like '".$date."'");
				//print the sql update statement to html for progress bar
				echo '<script language="javascript">
   				  document.getElementById("updated").innerHTML="'.$updated.'";
   				 </script>';
				
				//run update statement
				mysqli_query($MYcon,"UPDATE ".$MYtable." 
									SET ".$buildingName."_kwh=".round($sum,2)."
							  		where time like '".$date."'");
							  		
				//add an hour to current date for while date loop
				$date = date ("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date)));
			}
			else {
			//print to html if not found
			echo '<script language="javascript">
   				  document.getElementById("updated").innerHTML="No mach fround for '.$buildingName.' at '.$date.'";
   				 </script>';
				 
			//skip 2 hours becuase there was no data and sensor was probably off for a while
			$date = date ("Y-m-d H:i:s", strtotime("+2 hour", strtotime($date)));
			}
			
			
		}//end of while date loop
		
		//reset date for next building
		$date = $start_date;
		
		//reset i for next sub progress bar
		$i = 0;
		
		
	}//end of for loop of where statements
	
	// Tell user that the process is completed
		echo '<script language="javascript">
   				 document.getElementById("progress").innerHTML="<div style=\"width:100%;background-color:#ddd;\">&nbsp;</div>";
				 document.getElementById("ALLprogress").innerHTML="<div style=\"width:100%;background-color:#ddd;\">&nbsp;</div>";
   				 document.getElementById("information").innerHTML="done!";
				 document.getElementById("updated").innerHTML="";
   				 </script>';
}//end of set temp fuction


//close dbs
mysqli_close($MYcon);
mssql_close($MScon);

?>

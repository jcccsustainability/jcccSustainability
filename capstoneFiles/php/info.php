<?php
	$where = array();
	$where["CC"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%TMP%' and name like 'CC%' ";
	$where["CLB"] = "name like '%RMTMP%' and name like 'CLB%' ";
	$where["CSB"] = "name like '%TMP%' and name like 'CSB%' ";
	$where["GEB__COM"] = "name like '%RMTMP%' and name like 'GEB__COM%' ";
	$where["GYM"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%RMTMP%' and name like 'GYM%' ";
	$where["HCDC"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%RMTMP%' and name like 'HCDC%' ";
	$where["LIB"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%LIB%'and name like'%RMTMP%'";
	$where["NMOCA"] = "name like 'NERMAN%' and name like '%TEMP%'";
	$where["OCB"] = "name like 'OCB%' and name like '%TEMP%'";
	$where["PA"] = "where name like '%RPA%' and name like '%TEMP%'";
	$where["SC"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%TMP%' and name like 'SC%' ";
	$where["SCI"] = "object_id like '%".rand( 0 , 99 )."%' and name like '%SCI%' and name like '%tmp%' ";
	$where["WH"] = "name like 'WH%' and name like '%tmp%'";
	

foreach($where as $buildingName => $statment) {
  echo $statment.' is begin with ('.$buildingName.')<br>';
}
?>
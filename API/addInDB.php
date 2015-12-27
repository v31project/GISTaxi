<?php
	require "db.inc.php";
	$fileName = "../citys.txt";
	$tableName = "Citys";
	$columnName1 = "nameCity";
	$columnName2 = "idBrandCar";

	$data = array();
	$file = fopen($fileName, "r");
	while (!feof($file)) {
		$data[] = trim(fgets($file));
	}
	
	foreach ($data as $item) {
		/*$mysqli->query("INSERT INTO $tableName ($columnName1) 
			VALUES ('$item')");*/
	}
?>
<?php
	require_once "smsc_api.php";

	function clearData($data, $mysqli, $type="s"){
		switch($type){
			case "s":
				return $mysqli->real_escape_string(trim(strip_tags($data)));
			case "sf":
				return trim(strip_tags($data));
			case "i":
				return (int)$data;
		}
	}

	function db2Array($mysqli){
		$arr = array();
		while($row = $mysqli->fetch_array(MYSQLI_ASSOC)){
			$arr[] = $row;
		}
		return $arr;
	}

	function randomString($len){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$numChars = strlen($chars);
	  	$randName = '';
	  	for ($i = 0; $i < $len; $i++) {
	    	$randName .= substr($chars, rand(1, $numChars) - 1, 1);
	  	}
	  	return $randName;
	}

	function checkToken($mysqli, $phone){
		$headers = apache_request_headers();
		//var_dump($headers);
		$data = explode(":", $headers['Token']);
		$token = $headers['Token'];
		$status = $data[1];
		
		if ($phone == "" or $token == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			return $output_arr;
		}

		$sql = "SELECT phone FROM Tokens WHERE phone=$phone AND token='$token'";
		$result = $mysqli->query($sql);
		$myrow = db2Array($result);
		if (!$myrow[0]['phone']) {
			$output_arr["id"] = 607;
			$output_arr["name"] = "Invalid TOKEN or PHONE";
			return $output_arr;
		}else{
			$data['status'] = $status;
			$data['token'] = $token;
			return $data;
		}
	}

	function readStream(){
		$_DATA = array();
		$data = file_get_contents('php://input');
	  	$exploded = explode('&', $data);
	  	foreach($exploded as $pair) { 
	    	$item = explode('=', $pair);
	    	if(count($item) == 2) { 
      			$_DATA[urldecode($item[0])] = urldecode($item[1]); 
	    	}
	  	}
	  	return $_DATA;
	}

	function fullPathPhoto($relativePathPhoto){
		list(, $tempPhotoPath) = explode("..", $relativePathPhoto);
		return $_SERVER['SERVER_NAME'].$tempPhotoPath;
	}
	





//функции API

	//+регистрация пользователя
	function registration($mysqli){
		$output_arr = array();
		$phone = clearData($_POST['phone'], $mysqli);
		$status = clearData($_POST['status'], $mysqli);
		$regDate = date("Y-m-d");

		if ($status == "passenger") {
			$tableName = "Passengers";
			$tableNameOther = "Drivers";
		}elseif ($status == "driver") {
			$tableName = "Drivers";
			$tableNameOther = "Passengers";
		}else{
			$output_arr["id"] = 602;
			$output_arr["name"] = "Invalid parameter STATUS";
			$data['code'] = 400;
			$data['content'] = json_encode($output_arr);
			return $data;
		}

		if ($phone == 0 or $status == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
			$data['content'] = json_encode($output_arr);
			return $data;
		}else{
			$result = $mysqli->query("SELECT * FROM $tableNameOther WHERE phone=$phone");
			$myrow = db2Array($result);
			if ($myrow[0]['phone']) {
				$output_arr["id"] = 603;
				$output_arr["name"] = "This number is registered as the ".$tableNameOther;
				$data['code'] = 400;
				$data['content'] = json_encode($output_arr);
				return $data;
			}

			$securityKey = randomString(4);

			$result = $mysqli->query("SELECT * FROM $tableName WHERE phone=$phone");
			$myrow = db2Array($result);
			if (!$myrow[0]['phone']) {
				$result = $mysqli->query("INSERT INTO $tableName (phone,securityKey,regDate) VALUES ($phone,'$securityKey','$regDate')");
				if (!$result) {
    				$output_arr["id"] = 604;
    				$output_arr["name"] = "Failed to MySQL query: (".$mysqli->errno.") ".$mysqli->error;
    				$data['code'] = 400;
					$data['content'] = json_encode($output_arr);
					return $data;
    			}
			}else{
				$result = $mysqli->query("UPDATE $tableName SET securityKey='$securityKey' WHERE phone=$phone");
				if (!$result) {
    				$output_arr["id"] = 604;
    				$output_arr["name"] = "Failed to MySQL query: (".$mysqli->errno.") ".$mysqli->error;
    				$data['code'] = 400;
					$data['content'] = json_encode($output_arr);
					return $data;
				}
			}
			$sms = send_sms($phone, "Код подтверждения:\n$securityKey");
			if (isset($sms[3])) {
				$output_arr["name"] = "SMS sent successfully";
				$data['code'] = 200;
				//testing ||
				//		  \/
				$output_arr["securityKey"] = $securityKey;
			}else{
				$output_arr["id"] = 605;
				$output_arr["name"] = "SMS not send: error №".$sms[1];
				$data['code'] = 400;
			}
			$data['content'] = json_encode($output_arr);
			return $data;

		}
	}


	//+подтверждение регистарции пользователя
	function activation($mysqli){
		$_PUT = readStream();
		$phone = clearData($_PUT['phone'], $mysqli);
		$status = clearData($_PUT['status'], $mysqli);
		$securityKey = clearData($_PUT['securityKey'], $mysqli);
		//var_dump($_PUT);

		if ($status == "passenger") {
			$tableName = "Passengers";
			$tableNameOther = "Drivers";
		}elseif ($status == "driver") {
			$tableName = "Drivers";
			$tableNameOther = "Passengers";
		}else{
			$output_arr["id"] = 602;
			$output_arr["name"] = "Invalid parameter STATUS";
			$data['code'] = 400;
			$data['content'] = json_encode($output_arr);
			return $data;
		}

		if ($phone and $status and $securityKey) {
			$result = $mysqli->query("SELECT * FROM $tableName WHERE phone=$phone AND securityKey='$securityKey'");
			$myrow = db2Array($result);
			if (!$myrow[0]['phone']) {
				$output_arr["id"] = 606;
				$output_arr["name"] = "Invalid securityKey";
				$data['code'] = 400;
			}else{
				$token = md5(uniqid()).':'.$status;
				$result = $mysqli->query("INSERT INTO Tokens (phone,token) VALUES ($phone,'$token')");
				if (!$result) {
    				$output_arr["id"] = 604;
    				$output_arr["name"] = "Failed to MySQL query: (".$mysqli->errno.") ".$mysqli->error;
    				$data['code'] = 400;
    			}else{
					$data['code'] = 200;
					$output_arr["name"] = "Successfully activated";
					$output_arr["token"] = $token;
					//header("Token: $token");
	    		}
			}
			$data['content'] = json_encode($output_arr);
		}else{
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
			$data['content'] = json_encode($output_arr);
		}
		//var_dump($data);
		//header("Access-Control-Allow-Origin: *");
		return $data;
	}

	//+изменение информации пользователя
	function editInfo($mysqli){
		$_PUT = readStream();
		$phone = clearData($_PUT['phone'], $mysqli);
		$name = clearData($_PUT['name'], $mysqli);
		$idCity = clearData($_PUT['idCity'], $mysqli);
		$sex = clearData($_PUT['sex'], $mysqli);
		$idBrandCar = clearData($_PUT['idBrandCar'], $mysqli);
		$idModelCar = clearData($_PUT['idModelCar'], $mysqli);
		$idColorCar = clearData($_PUT['idColorCar'], $mysqli);
		$stateNumber = clearData($_PUT['stateNumber'], $mysqli);

		$auth = checkToken($mysqli, $phone);
		if (!empty($auth['id'])) {
			$data['code'] = 400;
			$data['content'] = json_encode($auth);
			return $data;
		}else{
			$status = $auth['status'];
		}

		if ($status == "driver") {
			if ($phone == "" or $idModelCar == "" or $idBrandCar == "" or $idColorCar == "" or 
				$stateNumber == "" or $idCity == "") {
				$output_arr["id"] = 601;
				$output_arr["name"] = "Not all parameters set";
    			$data['code'] = 400;
				$data['content'] = json_encode($output_arr);
				return $data;
			}else{
				$result = $mysqli->query("UPDATE Drivers SET idModelCar=$idModelCar,
															 idBrandCar=$idBrandCar,
															 idColor=$idColorCar,
															 stateNumber='$stateNumber',
															 idCity=$idCity
														 WHERE phone=$phone");
				if (!$result) {
    				$output_arr["id"] = 604;
    				$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
    				$data['code'] = 400;
					$data['content'] = json_encode($output_arr);
    			}else{
    				$output_arr["name"] = "Successfully updated";
    				$data['code'] = 200;
					$data['content'] = json_encode($output_arr);
    			}
			}
		}elseif ($status == "passenger"){
			if ($phone == "" or $name == "" or $sex == "" or $idCity == "") {
				$output_arr["id"] = 601;
				$output_arr["name"] = "Not all parameters set";
    			$data['code'] = 400;
				$data['content'] = json_encode($output_arr);
				return $data;
			}else{
				if ($sex != "men" and $sex != "women") {
					$output_arr["id"] = 608;
					$output_arr["name"] = "Invalid parameter SEX";
					$data['code'] = 400;
					$data['content'] = json_encode($output_arr);
					return $data;
				}
				$result = $mysqli->query("UPDATE Passengers SET name='$name',
															 sex='$sex',
															 idCity=$idCity
														 WHERE phone=$phone");
				if (!$result) {
    				$output_arr["id"] = 604;
    				$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
    				$data['code'] = 400;
					$data['content'] = json_encode($output_arr);
					return $data;
    			}else{
    				$output_arr["name"] = "Successfully updated";
    				$data['code'] = 200;
					$data['content'] = json_encode($output_arr);
					return $data;
    			}
			}
		}
		return $data;
	}

	//+изменение фото-аватара
	function editPhoto($mysqli){
		$_PUT = readStream();
		$phone = clearData($_PUT['phone'], $mysqli);
		$sourceDataPhoto = clearData($_PUT['dataPhoto'], $mysqli);

		if ($phone == "" or $sourceDataPhoto == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
		}else{
			$auth = checkToken($mysqli, $phone);
			if (!empty($auth['id'])) {
				$data['code'] = 400;
				$data['content'] = json_encode($auth);
				return $data;
			}else{
				$status = $auth['status'];
			}

			if ($status == "passenger") {
				$tableName = "Passengers";
			}elseif ($status == "driver") {
				$tableName = "Drivers";
			}

			//$dataPhoto = str_replace("\\", "", $dataPhoto);
			//$data = 'data:image/png;base64,AAAFBfj42Pj4';
			list($type, $dataPhoto) = explode(';', $sourceDataPhoto);
			list(, $type) = explode("/", $type);
			list(, $dataPhoto) = explode(',', $dataPhoto);
			$filePhoto = base64_decode($dataPhoto);
			$photo = "../photo/$phone.$type";
			if (!file_put_contents($photo, $filePhoto)){
	 			
	 			$output_arr["id"] = 609;
				$output_arr["name"] = "Photo not upload";
	 			$data['code'] = 400;
	 			$data['content'] = json_encode($output_arr);
	 		}else{
	 			$result = $mysqli->query("UPDATE $tableName SET photo='$photo'
														WHERE phone=$phone");
				if (!$result) {
					$output_arr["id"] = 604;
    				$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
					$data['code'] = 400;
				}else{
					$output_arr["name"] = fullPathPhoto($photo);//$sourceDataPhoto;//realpath($photo);
					$data['code'] = 200;
				}
	 		}
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}

	//+полочение информации пользователя
	function getInfo($mysqli){
		
		$phone = clearData($_GET['phone'], $mysqli);

		if ($phone == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
			$data['content'] = json_encode($output_arr);
			return $data;
		}else{
			$auth = checkToken($mysqli, $phone);
			if (!empty($auth['id'])) {
				$data['code'] = 400;
				$data['content'] = json_encode($auth);
				return $data;
			}else{
				$status = $auth['status'];
			}

			if ($status == "passenger") {
				$tableName = "Passengers";
			}else{
				$tableName = "Drivers";
			}
			$result = $mysqli->query("SELECT * FROM $tableName WHERE phone=$phone");
			$myrow = db2Array($result);

			$myrow[0]['photo'] = fullPathPhoto($myrow[0]['photo']);


			$output_arr["name"] = $myrow[0];
			$data['code'] = 200;
			$data['content'] = json_encode($output_arr);
			return $data;
		}
	}

	//+получение фото пользователя
	function getPhoto($mysqli){
		$phone = clearData($_GET['phone'], $mysqli);
		$output_arr = array("errors"=>array(), "data"=>array());
		if ($phone == "") {
			$output_arr["errors"]["Parameters error"] = "Not all parameters set";
			$data['code'] = 400;
		}else{
			$auth = checkToken($mysqli, $phone);
			if (!empty($auth['id'])) {
				$data['code'] = 401;
				$data['content'] = json_encode($auth);
				return $data;
			}else{
				$status = $auth['status'];
			}

			if ($status == "passenger") {
				$tableName = "Passengers";
			}else{
				$tableName = "Drivers";
			}
			$result = $mysqli->query("SELECT photo FROM $tableName WHERE phone=$phone");
			$myrow = db2Array($result);
			$path = $myrow[0]['photo'];
			$type = pathinfo($path, PATHINFO_EXTENSION);
			if ($dataPhoto = @file_get_contents($path)) {
				$base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataPhoto);
				$output_arr["data"]["photo"] = $base64;
				$data['code'] = 200;
			}else{
				$output_arr["errors"]["Not found"] = "Photo is not found";
				$data['code'] = 404;
			}
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}

	//+выход пользователя из приложения
	function logOff($mysqli){
		$stringQuery = $_SERVER['QUERY_STRING'];
		$data = explode("=", $stringQuery);
		$phone = clearData($data[1], $mysqli);

		if ($phone == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
			$data['content'] = json_encode($output_arr);
			return $data;
		}else{
			$auth = checkToken($mysqli, $phone);
			if (!empty($auth['id'])) {
				$data['code'] = 400;
				$data['content'] = json_encode($auth);
				return $data;
			}else{
				$status = $auth['status'];
				$token = $auth['token'];
			}

			if ($status == "passenger") {
				$tableName = "Passengers";
			}elseif ($status == "driver") {
				$tableName = "Drivers";
			}

			$result = $mysqli->query("DELETE FROM Tokens WHERE phone=$phone AND token='$token'");
			if (!$result) {
				$output_arr["id"] = 604;
   				$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
				$data['code'] = 400;
			}else{
				$output_arr["name"] = "Successfully exit";
				$data['code'] = 200;
			}
			$data['content'] = json_encode($output_arr);
			return $data;
		}
	}

	//+добавление нового заказа
	function addOrder($mysqli){
		$phonePassenger = clearData($_POST['phonePassenger'], $mysqli);
		$pointStart = clearData($_POST['pointStart'], $mysqli);
		$pointFinish1 = clearData($_POST['pointFinish1'], $mysqli);
		$pointFinish2 = clearData($_POST['pointFinish2'], $mysqli);
		$typePrice = clearData($_POST['typePrice'], $mysqli);
		$price = clearData($_POST['price'], $mysqli, "i");
		$phoneDriver = clearData($_POST['phoneDriver'], $mysqli);
		$locationStatus = clearData($_POST['locationStatus'], $mysqli);
		$timeOrder = date("Y-m-d G:i:s");
		$dateStart = clearData($_POST['dateStart'], $mysqli);
		$comment = clearData($_POST['comment'], $mysqli);

		if ($phonePassenger == "" or $pointStart == "" or $pointFinish1 == "" or 
			$typePrice == "" or $price == 0 or $locationStatus == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
		}else{
			$auth = checkToken($mysqli, $phonePassenger);
			if (!empty($auth['id'])) {
				$data['code'] = 400;
				$data['content'] = json_encode($auth);
				return $data;
			}
			if ($locationStatus == "intercity") {
				if ($dateStart == "") {
					$output_arr["id"] = 601;
					$output_arr["name"] = "Not all parameters set";
					$data['code'] = 400;
				}else{
					$result = $mysqli->query("INSERT INTO Orders (phonePassenger,
																	locationStatus,
																	timeOrder,
																	dateStart,
																	pointStart,
																	pointFinish1,
																	price,
																	typePrice,
																	comment,
																	statusOrder) 
														VALUES ($phonePassenger,
																	'$locationStatus',
																	'$timeOrder',
																	'$dateStart',
																	'$pointStart',
																	'$pointFinish1',
																	$price,
																	'$typePrice',
																	'$comment',
																	'expect')");
					if (!$result) {
	    				$output_arr["id"] = 604;
	    				$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
	    				$data['code'] = 400;
					}else{
						$output_arr["name"] = "Order successfully added";
	    				$data['code'] = 200;
					}
				}
			}elseif ($locationStatus == "city") {
				if ($phoneDriver == "") {
					$output_arr["id"] = 601;
					$output_arr["name"] = "Not all parameters set";
					$data['code'] = 400;
				}else{
					if ($pointFinish2 == "") {
						$fieldPointFinish2 = "";
						$pFinish2 = "";
					}else{
						$fieldPointFinish2 = "pointFinish2,";
						$pFinish2 = "'$pointFinish2',";
					}
					$result = $mysqli->query("INSERT INTO Orders (phonePassenger,
																	locationStatus,
																	timeOrder,
																	pointStart,
																	pointFinish1,".
																	$fieldPointFinish2.
																	"price,
																	typePrice,
																	comment,
																	statusOrder,
																	phoneDriver) 
														VALUES ($phonePassenger,
																	'$locationStatus',
																	'$timeOrder',
																	'$pointStart',
																	'$pointFinish1',".
																	$pFinish2.
																	"$price,
																	'$typePrice',
																	'$comment',
																	'matching',
																	$phoneDriver)");
					if (!$result) {
	    				$output_arr["id"] = 604;
    					$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
	    				$data['code'] = 400;
					}else{
						$output_arr["name"] = "Order successfully added";
	    				$data['code'] = 200;
					}
				}
			}else{
				$output_arr["id"] = 610;
				$output_arr["name"] = "Invalid parameter LOCATIONSTATUS";
	    		$data['code'] = 400;
			}
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}

	//+получение списка марок автомобилей и их моделей
	function getAutos($mysqli){
		$resultAutos = $mysqli->query("SELECT * FROM BrandsCars");
		if (!$resultAutos) {
			$output_arr["id"] = 604;
			$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
			$data['code'] = 400;
		}else{
			$autos = db2Array($resultAutos);
			$arr_brands = array();
			foreach ($autos as $auto) {
				$idBrandCar = trim($auto["idBrandCar"]);
				$resultModels = $mysqli->query("SELECT * FROM ModelsCars WHERE idBrandCar=$idBrandCar");
				if (!$resultModels) {
					$output_arr["id"] = 604;
					$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
					$data['code'] = 400;
					$data['content'] = json_encode($output_arr);
					return $data;
				}else{
					$models = db2Array($resultModels);
					$arr_models = array();
					foreach ($models as $model) {
						$arr_models[] = array("idModelCar"=>trim($model['idModelCar']), 
												"modelCar"=>trim($model['modelCar']));
					}
					$output_arr[] = array("idBrandCar"=>$idBrandCar,
											"barandCar"=>trim($auto["brandCar"]),
											"models"=>$arr_models);

				}
			}
			$data['code'] = 200;
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}

	//+получение списка цветов автомобилей
	function getColors($mysqli){
		$resultColors = $mysqli->query("SELECT * FROM Colors");
		if (!$resultColors) {
			$output_arr["id"] = 604;
			$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
			$data['code'] = 400;
		}else{
			$colors = db2Array($resultColors);
			foreach ($colors as $color) {
				$output_arr[] = array("idColor"=>$color["idColor"], "color"=>$color["color"]);
			}
			$data['code'] = 200;
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}

	//+получение списка городов
	function getCitys($mysqli){
		$resultCitys = $mysqli->query("SELECT * FROM Citys");
		if (!$resultCitys) {
			$output_arr["id"] = 604;
			$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
			$data['code'] = 400;
		}else{
			$citys = db2Array($resultCitys);
			foreach ($citys as $city) {
				$output_arr[] = array("idCity"=>trim($city["idCity"]), "nameCity"=>trim($city["nameCity"]));
			}
			$data['code'] = 200;
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}
	
	//НЕДОПИСАН!!!//получение n ближайших водителей
	function getNearbyDrivers($mysqli){
		$amountDrivers = clearData($_GET["amountDrivers"], $mysqli);
		$phone = clearData($_GET["phone"], $mysqli);
		$token = clearData($_GET["token"], $mysqli);

		checkToken($mysqli, $phone, $token, $status);
	}

	//+получение списка ожидающих заказов
	function getExpectOrders($mysqli){
		$phone = clearData($_GET['phone'], $mysqli);
		$locationStatus = clearData($_GET['locationStatus'], $mysqli);

		if ($phone == "" or $locationStatus == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
		}else{
			$auth = checkToken($mysqli, $phone);
			if (!empty($auth['id'])) {
				$data['code'] = 400;
				$data['content'] = json_encode($auth);
				return $data;
			}else{
				$status = $auth['status'];
			}
			if ($status != "driver") {
				$output_arr["id"] = 602;
				$output_arr["name"] = "Invalid parameter STATUS";
				$data['code'] = 400;
				$data['content'] = json_encode($output_arr);
				return $data;
			}
			if ($locationStatus != "city" and $locationStatus != "intercity") {
				$output_arr["id"] = 610;
				$output_arr["name"] = "Invalid parameter LOCATIONSTATUS";
				$data['code'] = 400;
			}else{
				$result = $mysqli->query("SELECT Orders.*, Passengers.photo, Passengers.name 
															FROM Orders 
																INNER JOIN
															Passengers ON Orders.phonePassenger = Passengers.phone 
															WHERE Orders.statusOrder='expect' and
																	 Orders.locationStatus='$locationStatus'");
				if (!$result) {
					$output_arr["name"] = "Data request not found";
					$data['code'] = 200;
				}else{
					$myrow = db2Array($result);
					foreach ($myrow as $order) {
						$order['photo'] = fullPathPhoto($order['photo']);
					}
					
					$output_arr["name"] = $myrow;
					$data['code'] = 200;
				}
			}
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}

	//+установка статуса местоположения(город/межгород)
	function setLocationStatus($mysqli){
		$_PUT = readStream();
		$phone = clearData($_PUT['phone'], $mysqli);
		$locationStatus = clearData($_PUT['locationStatus'], $mysqli);

		if ($phone == "" or $locationStatus == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
		}else{
			$auth = checkToken($mysqli, $phone);
			if (!empty($auth['id'])) {
				$data['code'] = 400;
				$data['content'] = json_encode($auth);
				return $data;
			}else{
				$status = $auth['status'];
			}
			if ($status == "passenger") {
				$tableName = "Passengers";
			}elseif ($status == "driver") {
				$tableName = "Drivers";
			}

			if ($locationStatus == "city" or $locationStatus == "intercity") {
				$result = $mysqli->query("UPDATE $tableName SET locationStatus='$locationStatus' 
															WHERE phone=$phone");
				if (!$result) {
					$output_arr["id"] = 604;
					$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
					$data['code'] = 400;
				}else{
					$output_arr["name"] = "LOCATIONSTATUS successfully updated";
					$data['code'] = 200;
				}
			}else{
				$output_arr["id"] = 610;
				$output_arr["name"] = "Invalid parameter LOCATIONSTATUS";
				$data['code'] = 400;
			}
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}

	//+установка стутаса работы водителя
	function setWorkingStatus($mysqli){
		$_PUT = readStream();
		$phone = clearData($_PUT['phone'], $mysqli);
		$workingStatus = clearData($_PUT['workingStatus'], $mysqli);

		if ($phone == "" or $workingStatus == "") {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
		}else{
			$auth = checkToken($mysqli, $phone);
			if (!empty($auth['id']) or $auth['status'] != "driver") {
				$data['code'] = 400;
				$auth['id'] = 607;
				$auth['name'] = "Invalid TOKEN or PHONE";
				$data['content'] = json_encode($auth);
				return $data;
			}

			$workingStatus = (int)$workingStatus;
			if ($workingStatus == 0 or $workingStatus == 1) {
				$result = $mysqli->query("UPDATE Drivers SET workingStatus=$workingStatus 
															WHERE phone='$phone'");
				if (!$result) {
					$output_arr["id"] = 604;
					$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
					$data['code'] = 400;
				}else{
					$output_arr["name"] = "WORKINGSTATUS successfully updated";
					$data['code'] = 200;
				}
			}else{
				$output_arr["id"] = 611;
				$output_arr["name"] = "Invalid parameter WORKINGSTATUS";
				$data['code'] = 400;
			}
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}

	//+отзыв о водителе после выполнения заказа
	function addReview($mysqli){
		$phonePassenger = clearData($_POST['phonePassenger'], $mysqli);
		$rating = clearData($_POST['rating'], $mysqli, "i");
		$textReview = clearData($_POST['textReview'], $mysqli);
		$phoneDriver = clearData($_POST['phoneDriver'], $mysqli);
		$timeReview = date("Y-m-d G:i:s");

		if ($phonePassenger == "" or $phoneDriver == "" or !is_int($rating)) {
			$output_arr["id"] = 601;
			$output_arr["name"] = "Not all parameters set";
			$data['code'] = 400;
		}else{
			$auth = checkToken($mysqli, $phonePassenger);
			if (!empty($auth['id']) or $auth['status'] != "passenger") {
				$data['code'] = 400;
				$auth['id'] = 607;
				$auth['name'] = "Invalid TOKEN or PHONE";
				$data['content'] = json_encode($auth);
				return $data;
			}
			if ($rating < 0 or $rating > 5) {
				$output_arr["id"] = 612;
				$output_arr["name"] = "Invalid parameter RATING";
				$data['code'] = 400;
				$data['content'] = json_encode($output_arr);
				return $data;
			}
			
			$result = $mysqli->query("SELECT * FROM Passengers WHERE phone=$phonePassenger");
			$myrow = db2Array($result);
			$idPassenger = $myrow[0]["idPassenger"];

			$result = $mysqli->query("SELECT * FROM Drivers WHERE phone=$phoneDriver");
			$myrow = db2Array($result);
			if (!$myrow[0]) {
				$output_arr["id"] = 613;
				$output_arr["name"] = "Invalid parameter PHONEDRIVER";
				$data['code'] = 400;
				$data['content'] = json_encode($output_arr);
				return $data;
			}
			$idDriver = $myrow[0]["idDriver"];

			$result = $mysqli->query("INSERT INTO Reviews (idPassenger, 
															idDriver, 
															textReview,
															timeReview,
															rating) 
												VALUES ($idPassenger,
														$idDriver,
														'$textReview',
														'$timeReview',
														$rating)");
			if (!$result) {
				$output_arr["id"] = 604;
				$output_arr["name"] = "Failed to query: (".$mysqli->errno.") ".$mysqli->error;
				$data['code'] = 400;
			}else{
				$output_arr["name"] = "REVIEW successfully added";
				$data['code'] = 200;
			}
		}
		$data['content'] = json_encode($output_arr);
		return $data;
	}
?>
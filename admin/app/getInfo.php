<?php
	require "model.php";

	header('Content-Type: application/json; charset=utf-8');
	if (!empty($_POST)) {
		$phone = clearData($_POST['phone'], $mysqli);
		$table = clearData($_POST['table'], $mysqli);

		// var_export($column);
		// var_export($editVal);
		// var_export($id);

		if ($phone == "" or $table == "") {
			$errors = "Не удалось получить информацию! Пустые параметры!";
		}else{
			if ($table == "Passengers" or $table == "Drivers") {
				$table == "Passengers" ? $phoneField = "phonePassenger" : $phoneField = "phoneDriver";
				$info = array();
				if ($table == "Passengers") {
					$result = $mysqli->query("SELECT idPassenger AS idUser, 
														name,
														phone, 
														sex, 
														nameCity AS city, 
														regDate, 
														locationStatus, 
														balanceBonuses, 
														lockStatus, 
														photo 
												FROM $table INNER JOIN Citys ON $table.idCity=Citys.idCity
												WHERE phone=$phone");
				}else{
					$result = $mysqli->query("SELECT idDriver AS idUser, 
														phone, 
														workingStatus, 
														locationStatus, 
														nameCity AS city, 
														regDate, 
														balance, 
														lockStatus, 
														brandCar, 
														modelCar, 
														color AS colorCar, 
														stateNumber, 
														photo 
												FROM 
													$table 
												INNER JOIN 
													Citys ON ($table.idCity=Citys.idCity) 
												INNER JOIN 
													ModelsCars ON ($table.idModelCar=ModelsCars.idModelCar) 
												INNER JOIN 
													BrandsCars ON ($table.idBrandCar=BrandsCars.idBrandCar) 
												INNER JOIN 
													Colors ON ($table.idColor=Colors.idColor)
												WHERE phone=$phone");
					echo $mysqli->error;
				}
				$user = db2Array($result);
				if (empty($user[0])) {
					$errors = "Такой номер не зарегистрирован".$mysqli->error;
				}else{
					$result = $mysqli->query("SELECT * FROM Orders WHERE $phoneField=$phone");
					$orders = db2Array($result);
					$info[] = $user;
					$info[] = $orders;
					echo json_encode($info);
				}
			}else{
				$errors = "Неправильный тип пользователя";
			}
		}
	}

	if (!empty($_GET['idCity'])) {
		$idCity = clearData($_GET['idCity'], $mysqli);
		$result = $mysqli->query("SELECT idUser AS idModerator, login, idCity FROM Users 
																				WHERE status='admin' AND 
																				(idCity=0 OR idCity=$idCity)");
		$moderators = db2Array($result);
		echo json_encode($moderators);
	}

	if ($errors){
		http_response_code(400);
		echo $error;	
	}
?>
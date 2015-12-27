<?php

	define("DB_HOST", "localhost");
	define("DB_LOGIN", "root");
	define("DB_PASSWORD", "password");
	define("DB_NAME", "GISTaxi");
	define ("ORDERS_LOG", "orders.log");

	$mysqli = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD);

	if (mysqli_connect_errno()) {
	    printf("Не удалось подключиться: %s\n", mysqli_connect_error());
	    exit();
	}

	$mysqli->select_db(DB_NAME) or die (mysqli_error());
	$mysqli->query("SET names 'utf8'") or die (mysqli_error());

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

	function sortArray($array, $column, $sortAscDesc){
		if (!empty($array)) {
			foreach ($array as $key => $row) {
			    $arraySort[$key]  = $row["$column"];
			}
			if ($sortAscDesc == "DESC") {
				$sortAscDesc = SORT_DESC;
			}else{
				$sortAscDesc = SORT_ASC;
			}
			array_multisort($arraySort, SORT_REGULAR, $sortAscDesc, $array);
			return $array;
		}
	}

	function sortResult($mysqli, $isPagination = false, $columnValues = array(), $result = array()){
		if (isset($_GET['orderBy'])){
			$orderBy = clearData($_GET['orderBy'], $mysqli);
			if ($orderBy != ""){
				list($column, $sortAscDesc) = explode(" ", $orderBy);
				foreach ($columnValues as $value) {
					if ($value == $column){
						$data['form']['orderBy']['sortAscDesc'] = $sortAscDesc;
						$data['form']['orderBy']['column'] = $column;
						if ($isPagination === false) {
							$data['result'] = sortArray($result, $column, $sortAscDesc);
						}else{
							//$sortAscDesc == "ASC" ? $sortAscDesc = "DESC" : $sortAscDesc = "ASC";
							$data['result'] = " ORDER BY ISNULL($column), $column $sortAscDesc ";
						}
						return $data;
					}
				}
			}
		}
		if ($isPagination === false) {
			$data['result'] = $result;
		}else{
			$data['result'] = "";
		}
		return $data;
	}

	








	function testReg($mysqli){
		if (isset($_POST['login'])){
		$login = clearData($_POST['login'], $mysqli);
			if ($login == ''){
				unset($login);
			}
		}

	    if (isset($_POST['password'])){
			$password=clearData($_POST['password'], $mysqli);
			if ($password ==''){
				unset($password);
			}
		}
	    
		if (empty($login) or empty($password)){
			return "Вы ввели не всю информацию!";
		}
		$password = md5($password);
		$result = $mysqli->query("SELECT * FROM Users WHERE login='$login' AND 
															password='$password'");
		
		$myrow = db2Array($result);
		if (!empty($myrow)){
			$_SESSION['id'] = $myrow[0]['id'];
			$_SESSION['login'] = $myrow[0]['login'];
			$_SESSION['name'] = $myrow[0]['firstName'].' '.$myrow[0]['lastName'];
			$_SESSION['idCity'] = $myrow[0]['idCity'];
			$_SESSION['status'] = $myrow[0]['status'];
		}else{
			return "Введенные Вами логин или пароль неверны!";
		}
	}

	function logOff(){
		session_unset();
		echo "<script>location.href = '/gistaxi/admin'</script>";
	}

	function aboutUsers($mysqli){
		if ($_SESSION['status'] === "superadmin") {
			$result = $mysqli->query("SELECT nameCity, idCity FROM Citys");
			$citys = db2Array($result);
			$data['citys'] = $citys;
		}
		if (!empty($_GET)) {
			if (isset($_GET['citySel'])){
			$citySel = clearData($_GET['citySel'], $mysqli);
				if ($citySel == ''){
					unset($citySel);
				}else{
					$data['form']['citySel'] = $citySel;
				}
			}
			if (isset($_GET['user'])){
			$user = clearData($_GET['user'], $mysqli);
				if ($user == ''){
					unset($user);
				}else{
					$data['form']['user'] = $user;
				}
			}
			if (isset($_GET['dateStart'])){
			$dateStart = clearData($_GET['dateStart'], $mysqli);
				if ($dateStart == ''){
					unset($dateStart);
				}else{
					$data['form']['dateStart'] = $dateStart;
				}
			}
			if (isset($_GET['dateFinish'])){
			$dateFinish = clearData($_GET['dateFinish'], $mysqli);
				if ($dateFinish == ''){
					unset($dateFinish);
				}else{
					$data['form']['dateFinish'] = $dateFinish;
				}
			}

			if ($citySel and $user and $dateStart and $dateFinish) {
				if ($_SESSION['status'] !== "superadmin" and $citySel != $_SESSION['idCity'] ) {
					$data['errors'] = "Нет доступа к указонному городу!";
					return $data;
				}elseif ($citySel == "all") {
					$cityQuery = "";
				}elseif (is_numeric($citySel)) {
					$cityQuery = " WHERE Citys.idCity=$citySel ";
				}else{
					$data['errors'] = "Неправильный тип поля ГОРОД!";
					return $data;
				}

				$paginationManager = new Krugozor_Pagination_Manager(3, 5, $_REQUEST);

				if ($user == "passenger") {
					$columnValues = array("nameCity", "phone", "regDate", "locationStatus", 
										 	"balanceBonuses", "name", "sex", "lockStatus", "revenue");

					$dataTemp = sortResult($mysqli, true, $columnValues);
					$sort = $dataTemp['result'];

					//добавление названия таблицы к полю сортировки для исключения неоднозначности поля
					$search  = array("locationStatus", "revenue");
					$replace = array("Passengers.locationStatus", "sum(price)");
					$sort = str_replace($search, $replace, $sort);

					$sql = "SELECT SQL_CALC_FOUND_ROWS 
								nameCity, phone, regDate, Passengers.locationStatus AS locationStatus, balanceBonuses, name, sex, 
								lockStatus, photo, sum(price) AS revenue
							FROM 
								Passengers
							INNER JOIN
								Citys ON (Passengers.idCity = Citys.idCity) 
							LEFT JOIN 
								Orders ON (Passengers.phone = Orders.phonePassenger) 
							$cityQuery 
							GROUP BY phone 
							HAVING DATE(regDate) BETWEEN '$dateStart' AND '$dateFinish' 
							$sort "
							/*LIMIT " . $paginationManager->getStartLimit() . "," . $paginationManager->getStopLimit()*/;

				}elseif ($user == "driver") {
					$columnValues = array("nameCity", "phone", "regDate", "locationStatus", "balance", "workingStatus",
										 	"brandCar", "modelCar", "color", "stateNumber", "lockStatus", "revenue");
					
					$dataTemp = sortResult($mysqli, true, $columnValues);
					$sort = $dataTemp['result'];

					//добавление названия таблицы к полю сортировки для исключения неоднозначности поля
					$search  = array("locationStatus", "revenue");
					$replace = array("Drivers.locationStatus", "sum(price)");
					$sort = str_replace($search, $replace, $sort);

					$sql = "SELECT SQL_CALC_FOUND_ROWS 
								nameCity, phone, regDate, Drivers.locationStatus AS locationStatus, balance, workingStatus, 
								brandCar, modelCar, color, stateNumber, lockStatus, photo, sum(price) AS revenue
							FROM 
								Citys 
							INNER JOIN 
								Drivers ON (Citys.idCity = Drivers.idCity) 
							INNER JOIN 
								ModelsCars ON (Drivers.idModelCar = ModelsCars.idModelCar) 
							INNER JOIN 
								BrandsCars ON (ModelsCars.idBrandCar = BrandsCars.idBrandCar) 
							INNER JOIN 
								Colors ON (Drivers.idColor = Colors.idColor) 
							LEFT JOIN 
								Orders ON (Drivers.phone = Orders.phoneDriver) 
							$cityQuery 
							GROUP BY phone 
							HAVING DATE(regDate) BETWEEN '$dateStart' AND '$dateFinish' 
							$sort "
							/*LIMIT " . $paginationManager->getStartLimit() . "," . $paginationManager->getStopLimit()*/;
				}else{
					$data['errors'] = "Неправильный тип пользователя!";
					return $data;
				}

				$result = $mysqli->query($sql);
				//var_dump($_GET);
				//echo $mysqli->error;
				//echo $sql;
				$users = db2Array($result);

				$result = $mysqli->query("SELECT FOUND_ROWS()");
				$numCity = db2Array($result);
				$paginationManager->setCount($numCity[0]['FOUND_ROWS()']);
				$data['paginationManager'] = $paginationManager;

				if (empty($users)) {
					$data['errors'] = "По данным параметрам запроса ничего не найдено!";
					return $data;
				}

				/*$sql = stristr($sql, "LIMIT", true);
				$result = $mysqli->query($)*/

				$usersLimit = array();
				$locationStatusCity = $lockStatusLock = $sumRevenue = 0;
				$workingStatusWork = $sumBalance = 0;
				$sexMen = $sumBalanceBonuses = 0;
				$countUsers = count($users);
				foreach ($users as $key => $user1) {
					//отбор пользователей при пагинации
					if ($key >= $paginationManager->getStartLimit() and 
							$key < $paginationManager->getStartLimit() + $paginationManager->getStopLimit()) {
						$usersLimit[] = $user1;
					}
					$sumRevenue += $user1['revenue'];
					$user1['locationStatus'] == "city" ? $locationStatusCity++ : false;
					$user1['lockStatus'] == 1 ? $lockStatusLock++ : false;
					if ($user == "driver") {
						$user1['workingStatus'] == "1" ? $workingStatusWork++ : false;
						$sumBalance += $user1['balance'];
					}else{
						$user1['sex'] == "men" ? $sexMen++ : false;
						$sumBalanceBonuses += $user1['balanceBonuses'];
					}
				}
			}else{
				$data['errors'] = "Не все параметры для поиска заданы!";
				 return $data;
			}
		}
		$data['usersStatus'] = $user;
		$data['users'] = $usersLimit;
		$data['form']['orderBy'] = $dataTemp['form']['orderBy'];
		$data['total'] = array('sumRevenue' => $sumRevenue,
								'countUsers' => $countUsers,
								'locationStatusCity' => $locationStatusCity,
								'lockStatusLock' => $lockStatusLock,
								'workingStatusWork' => $workingStatusWork,
								'sumBalance' => $sumBalance,
								'sexMen' => $sexMen,
								'sumBalanceBonuses' => $sumBalanceBonuses);
		return $data;
	}

	function search($mysqli){
		if (!empty($_GET)) {
			if (isset($_GET['typeSearch'])){
				$typeSearch = clearData($_GET['typeSearch'], $mysqli);
				if ($typeSearch == ''){
					unset($typeSearch);
				}else{
					$data['form']['typeSearch'] = $typeSearch;
				}
			}
			if (isset($_GET['stringSearch'])){
				$stringSearch = clearData($_GET['stringSearch'], $mysqli);
				if ($stringSearch == ''){
					unset($stringSearch);
				}else{
					$data['form']['stringSearch'] = $stringSearch;
				}
			}
			/*if (isset($_GET['typeSort'])){
				$typeSort = clearData($_GET['typeSort'], $mysqli);
				if ($typeSort == ''){
					unset($typeSort);
				}else{
					$data['form']['typeSort'] = $typeSort;
				}
			}
			if (isset($_GET['sortAscDesc'])){
				$sortAscDesc = clearData($_GET['sortAscDesc'], $mysqli);
				if ($sortAscDesc == ''){
					unset($sortAscDesc);
				}else{
					$data['form']['sortAscDesc'] = $sortAscDesc;
				}
			}*/
			if ($typeSearch and $stringSearch/* and $typeSort and $sortAscDesc*/) {
				
				$paginationManager = new Krugozor_Pagination_Manager(10, 5, $_REQUEST);

				if ($typeSearch == "phone") {
					$columnValues = array("nameCity", "phone", "regDate", "locationStatus", "typeUser", "lockStatus");
					
					$dataTemp = sortResult($mysqli, true, $columnValues);
					$sort = $dataTemp['result'];

					if ($_SESSION['status'] !== "superadmin") {
						$selConditionStringPassengers = " AND  Passengers.idCity = " . $_SESSION['idCity'];
						$selConditionStringDrivers = " AND  Drivers.idCity = " . $_SESSION['idCity'];
					}
					
					$sql = "(SELECT SQL_CALC_FOUND_ROWS 'Passengers' AS typeUser, 
												phone, regDate, nameCity, locationStatus, lockStatus, photo 
											FROM 
												Passengers 
											INNER JOIN 
												Citys ON Passengers.idCity = Citys.idCity 
											WHERE phone LIKE '%$stringSearch%'" . $selConditionStringPassengers . ")
										UNION ALL 
							(SELECT 'Drivers' AS typeUser, 
												phone, regDate, nameCity, locationStatus, lockStatus, photo 
											FROM 
												Drivers 
											INNER JOIN 
												Citys ON Drivers.idCity = Citys.idCity 
											WHERE phone LIKE '%$stringSearch%'" . $selConditionStringDrivers . ") 
											$sort 
											LIMIT " . $paginationManager->getStartLimit() . "," . $paginationManager->getStopLimit();

					$result = $mysqli->query($sql);

					//echo $sql;
					//echo $mysqli->error;
					$users = db2Array($result);

					$result = $mysqli->query("SELECT FOUND_ROWS()");
					$numUsers = db2Array($result);
					$paginationManager->setCount($numUsers[0]['FOUND_ROWS()']);

					if (empty($users)) {
						$data['errors'] = "По данному запросу ничего не найдено.";
					}else{
						$data['result'] = $users;
					}

				}elseif ($typeSearch == "order") {
					$columnValues = array("idOrder", "phonePassenger", "phoneDriver", "timeOrder", "pointStart", 
											"pointFinish1", "price", "statusOrder", "locationStatus");
					
					$dataTemp = sortResult($mysqli, true, $columnValues);
					$sort = $dataTemp['result'];

					if ($_SESSION['status'] !== "superadmin") {
						$selConditionStringOrder = " AND phonePassenger IN (SELECT phone FROM Passengers WHERE idCity=" . $_SESSION['idCity'] . ")";
					}
					$result = $mysqli->query("SELECT SQL_CALC_FOUND_ROWS * FROM Orders 
														WHERE idOrder 
														LIKE '%$stringSearch%'" . 
														$selConditionStringOrder . 
														$sort . 
														" LIMIT " . $paginationManager->getStartLimit() . "," . $paginationManager->getStopLimit());
					$orders = db2Array($result);

					$result = $mysqli->query("SELECT FOUND_ROWS()");
					$numOrders = db2Array($result);
					$paginationManager->setCount($numOrders[0]['FOUND_ROWS()']);

					if (empty($orders)) {
						$data['errors'] = "По данному запросу ничего не найдено!";
					}else{
						$data['result'] = $orders;
					}
				}else{
					$data['errors'] = "Неверный тип параметра поиска!";
				}
				//сортировка массива результатов
				//$data['result'] = sortArray($data['result'], $typeSort, $sortAscDesc);
				$data['form']['orderBy'] = $dataTemp['form']['orderBy'];
			}else{
				$data['errors'] = "Не все параметры для поиска заданы!";
			}
			$data['paginationManager'] = $paginationManager;
		}
		return $data;
	}

	function listCity($mysqli){
		$paginationManager = new Krugozor_Pagination_Manager(15, 5, $_REQUEST);

		$columnValues = array("idCity", "avgPrice", "nameCity", "active", "activeBonuses", "inviteBonus", 
								"acceptInviteBonus", "rideBonus", "login", "idModerator");			
		$dataTemp = sortResult($mysqli, true, $columnValues);
		$sort = $dataTemp['result'];
		$data['form']['orderBy'] = $dataTemp['form']['orderBy'];

		$result = $mysqli->query("SELECT SQL_CALC_FOUND_ROWS Citys.idCity, 
															avgPrice,
															nameCity, 
															active, 
															activeBonuses, 
															inviteBonus, 
															acceptInviteBonus, 
															rideBonus, 
															login, 
															idUser AS idModerator 
									FROM Citys 
										LEFT JOIN 
									Users ON Citys.idCity=Users.idCity 
									$sort 
									LIMIT " . $paginationManager->getStartLimit() . "," . $paginationManager->getStopLimit());
		//echo $mysqli->error;
		$data['citys'] = db2Array($result);
		$result = $mysqli->query("SELECT FOUND_ROWS()");
		$numCity = db2Array($result);
		$paginationManager->setCount($numCity[0]['FOUND_ROWS()']);
		$data['paginationManager'] = $paginationManager;
		return $data;
	}

	function editCity($mysqli){
		if (!empty($_GET['idCity'])) {
			$idCity = clearData($_GET['idCity'], $mysqli);

			$result = $mysqli->query("SELECT idUser AS idModerator, login, idCity FROM Users 
																				WHERE status='admin' AND 
																				(idCity=0 OR idCity=$idCity)");
			$moderators = db2Array($result);
			$data['moderators'] = $moderators;

			if (!empty($_POST)) {
				$nameCity = clearData($_POST['nameCity'], $mysqli);
				$active = clearData($_POST['active'], $mysqli);
				$idModerator = clearData($_POST['idModerator'], $mysqli);
				$avgPrice = clearData($_POST['avgPrice'], $mysqli);

				if ($nameCity == "" or $active == "" or $idModerator == "" or $avgPrice == "") {
					$data['result'] = "Не все поля заполнены!";
				}else{
					$result1 = $mysqli->query("UPDATE Citys SET nameCity='$nameCity', avgPrice=$avgPrice, active=$active 
													WHERE idCity=$idCity");
					$result2 = $mysqli->query("UPDATE Users SET idCity=0 
													WHERE idCity=$idCity");
					$result3 = $mysqli->query("UPDATE Users SET idCity=$idCity 
													WHERE idUser=$idModerator");
					if ($result1 and $result2 and $result3) {
						$data['result'] = "Успешно обновлено!";
					}else{
						$data['result'] = "Ошибка обновления!";
					}
				}
			}

			$result = $mysqli->query("SELECT Citys.idCity, avgPrice, nameCity, active, login 
										FROM Citys LEFT JOIN Users 
										ON Citys.idCity=Users.idCity 
										WHERE Citys.idCity=$idCity");
			$infoCity = db2Array($result);
			$data['infoCity'] = $infoCity[0];
		}

		return $data;
	}

	function moderators($mysqli){
		$paginationManager = new Krugozor_Pagination_Manager(10, 5, $_REQUEST);

		$columnValues = array("idCity", "email", "nameCity", "firstName", "lastName", "login", "idModerator");			
		$dataTemp = sortResult($mysqli, true, $columnValues);
		$sort = $dataTemp['result'];
		$data['form']['orderBy'] = $dataTemp['form']['orderBy'];

		$result = $mysqli->query("SELECT SQL_CALC_FOUND_ROWS idUser AS idModerator, 
															email, 
															firstName,
															lastName, 
															login,
															Users.idCity, 
															Citys.nameCity 
									FROM Users 
										LEFT JOIN 
									Citys ON Users.idCity = Citys.idCity 
									WHERE status = 'admin' 
									$sort 
									LIMIT " . $paginationManager->getStartLimit() . "," . $paginationManager->getStopLimit());
		echo $mysqli->error;
		$data['moderators'] = db2Array($result);
		$result = $mysqli->query("SELECT FOUND_ROWS()");
		$numModerator = db2Array($result);
		$paginationManager->setCount($numModerator[0]['FOUND_ROWS()']);
		$data['paginationManager'] = $paginationManager;
		return $data;

		/*if (!empty($_GET['idModerator'])) {
			$idModerator = clearData($_GET['idModerator'], $mysqli);

			if (!empty($_POST)) {
				$firstName = clearData($_POST['firstName'], $mysqli);
				$lastName = clearData($_POST['lastName'], $mysqli);
				$login = clearData($_POST['login'], $mysqli);
				$email = clearData($_POST['email'], $mysqli);
				$password = clearData($_POST['password'], $mysqli);
				$verPassword = clearData($_POST['verPassword'], $mysqli);

				if ($firstName == "" or $lastName == "" or $login == "" or $email == "") {
					$data['result'] = "Не все поля заполнены!";
				}else{
					if ($password == "" and $verPassword == "") {
						$newPass = "";
					}else{
						if ($password !== $verPassword) {
							$data['result'] = "Пароли не совпадают!";
						}else{
							$password = md5($password);
							$newPass = ", password='$password'";
						}
					}
				}
				if (empty($data['result'])) {
					$result = $mysqli->query("UPDATE Users SET email='$email', firstName='$firstName',
															lastName='$lastName', login='$login'
															$newPass
															WHERE idUser=$idModerator");
					if (!$result) {
						$data['result'] = "Ошибка обновления!";
						var_dump($mysqli);
					}else{
						$data['result'] = "Успешно обновлено!";
					}
				}
			}

			$result = $mysqli->query("SELECT email, firstName, lastName, login FROM Users
																				WHERE idUser=$idModerator");
			$userInfo = db2Array($result);
			$data['userInfo'] = $userInfo[0];
		}
		return $data;
		*/
	}

	function addCity($mysqli){
		if (!empty($_POST)) {
			$nameCity = clearData($_POST['nameCity'], $mysqli);
			if ($nameCity == "") {
				$data['result'] = "Введите название города!";
			}else{
				$result = $mysqli->query("SELECT idCity FROM Citys WHERE nameCity='$nameCity'");
				$city = db2Array($result);
				if (isset($city[0]['idCity'])) {
					$data['result'] = "Город с таким именем уже существует!";
				}else{
					$result = $mysqli->query("INSERT INTO Citys (nameCity) VALUES ('$nameCity')");
					if ($result) {
						$data['result'] = "Город успешно добавлен!";
					}else{
						$data['result'] = "Ошибка! Город не добавлен!";
					}
				}
			}
		}
		return $data;
	}

	function addModerator($mysqli){
		if (!empty($_POST)) {
			$firstName = clearData($_POST['firstName'], $mysqli);
			$lastName = clearData($_POST['lastName'], $mysqli);
			$login = clearData($_POST['login'], $mysqli);
			$email = clearData($_POST['email'], $mysqli);
			$password = clearData($_POST['password'], $mysqli);
			$verPassword = clearData($_POST['verPassword'], $mysqli);

			if ($firstName == "" or $lastName == "" or $login == "" or 
				$email == "" or $password == "" or $verPassword == "") {
				
				$data['result'] = "Не все поля заполнены!";
			}else{
				$result = $mysqli->query("SELECT idUser FROM Users WHERE login='$login'");
				$user = db2Array($result);
				if (isset($user[0]['idUser'])) {
					$data['result'] = "Данный логин уже занят! Используйте другой логин!";
				}else{
					if ($password !== $verPassword) {
						$data['result'] = "Пароли не совпадают!";
					}else{
						$password = md5($password);
						$result = $mysqli->query("INSERT INTO Users (status, firstName, lastName, login, email, password)
													VALUES ('admin', '$firstName', '$lastName', '$login', '$email', '$password')");
						if ($result) {
							$data['result'] = "Модератор успешно добавлен!";
						}else{
							$data['result'] = "Ошибка! Модератор не добавлен!";
						}
					}
				}
			}
		}
		return $data;
	}

	function users($mysqli){
		if (!empty($_GET['typeUser'])) {
			$typeUser = clearData($_GET['typeUser'], $mysqli);
			if ($typeUser == "") {
				$data['errors'] = "Укажите тип пользователя";
			}else{
				if ($typeUser != "Passengers" and $typeUser != "Drivers") {
					$data['errors'] = "Неправильный тип пользователя";
				}else{
					if ($_SESSION['status'] !== "superadmin") {
						$selConditionString = " WHERE idCity = " . $_SESSION['idCity'] . " ";
					}
					$data['form']['typeUser'] = $typeUser;
					if ($typeUser == "Passengers") {
						$idUserField = "idPassenger";
						$userField = "phonePassenger";
					}else{
						$idUserField = "idDriver";
						$userField = "phoneDriver";
					}
					$paginationManager = new Krugozor_Pagination_Manager(5, 5, $_REQUEST);
					$result = $mysqli->query("SELECT SQL_CALC_FOUND_ROWS $idUserField AS idUser, phone, regDate, lockStatus FROM $typeUser ". $selConditionString .
												"LIMIT " . $paginationManager->getStartLimit() . "," . $paginationManager->getStopLimit());
					$users = db2Array($result);

					$result = $mysqli->query("SELECT FOUND_ROWS()");
					$numCity = db2Array($result);

					$paginationManager->setCount($numCity[0]['FOUND_ROWS()']);
					$data['paginationManager'] = $paginationManager;

					$tempUsers = array();
					foreach ($users as $user) {
						$phone = $user['phone'];
						$result = $mysqli->query("SELECT idOrder, statusOrder FROM Orders WHERE $userField=$phone");
						$orders = db2Array($result);
						$user["amountOrders"] = count($orders);
						$user["amountRejection"] = 0;
						foreach ($orders as $order) {
							if ($order['statusOrder'] == "rejection") {
								++$user['amountRejection'];
							}
						}
						$tempUsers[] = $user;
					}
					$data['result'] = $tempUsers;
				}
			}
			return $data;
		}
	}

	function userInfo($mysqli){
		$phone = clearData($_GET['phone'], $mysqli);
		$table = clearData($_GET['typeUser'], $mysqli);

		if ($phone == "" or $table == "") {
			$data['errors'] = "Не удалось получить информацию! Пустые параметры!";
		}else{
			if ($table == "Passengers" or $table == "Drivers") {
				$table == "Passengers" ? $phoneField = "phonePassenger" : $phoneField = "phoneDriver";
				
				if ($_SESSION['status'] == "superadmin") {
					$selConditionString = "";
				}elseif ($_SESSION['status'] == "admin") {
					$selConditionString = " and $table.idCity = " . $_SESSION['idCity'] . " ";
				}

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
												WHERE phone=$phone". $selConditionString);
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
												WHERE phone=$phone". $selConditionString);
					echo $mysqli->error;
				}
				$user = db2Array($result);
				if (empty($user[0])) {
					$data['errors'] = "Такой номер не зарегистрирован или у Вас недостаточно прав!".$mysqli->error;
				}else{
					$result = $mysqli->query("SELECT * FROM Orders WHERE $phoneField=$phone");
					$orders = db2Array($result);
					$data['user'] = $user[0];
					$data['orders'] = $orders;
					$data['typeUser'] = $table;
				}
			}else{
				$data['errors'] = "Неправильный тип пользователя";
			}
			return $data;
		}
	}

	function reviews($mysqli){
		if ($_SESSION['status'] == 'superadmin') {
			$selConditionString = "";
		}elseif ($_SESSION['status'] == 'admin') {
			$selConditionString = " WHERE Passengers.idCity = " . $_SESSION['idCity'] . " ";
		}
		$paginationManager = new Krugozor_Pagination_Manager(15, 5, $_REQUEST);

		$result = $mysqli->query("SELECT SQL_CALC_FOUND_ROWS idReview,  
										textReview, 
										timeReview, 
										rating, 
										status, 
										Drivers.phone AS phoneDriver, 
										Passengers.phone AS phonePassenger 
									FROM Reviews 
									INNER JOIN 
										Drivers ON Drivers.idDriver = Reviews.idDriver 
									INNER JOIN
										Passengers ON Passengers.idPassenger = Reviews.idPassenger 
									" . $selConditionString . "ORDER BY status DESC 
									LIMIT " . $paginationManager->getStartLimit() . "," . $paginationManager->getStopLimit());
		if ($result) {
			$data['reviews'] = db2Array($result);
			$result = $mysqli->query("SELECT FOUND_ROWS()");
			
			$numCity = db2Array($result);
			$paginationManager->setCount($numCity[0]['FOUND_ROWS()']);
			$data['paginationManager'] = $paginationManager;
		}else{
			$data['errors'] = $mysqli->error;
		}
		
		return $data;
	}
?>
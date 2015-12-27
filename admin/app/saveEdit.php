<?php
	session_start();
	require "model.php";

	header('Content-Type: application/json; charset=utf-8');
	if (!empty($_POST) and $_SESSION['login']) {
		$column = clearData($_POST['column'], $mysqli);
		$editVal = clearData($_POST['editVal'], $mysqli);
		$id = clearData($_POST['id'], $mysqli);
		$tableName = clearData($_POST['tableName'], $mysqli);
		// var_export($column);
		// var_export($editVal);
		// var_export($id);

		if ($column == "" or $editVal == "" or $id == "") {
			$error = "Не удалось изменить! Пустые параметры!";
		}else{
			if ($column == "lockStatus") {
				if ($tableName == "") {
					$error = "Не удалось изменить! Пустые параметры!";
				}else{
					$tableName == "Passengers" ? $field = "idPassenger" : $field = "idDriver";
					$result = $mysqli->query("UPDATE $tableName SET $column=$editVal WHERE $field=$id");
				}
			}elseif ($column == "status" or $column == "textReview") {
				$result = $mysqli->query("UPDATE Reviews SET $column='$editVal' WHERE idReview=$id");

			}elseif ($tableName == "moderators") {
					$result = $mysqli->query("UPDATE Users SET $column='$editVal' WHERE idUser=$id");

			}elseif ($column == "moderator") {
				$result = $mysqli->query("UPDATE Users SET idCity=0 WHERE idCity=$id");
				$result = $mysqli->query("UPDATE Users SET idCity=$id WHERE login='$editVal'");

			}else{
				$result = $mysqli->query("UPDATE Citys SET $column='$editVal' WHERE idCity=$id");
			}
			if (!$result) {
				$error = "Не удалось изменить! Ошибка выполнения запроса! " . $mysqli->error;
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

	if (!empty($_GET['idModerator'])) {
		$idModerator = clearData($_GET['idModerator'], $mysqli);
		$result = $mysqli->query("SELECT idCity, nameCity FROM Citys WHERE idCity NOT IN 
								(SELECT idCity 			  FROM Users WHERE idCity > 0 AND idUser <> '$idModerator')");
		$citys = db2Array($result);
		echo json_encode($citys);
	}

	if ($error){
		http_response_code(400);
		echo $error;	
	}
?>
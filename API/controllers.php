<?php
	function actionRegistration($mysqli){
		return registration($mysqli);	 
	}

	function actionActivation($mysqli){
		return activation($mysqli);
	}

	function actionLogOff($mysqli){
		return logOff($mysqli);
	}

	function actionGetInfo($mysqli){
		return getInfo($mysqli);
	}

	function actionGetPhoto($mysqli){
		return getPhoto($mysqli);
	}

	function actionEditInfo($mysqli){
		return editInfo($mysqli);
	}

	function actionEditPhoto($mysqli){
		return editPhoto($mysqli);
	}

	function actionAddOrder($mysqli){
		return addOrder($mysqli);
	}

	function actionGetAutos($mysqli){
		return getAutos($mysqli);
	}

	function actionGetColors($mysqli){
		return getColors($mysqli);
	}

	function actionGetCitys($mysqli){
		return getCitys($mysqli);
	}

	function actionGetExpectOrders($mysqli){
		return getExpectOrders($mysqli);
	}

	function actionSetLocationStatus($mysqli){
		return setLocationStatus($mysqli);
	}

	function actionSetWorkingStatus($mysqli){
		return setWorkingStatus($mysqli);
	}

	function actionAddReview($mysqli){
		return addReview($mysqli);
	}
?>
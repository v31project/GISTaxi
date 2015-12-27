<?php
	function start_action($mysqli, $option = "getIn"){
		//var_dump($_SERVER);
		if ($option == "authentication") {
			$data = testReg($mysqli);
		}
		require "templates/start.php";
	}

	function exit_action(){
		logOff();
	}

	function aboutUsers_action($mysqli){
		$data = aboutUsers($mysqli);
		require "templates/aboutUsers.php";
	}

	function users_action($mysqli){
		$data = users($mysqli);
		require "templates/users.php";
	}

	function userInfo_action($mysqli){
		$data = userInfo($mysqli);
		require "templates/userInfo.php";	
	}

	function search_action($mysqli){
		$data = search($mysqli);
		require "templates/search.php";
	}

	function listCity_action($mysqli){
		$data = listCity($mysqli);
		require "templates/listCity.php";
	}

	function editCity_action($mysqli){
		$data = editCity($mysqli);
		require "templates/editCity.php";
	}

	function moderators_action($mysqli){
		$data = moderators($mysqli);
		require "templates/moderators.php";
	}

	function addCity_action($mysqli){
		$data = addCity($mysqli);
		require "templates/addCity.php";
	}

	function addModerator_action($mysqli){
		$data = addModerator($mysqli);
		require "templates/addModerator.php";
	}

	function reviews_action($mysqli){
		$data = reviews($mysqli);
		require "templates/reviews.php";
	}
?>
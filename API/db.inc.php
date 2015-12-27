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
?>
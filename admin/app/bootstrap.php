<?php
	require_once "model.php";
	require_once "controllers.php";
	require_once "../vendor/symfony/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php";
	require_once "Pagination/Manager.php";
	require_once "Pagination/Helper.php";

	$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
	$loader->registerNamespaces(array('Symfony'=>__DIR__.'/../../vendor/symfony/symfony/src'));

	$loader->register();
?>
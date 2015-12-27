<?php
	require_once "db.inc.php";
	require_once "lib.inc.php";
	require_once "controllers.php";
	require_once "../vendor/symfony/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php";

	$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
	$loader->registerNamespaces(array('Symfony'=>__DIR__.'/../vendor/symfony/symfony/src'));

	$loader->register();
?>
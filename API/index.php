<?php
	require_once "bootstrap.php";

	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	$request = Request::createFromGlobals();
	$response = new Response();

	$uri = $request->getPathInfo();

	if ($uri == "/user") {
		switch ($request->getMethod()) {
			case 'POST':
				$data = actionRegistration($mysqli);
				break;

			case 'PUT':
				$data = actionActivation($mysqli);
				break;

			case 'DELETE':
				$data = actionLogOff($mysqli);
				break;

			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/user/info") {
		switch ($request->getMethod()) {
			case 'GET':
				$data = actionGetInfo($mysqli);
				break;

			case 'PUT':
				$data = actionEditInfo($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/user/photo") {
		switch ($request->getMethod()) {
			case 'GET':
				$data = actionGetPhoto($mysqli);
				break;

			case 'PUT':
				$data = actionEditPhoto($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/user/info/locationStatus") {
		switch ($request->getMethod()) {
			case 'PUT':
				$data = actionSetLocationStatus($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/user/info/workingStatus") {
		switch ($request->getMethod()) {
			case 'PUT':
				$data = actionSetWorkingStatus($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/review") {
		switch ($request->getMethod()) {
			case 'POST':
				$data = actionAddReview($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/order") {
		switch ($request->getMethod()) {
			case 'POST':
				$data = actionAddOrder($mysqli);
				break;

			case 'GET':
				$data = actionGetExpectOrders($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/autos") {
		switch ($request->getMethod()) {
			case 'GET':
				$data = actionGetAutos($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/colors") {
		switch ($request->getMethod()) {
			case 'GET':
				$data = actionGetColors($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}elseif ($uri == "/citys") {
		switch ($request->getMethod()) {
			case 'GET':
				$data = actionGetCitys($mysqli);
				break;
			
			default:
				$data['code'] = 404;
				break;
		}
	}else{
		$data['code'] = 404;
	}
	
	$response->setStatusCode($data['code']);
	$response->setContent($data['content']);
//	$response->headers->set('Access-Control-Allow-Origin', '*');
	$response->send();

	//var_dump(readStream());
	//var_dump($_GET);
	//echo $_SERVER['REQUEST_METHOD'];
	
?>
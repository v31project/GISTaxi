<?php
	session_start();
	require_once "app/bootstrap.php";

	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	$request = Request::createFromGlobals();
	$response = new Response();

	$uri = $request->getPathInfo();
	if ($uri == "/" and !$_POST['login'] and !$_POST['password']) {
		$response->setContent(start_action($mysqli));
	}elseif ($uri == "/" and ($_POST['login'] or $_POST['password'])) {
		$response->setContent(start_action($mysqli, "authentication"));
	}elseif ($uri == "/exit") {
		$response->setContent(exit_action());//
	}elseif ($uri == "/aboutUsers" and $_SESSION['login']) {
		$response->setContent(aboutUsers_action($mysqli));
	}elseif ($uri == "/users" and $_SESSION['login']) {
		$response->setContent(users_action($mysqli));
	}elseif ($uri == "/userInfo" and $_SESSION['login']) {
		$response->setContent(userInfo_action($mysqli));
	}elseif ($uri == "/search" and $_SESSION['login']) {
		$response->setContent(search_action($mysqli));
	}elseif ($uri == "/reviews" and $_SESSION['login']) {
		$response->setContent(reviews_action($mysqli));
	}elseif ($uri == "/listCity" and $_SESSION['status'] == "superadmin") {
		$response->setContent(listCity_action($mysqli));
	}elseif ($uri == "/listCity/editCity" and $_SESSION['status'] == "superadmin") {
		$response->setContent(editCity_action($mysqli));
	}elseif ($uri == "/listCity/addCity" and $_SESSION['status'] == "superadmin") {
		$response->setContent(addCity_action($mysqli));
	}elseif ($uri == "/moderators/addModerator"  and $_SESSION['status'] == "superadmin") {
		$response->setContent(addModerator_action($mysqli));
	}elseif ($uri == "/moderators" and $_SESSION['status'] == "superadmin") {
		$response->setContent(moderators_action($mysqli));
	}else{
		$html = "<html>
					<head>
						<title>Страница не найдена!</title>
					</head>
					<body>
						<h1>Page Not Found</h1>
						<a href=\"/gistaxi/admin\">На главную</a>
					</body>
				</html>";
		$response->setContent($html);
		$response->setStatusCode(404);
	}

	$response->send();
?>
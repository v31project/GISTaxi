<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' href='/gistaxi/admin/css/bootstrap.min.css' type='text/css' media='all'>
	<link rel='stylesheet' href='/gistaxi/admin/css/style.css' type='text/css' media='all'>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<title><?php echo $title; ?></title>
</head>
<body<?php echo $onload; ?>>
	<?php if ($contentNotAuth) {
			echo $contentNotAuth;
		}else{ 
			if ($notGetMenu !== true) {
				list($uri, ) = explode("index.php", $_SERVER['REQUEST_URI']);
				$uri .= "index.php";
				?>
				<div class="my-container">
					<div class="header">
						<div class="row my-container">
							<div class="col-md-3"><h1>Добро пожаловать</h1></div>
							<div class="col-md-3 col-md-offset-5"><p class="text-name-user text-right">Вы вошли на сайт как <?= $_SESSION['name']; ?>!</p></div>
							<div class="col-md-1 text-right"><a class="btn btn-danger" href="<?= $uri; ?>/exit">Выйти</a></div>
						</div>
						<div>
							<nav class="navbar navbar-default" role="navigation">
							  <div class="container-fluid">
							    <!-- Brand and toggle get grouped for better mobile display -->
							    <div class="navbar-header">
							      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
							            <span class="sr-only">Toggle navigation</span>
							            <span class="icon-bar"></span>
							            <span class="icon-bar"></span>
							            <span class="icon-bar"></span>
							          </button>
							      <a class="navbar-brand">Меню</a>
							    </div>

							    <!-- Collect the nav links, forms, and other content for toggling -->
							    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
							      <ul class="nav navbar-nav">
							        <li<?php if ($_SERVER['PHP_SELF'] == $uri) echo " class=\"active\""; ?>><a href="<?= $uri; ?>">Главная</a></li>
									<li<?php if ($_SERVER['PHP_SELF'] == $uri . "/aboutUsers") echo " class=\"active\""; ?>><a href="<?= $uri; ?>/aboutUsers">Информация о пользователях</a></li>
									<li<?php if ($_SERVER['PHP_SELF'] == $uri . "/search") echo " class=\"active\""; ?>><a href="<?= $uri; ?>/search">Найти</a></li>
									<?php if ($_SESSION['status'] === "superadmin"){ ?><li<?php if ($_SERVER['PHP_SELF'] == $uri . "/listCity") echo " class=\"active\""; ?>><a href="<?= $uri; ?>/listCity">Список городов</a></li><?php } ?>
									<li<?php if ($_SERVER['PHP_SELF'] == $uri . "/users") echo " class=\"active\""; ?>><a href="<?= $uri; ?>/users">Пользователи</a></li>
									<li<?php if ($_SERVER['PHP_SELF'] == $uri . "/reviews") echo " class=\"active\""; ?>><a href="<?= $uri; ?>/reviews">Отзывы</a></li>
									<?php if ($_SESSION['status'] === "superadmin"){ ?><li<?php if ($_SERVER['PHP_SELF'] == $uri . "/moderators") echo " class=\"active\""; ?>><a href="<?= $uri; ?>/moderators">Модераторы</a></li><?php } ?>
							      </ul>
							    </div><!-- /.navbar-collapse -->
							  </div><!-- /.container-fluid -->
							</nav>
						</div>
					</div>
			<?php }
			echo $content;
			//var_dump($_SERVER);
		} ?>
				</div>
				<script type="text/javascript" src="/gistaxi/admin/scripts/bootstrap.min.js"></script>
</body>
</html>
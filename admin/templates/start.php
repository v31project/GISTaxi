<?php $title = "Авторизация"; ?>

<?php ob_start(); ?>

<?php if ($_SESSION['login']) {?>
	
<?php $content = ob_get_clean();
}else{ ?>
	<div class="container">
		<div class="row">
			<h1 class="text-center">Войдите</h1>
			<div class="col-md-2  col-md-offset-5">
				<form class="form-horizontal" name="authentication" action="index.php" method="post">
					<div class="form-group">
						<input class="form-control" name="login" type="text" placeholder="login">
					</div>
					<div class="form-group">
						<input class="form-control" name="password" type="password" placeholder="password">
					</div>
					<div class="form-group">
						<button class="btn btn-primary btn-block" type="submit">Войти</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php if ($data) {
		echo "<p class=\"text-center text-danger\">" . $data . "</p>";
	} ?>

<?php $contentNotAuth = ob_get_clean();} ?>

<?php include "layout.php" ?>
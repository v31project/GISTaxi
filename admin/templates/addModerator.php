<?php 
	$title = "Добавление модератора";
	$notGetMenu = true;
 ?>

<?php ob_start(); ?>
	<div>
		<h1>Информация о модераторе</h1>
	</div>
	<div>
		<form name="userInfo" action="" method="post">
			<input type="text" name="firstName" placeholder="Имя" value="<?= $_POST['firstName']; ?>">
			<input type="text" name="lastName" placeholder="Фамилия" value="<?= $_POST['lastName']; ?>">
			<input type="text" name="login" placeholder="Логин" value="<?= $_POST['login']; ?>">
			<input type="text" name="email" placeholder="Email" value="<?= $_POST['email']; ?>">
			<input type="password" name="password" placeholder="Пароль">
			<input type="password" name="verPassword" placeholder="Подтвердите пароль">
			<input type="submit" value="Добавить">
		</form>
		
		<button type="button" onclick="window.close()">Закрыть</button>

		<p><?= $data['result']; ?></p>
	</div>
<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
<?php 
	$title = "Добавление города";
	$notGetMenu = true;
 ?>

<?php ob_start(); ?>
	<div>
		<h1>Информация о городе</h1>
	</div>
	<div>
		<form name="infoCity" action="" method="post">
			<input type="text" name="nameCity" placeholder="Название города" value="<?= $_POST['nameCity']; ?>">
			<input type="submit" value="Добавить">
		</form>
		<button type="button" onclick="window.close()">Закрыть</button>
		<p><?= $data['result']; ?></p>
	</div>
<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
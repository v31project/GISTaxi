<?php $title = "Показать"; ?>

<?php ob_start(); ?>
	<h1>Показать</h1>
	<p>Здесь будут показаны пользователи</p>
<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
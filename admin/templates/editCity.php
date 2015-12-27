<?php $title = "Изменение города"; ?>

<?php ob_start(); ?>
	
	<div>
		<h1>Изменение информации о городе</h1>
	</div>
	<div>
		<form name="editCity" action="" method="post">
			<input type="text" name="nameCity" placeholder="Имя города" value="<?= $data['infoCity']['nameCity']; ?>">
			<select name="active">
				<option disabled <?php if (!$data['infoCity']['active']) echo " selected"; ?>>Выберите статус</option>
				<option value="1" <?php if ($data['infoCity']['active']=='1') echo " selected"; ?>>Активен</option>
				<option value="0" <?php if ($data['infoCity']['active']=='0') echo " selected"; ?>>Неактивен</option>
			</select>
			<select name="idModerator">
				<option disabled selected>Выберите модератора</option>
				<option value="0">нет модератора</option>
				<?php
					foreach ($data['moderators'] as $moderator) {
					$login = $moderator['login'];
					$idModerator = $moderator['idModerator'];
					$sel = ($login == $data['infoCity']['login']) ? "selected" : "";
					echo "<option value=\"$idModerator\" ".$sel.">$login</option>";
				} ?>
			</select>
			<input type="text" name="avgPrice" placeholder="Средняя цена" value="<?= $data['infoCity']['avgPrice']; ?>">
			<input type="submit" value="Сохранить">
		</form>
		<button name="close" onclick="window.close();">Закрыть</button>
		<p><?= $data['result']; ?></p>
	</div>

<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
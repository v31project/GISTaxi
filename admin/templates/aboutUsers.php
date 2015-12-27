<?php $title = "Информация о пользователях"; ?>

<?php ob_start(); ?>

	<div class="row">
		<div class="col-md-10">
			<form class="form-inline" role="search" name="searchUser" action="aboutUsers" method="get">
				<?php if ($_SESSION['status'] == "superadmin") { ?>
				<select class="form-control" name="citySel">
					<option disabled <?php if (!$data['form']['citySel']) echo " selected"; ?>>Выберите город</option>
					<option value="all">Все города</option>
					<?php
						foreach ($data['citys'] as $city) {
							$nameCity = $city['nameCity'];
							$idCity = $city['idCity'];
							$sel = ($idCity == $data['form']['citySel']) ? " selected=\"selected\"" : "";
							echo "<option value=\"$idCity\"".$sel.">$nameCity</option>";
						}
					?>
				</select>
				<?php }else{
					echo "<input name=\"citySel\" type=\"hidden\" value=\"" . $_SESSION['idCity'] . "\">";
				} ?>

				<select class="form-control" name="user">
					<option disabled<?php if (!$data['form']['user']) echo " selected"; ?>>Выберите пользователя</option>
					<option value="passenger"<?php if ($data['form']['user']=='passenger') echo " selected"; ?>>Пассажир</option>
					<option value="driver"<?php if ($data['form']['user']=='driver') echo " selected"; ?>>Водитель</option>
				</select>

				<?php
					empty($data['form']['dateStart']) ? $dateStart = date("Y-m-d", time()-(30*24*60*60)) : $dateStart =  $data['form']['dateStart'];
					empty($data['form']['dateFinish']) ? $dateFinish = date("Y-m-d") : $dateFinish = $data['form']['dateFinish'];
				?>
				<input class="form-control" name="dateStart" type="date" value="<?= $dateStart; ?>" placeholder="С даты">
				<input class="form-control" name="dateFinish" type="date" value="<?= $dateFinish; ?>" placeholder="По дату">
				<button class="btn btn-success" type="submit">Найти</button>
		
	<?php /*var_dump($data['usersStatus']);*/ if ($data['errors']) {?>
				
				<span class="help-inline text-danger"><?= $data['errors']; ?></span>
			</form>
		</div>
	</div>

	<?php }elseif ($data['usersStatus'] == "driver" or $data['usersStatus'] == "passenger"){ ?>
	
			</form>
		</div>
	</div>
	<div class="row">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-hover table-condensed">

	<?php 	
		//var_dump($data);
		list($thisPagePath, ) = explode("&orderBy=", $_SERVER['REQUEST_URI']);

		function getAscDesc($column, $data){
			if ($data['form']['orderBy']['column'] == $column) {
				if ($data['form']['orderBy']['sortAscDesc'] == "ASC") {
					return "DESC";
				}
			}
			return "ASC";
		}

		if ($data['usersStatus'] == "driver") { 
	?>
			<tr>
				<th><a href="<?= $thisPagePath . "&orderBy=phone+" . getAscDesc("phone", $data); ?>">Телефон</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=regDate+" . getAscDesc("regDate", $data); ?>">Дата регистарции</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=nameCity+" . getAscDesc("nameCity", $data); ?>">Город</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=locationStatus+" . getAscDesc("locationStatus", $data); ?>">Статус локации</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=balance+" . getAscDesc("balance", $data); ?>">Баланс</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=workingStatus+" . getAscDesc("workingStatus", $data); ?>">Статус занятости</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=brandCar+" . getAscDesc("brandCar", $data); ?>">Марка авто</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=modelCar+" . getAscDesc("modelCar", $data); ?>">Модель авто</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=color+" . getAscDesc("color", $data); ?>">Цвет авто</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=stateNumber+" . getAscDesc("stateNumber", $data); ?>">Госномер авто</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=lockStatus+" . getAscDesc("lockStatus", $data); ?>">Статус блокировки</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=revenue+" . getAscDesc("revenue", $data); ?>">Заработано</a></th>
				<th>Фото</th>
			</tr>
			<?php foreach ($data['users'] as $user) { ?>
			<tr>
				<td class="col-md-1"><?= $user['phone']; ?></td>
				<td class="col-md-1"><?= $user['regDate']; ?></td>
				<td class="col-md-1"><?= $user['nameCity']; ?></td>
				<td class="col-md-1"><?= $user['locationStatus']; ?></td>
				<td class="col-md-1"><?= $user['balance']; ?></td>
				<td class="col-md-1"><?= $user['workingStatus']; ?></td>
				<td class="col-md-1"><?= $user['brandCar']; ?></td>
				<td class="col-md-1"><?= $user['modelCar']; ?></td>
				<td class="col-md-1"><?= $user['color']; ?></td>
				<td class="col-md-1"><?= $user['stateNumber']; ?></td>
				<td class="col-md-1"><?= $user['lockStatus']; ?></td>
				<td class="col-md-1"><?= $user['revenue']; ?></td>
				<td class="col-md-1"><img class="photo" src="../<?= $user['photo']; ?>"></td>
			</tr>
			<?php } ?>
			<tr>
				<td>Найдено: <?= $data['total']['countUsers']; ?></td>
				<td></td>
				<td></td>
				<td>Город: <?= $data['total']['locationStatusCity']; ?><br/>Межгород: <?= $data['total']['countUsers'] - $data['total']['locationStatusCity']; ?></td>
				<td>Среднее: <?= intval($data['total']['sumBalance'] / $data['total']['countUsers']); ?><br/>Сумма: <?= $data['total']['sumBalance']; ?></td>
				<td>Занято: <?= $data['total']['workingStatusWork']; ?><br/>Свободно: <?= $data['total']['countUsers'] - $data['total']['workingStatusWork']; ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>Блокипрванных: <?= $data['total']['lockStatusLock']; ?><br/>Не блокировано: <?= $data['total']['countUsers'] - $data['total']['lockStatusLock']; ?></td>
				<td>Среднее: <?= intval($data['total']['sumRevenue'] / $data['total']['countUsers']); ?><br/>Сумма: <?= $data['total']['sumRevenue']; ?></td>
				<td></td>
			</tr>

	<?php }elseif ($data['usersStatus'] == "passenger") { ?>

			<tr>
				<th><a href="<?= $thisPagePath . "&orderBy=phone+" . getAscDesc("phone", $data); ?>">Телефон</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=regDate+" . getAscDesc("regDate", $data); ?>">Дата регистарции</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=name+" . getAscDesc("name", $data); ?>">Имя</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=sex+" . getAscDesc("sex", $data); ?>">Пол</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=nameCity+" . getAscDesc("nameCity", $data); ?>">Город</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=locationStatus+" . getAscDesc("locationStatus", $data); ?>">Статус локации</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=balanceBonuses+" . getAscDesc("balanceBonuses", $data); ?>">Баланс бонусов</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=lockStatus+" . getAscDesc("lockStatus", $data); ?>">Статус блокировки</a></th>
				<th><a href="<?= $thisPagePath . "&orderBy=revenue+" . getAscDesc("revenue", $data); ?>">Потрачено</a></th>
				<th>Фото</th>
			</tr>
			<?php foreach ($data['users'] as $user) { ?>
			<tr>
				<td class="col-md-1"><?= $user['phone']; ?></td>
				<td class="col-md-1"><?= $user['regDate']; ?></td>
				<td class="col-md-1"><?= $user['name']; ?></td>
				<td class="col-md-1"><?= $user['sex']; ?></td>
				<td class="col-md-1"><?= $user['nameCity']; ?></td>
				<td class="col-md-1"><?= $user['locationStatus']; ?></td>
				<td class="col-md-1"><?= $user['balanceBonuses']; ?></td>
				<td class="col-md-1"><?= $user['lockStatus']; ?></td>
				<td class="col-md-1"><?= $user['revenue']; ?></td>
				<td class="col-md-1"><img class="photo" src="../<?= $user['photo']; ?>"></td>
			</tr>
			<?php } ?>
			<tr>
				<td>Найдено: <?= $data['total']['countUsers']; ?></td>
				<td></td>
				<td></td>
				<td>Мужчин: <?= $data['total']['sexMen']; ?><br/>Женщин: <?= $data['total']['countUsers'] - $data['total']['sexMen']; ?></td>
				<td></td>
				<td>Город: <?= $data['total']['locationStatusCity']; ?><br/>Межгород: <?= $data['total']['countUsers'] - $data['total']['locationStatusCity']; ?></td>
				<td>Среднее: <?= intval($data['total']['sumBalanceBonuses'] / $data['total']['countUsers']); ?><br/>Сумма: <?= $data['total']['sumBalanceBonuses']; ?></td>
				<td>Блокипрванных: <?= $data['total']['lockStatusLock']; ?><br/>Не блокировано: <?= $data['total']['countUsers'] - $data['total']['lockStatusLock']; ?></td>
				<td>Среднее: <?= intval($data['total']['sumRevenue'] / $data['total']['countUsers']); ?><br/>Сумма: <?= $data['total']['sumRevenue']; ?></td>
				<td></td>
			</tr>
	<?php }
	$paginationHelper = new Krugozor_Pagination_Helper($data['paginationManager']);
	//var_dump($paginationHelper);
    // Настройка внешнего вида пагинатора
                       // Хотим получить стандартный вид пагинации
    $paginationHelper->setPaginationType(Krugozor_Pagination_Helper::PAGINATION_NORMAL_TYPE)
                       // Устанавливаем CSS-класс каждого элемента <a> в интерфейсе пагинатора
                     ->setCssNormalLinkClass("normal_link")
                       // Устанавливаем CSS-класс элемента <span> в интерфейсе пагинатора,
                       // страница которого открыта в текущий момент.
                     ->setCssActiveLinkClass("active")
                       // Параметр для query string гиперссылки
                     ->setRequestUriParameter("param_1", "value_1")
                       // Параметр для query string гиперссылки
                     ->setRequestUriParameter("param_2", "value_2")
                       // Устанавливаем идентификатор фрагмента гиперссылок пагинатора
                     ->setFragmentIdentifier("result1");
    ?>
	    	</table>
		</div>
	</div>

    <div class="text-center">
        <?php if ($paginationHelper->getPagination()->getCount()): ?>
            <?=$paginationHelper->getHtml("citySel=" . $data['form']['citySel'] . "&user=" . $data['form']['user'] . "&dateStart=" . $data['form']['dateStart'] . "&dateFinish=" . $data['form']['dateFinish'] . "&orderBy=" . $data['form']['orderBy']['column'] . "+" . $data['form']['orderBy']['sortAscDesc'] . "&")?>
        <?php endif; ?>
    </div>
	<?php } ?>

<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
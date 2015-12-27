<?php 
	$title = "Поиск";
?>

<?php ob_start(); ?>
	<div>
		<form name="src" action="search" method="get">
			<p>Поиск по:</p>
			<p><input type="radio" name="typeSearch" value="phone"<?php if ($data['form']['typeSearch']=='phone' or $data['form']['typeSearch']!='order') echo " checked"; ?>>номеру телефона</p>
			<p><input type="radio" name="typeSearch" value="order"<?php if ($data['form']['typeSearch']=='order') echo " checked"; ?>>номеру заказа</p>
			<!-- <p>Сортировать по:
			<select id="selTypeSort" name="typeSort"></select>
			<select name="sortAscDesc">
				<option value="ASC"<?php if ($data['form']['sortAscDesc']=='ASC') echo " selected"; ?>>по возрастанию</option>
				<option value="DESC"<?php if ($data['form']['sortAscDesc']=='DESC') echo " selected"; ?>>по убыванию</option>
			</select>
			</p> -->
			<input type="text" name="stringSearch" placeholder="Введите искомое значение"<?php if ($data['form']['stringSearch']) echo " value=\"".$data['form']['stringSearch']."\""; ?>>
			<input type="submit" value="Найти">
		</form>
	</div>

	<?php /*var_dump($data['paginationManager']);*/
	if ($data['errors']) {?>
	<p><?= $data['errors']; ?></p>
	<?php }elseif($data){
		list($thisPagePath, ) = explode("&orderBy=", $_SERVER['REQUEST_URI']);

		function getAscDesc($column, $data){
			if ($data['form']['orderBy']['column'] == $column) {
				if ($data['form']['orderBy']['sortAscDesc'] == "ASC") {
					return "DESC";
				}
			}
			return "ASC";
		}

		if ($data['form']['typeSearch'] == "phone") {?>
			<div>
				<table>
					<tr>
						<th><a href="<?= $thisPagePath . "&orderBy=typeUser+" . getAscDesc("typeUser", $data); ?>">Тип пользователя</a></th>
						<th><a href="<?= $thisPagePath . "&orderBy=phone+" . getAscDesc("phone", $data); ?>">Телефон</th>
						<th><a href="<?= $thisPagePath . "&orderBy=regDate+" . getAscDesc("regDate", $data); ?>">Дата регистарции</a></th>
						<th><a href="<?= $thisPagePath . "&orderBy=nameCity+" . getAscDesc("nameCity", $data); ?>">Город</a></th>
						<th><a href="<?= $thisPagePath . "&orderBy=locationStatus+" . getAscDesc("locationStatus", $data); ?>">Статус локации</a></th>
						<th><a href="<?= $thisPagePath . "&orderBy=lockStatus+" . getAscDesc("lockStatus", $data); ?>">Статус блокировки</a></th>
						<th>Фото</th>
					</tr>
					<?php foreach ($data['result'] as $user) { ?>
					<tr onclick="openWin('userInfo?phone=<?= $user['phone']; ?>&amp;typeUser=<?= $user['typeUser']; ?>')">
						<td><?= $user['typeUser']; ?></td>
						<td><?= $user['phone']; ?></td>
						<td><?= $user['regDate']; ?></td>
						<td><?= $user['nameCity']; ?></td>
						<td><?= $user['locationStatus']; ?></td>
						<td><?= $user['lockStatus']; ?></td>
						<td><img src="../<?= $user['photo']; ?>"></td>
					</tr>
					<?php } ?>
				</table>
			</div>
		<?php }elseif ($data['form']['typeSearch'] == "order") { ?>
			<div>
				<table>
					<tr>
						<th><a href="<?= $thisPagePath . "&orderBy=idOrder+" . getAscDesc("idOrder", $data); ?>">Номер заказа</th>
						<th><a href="<?= $thisPagePath . "&orderBy=phonePassenger+" . getAscDesc("phonePassenger", $data); ?>">Телефон пассажира</th>
						<th><a href="<?= $thisPagePath . "&orderBy=phoneDriver+" . getAscDesc("phoneDriver", $data); ?>">Телефон водителя</th>
						<th><a href="<?= $thisPagePath . "&orderBy=timeOrder+" . getAscDesc("timeOrder", $data); ?>">Время заказа</th>
						<th><a href="<?= $thisPagePath . "&orderBy=pointStart+" . getAscDesc("pointStart", $data); ?>">Точка отправления</th>
						<th><a href="<?= $thisPagePath . "&orderBy=pointFinish1+" . getAscDesc("pointFinish1", $data); ?>">Конечная точка</th>
						<th><a href="<?= $thisPagePath . "&orderBy=price+" . getAscDesc("price", $data); ?>">Стоимоть</th>
						<th><a href="<?= $thisPagePath . "&orderBy=statusOrder+" . getAscDesc("statusOrder", $data); ?>">Статус заказа</th>
					</tr>
					<?php foreach ($data['result'] as $order) { ?>
					<tr>
						<td><?= $order['idOrder']; ?></td>
						<td onclick="openWin('userInfo?phone=<?= $order['phonePassenger']; ?>&amp;typeUser=Passengers')"><?= $order['phonePassenger']; ?></td>
						<td onclick="openWin('userInfo?phone=<?= $order['phoneDriver']; ?>&amp;typeUser=Drivers')"><?= $order['phoneDriver']; ?></td>
						<td><?= $order['timeOrder']; ?></td>
						<td><?= $order['pointStart']; ?></td>
						<td><?= $order['pointFinish1']; ?></td>
						<td><?= $order['price']; ?></td>
						<td><?= $order['statusOrder']; ?></td>
					</tr>
					<?php } ?>
				</table>
			</div>
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
	                     ->setCssActiveLinkClass("active_link")
	                       // Параметр для query string гиперссылки
	                     ->setRequestUriParameter("param_1", "value_1")
	                       // Параметр для query string гиперссылки
	                     ->setRequestUriParameter("param_2", "value_2")
	                       // Устанавливаем идентификатор фрагмента гиперссылок пагинатора
	                     ->setFragmentIdentifier("result1");
	    ?>
	    <div>
	        <?php if ($paginationHelper->getPagination()->getCount()): ?>
	        	<span>Страницы:</span>
	            <?=
	            	$paginationHelper->getHtml("typeSearch=" . $data['form']['typeSearch'] . 
	            								"&stringSearch=" . $data['form']['stringSearch'] . 
	            								"&orderBy=" . $data['form']['orderBy']['column'] . "+" . $data['form']['orderBy']['sortAscDesc'] . 
	            								"&");
	            ?>
	        <?php endif; ?>
	    </div>
	<?php } ?>

	<script>
		function openWin(url) {
			var features, w = 800, h = 600;
			var top = (screen.height - h)/2, left = (screen.width - w)/2;
			if(top < 0) top = 0;
			if(left < 0) left = 0;
			features = 'top=' + top + ',left=' +left;
			features += ',height=' + h + ',width=' + w + ',resizable=no';
			myWin = open(url, 'displayWindow', features);
		}


		/*function selectedOption(sel){
			typeSearch = '<?= $data["form"]["typeSearch"]; ?>';
			typeSort = '<?= $data["form"]["typeSort"]; ?>';
			if (typeSearch !== '' && typeSearch === sel.value) {
				$("#selTypeSort").val(typeSort);
			}
		}

		$(document).ready(function() {
	    	var elem = $('input[type=radio][name=typeSearch]:checked')
	        if (elem[0].value == 'phone') {
	            $('#selTypeSort')[0].innerHTML = '<option value="typeUser">тип пользователя</option><option value="regDate">дата регистрации</option><option value="nameCity">город</option><option value="locationStatus">статус локации</option><option value="lockStatus">статус блокировки</option>';
	        }
	        else if (elem[0].value == 'order') {
	           $('#selTypeSort')[0].innerHTML = '<option value="idOrder">номер заказа</option><option value="statusOrder">статус заказа</option><option value="timeOrder">время заказа</option><option value="dateStart">дата отправления</option><option value="locationStatus">статус локации</option><option value="price">стоимость</option><option value="typePrice">вид оплаты</option>';
	        }
	        selectedOption(elem[0]);
	    });

	    $('input[type=radio][name=typeSearch]').change(function() {
	        if (this.value == 'phone') {
	            $('#selTypeSort')[0].innerHTML = '<option value="typeUser">тип пользователя</option><option value="regDate">дата регистрации</option><option value="nameCity">город</option><option value="locationStatus">статус локации</option><option value="lockStatus">статус блокировки</option>';
	        }
	        else if (this.value == 'order') {
	           $('#selTypeSort')[0].innerHTML = '<option value="idOrder">номер заказа</option><option value="statusOrder">статус заказа</option><option value="timeOrder">время заказа</option><option value="dateStart">дата отправления</option><option value="locationStatus">статус локации</option><option value="price">стоимость</option><option value="typePrice">вид оплаты</option>';
	        }
	        selectedOption(this);
	    });*/
	</script>

<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
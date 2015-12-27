<?php $title = "Пользователи"; ?>

<?php ob_start(); ?>
	<form name="users" action="users" method="get">
		<select name="typeUser">
			<option value="Passengers"<?php if ($data['form']['typeUser']=='Passengers') echo " selected"; ?>>Пассажиры</option>
			<option value="Drivers"<?php if ($data['form']['typeUser']=='Drivers') echo " selected"; ?>>Водители</option>
		</select>
		<input type="submit" value="Показать" >
	</form>
	<?php if (!empty($_GET)) {
			if (!empty($data['errors'])) { ?>
				<p><?= $data['errors']; ?></p>
			<?php }elseif (empty($data['result'])) { ?>
				<p>Пользователи указанного типа не найдены!</p>
			<?php }else{ ?>
				<div>
					<table border="1">
						<tr>
							<th>ID пользователя</th>
							<th>Телефон</th>
							<th>Дата регистрации</th>
							<th>Всего заказов</th>
							<th>Количество отказов</th>
							<th>Заблокировать</th>
						</tr>
						<?php foreach ($data['result'] as $user) { ?>
						<tr class="user-info">
							<td class="id-user"><?= $user['idUser']; ?></td>
							<td class="phone"><?= $user['phone']; ?></td>
							<td class="reg-date"><?= $user['regDate']; ?></td>
							<td class="am-or"><?= $user['amountOrders']; ?></td>
							<td class="am-rej"><?= $user['amountRejection']; ?></td>
							<td><input class="lockStatus-check" type="checkbox" name="lockStatus" data-id="<?= $user['idUser']; ?>" <?= $user['lockStatus'] == 1 ? "checked=\"checked\"" : "" ;?>></td>
						</tr>
						<?php } ?>
					</table>
					<?php
				    $paginationHelper = new Krugozor_Pagination_Helper($data['paginationManager']);
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
				        Всего пользователей: <strong><?=$paginationHelper->getPagination()->getCount()?></strong>
				        <?php if ($paginationHelper->getPagination()->getCount()): ?>
				            <br /><br /><span>Страницы:</span>
				            <?=$paginationHelper->getHtml("typeUser=".$_GET['typeUser']."&")?>
				        <?php endif; ?>
				    </div>
				</div>
				<div id="div-user-info">
					<p>Здесь будет отображена информация о пользователе</p>
				</div>
			<?php }
			} ?>

	<script>
		function getInfo(divUserInfo, phone, table){
			$.ajax({
				type: 'POST',
				url: "../app/getInfo.php",
				data: 'phone=' + phone + '&table=' + table,
				dataType: 'JSON',
				success:function(data, jqXHR, textStatus, errorThrown){
					var content = '';
					if (table == 'Passengers'){
						content += '<h3>Информация о пассажире</h3><p>ID пользователя: ' + data[0][0]['idUser'] + '</p><p>Имя: ' + data[0][0]['name'] + '</p><p>Телефон: ' + data[0][0]['phone'] + '</p><p>Пол: ' + data[0][0]['sex'] + '</p><p>Город: ' + data[0][0]['city'] + '</p><p>Дата регистрации: ' + data[0][0]['regDate'] + '</p><p>Статус локации: ' + data[0][0]['locationStatus'] + '</p><p>Баланс бонусов: ' + data[0][0]['balanceBonuses'] + '</p><p>Статус блокировки: ' + data[0][0]['lockStatus'] + '</p><p>Фото<img src="../' + data[0][0]['photo'] + '"></p>';
					}else{
						content += '<h3>Информация о водителе</h3><p>ID пользователя: ' + data[0][0]['idUser'] + '</p><p>Телефон: ' + data[0][0]['phone'] + '</p><p>Статус работы: ' + data[0][0]['workingStatus'] + '</p><p>Статус локации: ' + data[0][0]['locationStatus'] + '</p><p>Город: ' + data[0][0]['city'] + '</p><p>Дата регистрации: ' + data[0][0]['regDate'] + '</p><p>Баланс: ' + data[0][0]['balance'] + '</p><p>Статус блокировки: ' + data[0][0]['lockStatus'] + '</p><p>Марка автомобиля: ' + data[0][0]['brandCar'] + '</p><p>Модель автомобиля: ' + data[0][0]['modelCar'] + '</p><p>Цвет автомобиля: ' + data[0][0]['colorCar'] + '</p><p>Государственный номер: ' + data[0][0]['stateNumber'] + '</p><p>Фото<img src="../' + data[0][0]['photo'] + '"></p>';
					}

					if (data[1].length > 0) {
						content += '<table><tr><th>Номер заказа</th><th>Телефон пассажира</th><th>Статус локации</th><th>Время заказа</th><th>Дата поездки</th><th>Точка отправления</th><th>Точка прибытия</th><th>Размер оплаты</th><th>Вид оплаты</th><th>Комментарий</th><th>Статус заказа</th><th>Телефон водителя</th></tr>';
						for (var i = 0; i < data[1].length; i++) {
							content += '<tr><td>' + data[1][i]['idOrder'] + '</td><td>' + data[1][i]['phonePassenger'] + '</td><td>' + data[1][i]['locationStatus'] + '</td><td>' + data[1][i]['timeOrder'] + '</td><td>' + data[1][i]['dateStart'] + '</td><td>' + data[1][i]['pointStart'] + '</td><td>' + data[1][i]['pointFinish1'] + '</td><td>' + data[1][i]['price'] + '</td><td>' + data[1][i]['typePrice'] + '</td><td>' + data[1][i]['comment'] + '</td><td>' + data[1][i]['statusOrder'] + '</td><td>' + data[1][i]['phoneDriver'] + '</td></tr>';
						};
						content += '</table>';
					};
					$('#div-user-info').html(content);
				}
			});
		}

		function saveToDatabase(editableVal,column,id,tableName) {
			if (typeof(tableName) === 'undefined') tableName = '';
			$.ajax({
				url: "../app/saveEdit.php",
				type: "POST",
				data:'column='+column+'&editVal='+editableVal+'&id='+id+'&tableName='+tableName,
				success: function(result, jqXHR, textStatus, errorThrown) {
					//$(editableObj).css("background","#FDFDFD");
	            },
	            error: function(result, jqXHR, textStatus, errorThrown) {
	                //alert(JSON.stringify(result));
	            }      
		   });
		}

		var table = '<?= $data["form"]["typeUser"]; ?>';
		$('tr.user-info').delegate('td','click',(function (e) {
				var phone = $(this).parent().find('td.phone').text();
				getInfo($('#div-user-info'), phone, table);
		}))

		$('input.lockStatus-check').change((function () {
				var status = 0;
				if ($(this).prop('checked') === true){
					status = 1;
				}
				saveToDatabase(status, 'lockStatus', $(this).data('id'), table);
		}))
	</script>

<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
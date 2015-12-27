<?php 
	$title = "Информация о пользователe";
	$notGetMenu = true;
?>

<?php ob_start(); ?>
<?php if ($data['errors']) {
	echo "<p>" . $data['errors'] . "</p>";
}else{
	if ($data['typeUser'] == "Passengers") { ?>
		<h3>Информация о пассажире</h3>
		<p>ID пользователя: <?=$data['user']['idUser']; ?></p>
		<p>Имя: <?=$data['user']['name']; ?></p>
		<p>Телефон: <?=$data['user']['phone']; ?></p>
		<p>Пол: <?=$data['user']['sex']; ?></p>
		<p>Город: <?=$data['user']['city']; ?></p>
		<p>Дата регистрации: <?=$data['user']['regDate']; ?></p>
		<p>Статус локации: <?=$data['user']['locationStatus']; ?></p>
		<p>Баланс бонусов: <?=$data['user']['balanceBonuses']; ?></p>
		<p>Статус блокировки: <?=$data['user']['lockStatus']; ?></p>
		<p>Фото<img src="../<?=$data['user']['photo']; ?>"></p>
	<?php }else{ ?>
		<h3>Информация о водителе</h3>
		<p>ID пользователя: <?=$data['user']['idUser']; ?></p>
		<p>Телефон: <?=$data['user']['phone']; ?></p>
		<p>Статус работы: <?=$data['user']['workingStatus']; ?></p>
		<p>Статус локации: <?=$data['user']['locationStatus']; ?></p>
		<p>Город: <?=$data['user']['city']; ?></p>
		<p>Дата регистрации: <?=$data['user']['regDate']; ?></p>
		<p>Баланс: <?=$data['user']['balance']; ?></p>
		<p>Статус блокировки: <?=$data['user']['lockStatus']; ?></p>
		<p>Марка автомобиля: <?=$data['user']['brandCar']; ?></p>
		<p>Модель автомобиля: <?=$data['user']['modelCar']; ?></p>
		<p>Цвет автомобиля: <?=$data['user']['colorCar']; ?></p>
		<p>Государственный номер: <?=$data['user']['stateNumber']; ?></p>
		<p>Фото<img src="../<?=$data['user']['photo']; ?>"></p>
	<?php }
	if (!empty($data['orders'])) { ?>
		<table>
			<tr>
				<th>Номер заказа</th>
				<th>Телефон пассажира</th>
				<th>Статус локации</th>
				<th>Время заказа</th>
				<th>Дата поездки</th>
				<th>Точка отправления</th>
				<th>Точка прибытия</th>
				<th>Размер оплаты</th>
				<th>Вид оплаты</th>
				<th>Комментарий</th>
				<th>Статус заказа</th>
				<th>Телефон водителя</th>
			</tr>
		<?php foreach ($data['orders'] as $order) { ?>
			<tr>
				<td><?= $order['idOrder']; ?></td>
				<td><?= $order['phonePassenger']; ?></td>
				<td><?= $order['locationStatus']; ?></td>
				<td><?= $order['timeOrder']; ?></td>
				<td><?= $order['dateStart']; ?></td>
				<td><?= $order['pointStart']; ?></td>
				<td><?= $order['pointFinish1']; ?></td>
				<td><?= $order['price']; ?></td>
				<td><?= $order['typePrice']; ?></td>
				<td><?= $order['comment']; ?></td>
				<td><?= $order['statusOrder']; ?></td>
				<td><?= $order['phoneDriver']; ?></td>
			</tr>
		<?php } ?>
		</table>
	<?php }
} ?>

<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
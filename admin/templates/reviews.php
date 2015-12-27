<?php $title = "Отзывы"; ?>

<?php ob_start(); ?>
<?php if ($data['errors']) { 
	echo "<p>" . $data['errors'] . "</p>";
}elseif ($data['reviews']){ ?>
	<table>
		<tr>
			<th>№ отзыва</th>
			<th>Телефон пассажира</th>
			<th>Телефон водителя</th>
			<th>Текст отзыва</th>
			<th>Рейтинг</th>
			<th>Время отзыва</th>
			<th>Одобрен</th>
		</tr>
		<?php foreach ($data['reviews'] as $review) { ?>
		<tr class="table-row">
			<td><?= $review['idReview']; ?></td>
			<td onclick="openWin('userInfo?phone=<?= $review['phonePassenger']; ?>&amp;typeUser=Passengers')"><?= $review['phonePassenger']; ?></td>
			<td onclick="openWin('userInfo?phone=<?= $review['phoneDriver']; ?>&amp;typeUser=Drivers')"><?= $review['phoneDriver']; ?></td>
			<td contenteditable="true" onBlur="saveToDatabase(this.innerText,'textReview','<?= $review['idReview']; ?>')"><?= $review['textReview']; ?></td>
			<td><?= $review['rating']; ?></td>
			<td><?= $review['timeReview']; ?></td>
			<td><input class="status-check" data-id="<?= $review['idReview']; ?>" type="checkbox" <?= $review['status'] == "approve" ? "checked=\"checked\"" : "" ;?>></td>
		</tr>
		<?php } ?>
	</table>
	<?php
    
    $paginationHelper = new Krugozor_Pagination_Helper($data['paginationManager']);

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
        Всего отзывов: <strong><?=$paginationHelper->getPagination()->getCount()?></strong>
        <?php if ($paginationHelper->getPagination()->getCount()): ?>
            <br /><br /><span>Страницы:</span>
            <?=$paginationHelper->getHtml()?>
        <?php endif; ?>
    </div>

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

		function saveToDatabase(editableVal,column,id) {
			$.ajax({
				url: "../app/saveEdit.php",
				type: "POST",
				data:'column='+column+'&editVal='+editableVal+'&id='+id,
				success: function(result, jqXHR, textStatus, errorThrown) {
					//$(editableObj).css("background","#FDFDFD");
	            },
	            error: function(result, jqXHR, textStatus, errorThrown) {
	                //alert(JSON.stringify(result));
	            }      
		   });
		}

		$('input.status-check').change((function () {
				var status = 'expect';
				if ($(this).prop('checked') === true){
					status = 'approve';
				}
				saveToDatabase(status, 'status', $(this).data('id'));
		}))
	</script>
<?php }else{ ?>
	<p>Отзывов нет!</p>
<?php } ?>

<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
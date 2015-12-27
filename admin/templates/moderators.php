<?php $title = "Модераторы"; ?>

<?php ob_start(); ?>
	
		<?php
		list($thisPagePath, ) = explode("?orderBy=", $_SERVER['REQUEST_URI']);

		function getAscDesc($column, $data){
			if ($data['form']['orderBy']['column'] == $column) {
				if ($data['form']['orderBy']['sortAscDesc'] == "ASC") {
					return "DESC";
				}
			}
			return "ASC";
		}
	?>

	<div>
		<a onclick="openWin('listCity/addCity')">Добавить город</a>
		<a onclick="openWin('moderators/addModerator')">Добавить модератора</a>
	</div>

	<table class="tbl-qa" style="width:800px">
		<tr>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=login+" . getAscDesc("login", $data); ?>">Модератор</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=firstName+" . getAscDesc("firstName", $data); ?>">Имя</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=lastName+" . getAscDesc("lastName", $data); ?>">Фамилия</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=email+" . getAscDesc("email", $data); ?>">Email</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=nameCity+" . getAscDesc("nameCity", $data); ?>">Город</a></th>
		</tr>
		<?php foreach ($data['moderators'] as $k => $moderator) { 
			empty($moderator['nameCity']) ? $nameCity = "не назначен" : $nameCity = $moderator['nameCity'];
			
			$idModerator = $moderator['idModerator'];
			?>
		<tr class="table-row">
			<td class="login" data-id="<?= $moderator['idModerator']; ?>" contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'login','<?= $moderator['idModerator']; ?>', 'moderators');"><?= $moderator['login']; ?></td>			
			<td contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'firstName','<?= $moderator['idModerator']; ?>', 'moderators')"><?= $moderator['firstName']; ?></td>
			<td contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'lastName','<?= $moderator['idModerator']; ?>', 'moderators')"><?= $moderator['lastName']; ?></td>
			<td contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'email','<?= $moderator['idModerator']; ?>', 'moderators')"><?= $moderator['email']; ?></td>
			<td class="is-moderation" data-id="<?= $moderator['idModerator']; ?>"><span><?= $nameCity; ?></span></td>
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
        Всего модеротаров: <strong><?=$paginationHelper->getPagination()->getCount()?></strong>
        <?php if ($paginationHelper->getPagination()->getCount()): ?>
            <br /><br /><span>Страницы:</span>
            <?=$paginationHelper->getHtml("orderBy=" . $data['form']['orderBy']['column'] . "+" . $data['form']['orderBy']['sortAscDesc'] . "&")?>
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

		function saveToDatabase(editableVal,column,id,tableName) {
			//alert('имя' + tableName);
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

		function showEditCity(editableObj, column, id) {
			$.ajax({
				url: "../app/saveEdit.php",
				type: "GET",
				data: 'idModerator='+id,
				dataType: 'JSON',
				success:function(data, jqXHR, textStatus, errorThrown){
					var content = '<select id="selCity" data-id="'+ id +'"><option value="0">не назначен</option>';
					for (var x = 0; x < data.length; x++) {
						var selected = '';
						if (editableObj.innerHTML === data[x].nameCity) {
							selected = 'selected="selected"';
						};
		                content += '<option value="' + data[x].idCity + '" ' + selected + '>' + data[x].nameCity + '</option>';
		            }
		            content += '</select>';
		        	editableObj.parentNode.innerHTML = content;

				}
		    });
		}

		$('td.is-moderation').delegate('span','click',(function (e) {
				var id = $(this).parent().data('id');
				showEditCity(this, 'city', id);
				//$(this).parent().html(contentHTML);
				
		}))
		
		$('td.is-moderation').delegate('#selCity','blur',function () {
			var id = $(this).data('id');
			var selVal = $(this).find(":selected").val();
			var selText = $(this).find(":selected").text();
			var login = $('.login[data-id='+ id +']')[0];
			var elem = $('.is-moderation[data-id='+ id +']')[0];
			elem.innerHTML = '<span>' + selText + '</span>';
			saveToDatabase(login.innerText,'moderator', selVal);
		})

	</script>

<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
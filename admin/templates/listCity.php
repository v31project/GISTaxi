<?php 
	
	$title = "Список городов";
	//$onload = " onload=\"initialize()\"";
?>

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

	<!--<div id="result-div"></div>-->

	<div>
		<a onclick="openWin('listCity/addCity')">Добавить город</a>
		<a onclick="openWin('moderators/addModerator')">Добавить модератора</a>
	</div>

	<table class="tbl-qa" style="width:1500px">
		<tr>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=nameCity+" . getAscDesc("nameCity", $data); ?>">Город</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=active+" . getAscDesc("active", $data); ?>">Активен</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=avgPrice+" . getAscDesc("avgPrice", $data); ?>">Средняя цена</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=login+" . getAscDesc("login", $data); ?>">Модератор</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=activeBonuses+" . getAscDesc("activeBonuses", $data); ?>">Бонусы</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=inviteBonus+" . getAscDesc("inviteBonus", $data); ?>">Отправил приглашение</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=acceptInviteBonus+" . getAscDesc("acceptInviteBonus", $data); ?>">Принял приглашение</a></th>
			<th class="table-header"><a href="<?= $thisPagePath . "?orderBy=rideBonus+" . getAscDesc("rideBonus", $data); ?>">За поездку</a></th>
		</tr>
		<?php foreach ($data['citys'] as $k => $city) { 
			empty($city['login']) ? $moderator = "не назначен" : $moderator = $city['login'];
			
			$idCity = $city['idCity'];
			?>
		<tr class="table-row">
			<td contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'nameCity','<?= $city['idCity']; ?>');"><?= $city['nameCity']; ?></td>
			<td><input  class="is-active" type="checkbox" data-id="<?= $city['idCity']; ?>" <?= $city['active'] == 1 ? "checked=\"checked\"" : "" ;?>></td>
			<td contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'avgPrice','<?= $city['idCity']; ?>')"><?= $city['avgPrice']; ?></td>
			<td class="is-moderation" data-id="<?= $city['idCity']; ?>"><span><?= $moderator; ?></span></td>
			<td><input  class="is-activeBonuses" type="checkbox" data-id="<?= $city['idCity']; ?>" <?= $city['activeBonuses'] == 1 ? "checked=\"checked\"" : "" ;?>></td>
			<td contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'inviteBonus','<?= $city['idCity']; ?>')"><?= $city['inviteBonus']; ?></td>
			<td contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'acceptInviteBonus','<?= $city['idCity']; ?>')"><?= $city['acceptInviteBonus']; ?></td>
			<td contenteditable="true" onBlur="saveToDatabase(this.innerHTML,'rideBonus','<?= $city['idCity']; ?>')"><?= $city['rideBonus']; ?></td>
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
                     ->setCssActiveLinkClass("active")
                       // Параметр для query string гиперссылки
                     ->setRequestUriParameter("param_1", "value_1")
                       // Параметр для query string гиперссылки
                     ->setRequestUriParameter("param_2", "value_2")
                       // Устанавливаем идентификатор фрагмента гиперссылок пагинатора
                     ->setFragmentIdentifier("result1");
    ?>
    Всего городов: <strong><?=$paginationHelper->getPagination()->getCount()?></strong>
    <div class="text-center">
        <?php if ($paginationHelper->getPagination()->getCount()): ?>
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

		/*function saveToDatabase(editableObj,column,id) {
			$(editableObj).css("background","#FFF url(loaderIcon.gif) no-repeat right");
			$.ajax({
				url: "../app/saveEdit.php",
				type: "POST",
				data:'column='+column+'&editVal='+editableObj.innerHTML+'&id='+id,
				success: function(result, jqXHR, textStatus, errorThrown) {
					$(editableObj).css("background","#FDFDFD");
	            },
	            error: function(result, jqXHR, textStatus, errorThrown) {
	                //alert(JSON.stringify(result));
	            }      
		   });
		}*/

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

		function showEditModerator(editableObj, column, id) {
			$.ajax({
				url: "../app/saveEdit.php",
				type: "GET",
				data: 'idCity='+id,
				dataType: 'JSON',
				success:function(data, jqXHR, textStatus, errorThrown){
					var content = '<select id="selModerator" data-id="'+ id +'"><option value="0">не назначен</option>';
					for (var x = 0; x < data.length; x++) {
						var selected = '';
						if (editableObj.innerHTML === data[x].login) {
							selected = 'selected="selected"';
						};
		                content += '<option value="' + data[x].idModerator + '" ' + selected + '>' + data[x].login + '</option>';
		            }
		            content += '</select>';
		        	editableObj.parentNode.innerHTML = content;

				}
		    });
		}

		$('td.is-moderation').delegate('span','click',(function (e) {
				var id = $(this).parent().data('id');
				showEditModerator(this, 'moderator', id);
				//$(this).parent().html(contentHTML);
				
		}))
		
		$('td.is-moderation').delegate('#selModerator','blur',function () {
			var id = $(this).data('id');
			var selVal = $(this).find(":selected").val();
			var selText = $(this).find(":selected").text();
			var elem = $('.is-moderation[data-id='+ id +']')[0];
			elem.innerHTML = '<span>' + selText + '</span>';
			saveToDatabase($(elem).children().html(),'moderator', id);
		})

		$('input.is-active').change((function () {
				var active = 0;
				if ($(this).prop('checked') === true){
					active = 1;
				}
				saveToDatabase(active, 'active', $(this).data('id'));
		}))

		$('input.is-activeBonuses').change((function () {
				var activeBonuses = 0;
				if ($(this).prop('checked') === true){
					activeBonuses = 1;
				}
				saveToDatabase(activeBonuses, 'activeBonuses', $(this).data('id'));
		}))

	</script>

	<!--<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> 
	<script type="text/javascript">  
	  function initialize() {
			var options = {
			  scrollwheel: false,
			  scaleControl: true,
			  mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
			}
	 
			var map = new google.maps.Map(document.getElementById("map"), options);
			map.setCenter(new google.maps.LatLng(53.8840092,27.4548916));
			map.setZoom(12);
	 
			// Задаем слой с OSM
	 
			var openStreet = new google.maps.ImageMapType({
			  getTileUrl: function(ll, z) {
				var X = ll.x % (1 << z);  // wrap
				return "http://tile.openstreetmap.org/" + z + "/" + X + "/" + ll.y + ".png";
			  },
			  tileSize: new google.maps.Size(256, 256),
			  isPng: true,
			  maxZoom: 18,
			  name: "OSM",
			  alt: "Слой с Open Streetmap"
			}); 
	 
			//Добавляем слои к карте
	 
			map.mapTypes.set('osm', openStreet);
			map.setMapTypeId('osm');
	 
			map.setOptions({
			  mapTypeControlOptions: {
				mapTypeIds: [
				  'osm',
				  google.maps.MapTypeId.ROADMAP,
				  google.maps.MapTypeId.TERRAIN,
				  google.maps.MapTypeId.SATELLITE,
				  google.maps.MapTypeId.HYBRID
				],
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
			  }
			});
	 
	  }
	</script>-->

<?php $content = ob_get_clean(); ?>

<?php include "layout.php" ?>
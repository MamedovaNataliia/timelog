<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
CJSCore::Init(array("jquery"));
$this->addExternalCss("/bitrix/css/main/bootstrap.css");
$this->addExternalCss("/bitrix/css/main/font-awesome.css");


if($_GET['log'])
{
	echo "<pre>";
	print_r($arResult);
	echo "</pre>";
	
}

?>
<h2>
	Режим администрирования:
</h2>
<div class="panel panel-default">
 <div class="panel-heading">Цветовая схема рабочего времени</div>
 <div class="panel-body">
<div id="color" class="color" data-typetable="1">
	<table id="timelog-table" class="table">
		<thead>
			<tr>
				<th><span>#ID</span></th>
				<th><span>От</span></th>
				<th><span>До</span></th>
				<th><span>Цвет</span></th>
				<th><span>Действие</span></th>
			</tr>
		</thead>	
		<tbody id="timelog-tbody">
			<?php if(count($arResult['TIMES']) > 0){?>
				<?php for($i = 0; $i < count($arResult['TIMES']); $i++){ ?>
				<?if($arResult['TIMES'][$i]['t_type_id'] != 1) continue; ?>
				<tr id="tr-timelog-<?php echo $arResult['TIMES'][$i]['id']; ?>">
					<td><span><?php echo $arResult['TIMES'][$i]['id']; ?></span></td>
					
					
					<td>
						<input type="number" class="hourse" id="from_minutes-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="from_minutes" value="<?php echo $arResult['TIMES'][$i]['from_minutes']; ?>" />:
						<input type="number" class="minutes" id="from_seconds-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="from_seconds" value="<?php echo $arResult['TIMES'][$i]['from_seconds']; ?>" />
					</td>
					<td>
						<input type="number" class="hourse" id="to_minutes-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="to_minutes" value="<?php echo $arResult['TIMES'][$i]['to_minutes']; ?>" />:
						<input type="number" class="minutes" id="to_seconds-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="to_seconds" value="<?php echo $arResult['TIMES'][$i]['to_seconds']; ?>" />
					</td>
					<td><input type="color" id="color_hex-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="color_hex" value="<?php echo $arResult['TIMES'][$i]['color_hex']; ?>" /></td>
					<td><span data-id="<?php echo $arResult['TIMES'][$i]['id']; ?>" class="link change btn btn-success">Сохранить</span> <span data-id="<?php echo $arResult['TIMES'][$i]['id']; ?>" class="link delete btn btn-danger">Удалить</span></td>
				</tr>
				<?php } ?>
			<?php }else{ ?>
				<tr id="default">
					<td colspan="5"><span>Данные по цветовой схеме не найдены...</span></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<button id="add-time-range-button" class="btn btn-success">Добавить новый временной диапазон</button>
</div>
</div>
</div>


<div class="panel panel-default">
 <div class="panel-heading">Цветовая схема перерывов</div>
 <div class="panel-body">
<div id="color-leaks" class="color" data-typeTable="2">
	<table id="timelog-leaks-table" class="table">
		<thead>
			<tr>
				<th><span>#ID</span></th>
				<th><span>От</span></th>
				<th><span>До</span></th>
				<th><span>Цвет</span></th>
				<th><span>Действие</span></th>
			</tr>
		</thead>	
		<tbody id="timelog-leaks-tbody">
			<?php if(count($arResult['TIMES']) > 0){?>
				<?php for($i = 0; $i < count($arResult['TIMES']); $i++){ ?>
				<?if($arResult['TIMES'][$i]['t_type_id'] != 2) continue; ?>
				<tr id="tr-timelog-<?php echo $arResult['TIMES'][$i]['id']; ?>">
					<td><span><?php echo $arResult['TIMES'][$i]['id']; ?></span></td>
					<td>
						<input type="number" class="hourse" id="from_minutes-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="from_minutes" value="<?php echo $arResult['TIMES'][$i]['from_minutes']; ?>" />:
						<input type="number" class="minutes" id="from_seconds-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="from_seconds" value="<?php echo $arResult['TIMES'][$i]['from_seconds']; ?>" />
					</td>
					<td>
						<input type="number" class="hourse" id="to_minutes-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="to_minutes" value="<?php echo $arResult['TIMES'][$i]['to_minutes']; ?>" />:
						<input type="number" class="minutes" id="to_seconds-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="to_seconds" value="<?php echo $arResult['TIMES'][$i]['to_seconds']; ?>" />
					</td>
					<td><input type="color" id="color_hex-<?php echo $arResult['TIMES'][$i]['id']; ?>" name="color_hex" value="<?php echo $arResult['TIMES'][$i]['color_hex']; ?>" /></td>
					<td><span data-id="<?php echo $arResult['TIMES'][$i]['id']; ?>" class="link change btn btn-success">Сохранить</span> <span data-id="<?php echo $arResult['TIMES'][$i]['id']; ?>" class="link delete btn btn-danger">Удалить</span></td>
					
				</tr>
				<?php } ?>
			<?php }else{ ?>
				<tr class="default-leaks">
					<td colspan="5"><span>Данные по цветовой схеме не найдены...</span></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<button id="add-time-leaks-range-button" class="btn btn-success">Добавить новый временной диапазон</button>
</div>
</div>
</div>

<div class="panel panel-default">
 <div class="panel-heading">Дополнительные настройки</div>
 <div class="panel-body">
	
	<div id="chekbox_timelog_opt">
	<?foreach($arResult['CHECKBOXES'] as $item): ?>
		<div class="checkbox">
			<label>

				<? if($item['id'] == 1){?>
					<input
						<?=($item['is_show_turn']) ? "checked " : "";?> 
						data-id="<? echo $item['id']?>" 
						class="checkbox_timelog"
						type="checkbox"
						value=""
					>
				<?};?>

				<? if($item['id'] == 2){?>
					<input data-id="2" type="number" id="count-on-page" min="0" data-bind="value:count-on-page" value="<? echo $item['is_show_turn'];?>"/>
				<?};?>

				<span><?=$item['title']?></span>
			</label>
		</div>
	<?endforeach?>
	</div>
 
 </div>
</div>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

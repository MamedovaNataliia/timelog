<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
CJSCore::Init(array("jquery", "jquery-ui"));
$this->addExternalCss("/bitrix/css/main/bootstrap.css");
$this->addExternalCss("/bitrix/css/main/font-awesome.css");
$this->addExternalCss("/bitrix/components/expertit/timelogger/templates/.default/jquery-ui/jquery-ui.css");
$this->addExternalJs("/bitrix/components/expertit/timelogger/templates/.default/jquery-ui/jquery-ui.js");
$this->addExternalJs("/bitrix/components/expertit/timelogger/templates/.default/pop-up.js");
$this->addExternalJs("/bitrix/components/expertit/timelogger/templates/.default/section.js");

if($_GET['log'])
{
	echo "<pre>";
	echo var_dump($arResult['timelog_date_begin']);
	print_r($arResult);
	echo "</pre>";
	
}
?>

<div id="popUpContent">

</div>

<div id="timelog_date_wrong_leaks" data-timebegin="<?=$arResult['timelog_date_begin']?>" data-timeend="<?=$arResult['timelog_date_end']?>" ></div>



<div id="timelog" class="timelog">
	<div id="options-container" class="container">
			
	</div>
	<div id="timelog-form-date" class="timelog-form-date">
	<form class="form-range-data" name="select-date-range" action="/" method="GET" onsubmit="return false;">
		<div>
			<input type="checkbox" name="control" id="control" />
			<label for="control">Указать другой диапазон</label>
		</div>
		<div id="range-select" style="display: inline;">
			<select name="year" id="years-select" class="years-select form-control">
				<? for($i = $arResult['MAX_YEAR']; $i >= $arResult['MIN_YEAR']; $i--){ ?>
					<option value="<? echo $i; ?>"><?php echo $i; ?></option>
				<? } ?>
			</select>
			<select name="months" id="months-select" class="months-select form-control">
				<option value="1">Январь</option>
				<option value="2">Февраль</option>
				<option value="3">Март</option>
				<option value="4">Апрель</option>
				<option value="5">Май</option>
				<option value="6">Июнь</option>
				<option value="7">Июль</option>
				<option value="8">Август</option>
				<option value="9">Сентябрь</option>
				<option value="10">Октябрь</option>
				<option value="11">Ноябрь</option>
				<option value="12">Декабрь</option>
			</select>
		</div>
		<span id="range-datepicker" hidden="hidden">
			<input type="text" id="from-datepicker" class="form-control" name="from-datepicker" value="" />
			<input type="text" id="to-datepicker" class="form-control" name="to-datepicker" value="" />
		</span>
        <div id="selectMarker"></div>
        <!--
		<select id="groups" name="groups" class="group-select form-control">
			<option value="all">Все</option>
			<? for($i = 0; $i < count($arResult['GROUPS']); $i++){ ?>
				<option value="<? echo $arResult['GROUPS'][$i]['ID']; ?>"><? echo $arResult['GROUPS'][$i]['NAME']; ?></option>
			<? } ?>
		</select>
		<select id="users" name="fio" class="fio-select form-control">
			<option value="all">Все</option>
			<? for($i = 0; $i < count($arResult['USERS']); $i++){ ?>
				<option value="<? echo $arResult['USERS'][$i]['ID']; ?>"><? echo $arResult['USERS'][$i]['NAME'] . ' ' . $arResult['USERS'][$i]['LAST_NAME']; ?></option>
			<? } ?>
		</select>
        -->
        <input id="sectionInput" type="hidden" name="section" value="" />
		<a class="clear-filter" href="<?php echo $APPLICATION->GetCurPage(); ?>">Сбросить фильтр</a>
		<input type="submit" id="selected-filter-top" class="btn btn-primary" name="select-date-range-submit" value="Применить" />
		
	</form>
	</div>
	<!--
	<div class="data-range">
		<table class="table">
			<tbody>
				<tr>
					<td><span class="label">Начальная дата:</span></td>
					<td><span><?php echo $arResult['START_DATE']; ?></span></td>
				</tr>
				<tr>
					<td><span class="label">Конечная дата:</span></td>
					<td><span><?php echo $arResult['FINISH_DATE']; ?></span></td>
				</tr>
				<tr>
					<td><span class="label">Группа:</span></td>
					<td><span><?php echo $arResult['CURRENT_GROUP_NAME']; ?></span></td>
				</tr>
				<tr>
					<td><span class="label">Пользователь:</span></td>
					<td><span><?php echo $arResult['CURRENT_USER_FIO']; ?></span></td>
				</tr>
			</tbody>
		</table>
	</div>-->
	<div style="clear: both"></div>
	
	
	<div class="scrolable-element" id="scrolable-element">
	
	<div id="options-timelog" class="options-timelog">
		<div id="options-btn" class="btn-opt"></div>		
	</div>
	
	<table id="timelog_table" class="table table-striped">
		<colgroup>
			<col class="col1">
			<col class="col2">
			<col id="count-columns" span="0" class="coln">
		</colgroup>
		<thead id="header-table-timelog">
			<tr>
			<th>
				ФИО
			</th>
			<th>ВСЕГО</th>
			<?
				$dateFrom = $arResult['DATE_FROM']; 
				while($dateFrom < $arResult['DATE_TO']){
			?>
				<th data-time="<?=date("Y-m-d", strtotime($dateFrom))?>">
				<?php echo date("d", strtotime($dateFrom)); ?></th>
			<?
					$dateFrom = date("Y-m-d", strtotime($dateFrom . " +1 day"));
				}
				$dateFrom = $arResult['DATE_FROM']; 
			?>
		</thead>
		
			<tbody>
			<?
			// echo '<pre>'; print_r($arResult['TIMELOG']); exit();
			if(count($arResult['TIMELOG']) > 0){
				$timelog = new Timelog();

				foreach($arResult['TIMELOG'] as $keyFio => $timelogValues){ 
				?>
					<tr>
					<td data-id="<?=$timelogValues['ID']?>"><? echo $keyFio; ?></td>
						<? // ob_clean(); echo '<pre>'; print_r($timelogValues); exit();
							$tds = '';
							$totalTime = '0:0';
							//for($i = 1; $i <= $arResult['TOTAL_DAYS']; $i++){
							$dateFrom = $arResult['DATE_FROM']; 
							/*ob_clean(); echo $dateFrom; 
							echo '<pre>'; print_r($timelogValues);
							exit();*/
							while($dateFrom < $arResult['DATE_TO']){
								if(isset($timelogValues[$dateFrom])){ 
									$totalTime = $timelog->summTime($totalTime, $timelogValues[$dateFrom]['time']);
									$tds .= '<td style="background-color: ' . $timelogValues[$dateFrom]['class'] . ';">' . $timelogValues[$dateFrom]['time'] . '</td>';
								}else{
									$tds .= '<td style="background-color: ' . $arResult['COLOR_FOR_ZERO']  .';">0:0</td>';
								}
								$dateFrom = date("Y-m-d", strtotime($dateFrom . " +1 day"));
							}
						?>
					<td><? echo $totalTime; ?></td>
					<? echo $tds; ?>
					</tr>
				<? } ?>
			<? }else{ ?>
			<tr>
				<td colspan="<? echo (2 + $arResult['TOTAL_DAYS']); ?>"><span class="notfound">Записи по текущей дате не найдены</span></td>
			</tr>
			<? }  ?>
			</tbody>
		
	</table>
	</div>
	
	<div id="timelog-footer" class="timelog-footer">
		<div class="pagination-left">
		<? if($arResult['PAGINATION_FINISH'] > 1){?>
			<p style="display:none;" class="pagination">Показано: <? echo $arResult['CURRENT_ROW_ON_PAGE']; ?> из <? echo $arResult['TOTAL_ROWS']; ?> записей.</p>
			<nav>
			<ul class="pagination">
				<? for($i = $arResult['PAGINATION_START']; $i <= $arResult['PAGINATION_FINISH']; $i++){ ?>
					<? if($i != $arResult['CURRENT_COUNT_PAGE']){?>
						<li><a href="#" class="href-pagination" data-page="<? echo $i; ?>"><? echo $i; ?></a></li>
					<? }else{ ?>
						<li class="active"><a href="<? echo $_SERVER['REQUEST_URI']; ?>"><? echo $i; ?> <span class="sr-only">(current)</span></a></li>
					<? } ?>
				<? } ?>
			</ul>
			</nav>
		<? } ?>
		</div>
		<div class="upload">
			<button id="export-csv" class="btn btn-primary">Экспорт в CSV</button>
			<button id="clearcsv" class="btn btn-danger">Очистить выгрузки</button>
			
		</div>
	</div>
</div>
<? $a = json_encode($arResult['SECTIONS']); ?>
<script>
	var timelog;
    var setMenu;
	window.onload = function(){
		var option = {
			//year : <? echo (int)$arResult['CURRENT_YEAR']; ?>,
			//month : <? echo (int)$arResult['CURRENT_MONTH']; ?>,
			userId : <? echo (int)$arResult['CURRENT_USER_ID']; ?>,
			groupId : <? echo (int)$arResult['CURRENT_GROUP_ID']; ?>,
			dateFrom : '<? echo $arResult['DATE_FROM']; ?>',
			dateTo : '<? echo $arResult['DATE_TO']; ?>',
			mode : <? echo $arResult['MODE']; ?>,
			pathForExportCsv : location.protocol + '//' + location.host + '/bitrix/components/expertit/timelogger/export/csv/',
			pathForClearCsv : location.protocol + '//' + location.host + '/bitrix/components/expertit/timelogger/export/csv/clear.php',
			pathForGetUser : location.protocol + '//' + location.host + '/bitrix/components/expertit/timelogger/ajax/user.php',
		};
		timelog = new Timelog(option);
		timelog.init();
		
		
		timelogPopUp = new TimelogPopUp(
			<?='\''.$arResult['USER_RIGHTS'].'\''?>,
			<?= json_encode($arResult['COLOR_TYPES'])?>,
			<?= (int)$arResult['CURRENT_USER_ID']?>
		);
		timelogPopUp.Init();
		timelogPopUp.InitDocHandlers();

        setMenu = new SetMenu();
        setMenu.init(<? echo $a; ?>);
	};
	
	
</script>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
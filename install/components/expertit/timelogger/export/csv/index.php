<?php 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $DB, $APPLICATION;

$APPLICATION->RestartBuffer();

 if(!CModule::IncludeModule('expertit.timelog')){
	echo 'Модуль "Мониторинг рабочего процесса" не установлен';
	exit();
}
/*
$year = '';
$month = '';
if(isset($_GET['year'])){
	$year = $_GET['year'];
}else{
	$year = date('Y');
}

if(isset($_GET['month'])){
	$month = $_GET['month'];
}else{
	$month = date('m');
}
*/

$timelog = new Timelog();

$sql = '';
if(isset($_GET['user'])){
	$sql .= ' AND b_user.ID = ' . '\'' . $_GET['user'] . '\'';
}elseif(isset($_GET['group'])){
	$users = $timelog->getUsersByGroupId($_GET['group']);
	$userIds = array();
	for($i = 0; $i < count($users); $i++){
		$userIds[] = $users[$i]['ID'];
	}
	$sql .= ' AND b_user.ID IN(\'' . implode('\',\'', $userIds) . '\')';
}

$userId = 'all';
if(isset($_GET['user'])){
	$userId = $_GET['user'];
}

$dataCurrent = '';
if(isset($_GET['from'])){
	$dataCurrent = $_GET['from'];
}else{
	$dataCurrent = date('Y') . '-' . date('m') . '-01';
}

$dataCurrentEnd = '';
if(isset($_GET['to'])){
	$dataCurrentEnd = $_GET['to'];
}else{
	//$dateTemp = date('Y') . '-' . date('m') . '-01';
	$dataCurrentEnd = date("Y-m-d", strtotime($dataCurrent . " +1 month"));
}

/*
if((int)$month < 10){
	$month = '0' . $month;
}*/

//$dataCurrent = $year . '-' . $month . '-01';
//$dataCurrentEnd = date("Y-m-d", strtotime($dataCurrent . " +1 month"));
$datetime1 = date_create($dataCurrent);
$datetime2 = date_create($dataCurrentEnd);
$interval = date_diff($datetime1, $datetime2);
$totalDays = $interval->days;

$pathFileCsv = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/expertit/timelogger/export/csv/uploads/' . date('Y-m-d-h-m-i') . $dataCurrent . '-' . $dataCurrentEnd . '-' . $userId . '.csv';

if(!is_dir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/expertit/timelogger/export/csv/uploads/')){
	mkdir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/expertit/timelogger/export/csv/uploads/');
}

$res = $DB->query('SELECT p.NAME, p.LAST_NAME, p.SECONDS, p.CREATED_DATE  
				FROM (SELECT b_user.NAME, b_user.LAST_NAME, SUM(b_tasks_elapsed_time.SECONDS) AS SECONDS, date(b_tasks_elapsed_time.CREATED_DATE) AS CREATED_DATE 
				FROM b_user
				INNER JOIN b_tasks_elapsed_time ON b_tasks_elapsed_time.USER_ID = b_user.ID
				WHERE b_tasks_elapsed_time.CREATED_DATE BETWEEN \'' . $dataCurrent . '\' AND \'' . $dataCurrentEnd . '\' ' . $sql . '
				GROUP BY b_user.NAME, b_user.LAST_NAME, date(b_tasks_elapsed_time.CREATED_DATE)) p');

$timelogs = array();
$r = array();
while($row = $res->fetch()){
	$r[] = $row;
	$fio = $row['NAME'] . ' ' . $row['LAST_NAME'];
	$createDate = $row['CREATED_DATE'];
	if(!isset($timelogs[$createDate])){
		$timelogs[$createDate] = array();
	}
	$timelogs[$createDate][$fio] = convertTime($row['SECONDS']);
}

$oldDay = 0;

//for($i = 1; $i <= $totalDays; $i++){
	/*$day = $i;
	if($i < 10){
		$day = '0' . $i;
	}*/
	/*if(!isset($timelogs[$year . '-' . $month . '-' . $day])){
		$timelogs[$year . '-' . $month . '-' . $day] = array();
	}*/
	$dateFrom = $arResult['DATE_FROM']; 
	while($dateFrom < $arResult['DATE_TO']){
		if(!isset($timelogs[$dateFrom])){
			$timelogs[$dateFrom] = array();
		}
		$dateFrom = date("Y-m-d", strtotime($dateFrom . " +1 day"));
	}
	
$fileHandler = fopen($pathFileCsv, 'w+');

$data = array();
$data[] = 'Дата/имя';

$users = maxCountArray($timelogs);
$users = additionalUsers($users, $timelogs);

ksort($users);

ksort($timelogs);

foreach($timelogs as $keyData => &$userTimelog){ 
	ksort($userTimelog);
}

$fios = array();
foreach($users as $keyFio => $time){
	$fios[] = $keyFio;
	$data[] = iconv('utf-8', 'windows-1251', $keyFio);
}

fputcsv($fileHandler, $data, ';');

foreach($timelogs as $keyData2 => $userTimelog2){
	$data = array();
	$data[] = $keyData2;
	for($i = 0; $i < count($fios); $i++){
		if(isset($userTimelog2[$fios[$i]])){
			$data[] = $userTimelog2[$fios[$i]];
		}else{
			$data[] = '0:0';
		}
	}
	fputcsv($fileHandler, $data, ';');
}
fclose($fileHandler);

header("Content-Type: application/octet-stream");
header("Accept-Ranges: bytes");
header("Content-Length: ".filesize($pathFileCsv));  
header("Content-Disposition: attachment; filename=".$pathFileCsv);  
readfile($pathFileCsv);

function convertTime($seconds){
	$hourses = $seconds / 60.0 / 60.0;
	$ost = $hourses - floor($hourses);
	$hourses -= $ost;
	$minutes = $ost * 60;
	$ostmin = $minutes - floor($minutes);
	$minutes -= $ostmin;
	
	if(((int)$minutes) < 10){
		$minutes = '0' . $minutes;
	}

	return $hourses . ':' . $minutes;;
}

function maxCountArray($timelogs){
	$result = array();
	$totalMax = 0;
	
	foreach($timelogs as $keyData => $userTimelog){
		$count = count($userTimelog);
		if($count > $totalMax){
			$totalMax = $count;
			$result = $userTimelog;
		}
	}
	
	return $result;
}

function additionalUsers($users, $timelogs){
	
	$fios = array_keys($users);
	foreach($timelogs as $date => $usersItem){
		foreach($usersItem as $keyFio => $seconds){
			if(!in_array($keyFio, $fios)){
				$users[$keyFio] = $seconds;
				$fios[] = $keyFio;
			}
		}
	}
	
	return $users;
}
?>
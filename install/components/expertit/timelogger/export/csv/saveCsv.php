<?php 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $DB, $APPLICATION;

$APPLICATION->RestartBuffer();
$sql = '';
$userIds = [];

$access_timelog = $APPLICATION->GetUserRight("expertit.timelog");

if($access_timelog != 'W' && in_array(1, $USER->GetUserGroup($USER->GetId()))){
	$access_timelog = 'W';
}

if($access_timelog < "R" || IsModuleInstalled("expertit.timelog") === false){
	echo "ACCESS DENIED";
	exit();
}

if(!CModule::IncludeModule('expertit.timelog')){
	echo 'Модуль "Мониторинг рабочего процесса" не установлен';
	exit();
}

$arResult = array();
if (isset($_GET['clearFilter'])) $_SESSION["FILTER_OPTION"] = null;

if (isset($_SESSION["FILTER_OPTION"]) && is_array($_SESSION["FILTER_OPTION"])) {
	$filter =  [];
	foreach ($_SESSION["FILTER_OPTION"] as $key => $value) $filter[$key] = $value;	
}

$dataCurrent = '';
if(isset($_GET['from'])){
	$dataCurrent = $_GET['from'];
	$_SESSION["FILTER_OPTION"]["DATA_CURRENT"] = $_GET['from'];
	if (!isset($_GET['to'])) $_SESSION["FILTER_OPTION"]["DATA_CURRENT_END"] = date("Y-m-d", strtotime($dataCurrent . " +1 month"));
}else{
	$dataCurrent = (isset($filter["DATA_CURRENT"])) ? 
		$filter["DATA_CURRENT"] : date('Y') . '-' . date('m') . '-01';
} 

$dataCurrentEnd = '';
if(isset($_GET['to'])){
	$dataCurrentEnd = $_GET['to'];
	$_SESSION["FILTER_OPTION"]["DATA_CURRENT_END"] = $_GET['to'];
}else{
	$dataCurrentEnd = (isset($filter["DATA_CURRENT_END"])) ? 
		$filter["DATA_CURRENT_END"] : date("Y-m-d", strtotime($dataCurrent . " +1 month"));
}

$datetime1 = date_create($dataCurrent);
$datetime2 = date_create($dataCurrentEnd);

$interval = date_diff($datetime1, $datetime2);

$userId = 0;
$userIds = [];
$users = [];
$arStaffId =[];

$timelog = new Timelog();

$rangeDate = $timelog->getRangeDate();
// ----------------------------------------------------------------------------------------------------------
$sectionId = 0;
$sections = $timelog->getSection();

$ownSectionId = 0;
$ownUserId = 0;

if (isset($filter["SECTION"])) $ownSectionId = intval($filter["SECTION"]);
if (isset($_GET['section'])) {
	$ownSectionId = intval($_GET['section']);
	$_SESSION["FILTER_OPTION"]["SECTION"] = intval($_GET['section']);
}

if (isset($filter["USER"])) $ownUserId = intval($filter["USER"]);
if (isset($_GET['user'])) {
	$ownUserId = intval($_GET['user']);
	$_SESSION["FILTER_OPTION"]["USER"] = intval($_GET['user']);
}

$arrLevelList = [];
if ($ownSectionId) {
	$level = 1;	
	$arrLevelList = Timelog::getLevelList($ownSectionId, $sections, $level, $arrLevelList);
	if (count($arrLevelList) == 0) $ownSectionId = 0;
}

if ($ownSectionId) {
    $sectionId = $ownSectionId;
    $arSectionId = $timelog->getArSection($sectionId, $sections);
    $users = $timelog->getUsersByArSectionId($arSectionId);
    $userIds = array_keys($users);
} elseif ($ownUserId) {
    $userId = $ownUserId;
    $userIds = [$ownUserId];
} else {
    $users = $timelog->getUsers();
    $userIds = array_keys($users);
}
// ---------------------------------------------------------------------------------
if ($access_timelog != 'W') {

    $arDepartamentId = [];
	$arMyDepartamentId = $timelog->getArDepartamentByHead($USER->GetId());

    foreach($arMyDepartamentId as $depId)
        $arDepartamentId = array_merge($arDepartamentId, $timelog->getArSection($depId, $sections));

    $arStaffId = array_keys($timelog->getUsersByArSectionId($arDepartamentId));
    $arStaffId = array_unique( array_merge( $arStaffId , [ intval($USER->GetId()) ] ) );

    $userIds = array_intersect($userIds, $arStaffId);
}
// -----------------------------------------------------------------------------------------
$datetime1 = date_create($dataCurrent);
$datetime2 = date_create($dataCurrentEnd);
$interval = date_diff($datetime1, $datetime2);
$totalDays = $interval->days;

$pathFileCsv = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/expertit/timelogger/export/csv/uploads/' . date('Y-m-d-h-m-i') . $dataCurrent . '-' . $dataCurrentEnd . '-' . $userId . '.csv';

if(!is_dir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/expertit/timelogger/export/csv/uploads/')){
	mkdir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/expertit/timelogger/export/csv/uploads/');
}
$sql = " AND e.USER_ID IN('" . implode("','", $userIds) . "')";
$strQuery = "SELECT e.USER_ID, u.NAME, u.LAST_NAME,u.WORK_DEPARTMENT,u.WORK_POSITION,e.TASK_ID, t.TITLE, t.GROUP_ID, date(e.CREATED_DATE) AS CREATED_DATE, sum(e.SECONDS) AS SEC
	FROM b_tasks_elapsed_time AS e
	INNER JOIN b_tasks AS t ON t.ID = e.TASK_ID AND t.ZOMBIE = 'N'
	INNER JOIN b_user AS u ON u.ID = e.USER_ID
	WHERE e.CREATED_DATE BETWEEN '" . $dataCurrent . "' AND '" . $dataCurrentEnd . "'  " . $sql . " 
	GROUP BY  date(e.CREATED_DATE), e.TASK_ID, e.USER_ID
	ORDER BY e.CREATED_DATE, u.LAST_NAME";
$res = $DB->query($strQuery);
if (!is_object($res)) return false;
$result = [];
$arGroupId = [];
$arGroup = [];

while($row = $res->fetch())
{
	$result[] = $row;
	if (intval($row["GROUP_ID"])) $arGroupId[] = intval($row["GROUP_ID"]);
}

$arGroupId = array_unique($arGroupId);

if (!empty($arGroupId)) {
	//$strQuery = "SELECT g.ID, g.NAME, u.UF_PROJECT_CLIENT, u.UF_1C_NUMBER From b_sonet_group AS g LEFT JOIN b_uts_sonet_group AS u ON u.VALUE_ID = g.ID WHERE g.ID IN ('" . implode("','", $arGroupId) . "') ";	
	$strQuery = "SELECT g.ID, g.NAME, u.UF_PROJECT_CLIENT, u.UF_1C_NUMBER, c.TITLE From b_sonet_group AS g LEFT JOIN b_uts_sonet_group AS u ON u.VALUE_ID = g.ID LEFT JOIN b_crm_company AS c ON c.ID = u.UF_PROJECT_CLIENT WHERE g.ID IN ('" . implode("','", $arGroupId) . "')";		
	$res = $DB->query($strQuery);
	if (is_object($res)) {
		while($row = $res->fetch()) $arGroup[$row["ID"]] = $row;
	}
}

$data = [];
$data[] = ["Учет затраченного времени c " . $dataCurrent . " по " . $dataCurrentEnd,"","","",""];
$data[] = ["Дата", "Пользователь", "Бренд", "Должность", "Проект", "Клиент", "Номер проекта из 1С", "Задача", "Время"];

foreach ($result as $value) 
{
	$project = (isset($arGroup[$value["GROUP_ID"]])) ? $arGroup[$value["GROUP_ID"]]["NAME"] : "";
	$project1C = (isset($arGroup[$value["GROUP_ID"]])) ? $arGroup[$value["GROUP_ID"]]["UF_1C_NUMBER"] : "";
	$projectClient = (isset($arGroup[$value["GROUP_ID"]])) ? $arGroup[$value["GROUP_ID"]]["TITLE"] : "";
	$data[] = [
		$value["CREATED_DATE"],
		$value["NAME"]." ".$value["LAST_NAME"],
		$value["WORK_DEPARTMENT"],
		$value["WORK_POSITION"],
		$project,
		$projectClient,
		$project1C,
		$value["TITLE"],
		getStrTime($value["SEC"])
	];
}

$handle = fopen($pathFileCsv, "w+");
$status = true;
foreach ($data as $row)
{
    $len = fputcsv($handle, $row, ";");
    if ($len === false) $status = false;
}
fclose($handle);

header("Content-Type: application/octet-stream");
header("Accept-Ranges: bytes");
header("Content-Length: ".filesize($pathFileCsv));  
header("Content-Disposition: attachment; filename=".$pathFileCsv);  
readfile($pathFileCsv);

function getStrTime($second)
{ 
	$str = [];
	$hour = floor($second / 3600);
	$second -= $hour * 3600;
	$minute = floor($second / 60);
	$second -= $minute * 60;
	
	if (!$hour) $hour = 0;
	if (!$minute) $minute = 0;
	if (!$second) $second = 0;

	return sprintf("%02d:%02d:%02d",$hour,$minute,$second);
	/*
	if ($hour) {
		$str[] = $hour;
		$str[] = " ч. ";
	}
	if ($minute) {
		$str[] = $minute;
		$str[] = " мин. ";
	}
	$str[] = $second;
	$str[] = " сек.";

	return implode(" ",$str);
	*/
}
<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); 

global $APPLICATION, $USER;


$APPLICATION->AddHeadScript($_SERVER['DOCUMENT_ROOT']."/bitrix/components/expertit/timelogger/js/main.js");



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
	$dataCurrentEnd = date("Y-m-d", strtotime($dataCurrent . " +1 month"));
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

if (isset($_GET['section'])) {
    $sectionId = intval($_GET['section']);
    $arSectionId = $timelog->getArSection($sectionId, $sections);
    $users = $timelog->getUsersByArSectionId($arSectionId);
    $userIds = array_keys($users);
} elseif (isset($_GET['user'])) {
    $userId = intval($_GET['user']);
    $userIds = [intval($_GET['user'])];
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
// ----------------------------------------------------------------------------------------------------------
$setCountOnPage = $DB->query('SELECT is_show_turn FROM t_options WHERE id = 2')->fetch()['is_show_turn'];
$page = 1;
if(isset($setCountOnPage)){
	$countOnPage = $setCountOnPage;
}else{
	$countOnPage = 20;	
}

$totalTimelogs = $timelog->totalTimelogs($userIds, $dataCurrent, $dataCurrentEnd);
$totalPage = ceil($totalTimelogs / $countOnPage);

if(isset($_GET['page'])){
	$page = $_GET['page'];
}

$offset = ($page * $countOnPage) - $countOnPage;

$countPagination = 10;
if($page <= 5){
	$paginationStart = 1;
	$paginationfinish = $page + ($countPagination - $page);
}elseif($page > 5 && $page + ($countPagination/2) <= $totalPage){
	$paginationStart = $page - ($countPagination/2);
	$paginationfinish = $page + ($countPagination/2) - 1;
}else{
	$paginationStart = $page - ($countPagination - ($totalTimelogs - $page)) + 1;
	$paginationfinish = $totalPage;
}

if($paginationfinish - $paginationStart < $countPagination-1){
	$paginationStart = $paginationfinish - $countPagination;
}

if($paginationfinish > $totalPage){
	$paginationfinish = $totalPage;
}
if($paginationStart < 1){
	$paginationStart = 1;
}

$timelogList = $timelog->getTimelog($userIds, $dataCurrent, $dataCurrentEnd, $offset, $countOnPage);
// ---------------------------------------------------------------------------------
$arResult['timelog_date_begin'] = $dataCurrent;
$arResult['timelog_date_end'] = $dataCurrentEnd;
$arResult['TIMELOG'] = $timelogList;
$arResult['COLOR_TYPES'] = $timelog->getColorByType();

$arResult['USERS'] = $users;
$arResult['GROUPS'] = $groups;
$arResult['MIN_YEAR'] = date("Y", strtotime($rangeDate['MIN_YEAR']));
$arResult['MAX_YEAR'] = date("Y", strtotime($rangeDate['MAX_YEAR']));
$arResult['TOTAL_DAYS'] = $interval->days;
$arResult['TOTAL_PAGINATION_PAGE'] = $totalPage;

$arResult['CURRENT_YEAR'] = date("Y", strtotime($dataCurrent));
$arResult['CURRENT_MONTH'] = date("m", strtotime($dataCurrent));
$arResult['CURRENT_USER_FIO'] = $timelog->getUserFio($userId, $users);
$arResult['CURRENT_GROUP_NAME'] = $timelog->getGroupName($groupId, $groups);
$arResult['CURRENT_GROUP_ID'] = $groupId;
$arResult['CURRENT_USER_ID'] = $userId;
$arResult['CURRENT_COUNT_PAGE'] = $page;
$arResult['CURRENT_ROW_ON_PAGE'] = count($arResult['TIMELOG']);
$arResult['TOTAL_ROWS'] = $totalTimelogs;
$arResult['USER_RIGHTS'] = $access_timelog;
// ------------------------------------------
$arResult['SECTIONS'] = $sections;
$arResult['sectionId'] = $sectionId;
$arResult['arSectionId'] = $arSectionId;
$arResult['userIds'] = $userIds;
$arResult['users'] = $users;
$arResult['filterUserIds'] = $filterUserIds;

$arResult['arMyDepartamentId'] = $arMyDepartamentId;
$arResult['arStaffId'] = $arStaffId;
$arResult['arDepartamentId'] = $arDepartamentId;
// -------------------------------------------
$date1 = date('Y-m') . '-01';
$date2 = date("Y-m-d", strtotime($dataCurrent . " +1 month"));
/*
echo $date1.' - ' . $dataCurrent . '<br />';
echo $date2.' - ' . $dataCurrentEnd. '<br />';
*/
if($date1 == $dataCurrent && $date2 == $dataCurrentEnd){
	$arResult['MODE'] = 1;
}else{
	$arResult['MODE'] = 2;
}

$arResult['DATE_FROM'] = $dataCurrent;
$arResult['DATE_TO'] = $dataCurrentEnd;

$arResult['PAGINATION_START'] = $paginationStart;
$arResult['PAGINATION_FINISH'] = $paginationfinish;

$arResult['START_DATE'] = date("Y-m-d", strtotime($dataCurrent));
$arResult['FINISH_DATE'] = date("Y-m-d", strtotime($dataCurrentEnd . " -1 day"));

$arResult['COLOR_FOR_ZERO'] = $timelog->getColor(0, 0);

$this->IncludeComponentTemplate();
?>
<?php
function  mom_log($MySTR, $FILE = '/log/momLog.txt')
{
    $date = date("d-m-Y H:i:s");
    $fp = fopen($_SERVER["DOCUMENT_ROOT"] . $FILE, 'a+');
    $str = $date . " " . print_r($MySTR,true). "\r\n";
    fwrite($fp, $str);
    fclose($fp);
}

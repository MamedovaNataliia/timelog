<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); 

global $APPLICATION, $USER;

$access_timelog = $APPLICATION->GetUserRight("expertit.timelog");

if($access_timelog != 'W' && in_array(1, $USER->GetUserGroup($USER->GetId()))){
	$access_timelog = 'W';
}

if($access_timelog < "W" || IsModuleInstalled("expertit.timelog") === false){
	echo "ACCESS DENIED";
	exit();
}

if(!CModule::IncludeModule('expertit.timelog')){
	echo 'Модуль "Мониторинг рабочего процесса" не установлен';
	exit();
}

$arResult = array();

$times = new Times();
$checkbox = new Dbcheckbox();
	

$arResult['TIMES'] = $times->getAll();
$arResult['CHECKBOXES'] = $checkbox->getAll();

$this->IncludeComponentTemplate();
?>
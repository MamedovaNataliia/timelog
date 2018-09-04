<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

global $DB, $APPLICATION;

$APPLICATION->RestartBuffer();
 
 if(!CModule::IncludeModule('expertit.timelog')){
	echo 'Модуль "Мониторинг рабочего процесса" не установлен';
	exit();
}

 $timelog = new Timelog();
 $users = array();
 
$groupId = $_GET['group_id'];
 
if($groupId != 0 && $groupId != 'all'){
	$users = $timelog->getUsersByGroupId($_GET['group_id']);
}else{
	$users = $timelog->getUsers();
}

echo json_encode($users);
?>
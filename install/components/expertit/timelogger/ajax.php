<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

global $APPLICATION;

$APPLICATION->RestartBuffer();

header('Content-Type: application/json');

if(!CModule::IncludeModule('expertit.timelog')){
	echo json_encode('Модуль "Мониторинг рабочего процесса" не установлен');
	exit();
}


function getName(){
	global $class;
	return $class;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	CModule::IncludeModule('expertit.knowledge');
	
	$GLOBALS['class'] = $_REQUEST['class']; 
	global $class;
	
    if(isset($_REQUEST['action']) && isset($_REQUEST['class']) && file_exists(dirname(__FILE__) . '/ajax/' . $class . '.php')){
        $action = $_REQUEST['action'];
        require_once(dirname(__FILE__) . '/ajax/' . getName() . '.php');
        $obj = new $class($_REQUEST);
        echo json_encode($obj->$action());
    }else{
       echo json_encode(array('result' => 'error1 '.dirname(__FILE__) . '/ajax/' . $class . '.php', 'message' => 'Не указаны параметры запроса!'));
    }
	
	
}else{
	echo json_encode(array('result' => 'error2', 'message' => 'Не разрешенный метод запроса!'));
}

//require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();
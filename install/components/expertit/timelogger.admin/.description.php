<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => 'timelogger.admin',
	"DESCRIPTION" => "Мониторинг рабочего процесса: Режим администрирования",
	"ICON" => "/images/icon/icon.gif",
	"COMPLEX" => "N",
	"PATH" => array(
	  "ID" => "ExpertIT",
	  "CHILD" => array(
	     "ID" => "timelogger.admin",
	     "NAME" => "Мониторинг рабочего процесса: Режим администрирования""
	  )
	),
);
?>
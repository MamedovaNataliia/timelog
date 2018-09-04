<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => 'timelogger',
	"DESCRIPTION" => "Мониторинг рабочего процесса",
	"ICON" => "/images/icon/icon.gif",
	"COMPLEX" => "N",
	"PATH" => array(
	  "ID" => "ExpertIT",
	  "CHILD" => array(
	     "ID" => "timelogger",
	     "NAME" => "Мониторинг рабочего процесса"
	  )
	),
);
?>
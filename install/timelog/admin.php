<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мониторинг рабочего времени: Режим администрирования");
?><?$APPLICATION->IncludeComponent(
	"expertit:timelogger.admin",
	"",
Array()
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
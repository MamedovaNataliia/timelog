<?

global $APPLICATION;

$aMenuKnowlege = [];
if($APPLICATION->GetUserRight("expertit.timelog") >= "R"){
	$aMenuKnowlege[] = [
		"Рабочее время",
		"/timelog/",
		Array(),
		Array(),
		"",
	];
}
if($APPLICATION->GetUserRight("expertit.timelog") >= "W"){
	$aMenuKnowlege[] = [
		"Режим администрирования",
		"/timelog/admin.php",
		Array(),
		Array(),
		"",
	];
}
$aMenuLinks = array_merge($aMenuKnowlege, $aMenuLinks);
?>

<? 
global $APPLICATION; 
$APPLICATION->SetTitle("Мониторинг рабочего процесса");

CModule::AddAutoloadClasses(
'expertit.timelog',
	array(
		'Edition' => 'classes/general/edition.php',
		'Timelog' => 'classes/general/timelog.php',
		'Times' => 'classes/general/times.php',
		'Dbcheckbox' => 'classes/general/dbcheckbox.php',
		'Dbpopuptimelog' => 'classes/general/dbpopuptimelog.php'
	)
);

?>
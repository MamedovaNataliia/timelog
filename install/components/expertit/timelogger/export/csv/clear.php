<?php
	
	$exportPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/expertit/timelogger/export/csv/uploads/';
	
	$files = scandir($exportPath);
	
	unset($files[0]);
	unset($files[1]);
	
	$files = array_values($files);
	
	$totalFiles = count($files);
	
	for($i = 0; $i < count($files); $i++){
		unlink($exportPath . $files[$i]);
	}
	
	echo $totalFiles;
?>
<?php 

IncludeTemplateLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

class Edition {
	
	public function __construct(){
		
	}
	
	/**
		Проверяет на корпоративный портал, возвращает true если редакция является корпоративным порталом
	*/
	public static function is_corp_portal(){
		$errorMessage = '';
		$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
		$arUpdateList = CUpdateClient::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly);
		$edition = $arUpdateList["CLIENT"][0]["@"]["LICENSE"];
		
		if(strripos($edition, 'Битрикс24') !== false || strripos($edition, 'Bitrix24') !== false){
			return true;
		}
		
		return false;
	}
}


?>
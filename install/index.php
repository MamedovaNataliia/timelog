<?

global $USER, $APPLICATION;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/condition.php");

if(!$USER->CanDoOperation('fileman_edit_menu_elements'))
	$APPLICATION->AuthForm("ACCESS_DENIED");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

class expertit_timelog extends CModule{
	const MODULE_ID = 'expertit.timelog';
	var $MODULE_ID = "expertit.timelog";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	public function expertit_timelog(){
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)){
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else{
			$this->MODULE_VERSION = "1.0.2";
			$this->MODULE_VERSION_DATE = "2016-05-12 17:11:02";
		}

		$this->MODULE_NAME = "Мониторинг рабочего процесса";
		$this->MODULE_DESCRIPTION = "Мониторинг рабочего процесса и времени...";
		$this->PARTNER_NAME = "SKALAR"; 
		$this->PARTNER_URI = "http://skalar.com.ua";
	}
	
	public function GetModuleTasks(){
		return array(
			'Просмотр' => array(
				"LETTER" => "R",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'Просмотр',
				),
			),
			'Редактирование' => array(
				"LETTER" => "W",
				"BINDING" => "module",
				"OPERATIONS" => array('Просмотр', 'Редактирование'),
			),
			'Полный доступ' => array(
				"LETTER" => "Z",
				"BINDING" => "module",
				"OPERATIONS" => array('Просмотр', 'Редактирование', 'Полный доступ'),
			),
			'Доступ запрещен' => array(
				"LETTER" => "D",
				"BINDING" => "module",
				"OPERATIONS" => array('Доступ запрещен'),
			),
		);
	}
	
	public function InstallFiles($is_corp = true){
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $this->MODULE_ID . "/install/components/expertit", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/expertit", true, true); 
		if($is_corp)CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $this->MODULE_ID . "/install/timelog", $_SERVER["DOCUMENT_ROOT"]."/timelog");
	}
	
	function SaveMenu($path, $aMenuLinksTmp, $sMenuTemplateTmp){
		global $APPLICATION;
		
		$strMenuLinks = "";
		if(strlen($sMenuTemplateTmp)>0)
			$strMenuLinks .= "\$sMenuTemplate = \"".CFileMan::EscapePHPString($sMenuTemplateTmp)."\";\n";

		$strMenuLinks .= "\$aMenuLinks = Array(";
		$i=0;
		foreach($aMenuLinksTmp as $arMenuItem)
		{
			$i++;
			$strMenuLinksTmp = "";

			if($i>1)
				$strMenuLinksTmp .= ",";

			$strMenuLinksTmp .= "\n".
				"	Array(\n".
				"		\"".CFileMan::EscapePHPString($arMenuItem[0])."\", \n".
				"		\"".CFileMan::EscapePHPString($arMenuItem[1])."\", \n".
				"		Array(";

			if(is_array($arMenuItem[2]))
			{
				for($j = 0, $l = count($arMenuItem[2]); $j < $l; $j++)
				{
					if($j>0)
						$strMenuLinksTmp .= ", ";
					$strMenuLinksTmp .= "\"".CFileMan::EscapePHPString($arMenuItem[2][$j])."\"";
				}
			}
			$strMenuLinksTmp .= "), \n";

			$strMenuLinksTmp .= "		Array(";
			if(is_array($arMenuItem[3]))
			{
				$arParams = array_keys($arMenuItem[3]);
				for($j = 0, $l = count($arParams); $j < $l; $j++)
				{
					if($j>0)
						$strMenuLinksTmp .= ", ";
					$strMenuLinksTmp .= "\"".CFileMan::EscapePHPString($arParams[$j])."\"=>"."\"".CFileMan::EscapePHPString($arMenuItem[3][$arParams[$j]])."\"";
				}
			}

			$strMenuLinksTmp .= "), \n".
				"		\"".CFileMan::EscapePHPString($arMenuItem[4])."\" \n".
				"	)";

			$strMenuLinks .= $strMenuLinksTmp;
		}
		$strMenuLinks .= "\n);";
		$APPLICATION->SaveFileContent($path, "<"."?\n".$strMenuLinks."\n?".">");
		$GLOBALS["CACHE_MANAGER"]->CleanDir("menu");
		CBitrixComponent::clearComponentCache("bitrix:menu");
	}
	
	public function InstallMenu(){
		CModule::IncludeModule('fileman');
		$menu = CFileMan::GetMenuArray($_SERVER['DOCUMENT_ROOT']."/.top.menu.php");
		
		$aMenuLinksTmp = array(
			0 => 'Мониторинг рабочего процесса',
			1 => '/timelog/',
			2 => false,
			3 => false,
			4 => "",
		);
		
		$aMenuLinksTmpAdmin = array(
			0 => 'Администрирование мониторинга рабочего процесса',
			1 => '/timelog/admin.php',
			2 => false,
			3 => false,
			4 => "",
		);
		
		$menu['aMenuLinks'][] = $aMenuLinksTmp;
		$menu['aMenuLinks'][] = $aMenuLinksTmpAdmin;
		
		$this->SaveMenu($_SERVER['DOCUMENT_ROOT']."/.top.menu.php", $menu['aMenuLinks'], $menu['sMenuTemplate']);
	}
	
	public function UnInstallMenu(){
		CModule::IncludeModule('fileman');
		
		$menu = CFileMan::GetMenuArray($_SERVER['DOCUMENT_ROOT']."/.top.menu.php");
		$new_menu = array();
		$new_menu['aMenuLinks'] = array();
		$new_menu['sMenuTemplate'] = $menu['sMenuTemplate'];
		
		if(is_array($menu['aMenuLinks']) && count($menu['aMenuLinks']) > 0){
			foreach($menu['aMenuLinks'] as $menuItem){
				if($menuItem[0] == 'Timelog' && $menuItem[4] == "" && $menuItem[1] == "/timelog/"){
					continue;
				}
				$new_menu['aMenuLinks'][] = $menuItem;
			}
		}
		
		$this->SaveMenu($_SERVER['DOCUMENT_ROOT']."/.top.menu.php", $new_menu['aMenuLinks'], $new_menu['sMenuTemplate']);
	}
	
	public function InstallDB(){
		global $APPLICATION, $DB, $DBType;
		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $this->MODULE_ID . "/install/db/".$DBType."/install.sql");
		if(!empty($errors)){
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}
	}
	
	public function UnistallDB(){
		global $APPLICATION, $DB, $DBType;
		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $this->MODULE_ID . "/install/db/".$DBType."/uninstall.sql");
		if(!empty($errors)){
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}
	}
	
	/*
		Определяем корректность редакции
	*/
	public function is_corp_portal(){
		$errorMessage = '';
		$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
		$arUpdateList = CUpdateClient::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly);
		$edition = $arUpdateList["CLIENT"][0]["@"]["LICENSE"];
		
		if(strripos($edition, 'Битрикс24') !== false || strripos($edition, 'Bitrix24') !== false){
			return true;
		}
		
		return false;
	}
	
	function DoInstall(){
		//если корп портал

		if(!$this->is_corp_portal()){
			//если не корп портал
			//$this->InstallFiles(false);
		}else{
			// если корп портал
			$this->InstallFiles();
			$this->InstallMenu();
			
			$urlRewriter = new CUrlRewriter();
			$rewriter = array(
				"CONDITION" => "#^/timelog/#",
				"RULE" => "",
				"ID" => "expertit:timelog",
				"PATH" => "/timelog/index.php",
			);
			$urlRewriter->Add($rewriter);
			$this->InstallTasks();
			$this->InstallDB();
			RegisterModule($this->MODULE_ID);
		}
	}
	public function DoUninstall(){

		global $step;
		$step = IntVal($step);
		
		if($this->is_corp_portal()){
			// если корп портал
			DeleteDirFilesEx("/timelog");
			$this->UnInstallMenu();
			$urlRewriter = new CUrlRewriter();
			$rewriter = array(
				"CONDITION" => "#^/timelog/#",
				"RULE" => "",
				"ID" => "expertit:timelog",
				"PATH" => "/timelog/index.php",
			);
			$urlRewriter->Delete($rewriter);
		}
		
		$this->UnInstallTasks();
		DeleteDirFilesEx("/bitrix/components/expertit/timelogger");
		DeleteDirFilesEx("/bitrix/components/expertit/timelogger.admin");
		$this->UnistallDB();
		COption::removeOption($this->MODULE_ID);
		
		UnRegisterModule($this->MODULE_ID);
	}
}
?>
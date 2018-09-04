<?php 

IncludeTemplateLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

class Times {
	
	public function __construct(){
		
	}
	
	public function getAll(){
		global $DB;
		
		$times = array();
		
		$res = $DB->query('SELECT * FROM t_color');
		
		for(;$row = $res->fetch();){
			$times[] = $row;
		}
		
		return $times;
	}
	
	public function save($data){
		global $DB;
		
		$arFields = array(
			'from_minutes' => '\'' . $data['fromMinutes'] . '\'',
			'from_seconds' => '\'' . $data['fromSeconds'] . '\'',
			'to_minutes' => '\'' . $data['toMinutes'] . '\'',
			'to_seconds' => '\'' . $data['toSeconds'] . '\'',
			'color_hex' => '\'' . $data['colorHex'] . '\'',
			't_type_id' => '\'' . $data['t_type_id'] . '\'',
		);
		
		return $DB->Insert('t_color', $arFields);
	}
	
	public function change($data){
		global $DB;
		
		$DB->query('UPDATE t_color SET from_minutes=\'' . $data['fromMinutes'] . '\', from_seconds=\'' . $data['fromSeconds'] . '\', to_minutes=\'' . 
		            $data['toMinutes'] . '\', to_seconds=\'' . $data['toSeconds'] . '\', color_hex=\'' . $data['colorHex'] . '\' WHERE id = \'' . $data['id'] . '\'');
		
	}
	
	public function deleteById($id){
		global $DB;
		$DB->query('DELETE FROM t_color WHERE id = \'' . $id .'\' LIMIT 1');
	}
}
?>
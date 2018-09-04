<?

class Dbcheckbox {
		
	public function __construct(){
		
	}
	
	public function CBOpt($id)
	{
		$options = [
			1 => 'Отображать информацию о перерывах',
			2 => 'Колличество пользователей на странице'
		];
		
		return $options[$id];
	}
	
	public function getAll()
	{
		global $DB;
		
		$checkboxes = array();
		
		$res = $DB->query('SELECT * FROM t_options');
		
		for(;$row = $res->fetch();){
			$row['title']= $this->CBOpt($row['id']);
			$checkboxes[] = $row;
		}
		
		return $checkboxes;
	}
	
	public function save($data){
		global $DB;
		
		$arFields = array(
			'id' => '\'' . $data['id'] . '\'',
			'is_show_turn' => '\'' . $data['is_show_turn'] . '\'',
		);
		
		return $DB->Insert('t_options', $arFields);
	}
	
	public function change($data){
		global $DB;
		
		$DB->query('UPDATE t_options SET is_show_turn=\'' .
		$data['is_show_turn'] .'\' WHERE id = \'' .$data['id'] . '\' LIMIT 1');
		
	}
	
	public function deleteById($id){
		global $DB;
		$DB->query('DELETE FROM t_options WHERE id = \'' . $id .'\' LIMIT 1');
	}
}

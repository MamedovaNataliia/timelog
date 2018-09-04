<?php

class Checkbox{
	
	private $data;
	
	public function Checkbox($data){
		$this->data = $data;
	}
	
	public function save(){
		$dbcheckbox = new Dbcheckbox();
		return $dbcheckbox->save($this->data);
	}
	
	public function change(){
		$dbcheckbox = new Dbcheckbox();
		$dbcheckbox->change($this->data);
	}
	
	public function delete(){
		$dbcheckbox = new Dbcheckbox();
		$dbcheckbox->deleteById($this->data['id']);
	}
}

?>
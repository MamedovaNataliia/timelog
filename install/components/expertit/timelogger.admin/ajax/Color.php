<?php

class Color{
	
	private $data;
	
	public function Color($data){
		$this->data = $data;
	}
	
	public function save(){
		$times = new Times();
		return $times->save($this->data);
	}
	
	public function change(){
		$times = new Times();
		$times->change($this->data);
		return 'ok';
	}
	
	public function delete(){
		$times = new Times();
		$times->deleteById($this->data['id']);
		return 'ok';
	}
	
}

?>
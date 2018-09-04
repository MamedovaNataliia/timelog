<?php

class Popuptimelog{
	
	private $data;
	
	public function Popuptimelog($data){
		$this->data = $data;
	}
	
	public function get_timeleaks_all()
	{
		$dbpopuptimelog = new Dbpopuptimelog();
		$res = $dbpopuptimelog->getTimeleaksAll($this->data);
		
		return $res;
	}
	
	public function pop_up_data()//Получение перерыва
	{ 
		$dbpopuptimelog = new Dbpopuptimelog();
		$res = $dbpopuptimelog->getPopUpInfo($this->data);
		
		$res['LEAKS']['TIME_LEAKS'] = intval($res['LEAKS']['TIME_LEAKS']);
		
		$res['h'] = intval($res['LEAKS']['TIME_LEAKS'] / 60 / 60) ;
		$res['m'] = intval($res['LEAKS']['TIME_LEAKS'] / 60 % 60) ;
		
		
		//$data_res['LEAKS'] = (($res['h']<9) ? "0".$res['h']:$res['h']).":".(($res['m']<9) ? "0".$res['m']:$res['m']);
		$data_res['LEAKS'] = (($res['h']<9) ? "0".$res['h']:$res['h']).":".(($res['m']<9) ? "0".$res['m']:$res['m']);
		$data_res['TASKS'] = $res['TASKS'];
		
		return $data_res;

	}
	
}

?>
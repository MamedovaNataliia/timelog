<?

class Dbpopuptimelog {
		
	public function __construct(){
		
	}
	
	public function getTimeleaks($data)
	{
		global $DB;
		
		$res = $DB->query('SELECT TIME_LEAKS FROM b_timeman_entries'.
		' WHERE USER_ID = \'' .$data['USER_ID'] . '\' AND DATE(DATE_START) = \''.$data['TIMESTAMP_X'].'\' LIMIT 1');
		
		return $res->fetch();
	}
	
	public function getTimeleaksAll($data)
	{
		global $DB;
		$timeleaks_all = array();
		
		$opt = $DB->query('SELECT * FROM t_options WHERE id = 1 LIMIT 1');
		$opt = $opt->fetch();
		if($opt['is_show_turn'] == 1)
		{
			
			$res = $DB->query('SELECT Date(DATE_START) as DATE_START,
			TIME_LEAKS, USER_ID FROM b_timeman_entries
			WHERE DATE(DATE_START) BETWEEN \''.$data['DATE_BEGIN'].'\' AND \''.$data['DATE_END'].'\'
			');
			
			
			while($row = $res->fetch()){
				$timeleaks_all[] = $row;
			}
		}

		return $timeleaks_all;
	}
	
	public function getTasks($data)
	{
		global $DB;
		
		$tasks = array();
		
		//b_tasks
		//b_sonet_group
		/* (SELECT DISTINCT SUM(e_time.SECONDS) FROM b_tasks_elapsed_time as e_time 
		WHERE e_time.USER_ID = '.$data['USER_ID'].' AND e_time.TASK_ID = task.ID) SECONDS */
		
		
		$res = $DB->query('SELECT e_time.TASK_ID, Date(e_time.CREATED_DATE),
		(SELECT DISTINCT SUM(e_time.SECONDS) FROM b_tasks_elapsed_time as e_time 
		WHERE e_time.USER_ID = '.$data['USER_ID'].' AND e_time.TASK_ID = task.ID
		AND Date(e_time.CREATED_DATE) = \''.$data['TIMESTAMP_X'].'\' GROUP BY TASK_ID,GROUP_ID) SECONDS
		, t_group.NAME as GROUP_NAME,task.TITLE as TASK_NAME, task.GROUP_ID FROM 
			b_tasks_elapsed_time as e_time
			INNER JOIN b_tasks as task on task.ID = e_time.TASK_ID
			INNER JOIN b_sonet_group as t_group on t_group.ID = task.GROUP_ID OR task.GROUP_ID = 0
			WHERE e_time.USER_ID = '.$data['USER_ID'].'
			AND Date(e_time.CREATED_DATE) = \''.$data['TIMESTAMP_X'].'\'
			GROUP BY TASK_ID, GROUP_ID
			'
			
		);
		
		for(;$row = $res->fetch();){
			$tasks[] = $row;
		}
	
		return $tasks;
	}
	
	public function getPopUpInfo($data)
	{		
		$data_res['LEAKS'] = $this->getTimeleaks($data);
		$data_res['TASKS'] = $this->getTasks($data);
		
		return $data_res;
	}
	
}

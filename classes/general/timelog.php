<?php 

IncludeTemplateLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

class Timelog {
	
	public function __construct(){
		
	}
	
	public function getTimelog($userId, $dataCurrent, $dataCurrentEnd, $offset, $countOnPage){
		$sql = '';
		if(!is_array($userId) && $userId > 0){
			$sql = ' AND b_user.ID = ' . $userId;
		}elseif(is_array($userId)){
			$sql = ' AND b_user.ID IN(\'' . implode('\',\'', $userId) . '\')';
		}
		
		global $DB;

		$res = $DB->query('SELECT b_user.ID
							FROM b_user 
							INNER JOIN b_tasks_elapsed_time ON b_tasks_elapsed_time.USER_ID = b_user.ID 
							INNER JOIN b_tasks on b_tasks.ID = b_tasks_elapsed_time.TASK_ID AND b_tasks.ZOMBIE = "N"
							WHERE b_tasks_elapsed_time.CREATED_DATE BETWEEN \'' . $dataCurrent . '\' AND \'' . $dataCurrentEnd . '\'' . $sql . ' 
							GROUP BY b_user.ID ORDER BY b_user.NAME, b_user.LAST_NAME ASC LIMIT ' . $offset . ', ' . $countOnPage);
		
		$userId = array();
		while($row = $res->fetch()){
			$userId[] = $row['ID'];
		}
		$sql = ' AND b_user.ID IN(\'' . implode('\',\'', $userId) . '\')';
		
		$res = $DB->query('SELECT p.ID, p.ID, p.NAME, p.LAST_NAME, p.SECONDS, p.CREATED_DATE  
							FROM (SELECT b_user.ID, b_user.NAME, b_user.LAST_NAME, SUM(b_tasks_elapsed_time.SECONDS) AS SECONDS, date(b_tasks_elapsed_time.CREATED_DATE) AS CREATED_DATE
							FROM b_user
							INNER JOIN b_tasks_elapsed_time ON b_tasks_elapsed_time.USER_ID = b_user.ID
							INNER JOIN b_tasks on b_tasks.ID = b_tasks_elapsed_time.TASK_ID AND b_tasks.ZOMBIE = "N"
							WHERE b_tasks_elapsed_time.CREATED_DATE BETWEEN \'' . $dataCurrent . '\' AND \'' . $dataCurrentEnd . '\' ' . $sql . '
							GROUP BY b_user.NAME, b_user.LAST_NAME, date(b_tasks_elapsed_time.CREATED_DATE)) p ORDER BY p.SECONDS DESC');	
							
		
		
		$timelog = array();
		while($row = $res->fetch()){
			$fio = $row['NAME'] . ' ' . $row['LAST_NAME'];
			if(!isset($timelog[$fio])){
				$timelog[$fio] = array();
			}
			$timelog[$fio]['ID'] = $row['ID'];
			
			$day = date('Y-m-d', strtotime($row['CREATED_DATE'])); 
			$timelog[$fio][$day] = $this->convertTime($row['SECONDS']);
		}
		
		return $timelog;
	}
	
	public function getUsers(){
		
		global $DB;
		
		$res = $DB->query('SELECT b_user.ID, b_user.NAME, b_user.LAST_NAME FROM b_user WHERE b_user.NAME != \'\' AND b_user.LAST_NAME != \'\' ORDER BY b_user.NAME, b_user.LAST_NAME ASC');
		
		$users = array();
		while($row = $res->fetch()){
			$users[$row["ID"]] = $row;
		}

		return $users;
	}
	
	public function getUsersByGroupId($groupId){
		global $DB;
		
		$res = $DB->query('SELECT b_user.ID, b_user.NAME, b_user.LAST_NAME 
							FROM b_user
							INNER JOIN b_user_group ON b_user_group.USER_ID = b_user.ID
							WHERE b_user.NAME != \'\' AND b_user.LAST_NAME != \'\'  AND b_user_group.GROUP_ID = \'' . $groupId . '\'
							ORDER BY b_user.NAME, b_user.LAST_NAME ASC');
		
		$users = array();
		while($row = $res->fetch()){
			$users[] = $row;
		}

		return $users;
	}
	
	public function getUserFio($userId, $users){
		$userFio = '';
		
		foreach($users as $user){
			if($user['ID'] == $userId){
				$userFio = $user['NAME'] . ' ' . $user['LAST_NAME'];
			}
		}
		
		if($userFio == ''){
			$userFio = iconv('windows-1251', 'utf-8', '���');
		}
		
		return $userFio;
	}
	
	public function getGroupName($groupId, $groups){
		$groupName = '';
		
		foreach($groups as $group){
			if($group['ID'] == $groupId){
				$groupName = $group['NAME'];
			}
		}
		
		if($groupName == ''){
			$groupName = iconv('windows-1251', 'utf-8', '���');
		}
		
		return $groupName;
	}

	// --------------------------------------------------
	public function getSection()
    {
        $maxLevel = 0;
        $rootId = 0;
        $arSection = [];
        $sectionTree = [];

        global $DB;
        $res = $DB->query("SELECT ID, IBLOCK_SECTION_ID, NAME, DEPTH_LEVEL FROM b_iblock_section WHERE IBLOCK_ID = 5 AND ACTIVE = 'Y'; ");

        while ($row = $res->fetch())
        {
            $arSection[intval($row["ID"])] = $row;
            if (intval($row["DEPTH_LEVEL"]) > $maxLevel) $maxLevel = intval($row["DEPTH_LEVEL"]);
            if (intval($row["DEPTH_LEVEL"]) == 1 && intval($row["IBLOCK_SECTION_ID"]) == 0) $rootId = intval($row["ID"]);
        }

        $sectionTree[$rootId] = [
            "ID" => $rootId,
            "NAME" => $arSection[$rootId]["NAME"],
            "CHILD" => self::getChild($rootId, $arSection, []),
        ];
        return $sectionTree;
    }

    public static function getChild($parentId, $arSection)
    {
        $arChild = [];
        foreach($arSection as $section)
        {
            if (intval($section["IBLOCK_SECTION_ID"]) != $parentId) continue;

            $id = intval($section["ID"]);
            $arChild[$id] = [
                "ID" => $id,
                "NAME" => $arSection[$id]["NAME"],
                "CHILD" => self::getChild($id, $arSection),
            ];
        }
        return $arChild;
    }

    public function getArSection($sectionId, $sections)
    {
        return self::getChildSection($sectionId, $sections, [], false);
    }

    public static function getChildSection($sectionId, $sections, $arr, $childFlag)
    {
        foreach($sections as $id => $section)
        {
            $flag =  (($id == $sectionId) || $childFlag) ? true : false ;
            if ($flag) $arr[] = $id;
            $arr = self::getChildSection($sectionId, $section["CHILD"], $arr, $flag);
        }
        return $arr;
    }

    public function getUsersByArSectionId($arSectionId)
    {
        $users = [];
        $filter = ["UF_DEPARTMENT" => $arSectionId];
        $param = ["SELECT" => ["UF_DEPARTMENT"], "FIELDS" => ["ID", "NAME", "LAST_NAME"]];

        $res = \CUser::GetList(($by = "LAST_NAME"), ($order = "asc"), $filter, $param);

        while($row = $res->fetch()) $users[$row["ID"]] = $row;

        return $users;
    }

    public function getArDepartamentByHead($headId)
    {
        $arDepartament = [];
        global $DB;
        $res = $DB->query("SELECT VALUE_ID FROM b_uts_iblock_5_section WHERE UF_HEAD = " . intval($headId));

        while ($row = $res->fetch()) $arDepartament[] = intval($row["VALUE_ID"]);

        return $arDepartament;
    }

    public static function getLevelList($sectionId, $sections, $level, $arr)
	{
		foreach ($sections as $key => $value) {

			if ($key == $sectionId) {
				$arr[$level] = $key;
				break; 
			} else {
				$arr = self::getLevelList($sectionId, $value["CHILD"], ($level + 1), $arr);
				if (count($arr) > 0) {
					$arr[$level] = $key;
					break; 
				}
			}
		}
		return $arr;
	}
	// --------------------------------------------------
	
	public function getRangeDate(){
		global $DB;
		$res = $DB->query('SELECT MIN(DATE(b_tasks_elapsed_time.CREATED_DATE)) AS MIN_YEAR, MAX(DATE(b_tasks_elapsed_time.CREATED_DATE)) AS MAX_YEAR FROM b_tasks_elapsed_time;');
		return $res->fetch();
	}
	
	private function convertTime($seconds){
		$result = array();

		$hourses = intval($seconds / 3600);
	        $minutes = intval(($seconds - $hourses * 3600) / 60);
	        if ($minutes < 10) $minutes = '0'.$minutes;
		
		$result['time'] = $hourses . ':' . $minutes;
		$result['class'] = $this->getColor($hourses, $minutes);
		
		return $result;
	}
	
	public function getColor($hourse, $minute){
		
		global $DB;
		
		$res = $DB->query('SELECT * FROM t_color WHERE t_type_id = 1');
		
		$needleSeconds = ($hourse * 60 * 60) + ($minute*60);
		
		for(;$row = $res->fetch();){
			$hourses = (int)$row['from_minutes'];
			$minutes = (int)$row['from_seconds'];
			$fromSeconds = ($hourses * 60 * 60) + ($minutes*60);
			
			$hourses = (int)$row['to_minutes'];
			$minutes = (int)$row['to_seconds'];
			$toSeconds = ($hourses * 60 * 60) + ($minutes*60);
			
			if(($needleSeconds >= $fromSeconds) && ($needleSeconds <= $toSeconds)){
				return $row['color_hex'];
			}
		}
		
		return '#FFF';
	}
	
	public function getColorByType(){
		
		global $DB;
		
		$res = $DB->query('
		SELECT t_type_id as TYPE,
		tc.color_hex as COLOR,
		(tc.from_minutes*60*60 + tc.from_seconds*60) as BEGIN,
		(tc.to_minutes*60*60 + tc.to_seconds*60) as END
		FROM t_color tc
		');
		
		$colorsType = array();
		
		while($row = $res->fetch()){
			$colorsType[] = $row;
		}
		
		return $colorsType;
	}
	
	public function totalTimelogs($userId, $dataCurrent, $dataCurrentEnd){
		$sql = '';
		if(!is_array($userId) && $userId > 0){
			$sql = ' AND b_user.ID = ' . $userId;
		}elseif(is_array($userId) && count($userId) > 0){
			$sql = ' AND b_user.ID IN(\'' . implode('\',\'', $userId) . '\')';
		}
		
		global $DB;
		
		$res = $DB->query('SELECT COUNT(*) AS count_rows 
							FROM (SELECT b_user.NAME, b_user.LAST_NAME 
							FROM b_user
							INNER JOIN b_tasks_elapsed_time ON b_tasks_elapsed_time.USER_ID = b_user.ID
							WHERE b_tasks_elapsed_time.CREATED_DATE BETWEEN \'' . $dataCurrent . '\' AND \'' . $dataCurrentEnd . '\' ' . $sql . '
							GROUP BY b_user.NAME, b_user.LAST_NAME) p');	
							
		$row = $res->fetch();		
		return $row['count_rows'];
	}
	
	public function summTime($totalTime, $time){
		$times = explode(':', $time);
		$newHourse = (int)$times[0];
		$newMinutes = (int)$times[1];
		
		$totalTimes = explode(':', $totalTime);
		$totalHourse = (int)$totalTimes[0];
		$totalMinutes = (int)$totalTimes[1];
		
		$totalHourse += $newHourse;
		$totalMinutes += $newMinutes;
		
		if($totalMinutes >= 60){
			$totalMinutes -= 60;
			$totalHourse++;
		}
		
		if($totalHourse < 10){
			$totalHourse = '0' . $totalHourse;
		}
		
		if($totalMinutes < 10){
			$totalMinutes = '0' . $totalMinutes;
		}
		
		return $totalHourse . ':' . $totalMinutes;
	}
}
?>
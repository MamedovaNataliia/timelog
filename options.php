<?
global $USER;
if(!$USER->IsAdmin())
	return;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), array());
$rsGroups->NavStart(500);
while($zr = $rsGroups->Fetch()){
	$ar = array();
	$ar["ID"] = intval($zr["ID"]);
	$ar["NAME"] = htmlspecialcharsbx($zr["NAME"]);
	$arGROUPS[] = $ar;
}


$aTabs = array(
	array("DIV" => "edit1", "TAB" => "Доступы", "ICON" => "ib_settings", "TITLE" => "Доступы"),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$module_id = 'expertit.timelog';
if($_SERVER["REQUEST_METHOD"]=="POST" && ($_POST['Update'] || $_POST['Apply'] || $_POST['RestoreDefaults'])>0 && check_bitrix_sessid()){
	$nID = COperation::GetIDByName('edit_subordinate_users');
	COption::SetOptionString($module_id, "GROUP_DEFAULT_TASK", $GROUP_DEFAULT_TASK, "Task for groups by default");
	$letter = ($l = CTask::GetLetter($GROUP_DEFAULT_TASK)) ? $l : 'D';
	COption::SetOptionString($module_id, "GROUP_DEFAULT_RIGHT", $letter, "Right for groups by default");
	
	$arTasksInModule = Array();
	foreach($arGROUPS as $value){
		$tid = ${"TASKS_".$value["ID"]};
		$arTasksInModule[$value["ID"]] = Array('ID' => $tid);

		$subOrdGr = false;
		if (strlen($tid) > 0 && in_array($nID,CTask::GetOperations($tid)) && isset($_POST['subordinate_groups_'.$value["ID"]]))
			$subOrdGr = $_POST['subordinate_groups_'.$value["ID"]];

		CGroup::SetSubordinateGroups($value["ID"], $subOrdGr);

		$rt = ($tid) ? CTask::GetLetter($tid) : '';
		if (strlen($rt) > 0 && $rt != "NOT_REF")
			$APPLICATION->SetGroupRight($module_id, $value["ID"], $rt);
		else
			$APPLICATION->DelGroupRight($module_id, array($value["ID"]));
	}

	CGroup::SetTasksForModule($module_id, $arTasksInModule);
	if($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == ""){
		LocalRedirect($_REQUEST["back_url_settings"]);
	}else{
		LocalRedirect("/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=".urlencode($mid)."&tabControl_active_tab=".urlencode($_REQUEST["tabControl_active_tab"])."&back_url_settings=".urlencode($_REQUEST["back_url_settings"]));
	}
}

$tabControl->Begin();
?>
<form name="access_options" method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?echo LANGUAGE_ID?>">
<?

$GROUP_DEFAULT_TASK = COption::GetOptionString($module_id, "GROUP_DEFAULT_TASK", "");

if ($GROUP_DEFAULT_TASK == ''){
	$GROUP_DEFAULT_RIGHT = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT", "D");
	$GROUP_DEFAULT_TASK = CTask::GetIdByLetter($GROUP_DEFAULT_RIGHT,$module_id,'module');
	if ($GROUP_DEFAULT_TASK)
		COption::SetOptionString($module_id, "GROUP_DEFAULT_TASK", $GROUP_DEFAULT_TASK);
}
?>
<?$tabControl->BeginNextTab();?>
	<tr>
		<td width="50%"><b>Доступ по умолчанию:</b></td>
		<td width="50%">
		<script>var arSubordTasks = [];</script>
		<?
		$arTasksInModule = CTask::GetTasksInModules(true, $module_id, 'module');
		$nID = COperation::GetIDByName('edit_subordinate_users');
		$arTasks = $arTasksInModule[$module_id];
		echo SelectBoxFromArray("GROUP_DEFAULT_TASK", $arTasks, htmlspecialcharsbx($GROUP_DEFAULT_TASK));

		$show_subord = false;
		$arTaskIds = $arTasks['reference_id'];
		$arSubordTasks = Array();
		$l = count($arTaskIds);
		for ($i=0;$i<$l;$i++)
		{
			$arOpInTask = CTask::GetOperations($arTaskIds[$i]);
			if (in_array($nID,$arOpInTask))
			{
				$arSubordTasks[] = $arTaskIds[$i];
				?><script>
				arSubordTasks.push(<?=$arTaskIds[$i]?>);
				</script><?
			}
		}

		?>
		<script>
		var taskSelectOnchange = function(select){
			var show = false;
			for (var s = 0; s < arSubordTasks.length; s++){
				if (arSubordTasks[s].toString() == select.value){
					show = true;
					break;
				}
			}
			var div = jsUtils.FindNextSibling(select, "div");
			if (show)
				div.style.display = 'block';
			else
				div.style.display = 'none';
		};
		</script>
		</td>
	</tr>
	<?
	$arUsedGroups = array();
	$arTaskInModule = CGroup::GetTasksForModule($module_id);
	foreach($arGROUPS as $value):
		$v = (isset($arTaskInModule[$value["ID"]]['ID'])? $arTaskInModule[$value["ID"]]['ID'] : false);
		if($v == false)
			continue;
		$arUsedGroups[$value["ID"]] = true;
	?>
		<tr valign="top">
			<td><?=$value["NAME"]." [<a title=\""."MAIN_USER_GROUP_TITLE"."\" href=\"/bitrix/admin/group_edit.php?ID=".$value["ID"]."&amp;lang=".LANGUAGE_ID."\">".$value["ID"]."</a>]:"?></td>
			<td>
			<?
			echo SelectBoxFromArray("TASKS_".$value["ID"], $arTasks, $v, "По умолчанию", 'onchange="taskSelectOnchange(this)"');
			$show_subord = (in_array($v,$arSubordTasks));
			?>
			<div<?echo $show_subord? '' : ' style="display:none"';?>>
				<div style="padding:6px 0 6px 0"><?=GetMessage('SUBORDINATE_GROUPS');?>:</div>
				<select name="subordinate_groups_<?=$value["ID"]?>[]" multiple size="6">
				<?
				$arSubordinateGroups = CGroup::GetSubordinateGroups($value["ID"]);
				foreach($arGROUPS as $v_gr)
				{
					if ($v_gr['ID'] == $value["ID"])
						continue;
					?><option value="<?=$v_gr['ID']?>" <?echo (in_array($v_gr['ID'],$arSubordinateGroups)) ? 'selected' : ''?>><? echo $v_gr['NAME'].' ['.$v_gr['ID'].']'?></option><?
				}
				?>
				</select>
			</div>
			</td>
		</tr>
	<?endforeach;?>

	<tr valign="top">
		<td><select onchange="settingsSetGroupID(this)">
			<option value="">Выберите группу</option>
	<?
	foreach($arGROUPS as $group):
		if($arUsedGroups[$group["ID"]] == true)
			continue;
	?>
			<option value="<?=$group["ID"]?>"><?=$group["NAME"]." [".$group["ID"]."]"?></option>
	<?endforeach?>
		</select>
	</td>
			<td>
			<?
			echo SelectBoxFromArray("", $arTasks, "", "По умолчанию", 'onchange="taskSelectOnchange(this)"');
			?>
			<div style="display:none">
				<div style="padding:6px 0 6px 0">SUBORDINATE_GROUPS:</div>
				<select name="" multiple size="6">
				<?
				foreach($arGROUPS as $v_gr)
				{
					?><option value="<?=$v_gr['ID']?>"><? echo $v_gr['NAME'].' ['.$v_gr['ID'].']'?></option><?
				}
				?>
				</select>
			</div>
			</td>
	</tr>
	<tr>
		<td colspan="2">
			<script type="text/javascript">
				function settingsSetGroupID(el){
					var tr = jsUtils.FindParentObject(el, "tr");
					var sel = jsUtils.FindChildObject(tr.cells[1], "select");
					sel.name = "TASKS_"+el.value;
					var div = jsUtils.FindNextSibling(sel, "div");
					sel = jsUtils.FindChildObject(div, "select");
					sel.name = "subordinate_groups_"+el.value+"[]";
				}
				
				function settingsAddRights(a){
					var row = jsUtils.FindParentObject(a, "tr");
					var tbl = row.parentNode;

					var tableRow = tbl.rows[row.rowIndex-1].cloneNode(true);
					tbl.insertBefore(tableRow, row);

					var sel = jsUtils.FindChildObject(tableRow.cells[1], "select");
					sel.name = "";
					sel.selectedIndex = 0;

					var div = jsUtils.FindNextSibling(sel, "div");
					div.style.display = "none";
					sel = jsUtils.FindChildObject(div, "select");
					sel.name = "";
					sel.selectedIndex = -1;

					sel = jsUtils.FindChildObject(tableRow.cells[0], "select");
					sel.selectedIndex = 0;
				}
			</script>
			<a href="javascript:void(0)" onclick="settingsAddRights(this)" hidefocus="true" class="bx-action-href">Добавить право доступа</a>
		</td>
	</tr>
<?$tabControl->Buttons();?>
	<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<input type="submit" name="Update" value="Сохранить" title="Сохранить" class="adm-btn-save">
	<input type="submit" name="Apply" value="Применить" title="Применить">
<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
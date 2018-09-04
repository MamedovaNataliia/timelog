<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
	<input type="hidden" name="id" value="custom">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?echo CAdminMessage::ShowMessage("Удаление таблицы")?>
	<p><?echo "Удаление таблиц из БД:"?></p>
	<p><input type="checkbox" name="droptable" id="droptable" value="Y" checked><label for="droptable"><?echo "Удалить таблицу?"?></label></p>
	<input type="submit" name="inst" value="<?echo "Удалить"?>">
</form>
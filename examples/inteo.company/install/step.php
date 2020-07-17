<?if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);
echo CAdminMessage::ShowNote(GetMessage("INTEO_COMPANY_INSTALLED"));?>
<p>
	<?=GetMessage("INTEO_COMPANY_SETUP")?><a href='/bitrix/admin/wizard_list.php?lang=ru'><?=GetMessage("INTEO_COMPANY_MASTER")?></a> <br /><?=GetMessage("INTEO_COMPANY_SELECT_INSTALL")?></p>
<form action="/bitrix/admin/wizard_list.php?lang=ru">
	<input type="submit" name="" value="<?=GetMessage("INTEO_COMPANY_OPEN_MASTER")?>">
<form>
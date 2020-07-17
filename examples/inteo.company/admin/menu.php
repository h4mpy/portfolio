<?

use Bitrix\Main\Localization\Loc;

AddEventHandler('main', 'OnBuildGlobalMenu', 'OnBuildGlobalMenuHandlerInteoCompany');
function OnBuildGlobalMenuHandlerInteoCompany(&$arGlobalMenu, &$arModuleMenu)
{
	$moduleID = 'inteo.company';

	$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/".$moduleID."/menu.css");

	if ($GLOBALS['APPLICATION']->GetGroupRight($moduleID) >= 'R')
	{
		$arMenu = array(
			array(
				'parent_menu' => 'global_menu_inteo',
				'menu_id' => 'global_menu_inteo_forms',
				'text' => Loc::getMessage("INTEO_GLOBAL_MENU_FORMS_TEXT"),
				'title' => Loc::getMessage("INTEO_GLOBAL_MENU_FORMS_TITLE"),
				'sort' => 1010,
				'items_id' => 'global_menu_inteo_forms_items',
				'icon' => 'subscribe_menu_icon',
				'items' => array(
					array(
						'text' => Loc::getMessage("INTEO_GLOBAL_MENU_FORMS_SMTP_TEXT"),
						'title' => Loc::getMessage("INTEO_GLOBAL_MENU_FORMS_SMTP_TITLE"),
						'sort' => 10,
						'url' => '/bitrix/admin/'.$moduleID.'_forms_smtp.php?lang='.LANG,
						'items_id' => 'global_menu_inteo_forms_smtp',
					)
				),
			),
			array(
				'menu_id' => 'global_menu_inteo_tools',
				'parent_menu' => 'global_menu_inteo',
				'text' => Loc::getMessage("INTEO_GLOBAL_MENU_TOOLS_TEXT"),
				'title' => Loc::getMessage("INTEO_GLOBAL_MENU_TOOLS_TITLE"),
				'sort' => 1020,
				'items_id' => 'global_menu_inteo_tools',
				'icon' => 'util_menu_icon',
				'items' => array(
					array(
						'text' => Loc::getMessage("INTEO_GLOBAL_MENU_TOOLS__LOG_TEXT"),
						'title' => Loc::getMessage("INTEO_GLOBAL_MENU_TOOLS__LOG_TITLE"),
						'sort' => 10,
						'url' => '/bitrix/admin/'.$moduleID.'_tools_log.php?lang='.LANG,
						'items_id' => 'global_menu_inteo_tools_log',
					),
					array(
						'text' => Loc::getMessage("INTEO_GLOBAL_MENU_TOOLS__EVENTLOG_TEXT"),
						'title' => Loc::getMessage("INTEO_GLOBAL_MENU_TOOLS__EVENTLOG_TITLE"),
						'sort' => 10,
						'url' => '/bitrix/admin/'.$moduleID.'_tools_event_log.php?PAGEN_1=1&SIZEN_1=20&set_filter=Y&adm_filter_applied=0&find_type=audit_type_id&find_item_id=inteo.company&lang='.LANG,
						'items_id' => 'global_menu_inteo_tools_event_log',
					)
				),
			)
		);
		if (!isset($arGlobalMenu['global_menu_inteo']))
		{
			$arGlobalMenu['global_menu_inteo'] = array(
				'menu_id' => 'global_menu_inteo',
				'text' => Loc::getMessage("INTEO_GLOBAL_MENU_TEXT"),
				'title' => Loc::getMessage("INTEO_GLOBAL_MENU_TITLE"),
				'sort' => 1000,
				'items_id' => 'global_menu_inteo_items',
			);
		}

		$arGlobalMenu['global_menu_inteo']['items'] = $arMenu;
	}
}
?>
<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;

Loc::loadMessages(__FILE__);

Class inteo_company extends CModule
{
	var $MODULE_ID = "inteo.company";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $PARTNER_NAME;
	var $PARTNER_URI;

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("INTEO_COMPANY_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("INTEO_COMPANY_MODULE_DESC");
		$this->MODULE_GROUP_RIGHTS = 'N';
		$this->PARTNER_NAME = Loc::getMessage("INTEO_COMPANY_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("INTEO_COMPANY_PARTNER_URI");
	}

	public function isVersionD7()
	{
		return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
	}

	function InstallDB($arParams = array())
	{

	}

	function UnInstallDB($arParams = array())
	{
		Loader::includeModule($this->MODULE_ID);

		Option::delete($this->MODULE_ID);
		CAgent::RemoveModuleAgents("inteo.company");
	}

	function InstallEvents()
	{

	}

	function UnInstallEvents()
	{

	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/images/",
			$_SERVER['DOCUMENT_ROOT']."/bitrix/images/".$this->MODULE_ID,
			true, true
		);

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/css/",
			$_SERVER['DOCUMENT_ROOT']."/bitrix/css/".$this->MODULE_ID,
			true, true
		);

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/admin/",
			$_SERVER['DOCUMENT_ROOT']."/bitrix/admin/",
			true
		);
		return true;
	}

	function UnInstallFiles()
	{
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/js/'.$this->MODULE_ID);
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/images/'.$this->MODULE_ID);
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/css/'.$this->MODULE_ID);

		DeleteDirFiles(__DIR__.'/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		if ($this->isVersionD7())
		{
			\Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();
			$APPLICATION->IncludeAdminFile(GetMessage("INTEO_COMPANY_INSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/step.php");
		}
		else
		{
			throw new \Bitrix\Main\SystemException(Loc::getMessage("INTEO_COMPANY_INSTALL_ERROR_VERSION"));
		}
	}

	function DoUninstall()
	{
		global $APPLICATION;
		$this->UnInstallDB();
		$this->UnInstallEvents();
		$this->UnInstallFiles();
		$_SESSION['INTEO_USER_ID'] = 0;
		ModuleManager::unRegisterModule($this->MODULE_ID);
		$APPLICATION->IncludeAdminFile(GetMessage("INTEO_COMPANY_UNINSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/unstep.php");
	}
}
?>

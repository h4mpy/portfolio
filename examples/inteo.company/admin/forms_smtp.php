<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
require $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/inteo.company/vendor/autoload.php';

global $APPLICATION;

$moduleID = "inteo.company";
Loader::includeModule($moduleID);

$RIGHT = $APPLICATION->GetGroupRight($moduleID);

if ($RIGHT >= "R")
{
	$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

	//Checking SMTP
	if ($request->isPost() && $request['check_smtp_server'] && check_bitrix_sessid())
	{
		$APPLICATION->RestartBuffer();
		$error = "";
		$status = 0;

		$mail = new PHPMailer(true);
		try
		{
			$mail->isSMTP();
			$mail->SMTPAuth = ($request['smtp_password']!='')?true:false;
			$mail->Host = $request['smtp_server'];
			$mail->SMTPSecure = $request['smtp_secure'];
			$mail->Port = $request['smtp_port'];
			$mail->Username = $request['smtp_login'];
			$mail->Password = $request['smtp_password'];

			$body = Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_TEST_DESCRIPTION");
			$email = Option::get('main', 'email_from');
			$mail->Timeout = 15;
			$mail->AddAddress($email);
			$mail->SetFrom($request['smtp_login'], Option::get('main', 'site_name'));
			$mail->setLanguage(($request['lang'])?$request['lang']:'ru');
			$mail->Subject = Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_TEST_TITLE");
			$mail->CharSet = LANG_CHARSET;
			$mail->Body = $body;
			$mail->send();
			$status = 1;
		}
		catch (Exception $e)
		{
			$error = $mail->ErrorInfo;
			$status = 0;
			if (LANG_CHARSET != 'utf-8')
			{
				$error = mb_convert_encoding($error, LANG_CHARSET);
			}
		}
		echo \Bitrix\Main\Web\Json::encode(Array('status' => $status, 'error' => $error));
		die();
	}

	$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage("INTEO_FORMS_SMTP_HEAD"));
	$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/".$moduleID."/admin.css");

	$arOptions = array(
		array("use_default", Loc::getMessage("INTEO_FORMS_OPTIONS_USE_DEFAULT"), "Y", Array("checkbox", "Y", 'onclick=""')),
		array("use_smtp", Loc::getMessage("INTEO_FORMS_OPTIONS_USE_SMTP"), "N", Array("checkbox", "Y", 'onclick="setSmtp();"')),
		array("smtp_login", Loc::getMessage("INTEO_FORMS_OPTIONS_LOGIN"), "", Array("text", 30)),
		array("smtp_password", Loc::getMessage("INTEO_FORMS_OPTIONS_PASSWORD"), "", Array("password", 30)),
		array("", Loc::getMessage("INTEO_FORMS_OPTIONS_READY"), "<div class=\"readyconfig readyconfig_default\"><a class=\"bx-action-href\" href=\"#\" data-config=\"yandex\" style=\"margin-right:7px\">".Loc::getMessage("INTEO_FORMS_OPTIONS_YANDEX")."</a><a class=\"bx-action-href\" href=\"#\" data-config=\"gmail\" style=\"margin-right:7px\">".Loc::getMessage("INTEO_FORMS_OPTIONS_GMAIL")."</a><a class=\"bx-action-href\" href=\"#\" data-config=\"mailru\" style=\"margin-right:7px\">".Loc::getMessage("INTEO_FORMS_OPTIONS_MAILRU")."</a></div>", Array("statichtml")),
		array("smtp_server", Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_SERVER"), "smtp.gmail.com", Array("text", 30)),
		array("smtp_secure", Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_SECURE"), "tls", Array("selectbox", Array(
			"" => Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_SECURE_NO"),
			"ssl" => "SSL",
			"tls" => "TLS",
		))),
		array("smtp_port", Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_PORT"), "587", Array("text", 30)),
		array("", Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_CHECK"), "<input id=\"checksmtpbutton\" type=\"button\" name=\"smtp_check\" value=\"".Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_CHECK_BUTTON")."\" title=\"".Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_CHECK_BUTTON")."\"><div id=\"checksmtp\" class=\"checksmtpresult\"></div>", Array("statichtml")),
		array("smtp_log", Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_LOG"), "N", Array("checkbox", "Y")),
	);

	$arTabs = array(
		array(
			"DIV" => "edit1",
			"TAB" => Loc::getMessage("INTEO_FORMS_SMTP_TAB_DEFAULT"),
			"ICON" => "im_path",
			"TITLE" => Loc::getMessage("INTEO_FORMS_SMTP_TITLE_DEFAULT"),
			"OPTIONS" => $arOptions
		),
	);

	$arSites = array();
	$by = "id";
	$sort = "asc";
	$dbSite = CSite::GetList($by, $sort, array("ACTIVE" => "Y"));

	while ($arSite = $dbSite->Fetch())
	{
		$arSites[] = $arSite;
	}

	if (count($arSites) > 1)
	{
		foreach ($arSites as $key => $arSite)
		{
			$arTabs[] = array(
				"DIV" => "editsite".($key+1),
				"TAB" => Loc::getMessage("INTEO_FORMS_SMTP_TAB_SITE", array("#SITE_NAME#" => $arSite["NAME"], "#SITE_ID#" => $arSite["ID"])),
				"TITLE" => Loc::getMessage("INTEO_FORMS_SMTP_TITLE_SITE", array("#SITE_NAME#" => $arSite["NAME"], "#SITE_ID#" => $arSite["ID"])),
				"SITE_ID" => $arSite["ID"],
				"OPTIONS" => $arOptions
			);
		}
	}

	if (Loader::includeModule('sender'))
	{
		$arTabs[] = array(
			"DIV" => "edit2",
			"TAB" => Loc::getMessage("INTEO_FORMS_SMTP_TAB_MAILING"),
			"ICON" => "im_path",
			"TITLE" => Loc::getMessage("INTEO_FORMS_SMTP_TITLE_MAILING"),
			"OPTIONS" => $arOptions,
			"POSTFIX" => 'mailing'
		);
	}

	//Saving settings
	if ($request->isPost() && $request['Apply'] && $RIGHT >= "W" && check_bitrix_sessid())
	{
		foreach ($arTabs as $arTab)
		{
			foreach ($arTab["OPTIONS"] as $Option)
			{
				if (!is_array($Option))
				{
					continue;
				}
				if ($Option['note'])
				{
					continue;
				}
				$optionName = $Option[0];
				if ($optionName != '')
				{
					if ($arTab["SITE_ID"])
					{
						$optionValue = $request->getPost($optionName.'_'.$arTab["SITE_ID"]);
						if (is_array($Option[3]) && $Option[3][0] == 'checkbox' && !$optionValue) $optionValue = 'N';
						Option::set($moduleID, $optionName, is_array($optionValue) ? implode(",", $optionValue):$optionValue, $arTab["SITE_ID"]);
					}
					elseif ($arTab["POSTFIX"])
					{
						$optionValue = $request->getPost($optionName.'_'.$arTab["POSTFIX"]);
						if (is_array($Option[3]) && $Option[3][0] == 'checkbox' && !$optionValue) $optionValue = 'N';
						Option::set($moduleID, $optionName.'_'.$arTab["POSTFIX"], is_array($optionValue) ? implode(",", $optionValue):$optionValue);
					}
					else
					{
						$optionValue = $request->getPost($optionName);
						if (is_array($Option[3]) && $Option[3][0] == 'checkbox' && !$optionValue) $optionValue = 'N';
						Option::set($moduleID, $optionName, is_array($optionValue) ? implode(",", $optionValue):$optionValue);
					}
				}
			}
		}
	}

	?><script>
		function setSmtp(tab) {
			if (typeof(tab)==='undefined') tab = "";
			if (tab!=="") tab='_'+tab;
			if (document.getElementsByName("use_smtp"+tab).length > 0)
			{
				var readyconfig = (tab!=="")?'.readyconfig'+tab:'.readyconfig_default';
				var container = document.querySelector(readyconfig);
				var elements = container.getElementsByClassName('bx-action-href'), check = document.getElementsByName("use_smtp"+tab)[0].checked;
				for (var i = 0, length = elements.length; i < length; i++)
				{
					if (check) elements[i].classList.remove('disabled');
					else elements[i].classList.add('disabled')
				}
				document.getElementsByName("smtp_login"+tab)[0].disabled =
				document.getElementsByName("smtp_password"+tab)[0].disabled =
				document.getElementsByName("smtp_server"+tab)[0].disabled =
				document.getElementsByName("smtp_secure"+tab)[0].disabled =
				document.getElementsByName("smtp_port"+tab)[0].disabled =
				document.getElementsByName("smtp_check"+tab)[0].disabled =
				document.getElementsByName("smtp_log"+tab)[0].disabled =
				!check;
			}

			//document.getElementsByName("smtp_login"+tab)[0].disabled;
		}
		function setVisibility(tab) {
			if (typeof(tab)==='undefined') tab = "";
			if (tab!=="") tab='_'+tab;
			if (document.getElementsByName("use_default"+tab).length > 0)
			{
				var elements = document.getElementsByClassName('overwrite'+tab), check = document.getElementsByName("use_default"+tab)[0].checked;
				for (var i = 0, length = elements.length; i < length; i++)
				{
					if (check) elements[i].classList.add('hideblock');
					else elements[i].classList.remove('hideblock');
				}
			}
			//document.getElementsByName("smtp_login"+tab)[0].disabled;
		}
	</script><?

	$tabControl = new CAdminTabControl("tabControl", $arTabs);
	?><form name="inteo_forms_smtp" method="post" action="<?
		echo $APPLICATION->GetCurPage();
	?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>">
		<?=bitrix_sessid_post();?>
		<?$tabControl->Begin();
		foreach ($arTabs as $arTab)
		{
			$tabControl->BeginNextTab();
			if ($arTab['DIV'] == 'edit1')
			{
				$settingsErrors = array();
				if (Option::get('main', 'convert_mail_header', 'Y') == 'N')
				{
					$settingsErrors[] = Loc::getMessage("INTEO_FORMS_OPTIONS_SETTINGS_8BIT");
				}
				if (isset($GLOBALS["INTEO_ERRORS"]["CUSTOMMAIL"]))
				{
					?><tr><td colspan="2" align="center">
						<? CAdminMessage::ShowMessage(Loc::getMessage("INTEO_FORMS_OPTIONS_CUSTOM_MAIL_ERROR"));?>
					</td></tr><?
				}
				if (isset($GLOBALS["INTEO_ERRORS"]["PHPMAILER"])){
					?><tr><td colspan="2" align="center">
						<? CAdminMessage::ShowMessage(Loc::getMessage("INTEO_FORMS_OPTIONS_PHPMAILER_ERROR"));?>
					</td></tr><?
				}
				foreach ($arTab['OPTIONS'] as $option)
				{
					if (!is_array($option) || $option[0] != 'use_default')
					{
						\Inteo\Company\Settings::DrawRow($moduleID, $option);
					}
				}
				if (count($settingsErrors) > 0) {
					?><tr><td colspan="2" align="center">
						<div class="notes">
							<table cellspacing="0" cellpadding="0" border="0" class="notes">
								<tbody>
									<tr class="top">
										<td class="left"><div class="empty"></div></td>
										<td><div class="empty"></div></td>
										<td class="right"><div class="empty"></div></td>
									</tr>
									<tr>
										<td class="left"><div class="empty"></div></td>
										<td class="content" style="font-size: 13px!important;padding:15px!important;">
											<?echo Loc::getMessage("INTEO_FORMS_OPTIONS_SETTINGS_ERROR").'<br><br>'.implode('<br>',$settingsErrors);?>
										</td>
										<td class="right"><div class="empty"></div></td>
									</tr>
									<tr class="bottom">
										<td class="left"><div class="empty"></div></td>
										<td><div class="empty"></div></td>
										<td class="right"><div class="empty"></div></td>
									</tr>
								</tbody>
							</table>
						</div>
					</td></tr><?
				}
				?><script>
				BX.ready(function(){
					setSmtp();
				});
				</script><?
			}
			else
			{
				if ($arTab['DIV'] == 'edit2')
				{
					$checkInit = false;
					$checkInitFile = false;
					if (is_file($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php')) $checkInitFile = $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php';
					if (is_file($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/init.php')) $checkInitFile = $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/init.php';
					if (strpos(file_get_contents($checkInitFile),"\Bitrix\Main\Loader::includeModule('inteo.company')") !== false) {
						$checkInit = true;
					}
					if (!$checkInit)
					{
						?><tr><td colspan="2">
							<p><?= Loc::getMessage("INTEO_FORMS_OPTIONS_SETTINGS_INITPHP") ?></p>
							<pre class="code" style="text-align: left;">&lt;?
\Bitrix\Main\Loader::includeModule('inteo.company');
?&gt;</pre>
						</td></tr><?
					}
				}
				$setSite = ($arTab["SITE_ID"])?$arTab["SITE_ID"]:false;
				$setPostfix = ($arTab["POSTFIX"])?$arTab["POSTFIX"]:false;
				$setClass = ($setPostfix)?$setPostfix:$setSite;
				foreach ($arTab['OPTIONS'] as $option)
				{
					if (is_array($option) && $option[0] == 'use_smtp')
					{
						$option[3][2] = 'onclick="setSmtp(\''.$setClass.'\');"';
					}
					if (is_array($option) && $option[0] == 'use_default')
					{
						$option[3][2] = 'onclick="setVisibility(\''.$setClass.'\');"';
					}
					if (is_array($option) && strpos($option[2], "checksmtpbutton") > 0)
					{
						$option[2] = "<input id=\"checksmtpbutton_".$setClass."\" type=\"button\" name=\"smtp_check_".$setClass."\" value=\"".Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_CHECK_BUTTON")."\" title=\"".Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_CHECK_BUTTON")."\"><div id=\"checksmtp_".$setClass."\" class=\"checksmtpresult\"></div>";
					}
					if (is_array($option) && strpos($option[2], "readyconfig_default") > 0 && $setClass)
					{
						$option[2] = str_replace('readyconfig_default', 'readyconfig_'.$setClass, $option[2]);
					}
					if (is_array($option) && $option[0] == 'use_default')
					{
						\Inteo\Company\Settings::DrawRow($moduleID, $option, $setSite, $setPostfix);
					}
					else
					{
						\Inteo\Company\Settings::DrawRow($moduleID, $option, $setSite, $setPostfix, 'overwrite overwrite_'.$setClass);
					}
				}
				?><script>
				BX.ready(function(){
					setSmtp("<?echo $setClass?>");
					setVisibility("<?echo $setClass?>");
				});
				</script><?
			}
		}
		$tabControl->Buttons();
		?><input <?
	if ($RIGHT < "W")
	{
		echo "disabled";
	}
	?> type="submit" name="Apply" class="adm-btn-save" value="<?echo Loc::getMessage("INTEO_FORMS_SMTP_APPLY")?>" title="<?echo Loc::getMessage("INTEO_FORMS_SMTP_APPLY")?>">
	<script type="text/javascript">
		var readylinks = document.querySelectorAll("[data-config]"),
			selectsecure = document.querySelectorAll("[name^=smtp_secure]"),
			smtpcheck = document.querySelectorAll("[name^=smtp_check]");
		for (i = 0; i < readylinks.length; i++) {
			readylinks[i].addEventListener('click', function(e) {
				if (!this.classList.contains('disabled')) {
					var readyConfig = this.getAttribute('data-config'), parent = this.closest('.edit-table');
					if (readyConfig === 'yandex') {
						parent.querySelector("[name^=smtp_server]").value = 'smtp.yandex.ru';
						parent.querySelector("[name^=smtp_secure]").value = 'ssl';
						parent.querySelector("[name^=smtp_port]").value = '465';
					}
					if (readyConfig === 'gmail') {
						parent.querySelector("[name^=smtp_server]").value = 'smtp.gmail.com';
						parent.querySelector("[name^=smtp_secure]").value = 'tls';
						parent.querySelector("[name^=smtp_port]").value = '587';
					}
					if (readyConfig === 'mailru') {
						parent.querySelector("[name^=smtp_server]").value = 'smtp.mail.ru';
						parent.querySelector("[name^=smtp_secure]").value = 'ssl';
						parent.querySelector("[name^=smtp_port]").value = '465';
					}
				}
				e.preventDefault();
			});
		}
		for (i = 0; i < selectsecure.length; i++) {
			selectsecure[i].addEventListener('change', function(e) {
				port = this.closest('.edit-table').querySelector("[name^=smtp_port]");
				if (this.value === 'ssl') port.value = '465';
				if (this.value === 'tls') port.value = '587';
				if (this.value === '') port.value = '25';
			});
		}
		BX.ready(function(){
			for (i = 0; i < smtpcheck.length; i++) {
				smtpcheck[i].addEventListener('click', function(e) {
					if (!this.disabled) {
						var element = this,
							parent = this.closest('.edit-table'),
							result = parent.querySelector(".checksmtpresult"),
							checktext = BX.create('P', {
								html: "<i class=\"sending\"><?=Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_CHECKING")?></i>"
							}),
							checkParams = {
								lang: "<?=$request['lang']?>",
								mid: "<?=$moduleID?>",
								sessid: BX.bitrix_sessid(),
								check_smtp_server: "Y",
								smtp_login: parent.querySelector("[name^=smtp_login]").value,
								smtp_password: parent.querySelector("[name^=smtp_password]").value,
								smtp_server: parent.querySelector("[name^=smtp_server]").value,
								smtp_port: parent.querySelector("[name^=smtp_port]").value,
								smtp_secure: parent.querySelector("[name^=smtp_secure]").value
							};
						BX.cleanNode(result);
						BX(result).appendChild(checktext);
						element.disabled = true;
						BX.ajax({
							'url':'<?echo $_SERVER['SCRIPT_NAME']?>',
							'method':'POST',
							'data' : checkParams,
							'dataType': 'json',
							'timeout': 20,
							'async': true,
							'start': true,
							'cache': false,
							'onsuccess': function(data) {
								if (data.status > 0) {
									BX.adjust(checktext, {
										style: {
											fontWeight: 'bold',
											color: 'green'
										},
										html: "<?=Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_CHECKING_SUCCESS", array("#SITE_EMAIL#" => Option::get('main', 'email_from')))?>"});
								}
								else {
									BX.adjust(checktext, {
										style: {
											fontWeight: 'bold',
											color: 'red'
										},
										html: data.error});
								}
								element.disabled = false;
							},
							'onfailure': function(){
								BX.adjust(checktext, {
									style: {
										fontWeight: 'bold',
										color: 'red'
									},
									text: "<?=Loc::getMessage("INTEO_FORMS_OPTIONS_SMTP_CHECKING_TIMEOUT")?>"});
								element.disabled = false;
							}
						});
					}
				});
			}
		});

	</script>

	<?$tabControl->End();?>
</form><?

}
else
{
	$m = new CAdminMessage(
		array(
			"MESSAGE" => Loc::getMessage("INTEO_NO_RIGHTS"),
		)
	);
	echo $m->Show();
}
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>
<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;

$moduleID = "inteo.company";
Loader::includeModule($moduleID);

if ($memory_limit = ini_get('memory_limit'))
{
	if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches))
	{
		if ($matches[2] == 'G')
		{
			$memory_limit = $matches[1] * 1024 * 1024 * 1024;
		}
		elseif ($matches[2] == 'M')
		{
			$memory_limit = $matches[1] * 1024 * 1024;
		}
		elseif ($matches[2] == 'K')
		{
			$memory_limit = $matches[1] * 1024;
		}
	}
}

$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if ($RIGHT >= "R")
{
	$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage("INTEO_TOOLS_LOG_TITLE"));
	$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/".$moduleID."/admin.css");

	$config = \Bitrix\Main\Config\Configuration::getInstance();
	$exception_handling = $config->get('exception_handling');

	$request = \Bitrix\Main\HttpApplication::getInstance()
		->getContext()
		->getRequest();
	if ($request->isPost() && $RIGHT >= "W" && check_bitrix_sessid())
	{
		if ($request['addlog'] && $request['logmessage'])
		{
			AddMessage2Log($request['logmessage']);
			$m = new CAdminMessage(
				array(
					"MESSAGE" => Loc::getMessage("INTEO_TOOLS_LOG_ADDED"),
					"TYPE" => "OK",
				)
			);
			echo $m->Show();
		}
		if ($request['clearlog'])
		{
			if (file_exists(LOG_FILENAME))
			{
				unlink(LOG_FILENAME);
			}
			$m = new CAdminMessage(
				array(
					"MESSAGE" => Loc::getMessage("INTEO_TOOLS_LOG_DELETED"),
					"TYPE" => "OK",
				)
			);
			echo $m->Show();
		}
		if ($request['clearexception'])
		{
			if (count($exception_handling) > 0)
			{
				if (isset($exception_handling['log']['settings']['file']) && $exception_handling['log']['settings']['file'] != '')
				{
					if (file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$exception_handling['log']['settings']['file']))
					{
						unlink($_SERVER['DOCUMENT_ROOT'].'/'.$exception_handling['log']['settings']['file']);
					}
				}
			}
			$m = new CAdminMessage(
				array(
					"MESSAGE" => Loc::getMessage("INTEO_TOOLS_LOG_DELETED"),
					"TYPE" => "OK",
				)
			);
			echo $m->Show();
		}
	}
	$arTabs = array();
	$arTabs[] = array(
		"DIV" => "edit1",
		"TAB" => Loc::getMessage("INTEO_TOOLS_LOG_TAB1"),
		"ICON" => "settings",
		"TITLE" => Loc::getMessage("INTEO_TOOLS_LOG_TAB1_TITLE"),
	);
	$arTabs[] = array(
		"DIV" => "edit2",
		"TAB" => Loc::getMessage("INTEO_TOOLS_LOG_TAB2"),
		"ICON" => "settings",
		"TITLE" => Loc::getMessage("INTEO_TOOLS_LOG_TAB2_TITLE"),
	);

	$tabControl = new CAdminTabControl("tabControl", $arTabs);

	$arLogTabs = array();
	$arLogTabs[] = array(
		"DIV" => "editlog1",
		"TAB" => Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_LAST"),
		"ICON" => "settings",
	);
	if (defined("LOG_FILENAME") && strlen(LOG_FILENAME) > 0)
	{
		$arLogTabs[] = array(
			"DIV" => "editlog2",
			"TAB" => Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_ACTIONS"),
			"ICON" => "settings",
		);
	}
	$arExceptionTabs = array();
	$arExceptionTabs[] = array(
		"DIV" => "editexception1",
		"TAB" => Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_LAST"),
		"ICON" => "settings",
	);
	if (count($exception_handling) > 0)
	{
		if (isset($exception_handling['log']['settings']['file']) && $exception_handling['log']['settings']['file'] != '')
		{
			$arExceptionTabs[] = array(
				"DIV" => "editexception2",
				"TAB" => Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_ACTIONS"),
				"ICON" => "settings",
			);
		}
	}
	$tabLogControl = new CAdminViewTabControl("subTabLogControl", $arLogTabs);
	$tabExceptionControl = new CAdminViewTabControl("subTabExceptionControl", $arExceptionTabs);

	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
	<tr>
	<td colspan="2"><?
		if (defined("LOG_FILENAME") && strlen(LOG_FILENAME) > 0)
		{
			if (file_exists(LOG_FILENAME) && is_readable(LOG_FILENAME))
			{
				echo Loc::getMessage(
					"INTEO_TOOLS_LOG_DEFAULT_PATH",
					array("#PATH#" => str_replace($_SERVER["DOCUMENT_ROOT"], "", LOG_FILENAME))
				);
				echo '<br><br><br>';
			}
			$tabLogControl->Begin();
			$tabLogControl->BeginNextTab();
			if (file_exists(LOG_FILENAME) && is_readable(LOG_FILENAME))
			{
				$fileSizeLimit = 1024 * 1024;
				if (intval($memory_limit) > 0)
				{
					$fileSizeLimit = round(intval($memory_limit) / 5); //Max 20% of memory limit
				}
				if (filesize(LOG_FILENAME) <= $fileSizeLimit)
				{
					$arLog = explode("----------\n", file_get_contents(LOG_FILENAME));
				if (is_array($arLog) && count($arLog) > 0 && !empty($arLog[0]))
				{
					?>
				<table class="internal" cellspacing="0" cellpadding="0" border="0" align="left" width="100%" style="float:none"><?
					?>
					<tr class="heading">
						<td>HOST</td>
						<td>DATE</td>
						<td>MODULE</td>
					</tr><?
					$arLog = array_reverse(array_slice($arLog, -11));
					foreach ($arLog as $log)
					{
						$logHost = explode("\nDate: ", $log);
						if (isset($logHost[0]) && strpos($logHost[0], "Host: ") !== false)
						{
							?>
							<tr><?
							?>
							<td style="white-space: nowrap"><?
							echo str_replace("Host: ", "", $logHost[0]); ?></td><?
							$logDate = explode("\nModule: ", $logHost[1]);
							?>
							<td style="white-space: nowrap"><?
							echo $logDate[0]; ?></td><?
							?>
							<td>
							<pre><?
								echo htmlspecialchars($logDate[1]); ?></pre></td><?
							?></tr><?
						}
					}
					?></table><?
				echo Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_DOC_FULL");
				}
				else
				{
					echo Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_EMPTY_LOG");
					echo Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_DOC");
				}

				}
				else
				{
					echo Loc::getMessage(
						"INTEO_TOOLS_LOG_DEFAULT_DOWNLOAD",
						array("#PATH#" => str_replace($_SERVER["DOCUMENT_ROOT"], "", LOG_FILENAME))
					);
				}
			}
			else
			{
				echo Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_EMPTY_LOG");
				echo Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_DOC");
			}
			if (defined("LOG_FILENAME") && strlen(LOG_FILENAME) > 0)
			{
				$tabLogControl->BeginNextTab();
				echo '<p>'.Loc::getMessage("INTEO_TOOLS_LOG_OPERATIONS_ADD").'</p>';
				?>
				<form name="inteo_tools_log_operations" method="post" action="<?
				echo $APPLICATION->GetCurPage(); ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?= LANGUAGE_ID ?>">
					<?= bitrix_sessid_post(); ?>
					<p><textarea name="logmessage" cols="80" rows="5" minlength="1"></textarea></p>
					<input type="submit" class="adm-btn-save" name="addlog" value="Отправить">
					<input type="submit" id="clearlog" name="clearlog" value="Очистить лог">
				</form>
				<script>
					BX.ready(function () {
						BX.bind(BX('clearlog'), 'click', function (e) {
							if (!confirm('<?echo Loc::getMessage("INTEO_TOOLS_LOG_CLEARLOG")?>')) e.preventDefault();
						});
					});
				</script>
				<?
			}
			$tabLogControl->End();
		}
		else
		{
			$m = new CAdminMessage(
				array(
					"MESSAGE" => Loc::getMessage("INTEO_TOOLS_LOG_UNDEFINED"),
				)
			);
			echo $m->Show();
			echo Loc::getMessage("INTEO_TOOLS_LOG_UNDEFINED_DESCRIPTION");
			echo BeginNote();
			echo '&lt;?';
			echo '<br>';
			echo Loc::getMessage("INTEO_TOOLS_LOG_UNDEFINED_DESCRIPTION_LINE1");
			echo '<br>';
			echo Loc::getMessage("INTEO_TOOLS_LOG_UNDEFINED_DESCRIPTION_LINE2");
			echo '<br><br>';
			echo 'define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log.txt");';
			echo '<br>';
			echo '?&gt;';
			echo EndNote();
			echo '<br>';
			echo Loc::getMessage("INTEO_TOOLS_LOG_UNDEFINED_NOTE");
			echo Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_DOC");
		}
		?></td></tr><?
	$tabControl->BeginNextTab();
		?>
		<tr>
		<td colspan="2"><?
		if (count($exception_handling) > 0 && isset($exception_handling['log']['settings']['file']) && $exception_handling['log']['settings']['file'] != '')
		{
			$logException = $_SERVER['DOCUMENT_ROOT'].'/'.$exception_handling['log']['settings']['file'];
			if (file_exists($logException) && is_readable($logException))
			{
				echo Loc::getMessage(
					"INTEO_TOOLS_LOG_DEFAULT_PATH",
					array("#PATH#" => str_replace($_SERVER["DOCUMENT_ROOT"], "", $logException))
				);
				echo '<br><br><br>';
			}
			$tabExceptionControl->Begin();
			$tabExceptionControl->BeginNextTab();
			if (file_exists($logException) && is_readable($logException))
			{
				$fileSizeLimit = 1024 * 1024;
				if (intval($memory_limit) > 0)
				{
					$fileSizeLimit = round(intval($memory_limit) / 5); //Max 20% of memory limit
				}

				if (filesize($logException) <= $fileSizeLimit)
				{
					$arLog = explode("----------\n", file_get_contents($logException));
					if (is_array($arLog) && count($arLog) > 0 && !empty($arLog[0]))
					{
						?><table class="internal" cellspacing="0" cellpadding="0" border="0" align="left" width="100%" style="float:none"><?
						?><tr class="heading">
							<td>DATE</td>
							<td>HOST</td>
							<td>TYPE</td>
							<td>TEXT</td>
						</tr><?
						$arLog = array_reverse(array_slice($arLog, -11));
						foreach ($arLog as $log)
						{
							$logHost = explode(" - Host: ", $log);
							if (isset($logHost[0]) && isset($logHost[1]))
							{
								?><tr>
									<td style="white-space: nowrap">
										<?echo $logHost[0];?>
									</td>
									<?
									$logText = explode(" - ", $logHost[1]);
									?>
									<td style="white-space: nowrap">
										<?echo array_shift($logText);?>
									</td>
									<td style="white-space: nowrap">
										<?echo array_shift($logText);?>
									</td>
									<td style="white-space: nowrap">
										<pre><?echo implode(' - ',$logText);?></pre>
									</td>
								</tr><?
							}
						}
						?></table><?
					}
					else
					{
						echo Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_EMPTY_LOG");
						echo Loc::getMessage("INTEO_TOOLS_LOG_DEFAULT_DOC");
					}

				}
				else
				{
					echo Loc::getMessage(
						"INTEO_TOOLS_LOG_DEFAULT_DOWNLOAD",
						array("#PATH#" => str_replace($_SERVER["DOCUMENT_ROOT"], "", $logException))
					);
				}
			}
			else
			{
				echo Loc::getMessage("INTEO_TOOLS_LOG_EXCEPTION_EMPTY_LOG");
			}
			$tabExceptionControl->BeginNextTab();?>
				<form name="inteo_tools_exceptions_operations" method="post" action="<?
				echo $APPLICATION->GetCurPage(); ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?= LANGUAGE_ID ?>">
					<?= bitrix_sessid_post(); ?>
				<input type="submit" id="clearexception" name="clearexception" value="Очистить лог">
				</form>
				<script>
					BX.ready(function () {
						BX.bind(BX('clearexception'), 'click', function (e) {
							if (!confirm('<?echo Loc::getMessage("INTEO_TOOLS_LOG_CLEARLOG")?>')) e.preventDefault();
						});
					});
				</script>
			<?$tabExceptionControl->End();
			echo '<br><br>';
			echo Loc::getMessage("INTEO_TOOLS_LOG_EXCEPTION_SETTINGS");
			echo "<pre class=\"code\">";
			print_r($exception_handling);
			echo "</pre>";
			echo Loc::getMessage("INTEO_TOOLS_LOG_EXCEPTION_NOTE");

		}
		else
		{
			$m = new CAdminMessage(
				array(
					"MESSAGE" => Loc::getMessage("INTEO_TOOLS_LOG_EXCEPTION_UNDEFINED"),
				)
			);
			echo $m->Show();
			echo Loc::getMessage("INTEO_TOOLS_LOG_EXCEPTION_DESCRIPTION");
			echo '. ';
			echo Loc::getMessage("INTEO_TOOLS_LOG_EXCEPTION_NOTE");
			echo '<br><br>';
			echo Loc::getMessage("INTEO_TOOLS_LOG_EXCEPTION_SETTINGS");
			echo "<pre class=\"code\">";
			print_r($exception_handling);
			echo "</pre>";
		}
		?></td></tr><?
	$tabControl->End();
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
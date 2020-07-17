<?php


namespace Inteo\Company;


use Bitrix\Main\Localization\Loc;

class Settings
{
	const MODULE_ID = "inteo.company";

	/**
	 * Extended function __AdmSettingsDrawRow
	 * @param $module_id
	 * @param $Option
	 * @param  bool|string  $siteId
	 * @param  bool|string  $postfix
	 * @param  bool|string  $rowClass
	 */
	public static function DrawRow($module_id, $Option, $siteId = false, $postfix = false, $rowClass = false)
	{
		$arControllerOption = \CControllerClient::GetInstalledOptions($module_id);
		if (!is_array($Option))
		{
			?>
			<tr class="heading<?if ($rowClass) echo ' '.$rowClass;?>">
				<td colspan="2"><?
					echo $Option;
				?></td>
			</tr>
			<?
		}
		elseif (isset($Option["note"]))
		{
			?>
			<tr<?if ($rowClass) echo ' class="'.$rowClass.'"';?>>
				<td colspan="2" align="center"><?
					echo BeginNote('align="center"');
					echo $Option["note"];
					echo EndNote();
				?></td>
			</tr>
			<?
		}
		else
		{
			if ($postfix) $Option[0] = $Option[0].'_'.$postfix;
			$name = ($siteId)?substr($Option[0], 0, (strlen($Option[0]) - strlen($siteId) - 1)):$Option[0];
			if ($Option[0] != "")
			{
				$val = \COption::GetOptionString($module_id, $name, $Option[2], $siteId);
			}
			else
			{
				$val = $Option[2];
			}
			if ($siteId) $Option[0] = $Option[0].'_'.$siteId;
			$type = $Option[3];
			$disabled = array_key_exists(4, $Option) && $Option[4] == 'Y'
				? ' disabled'
				: '';
			$sup_text = array_key_exists(5, $Option)
				? $Option[5]
				: '';
			?>
			<tr<?if ($rowClass) echo ' class="'.$rowClass.'"';?>>
				<td<?
				if ($type[0] == "multiselectbox" || $type[0] == "textarea" || $type[0] == "statictext" || $type[0] == "statichtml")
					echo ' class="adm-detail-valign-top"' ?> width="50%"><?
					if ($type[0] == "checkbox")
					{
						echo "<label for='".htmlspecialcharsbx($Option[0])."'>".$Option[1]."</label>";
					}
					else
					{
						echo $Option[1];
					}
					if (strlen($sup_text) > 0)
					{
						?><span class="required"><sup><?echo $sup_text ?></sup></span><?
					}
					?><a name="opt_<?echo htmlspecialcharsbx($Option[0]); ?>"></a>
				</td>
				<td width="50%"><?
					if ($type[0] == "checkbox")
					{
						?><input type="checkbox" <?
						if (isset($arControllerOption[$Option[0]]))
							echo ' disabled title="'.Loc::getMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"'; ?> id="<?
							echo htmlspecialcharsbx($Option[0]) ?>" name="<?
							echo htmlspecialcharsbx($Option[0]) ?>" value="Y"<?
						if ($val == "Y")
							echo " checked"; ?><?echo $disabled ?><?
						if ($type[2] <> '')
							echo " ".$type[2] ?>><?
					}
					elseif ($type[0] == "text" || $type[0] == "password")
					{
						?><input type="<?
						echo $type[0] ?>"<?
						if (isset($arControllerOption[$Option[0]]))
							echo ' disabled title="'.Loc::getMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"'; ?> size="<?
							echo $type[1] ?>" maxlength="255" value="<?
							echo htmlspecialcharsbx($val) ?>" name="<?
						echo htmlspecialcharsbx($Option[0]) ?>"<?echo $disabled ?><?echo ($type[0] == "password" || $type["noautocomplete"]
						? ' autocomplete="off"'
						: '') ?>><?
					}
					elseif ($type[0] == "selectbox")
					{
						$arr = $type[1];
						if (!is_array($arr))
						{
							$arr = array();
						}
						?><select name="<?
						echo htmlspecialcharsbx($Option[0]) ?>" <?
						if (isset($arControllerOption[$Option[0]]))
							echo ' disabled title="'.Loc::getMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"'; ?> <?echo $disabled ?>><?
						foreach ($arr as $key => $v):
							?>
							<option value="<?
							echo $key ?>"<?
							if ($val == $key)
								echo " selected" ?>><?
							echo htmlspecialcharsbx($v) ?></option><?
						endforeach;
						?></select><?
					}
					elseif ($type[0] == "multiselectbox")
					{
						$arr = $type[1];
						if (!is_array($arr))
						{
							$arr = array();
						}
						$arr_val = explode(",", $val);
						?><select size="5" <?
						if (isset($arControllerOption[$Option[0]]))
							echo ' disabled title="'.Loc::getMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"'; ?> multiple name="<?
						echo htmlspecialcharsbx($Option[0]) ?>[]"<?echo $disabled ?>><?
							foreach ($arr as $key => $v):
								?>
								<option value="<?
								echo $key ?>"<?
								if (in_array($key, $arr_val))
									echo " selected" ?>><?
								echo htmlspecialcharsbx($v) ?></option><?
							endforeach;
						?></select><?
					}
					elseif ($type[0] == "textarea")
					{
						?><textarea <?
						if (isset($arControllerOption[$Option[0]]))
							echo ' disabled title="'.Loc::getMessage("MAIN_ADMIN_SET_CONTROLLER_ALT").'"'; ?> rows="<?
							echo $type[1] ?>" cols="<?
							echo $type[2] ?>" name="<?
						echo htmlspecialcharsbx($Option[0]) ?>"<?echo $disabled ?>><?
						echo htmlspecialcharsbx($val) ?></textarea><?
					}
					elseif ($type[0] == "statictext")
					{
						echo htmlspecialcharsbx($val);
					}
					elseif ($type[0] == "statichtml")
					{
						echo $val;
					}
				?></td>
			</tr>
			<?
		}
	}
}
<?if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();?>
<?$this->setFrameMode(true);
$arJsFields = array();
$columnCounter = 1;
$columnFinished = true;
$maskedFields = false;
$compact = ($arParams["ERROR_MODE"] == 'COMPACT');

if (isset($arResult["POPUP"]) && $arResult["POPUP"] == "Y")
{
	?><a <?
		?>data-params="<?echo $this->GetEditAreaId($arParams['IBLOCK_ID']);?>" <?
		if ($arParams['COMPONENT_TEMPLATE'] != '.default')
		{
			?>data-template="<?echo $arParams['COMPONENT_TEMPLATE'];?>" <?
		}
		?>data-type="ajax" <?
		?>href="<?=$arResult["POPUP_LINK"]?>" <?
		?>class="modal-form <?=$arParams["POPUP_BUTTON_CSS"]?>"<?
		if (count($arParams["SET_FIELD"]) > 0)
		{
			foreach ($arParams["SET_FIELD"] as $setField)
			{
				$setValue = explode(':', $setField);
				if (count($setValue) == 2)
				{
					if (base64_encode(base64_decode($setValue[1], true)) === $setValue[1])
					{
						$setValue[1] = base64_decode($setValue[1]);
					} 
					echo ' data-'.strtolower(trim($setValue[0])).'="'.$setValue[1].'"';
				}
			}
		}
	?>><?=$arParams["~POPUP_TITLE"]?></a><?
?><script>
var <?echo $this->GetEditAreaId($arParams['IBLOCK_ID']);?> = '<?echo $arResult["PARAMS"];?>';
</script><?
}
else
{
	if (isset($arParams["POPUP"]) && $arParams["POPUP"] == "Y")
	{
		$popupClass = '';
		if ($arParams["POPUP_SIZE"] == 'SMALL') $popupClass.= ' popup-form_small';
		if ($arParams["POPUP_SIZE"] == 'MEDIUM') $popupClass.= ' popup-form_medium';
		echo '<div class="popup-form'.$popupClass.'">';
	}
	if ($arResult["isFormErrors"] == "Y")
	{
		echo $arResult["FORM_ERRORS_TEXT"];
	}
	echo htmlspecialchars_decode($arResult["FORM_NOTE"]);
	if ($arResult["isFormNote"] != "Y")
	{
		if ($arResult["isIblockTitle"] == "Y") {
			?><h3><?=$arResult["FORM_TITLE"]?></h3><?
		}
		echo $arResult["FORM_HEADER"];
		if ($arResult["FORM_DESCRIPTION"] != "") {
			?><div class="p"><?=$arResult["FORM_DESCRIPTION"]?></div><?
		}
		foreach ($arResult["DISPLAY_FIELDS"] as $arField)
		{
			if (isset($arField["HIDE"]) && $arField["HIDE"] == "Y")
			{
				continue;
			}
			/** File */
			if ($arField["TYPE"] == "file")
			{
				if (!$columnFinished) 
				{
					echo '</div>';
					$columnFinished = true;
					$columnCounter = 1;
				}
				$storeCode = $arField["CODE"];
				if ($arField["MULTIPLE"] == "Y") $arField["CODE"] = $arField["CODE"].'[]';
				?><div class="form-group<? if ($compact) echo ' form-group_compact';?>"><?
					if ($arParams["CUSTOM_INPUTFILE"] == "Y")
					{
						$listContainer = 'form_'.$arParams["IBLOCK_ID"].'_'.$storeCode.'_container';
						?><div id="<?=$listContainer?>" class="custom-file-container<?if ($arField["MULTIPLE"] != "Y") echo ' custom-file-container_min_1';?>"></div><div><label class="custom-file"><?
						?><input <?
 						?>class="custom-file-input" <?
						?>type="<?=$arField["TYPE"]?>" <?
						?>name="<?=$arField["CODE"]?>" <?
						?>id="form_<?=$arParams["IBLOCK_ID"]?>_<?=$storeCode?>" <?
						if ($arField["MULTIPLE"] == "Y") echo ' multiple';
						?>><?
						?><span class="custom-file-control btn btn-default"><?=GetMessage("INTEO_FORM_FILEUPLOAD")?> <?=$arField["NAME"]?></span></label></div><?

						$fileParams = array();
						$fileString = array();

						$fileParams[] = "listContainer: '#".$listContainer."'";
						if ($arField["MULTIPLE"] != "Y") $fileParams[] = "max: 1";
						if ($arField["IS_REQUIRED"] == "Y") $fileParams[] = "min: 1";
						if ($arField["FILE_TYPE"] != "") $fileParams[] = "accept: '".$arJsFields["rules"][$arField["CODE"]]["extension"]."'";
						?><script>
							$(function(){
								$('#form_<?=$arParams["IBLOCK_ID"]?>_<?=$storeCode?>').MultiFile({
									<? echo implode(',',$fileParams); ?>,
									STRING: {
										<? echo implode(',',$fileString); ?>
									}
								});
							});
						</script><?
					}
					else
					{
						?><label for="form_<?=$arParams["IBLOCK_ID"]?>_<?=$storeCode?>"><?=$arField["NAME"]?><?
							if ($arParams["SHOW_REQUIRED"] == "Y" && $arField["IS_REQUIRED"] == "Y")
							{
								?><span class="required">*</span><?
							}
						?></label><?
						?><input <?
						?>type="<?=$arField["TYPE"]?>" <?
						?>name="<?=$arField["CODE"]?>" <?
						?>id="form_<?=$arParams["IBLOCK_ID"]?>_<?=$storeCode?>" <?
						if ($arField["IS_REQUIRED"] == "Y") echo ' required';
						if ($arField["MULTIPLE"] == "Y") echo ' multiple';
						if ($arField["ACTIVE"] == "N") echo ' readonly';
						?>><?
						if ($arField["IS_REQUIRED"] == "Y")
						{
							$arJsFields["rules"][$arField["CODE"]]["required"] = true;
							$arJsFields["messages"][$arField["CODE"]]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_FILE");
						}
						if ($arField["FILE_TYPE"] != "")
						{
							$arJsFields["rules"][$arField["CODE"]]["extension"] = str_replace(',','|',str_replace(' ','',$arField["FILE_TYPE"]));
							$arJsFields["messages"][$arField["CODE"]]["extension"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_FILE_EXTENSION", Array ("#EXTENSION#" => $arField["FILE_TYPE"]));
						}
					}
					if ($arField["HINT"] != '')
					{
						?><p class="help-block"><?=$arField["HINT"]?></p><?
					}
				?></div><?
			}
			/** Checkbox */
			elseif ($arField["TYPE"] == "checkbox")
			{
				if (!$columnFinished) 
				{
					echo '</div>';
					$columnFinished = true;
					$columnCounter = 1;
				}
				?><div class="form-group<? if ($compact) echo ' form-group_compact';?>"><?
					?><label><?=$arField["NAME"]?><?
					if ($arParams["SHOW_REQUIRED"] == "Y" && $arField["IS_REQUIRED"] == "Y")
					{
						?><span class="required">*</span><?
					}
					?></label><?
					$oldCode = $arField["CODE"];
					$arField["CODE"] = $arField["CODE"].'[]';
					$iField = 1;
					foreach ($arField["VALUES"] as $arValue)
					{
						?><div class="checkbox<?if ($arParams["CUSTOM_BOXES"] == "Y") echo ' checkbox-styled';?>"><?
							?><input <?
							?>type="checkbox" <?
							?>name="<?=$arField["CODE"]?>" <?
							?>value="<?=$arValue["ID"]?>"<?
							?>id="form_<?=$arParams["IBLOCK_ID"]?>_<?=$oldCode?>_<?=$iField?>" <?
							if ($arField["ACTIVE"] == "N") echo ' readonly';
							if ((empty($arField["DEFAULT_VALUE"]) && $arValue["DEF"] == "Y") 
								|| ($arField["DEFAULT_VALUE"] == $arValue["ID"] 
									|| (is_array($arField["DEFAULT_VALUE"]) && in_array($arValue["ID"], $arField["DEFAULT_VALUE"])))) echo ' checked';
								?>> <label for="form_<?=$arParams["IBLOCK_ID"]?>_<?=$oldCode?>_<?=$iField?>"><?=$arValue["VALUE"]?></label><?
						?></div><?
						$iField++;
					}
					if ($arField["HINT"] != '')
					{
						?><p class="help-block"><?=$arField["HINT"]?></p><?
					}
				?></div><?
				if ($arField["IS_REQUIRED"] == "Y")
				{
					$arJsFields["rules"][$arField["CODE"]]["required"] = true;
					$arJsFields["messages"][$arField["CODE"]]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_CHECKBOX");
				}
			}
			/** Radio */
			elseif ($arField["TYPE"] == "radio")
			{
				if (!$columnFinished) 
				{
					echo '</div>';
					$columnFinished = true;
					$columnCounter = 1;
				}
				?><div class="form-group<? if ($compact) echo ' form-group_compact';?>"><?
					?><label><?=$arField["NAME"]?><?
					if ($arParams["SHOW_REQUIRED"] == "Y" && $arField["IS_REQUIRED"] == "Y")
					{
						?><span class="required">*</span><?
					}
					?></label><?
					$iField = 1;
					foreach ($arField["VALUES"] as $arValue)
					{
						?><div class="radio<?if ($arParams["CUSTOM_BOXES"] == "Y") echo ' radio-styled';?>"><?
							?><input <?
							?>type="radio" <?
							?>name="<?=$arField["CODE"]?>" <?
							?>value="<?=$arValue["ID"]?>"<?
							?>id="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>_<?=$iField?>" <?
							if ($arField["ACTIVE"] == "N") echo ' readonly';
							if ((empty($arField["DEFAULT_VALUE"]) && $arValue["DEF"] == "Y") || $arField["DEFAULT_VALUE"] == $arValue["ID"]) echo ' checked';
							?>> <label for="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>_<?=$iField?>"><?=$arValue["VALUE"]?></label><?
						?></div><?
						$iField++;
					}
					if ($arField["HINT"] != '')
					{
						?><p class="help-block"><?=$arField["HINT"]?></p><?
					}
				?></div><?
				if ($arField["IS_REQUIRED"] == "Y")
				{
					$arJsFields["rules"][$arField["CODE"]]["required"] = true;
					$arJsFields["messages"][$arField["CODE"]]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_RADIO");
				}
			}
			/** List */
			elseif ($arField["TYPE"] == "list")
			{
				if (!$columnFinished) 
				{
					echo '</div>';
					$columnFinished = true;
					$columnCounter = 1;
				}
				?><div class="form-group<? if ($compact) echo ' form-group_compact';?>"><?
					?><label for="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>"><?=$arField["NAME"]?><?
					if ($arParams["SHOW_REQUIRED"] == "Y" && $arField["IS_REQUIRED"] == "Y")
					{
						?><span class="required">*</span><?
					}
					?></label><?
					if ($arField["MULTIPLE"] == "Y") $arField["CODE"] = $arField["CODE"].'[]';
					?><select <?
					?>class="form-control" <?
					?>id="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>" <?
					?>name="<?=$arField["CODE"]?>"<?
					if ($arField["IS_REQUIRED"] == "Y") echo ' required';
					if ($arField["MULTIPLE"] == "Y") echo ' multiple';
					if ($arField["ACTIVE"] == "N") echo ' readonly';
					?>><?
						foreach ($arField["VALUES"] as $arValue)
						{
							?><option value="<?=$arValue["ID"]?>"<? 
							if ((empty($arField["DEFAULT_VALUE"]) && $arValue["DEF"] == "Y") 
								|| ($arField["DEFAULT_VALUE"] == $arValue["ID"] 
									|| (is_array($arField["DEFAULT_VALUE"]) && in_array($arValue["ID"], $arField["DEFAULT_VALUE"])))) echo ' selected';
								?>><?=$arValue["VALUE"]?></option><?
						}
					?></select><?
					if ($arField["HINT"] != '')
					{
						?><p class="help-block"><?=$arField["HINT"]?></p><?
					}
				?></div><?
				if ($arField["IS_REQUIRED"] == "Y")
				{
					$arJsFields["rules"][$arField["CODE"]]["required"] = true;
					if ($arField["MULTIPLE"] == "Y")
					{
						$arJsFields["messages"][$arField["CODE"]]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_CHECKBOX");
					}
					else
					{
						$arJsFields["messages"][$arField["CODE"]]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_RADIO");
					}
				}
			}
			/** Textarea */
			elseif ($arField["TYPE"] == "textarea")
			{
				if (!$columnFinished) 
				{
					echo '</div>';
					$columnFinished = true;
					$columnCounter = 1;
				}
				?><div class="form-group<? if ($compact) echo ' form-group_compact';?>"><?
					?><label for="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>"><?=$arField["NAME"]?><?
					if ($arParams["SHOW_REQUIRED"] == "Y" && $arField["IS_REQUIRED"] == "Y")
					{
						?><span class="required">*</span><?
					}
					?></label><?
					?><textarea <?
					?>class="form-control" <?
					?>name="<?=$arField["CODE"]?>" <?
					?>id="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>" <?
					?>rows="<?=(($arField["ROW_COUNT"] < 3) ? '3' : $arField["ROW_COUNT"])?>"<? 
					if ($arField["IS_REQUIRED"] == "Y") echo ' required';
					if ($arField["ACTIVE"] == "N") echo ' readonly';
					?>><?=((is_array($arField["DEFAULT_VALUE"])) ? $arField["DEFAULT_VALUE"]["TEXT"] : $arField["DEFAULT_VALUE"])?></textarea><?
					if ($arField["HINT"] != '')
					{
						?><p class="help-block"><?=$arField["HINT"]?></p><?
					}
				?></div><?
				if ($arField["IS_REQUIRED"] == "Y")
				{
					$arJsFields["rules"][$arField["CODE"]]["required"] = true;
					$arJsFields["messages"][$arField["CODE"]]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_TEXT");
				}
			}
			/** Hidden */
			elseif ($arField["TYPE"] == "hidden")
			{
				?><input <?
				?>type="<?=$arField["TYPE"]?>" <?
				?>name="<?=$arField["CODE"]?>" <?
				?>id="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>" <?
				?>value="<?=$arField["DEFAULT_VALUE"]?>"<? 
				?>><?
			}
			/** Default */
			else
			{
				if ($arField["TYPE"] == 'tel')
				{
					$maskedFields = true;
				}
				if (isset($arParams['FORM_TEMPLATE']) && $arParams['FORM_TEMPLATE'] > 1)
				{
					if ($columnCounter == 1 || $columnFinished) echo '<div class="row">';
					echo '<div class="col-md-'.(12 / $arParams['FORM_TEMPLATE']).'">';
				}
				?><div class="form-group<? if ($compact) echo ' form-group_compact';?>"><?
					?><label for="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>"><?=$arField["NAME"]?><?
					if ($arParams["SHOW_REQUIRED"] == "Y" && $arField["IS_REQUIRED"] == "Y")
					{
						?><span class="required">*</span><?
					}
					?></label><?
					?><input <?
					?>type="<?=$arField["TYPE"]?>" <?
					?>class="form-control" <?
					?>name="<?=$arField["CODE"]?>" <?
					?>id="form_<?=$arParams["IBLOCK_ID"]?>_<?=$arField["CODE"]?>" <?
					?>value="<?=$arField["DEFAULT_VALUE"]?>"<? 
					if ($arField["IS_REQUIRED"] == "Y") echo ' required';
					if ($arField["ACTIVE"] == "N") echo ' readonly';
					?>><?
					if ($arField["HINT"] != '')
					{
						?><p class="help-block"><?=$arField["HINT"]?></p><?
					}
				?></div><?
				if (isset($arParams['FORM_TEMPLATE']) && $arParams['FORM_TEMPLATE'] > 1)
				{
					echo '</div>';
					if ($columnCounter%$arParams['FORM_TEMPLATE'] == 0) 
					{
						echo '</div>';
						$columnFinished = true;
					}
					else
					{
						$columnFinished = false;
					}
					$columnCounter++;
				}
				if ($arField["IS_REQUIRED"] == "Y")
				{
					$arJsFields["rules"][$arField["CODE"]]["required"] = true;
					$arJsFields["messages"][$arField["CODE"]]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_TEXT");
					if ($arParams["PHONE_MASK"] == 'RU' && $arField["TYPE"] == 'tel')
					{
						$arJsFields["rules"][$arField["CODE"]]["ruPhone"] = true;
						$arJsFields["messages"][$arField["CODE"]]["ruPhone"] = GetMessage("INTEO_FORM_SHOW_PHONE_FORMAT");
					}
					if ($arField["TYPE"] == 'email')
					{
						$arJsFields["rules"][$arField["CODE"]]["email"] = true;
						$arJsFields["messages"][$arField["CODE"]]["email"] = GetMessage("INTEO_FORM_SHOW_EMAIL_FORMAT");
					}
				}
			}
		}
		if (!$columnFinished) echo '</div>';

		if ($arParams["SHOW_AGREEMENT"] == 'Y')
		{
			?><div class="form-group<? if ($compact) echo ' form-group_compact';?>"><?
				?><div class="checkbox<?if ($arParams["CUSTOM_BOXES"] == "Y") echo ' checkbox-styled';?>"><?
					?><input <?
					?>type="checkbox" <?
					?>name="CHECK_AGREEMENT" <?
					?>id="form_<?=$arParams["IBLOCK_ID"]?>_CHECK_AGREEMENT" <?
					?>value="Y"<?
					?> checked<?
					?>> <label for="form_<?=$arParams["IBLOCK_ID"]?>_CHECK_AGREEMENT"><?=GetMessage("INTEO_FORM_CHECK_AGREEMENT", Array ("#LINK#" => SITE_DIR.'agreement/'))?></label><?
				?></div><?
			?></div><?
			$arJsFields["rules"]["CHECK_AGREEMENT"]["required"] = true;
			$arJsFields["messages"]["CHECK_AGREEMENT"]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_AGREEMENT");
		}
		$frame = $this->createFrame()->begin('');
		$frame->setBrowserStorage(true);

		if ($arResult["isUseCaptcha"] == "Y") 
		{
			if (is_array($arResult["RECAPTCHA"]))
			{
				?><div<?
					?> id="recaptchaForm<?=$arParams["IBLOCK_ID"]?>"<?
					?> data-sitekey="<?=$arResult["RECAPTCHA"]["KEY"]?>"<?
					if ($arResult["RECAPTCHA"]["TYPE"] == 'invisible')
					{
						?> data-callback="onSubmit<?=$arParams["IBLOCK_ID"]?>"<?
					}
					if (isset($arResult["RECAPTCHA"]["COLOR"]))
					{
						?> data-theme="<?=$arResult["RECAPTCHA"]["COLOR"]?>"<?
					}
					?> data-size="<?
						if (isset($arResult["RECAPTCHA"]["SIZE"]) && $arResult["RECAPTCHA"]["SIZE"] == 'compact')
						{
							echo 'compact';
						}
						elseif ($arResult["RECAPTCHA"]["TYPE"] == 'invisible')
						{
							echo 'invisible';
						}
						else
						{
							echo 'normal';
						}
					?>"<?
					?> data-id="idRecaptchaForm<?=$arParams["IBLOCK_ID"]?>"<?
					?> class="g-recaptcha"<?
				?>><?
				?></div><?
			}
			else
			{
				?><div class="form-group<? if ($compact) echo ' form-group_compact';?>"><?
					?><label for="form_<?=$arParams["IBLOCK_ID"]?>_captcha_word"><?=GetMessage("INTEO_FORM_SHOW_CAPTCHA_TITLE")?></label><?
					?><div class="form-captcha" title="<?=GetMessage("INTEO_FORM_SHOW_CAPTCHA_RELOAD")?>"><?
						?><div class="form-captcha__image"><?
							?><input type="hidden" name="captcha_sid" class="js-captcha__sid" value="<?=htmlspecialcharsbx($arResult["CAPTCHACode"]);?>"><?
							?><img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CAPTCHACode"]);?>" class="js-captcha__image" width="180" height="46"><?
						?></div><?
						?><div class="form-captcha__reload"><span class="form-captcha__icon"><i class="material-icons">refresh</i></span></div><?
						?><div class="form-captcha__field"><?
							?><input class="form-control" type="text" id="form_<?=$arParams["IBLOCK_ID"]?>_captcha_word" name="CAPTCHA" value="" required><?
						?></div><?
					?></div><?
				?></div><?
				$arJsFields["rules"]["CAPTCHA"]["required"] = true;
				$arJsFields["messages"]["CAPTCHA"]["required"] = GetMessage("INTEO_FORM_SHOW_REQUIRED_CAPTCHA");
				?><script>
				$(document).ready(function(){
					'use strict';
					$('form[name="<?=$arResult["FORM_CODE"]?>"] .form-captcha__icon').click(function(){
						if ($(this).hasClass('active')) return false;
						var el = $(this), form = el.closest('form');
						el.addClass('active');
						$.getJSON('<?=$this->__folder?>/reload_captcha.php', function(data) {
							form.find('.js-captcha__image').attr('src','/bitrix/tools/captcha.php?captcha_sid='+data);
							form.find('.js-captcha__sid').val(data);
							el.removeClass('active');
						});
						return false;
					});
				});
				</script><?
			}
		}
		$frame->end();
		?><div class="form-submit"><?
			?><button type="submit" class="<?=$arParams["SEND_BUTTON_CLASS"]?>" data-loading-text="<?=GetMessage("INTEO_FORM_SHOW_SENDING")?>"><?=$arParams["~SEND_BUTTON_TEXT"]?></button><?
		?></div><?
		echo $arResult["FORM_FOOTER"];
		//No errors on first load from component
		echo(CJSCore::Init(array('validate','bootstrap'),true));
		if ($maskedFields)
		{
			echo(CJSCore::Init(array('inputmask'),true));
		}
		if ($arParams["CUSTOM_INPUTFILE"] == "Y")
		{
			echo(CJSCore::Init(array('multifile'),true));
		}
		?><script>
		var form_validator_<?=$arParams["IBLOCK_ID"]?>, idRecaptchaForm<?=$arParams["IBLOCK_ID"]?>;
		function onSubmit<?=$arParams["IBLOCK_ID"]?>(token) {
			if (typeof(token)==='undefined') token = "";
			var $form = $('#web_form_<?=$arParams["IBLOCK_ID"]?>');
			$form.find('button[type="submit"]').button('loading');
			var parentContainer = $form.closest('.form-wrap'),
				actionUrl = $form.attr('action'),
				formData = new FormData($form[0]);
			formData.append('params', '<?echo $arResult["PARAMS"];?>');
			parentContainer.find('.alert-danger').remove();
			$.ajax({
				url: actionUrl,
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				type: 'POST',
				//timeout: 30000,
				success: function (data) {
					$form.find('button[type="submit"]').removeAttr('disabled').button('reset');
					<?if ($arResult["isUseCaptcha"] == "Y" && is_array($arResult["RECAPTCHA"])):?>
						window.grecaptcha.reset(idRecaptchaForm<?=$arParams["IBLOCK_ID"]?>);
					<?endif;?>
					if (data.errors) {
						form_validator_<?=$arParams["IBLOCK_ID"]?>.showErrors(data.errors);
						form_validator_<?=$arParams["IBLOCK_ID"]?>.focusInvalid();
					}
					else if (data.errortext) {
						var tmp = document.createElement("DIV");
						tmp.innerHTML = data.errortext;
						alert(tmp.textContent || tmp.innerText || "");
					}
					else if (data.success) {
						form_validator_<?=$arParams["IBLOCK_ID"]?>.resetForm();
						<?if ($arParams["CONTAINER_ID"]!=''):?>
							$('#<?=$arParams["CONTAINER_ID"]?>').html('<div class="alert alert-success" role="alert">'+data.success+'</div>');
						<?else:?>
							parentContainer.append('<div class="alert alert-success" role="alert">'+data.success+'</div>');
							$form.hide();
						<?endif;?>
						inteoJSCore.sendForm('<?=str_replace("inteo_corp_","",array_search ($arParams["IBLOCK_ID"], \Inteo\Corporation\SubCache::$arIBlock))?>');
						//fancybox 2
						//$.fancybox.update();
						//$.fancybox.reposition();
					}
					else
					{
						parentContainer.prepend('<div class="alert alert-danger" role="alert"><?=GetMessage("INTEO_FORM_SHOW_SENDING_FAILED")?></div>');
						$form.find('button[type="submit"]').removeAttr('disabled').button('reset');
					}
				},
				error: function () {
					parentContainer.prepend('<div class="alert alert-danger" role="alert"><?=GetMessage("INTEO_FORM_SHOW_SENDING_FAILED")?></div>');
					$form.find('button[type="submit"]').removeAttr('disabled').button('reset');
				}
			});
			return false;
		}
		$(document).ready(function(){
			'use strict';
			inteoJSCore.openForm('<?=str_replace("inteo_corp_","",array_search ($arParams["IBLOCK_ID"], \Inteo\Corporation\SubCache::$arIBlock))?>');
			<?if ($arResult["isUseCaptcha"] == "Y" && is_array($arResult["RECAPTCHA"]))
			{
				?>if (window.grecaptcha && typeof window.grecaptcha.render === 'function') {
					var captchaParams = {}, $item = $('#recaptchaForm<?=$arParams["IBLOCK_ID"]?>');
					captchaParams['sitekey']  = $item.data("sitekey");
					if ($item.data("callback") !== undefined) {
						captchaParams['callback']  = $item.data("callback");
					}
					if ($item.data("theme") !== undefined) {
						captchaParams['theme']  = $item.data("theme");
					}
					if ($item.data("size") !== undefined) {
						captchaParams['size']  = $item.data("size");
					}
					if ($item.html() === '')
					{
						window[$item.data('id')] = window.grecaptcha.render($item.attr("id"), captchaParams);
					}

				}<?
			}?>
			<?if ($arParams["PHONE_MASK"] == 'RU' && $maskedFields):?>
			$('#web_form_<?=$arParams["IBLOCK_ID"]?> input[type=tel]').inputmask('mask', {'mask': '+7 (999) 999-99-99' });
			<?endif;?>
			$("#web_form_<?=$arParams["IBLOCK_ID"]?>").find('button[type="submit"]').removeAttr('disabled').button('reset');
			form_validator_<?=$arParams["IBLOCK_ID"]?> = $("#web_form_<?=$arParams["IBLOCK_ID"]?>").validate({
				debug: true, 
				<? if (count($arJsFields) > 0):?>
				rules: <? echo CUtil::PhpToJSObject($arJsFields['rules']) ?>,
				messages: <? echo CUtil::PhpToJSObject($arJsFields['messages']) ?>, 
				<?endif;?>
				<?if ($arParams["ERROR_MODE"] == 'N'):?>
				errorPlacement: function(error, element) {},
				<?endif;?>
				submitHandler: function(form) {
					<?if ($arResult["isUseCaptcha"] == "Y" && is_array($arResult["RECAPTCHA"]) && $arResult["RECAPTCHA"]["TYPE"] == 'invisible'):?>
					if (window.grecaptcha && typeof window.grecaptcha.execute === 'function') {
						window.grecaptcha.execute(idRecaptchaForm<?=$arParams["IBLOCK_ID"]?>);
					}
					else {
						alert('<?=GetMessage("INTEO_FORM_RECAPTCHA_LOAD_ERROR")?>');
					}
					<?else:?>
					onSubmit<?=$arParams["IBLOCK_ID"]?>();
					<?endif;?>
				}
			});
		});
		</script><?
	}
	if (isset($arResult["POPUP"]) && $arResult["POPUP"] == "Y")
	{
		echo '</div>';
	}
}
?>
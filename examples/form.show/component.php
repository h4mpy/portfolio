<?if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();

$ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
			&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if (!function_exists('reArrayFiles'))
{
	function reArrayFiles(&$file_post) {
		$file_ary = array();
		if (strcasecmp(LANG_CHARSET, 'windows-1251') == 0)
		{
			foreach ($file_post['name'] as &$fileName)
			{
				$fileName = Bitrix\Main\Text\Encoding::convertEncodingToCurrent($fileName);
			}
			unset($fileName);
		}
		$file_count = count($file_post['name']);
		$file_keys = array_keys($file_post);
		for ($i = 0; $i < $file_count; $i++) 
		{
			foreach ($file_keys as $key)
			{
				$file_ary[$i][$key] = $file_post[$key][$i];
			}
		}
		return $file_ary;
	}
}

if (!is_set( $arParams["CACHE_TIME"]))
{
	$arParams["CACHE_TIME"] = "36000000";
}

//Form handler
if ($arParams["HANDLER"] == "Y" && isset($_POST['params'])) 
{
	$arParams = unserialize(base64_decode($_POST['params']));
}

if ($arParams["POPUP"] == "Y" && !$ajax)
{
	$bCache = !(
		$arParams["CACHE_TYPE"] == "N"
		||
		(
			$arParams["CACHE_TYPE"] == "A"
			&&
			COption::GetOptionString("main", "component_cache_on", "Y") == "N"
		)
		||
		(
			$arParams["CACHE_TYPE"] == "Y"
			&&
			intval($arParams["CACHE_TIME"]) <= 0
		)
	);
	if ($bCache)
	{
		$arCacheParams = array();
		foreach ($arParams as $key => $value) if (substr($key, 0, 1) != "~") $arCacheParams[$key] = $value;
		$arCacheParams['POPUP_PARAMETERS'] = 'Y';
		$obFormCache = new CPHPCache;
		$CACHE_ID = SITE_ID."|".$componentName."|".md5(serialize($arCacheParams));
		$CACHE_PATH = "/".SITE_ID.CComponentEngine::MakeComponentPath($componentName);
	}
	if ($bCache && $obFormCache->InitCache($arParams["CACHE_TIME"], $CACHE_ID, $CACHE_PATH))
	{
		// if cache already exists - get vars
		$arCacheVars = $obFormCache->GetVars();
		$bVarsFromCache = true;
		$arResult = $arCacheVars["arResult"];
	}
	else
	{
		$bVarsFromCache = false;

		$arResult["PARAMS"] = base64_encode(serialize($arParams));
		if ($bCache)
		{
			$obFormCache->StartDataCache();
			$GLOBALS['CACHE_MANAGER']->StartTagCache($CACHE_PATH);
			$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_forms');
			$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_form_'.$arParams["IBLOCK_ID"]);
			$GLOBALS['CACHE_MANAGER']->EndTagCache();
			$obFormCache->EndDataCache(
				array(
					"arResult" => $arResult,
				)
			);
		}
	}
	$arResult["POPUP"] = "Y";
	$arResult["POPUP_LINK"] = SITE_DIR.'ajax/form.php';
	$this->IncludeComponentTemplate();
	return;
}

// Visual
if (CModule::IncludeModule("iblock"))
{

	/** Spambot Honeypot */
	$arSpamFields = array(
		'country',
		'surname',
		'patronymic',
		'website',
		'envelope',
		'postalcode',
		'street',
		'random_x_field_7kja',
	);

	/** HTML5 field types */
	$arExtraTypes = array(
		"PHONE" => "tel",
		"FAX" => "tel",
		"EMAIL" => "email",
		"MAIL" => "email",
		"WEBSITE" => "url",
		"SITE" => "url",
	);

	$arParams["USE_CAPTCHA"] = $arParams["USE_CAPTCHA"] == "Y" && !$USER->IsAuthorized();

	$bCache = !(
		$_SERVER["REQUEST_METHOD"] == "POST"
		&&
		(
			!empty($_REQUEST["form_submit"])
		)
		||
		$_REQUEST['formresult'] == 'ADDOK'
	)
	&&
	!(
		$arParams["CACHE_TYPE"] == "N"
		||
		(
			$arParams["CACHE_TYPE"] == "A"
			&&
			COption::GetOptionString("main", "component_cache_on", "Y") == "N"
		)
		||
		(
			$arParams["CACHE_TYPE"] == "Y"
			&&
			intval($arParams["CACHE_TIME"]) <= 0
		)
	);

	if ($bCache)
	{
		$arCacheParams = array();
		foreach ($arParams as $key => $value) if (substr($key, 0, 1) != "~") $arCacheParams[$key] = $value;
		$obFormCache = new CPHPCache;
		$CACHE_ID = SITE_ID."|".$componentName."|".md5(serialize($arCacheParams));
		$CACHE_PATH = "/".SITE_ID.CComponentEngine::MakeComponentPath($componentName);
	}
	if ($bCache && $obFormCache->InitCache($arParams["CACHE_TIME"], $CACHE_ID, $CACHE_PATH))
	{
		// if cache already exists - get vars
		$arCacheVars = $obFormCache->GetVars();
		$bVarsFromCache = true;

		$arResult = $arCacheVars["arResult"];

		$arResult['FORM_NOTE'] = '';
		$arResult['isFormNote'] = 'N';
	}
	else
	{
		$bVarsFromCache = false;
		$arResult["PARAMS"] = base64_encode(serialize($arParams));
		if($arParams["IBLOCK_ID"] > 0)
		{
			$arResult["F_RIGHT"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
			if ($arResult["F_RIGHT"] == "D")
			{
				$arResult["ERROR"] = "INTEO_FORM_C_FORM_ACCESS_DENIED";
			}

			$arIBlock = CIBlock::GetList(array(), array("ID" => $arParams["IBLOCK_ID"]))->Fetch();
			if ($arIBlock["IBLOCK_TYPE_ID"] != 'inteo_corp_forms') die();
			$arResult["FORM_CODE"] = $arIBlock["CODE"];
			$arResult["FORM_TITLE"] = $arIBlock["NAME"];
			$arResult["FORM_DESCRIPTION"] = $arIBlock["DESCRIPTION"];
			$arResult["FORM_DESCRIPTION_TYPE"] = $arIBlock["DESCRIPTION_TYPE"];

			$rsFields = CIBlock::GetProperties($arParams["IBLOCK_ID"], array("SORT" => "ASC", "NAME" => "ASC"), array());

			while ($arField = $rsFields->Fetch())
			{
				$arResult["FIELDS"][] = $arField;
			}

			foreach ($arResult["FIELDS"] as $key => $arField)
			{
				$field = array(
					"NAME" => $arField["NAME"],
					"ACTIVE" => $arField["ACTIVE"],
					"CODE" => $arField["CODE"],
					"PROPERTY_TYPE" => $arField["PROPERTY_TYPE"],
					"IS_REQUIRED" => $arField["IS_REQUIRED"],
					"HINT" => $arField["HINT"],
					"DEFAULT_VALUE" => $arField["DEFAULT_VALUE"],
					"MULTIPLE" => $arField["MULTIPLE"],
					"MULTIPLE_CNT" => $arField["MULTIPLE_CNT"],
					"ROW_COUNT" => $arField["ROW_COUNT"],
					"FILE_TYPE" => $arField["FILE_TYPE"],
					"USER_TYPE" => (isset($arField["USER_TYPE"]) ? $arField["USER_TYPE"] : ""),
				);
				/** String */
				if ($arField["PROPERTY_TYPE"] == "S") 
				{
					// temporarily disabled
					/*
					if (isset($arField["USER_TYPE"]) && $arField["USER_TYPE"] == "DateTime")
					{
						$field["TYPE"] = "datetime";
					}
					elseif (isset($arField["USER_TYPE"]) && $arField["USER_TYPE"] == "Date")
					{
						$field["TYPE"] = "date";
					}
					else*/
					if (strpos(strtoupper($arField["CODE"]), 'HIDDEN') !== false)
					{
						$field["TYPE"] = "hidden";
					}
					else
					{
						if ($arField["ROW_COUNT"] > 1 || (isset($arField["USER_TYPE"]) && $arField["USER_TYPE"] == "HTML"))
						{
							$field["TYPE"] = "textarea";
						}
						else
						{
							if (isset($arExtraTypes[$arField["CODE"]]))
							{
								$field["TYPE"] = $arExtraTypes[$arField["CODE"]];
							}
							else
							{
								$field["TYPE"] = "text";
							}
						}
					}
				}
				/** Number */
				elseif ($arField["PROPERTY_TYPE"] == "N") 
				{
					$field["TYPE"] = "number";
				}
				/** List */
				elseif ($arField["PROPERTY_TYPE"] == "L") 
				{
					if ($arField["LIST_TYPE"] == "C")
					{
						if ($arField["MULTIPLE"] == "Y")
						{
							$field["TYPE"] = "checkbox";
						}
						else
						{
							$field["TYPE"] = "radio";
						}
					}
					else
					{
						$field["TYPE"] = "list";
					}
					$field["VALUES"] = array();
					$rsPropValues = CIBlockProperty::GetPropertyEnum($arField["ID"]);
					while ($arPropValue = $rsPropValues->GetNext())
					{
						$field["VALUES"][$arPropValue["ID"]] = $arPropValue;
					}
				}
				/** File */
				elseif ($arField["PROPERTY_TYPE"] == "F") 
				{
					$field["TYPE"] = "file";
				}
				else 
				{
					continue;
				}
				$arResult["DISPLAY_FIELDS"][$arField["CODE"]] = $field;
			}
			if (isset($arParams['BASKET_FIELD']))
			{
				$arResult["DISPLAY_FIELDS"][$arParams['BASKET_FIELD']]["HIDE"] = "Y";
			}
		}
		else
		{
			$arResult["ERROR"] = "INTEO_FORM_C_FORM_NOT_FOUND";
		}

		$arResult["SITE"] = CSite::GetByID(SITE_ID)->Fetch();

		if ($bCache)
		{
			$obFormCache->StartDataCache();
			$GLOBALS['CACHE_MANAGER']->StartTagCache($CACHE_PATH);
			$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_forms');
			$GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_form_'.$arParams["IBLOCK_ID"]);
			$GLOBALS['CACHE_MANAGER']->EndTagCache();
			$obFormCache->EndDataCache(
				array(
					"arResult" => $arResult,
				)
			);
		}
	}
	if (strlen($arResult["ERROR"]) <= 0)
	{
		if (count($arResult["DISPLAY_FIELDS"]) > 0)
		{
			foreach ($arResult["DISPLAY_FIELDS"] as $field)
			{
				$arSpamFields = array_diff($arSpamFields, array(strtolower($field["CODE"])));
			}
		}

		$spamField = strval(reset($arSpamFields));

		/** get/post processing */
		$arResult["arrVALUES"] = array();
		if (($_REQUEST['FORM_ID'] == $arParams["IBLOCK_ID"]) && strlen($_REQUEST["form_submit"]) > 0 && (isset($_REQUEST[$spamField]) && $_REQUEST[$spamField] === ""))
		{
			/** check errors */
			foreach ($arResult["DISPLAY_FIELDS"] as $fieldCode => $arField)
			{
				if (($arField["TYPE"] != 'file' && empty($_REQUEST[$fieldCode]) || ($arField["TYPE"] == 'file' && empty($_FILES[$fieldCode]))) && $arField["IS_REQUIRED"] == "Y")
				{
					if ($arField["MULTIPLE"] == 'Y') 
					{
						$fieldCode.='[]';
					}
					$arResult["FORM_ERRORS"][$fieldCode] = GetMessage("INTEO_FORM_C_FORM_REQUIRED_FIELD", Array ("#FIELD#" => $arField["NAME"]));
				}
			}
			if ($arParams["SHOW_AGREEMENT"] == "Y")
			{
				if (empty($_REQUEST["CHECK_AGREEMENT"]))
				{
					$arResult["FORM_ERRORS"]["CHECK_AGREEMENT"] = GetMessage("INTEO_FORM_C_FORM_REQUIRED_AGREEMENT");
				}
			}
			if ($arParams["USE_CAPTCHA"])
			{
				if (COption::GetOptionString("inteo.corporation", "recaptcha", "N") == "Y")
				{
					require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/inteo.corporation/recaptcha/autoload.php');
					if (isset($_POST['g-recaptcha-response']))
					{
						$recaptcha = new \ReCaptcha\ReCaptcha(COption::GetOptionString("inteo.corporation", "recaptcha_secret", "N"));
						$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
						if ($resp->isSuccess()) {
						}
						else
						{
							$arResult["FORM_ERRORS"] = GetMessage("INTEO_FORM_C_WRONG_RECAPTCHA");
						}
					}
					else 
					{
						$arResult["FORM_ERRORS"] = GetMessage("INTEO_FORM_C_WRONG_RECAPTCHA");
					}
				}
				else
				{
					$captcha_sid = $_POST['captcha_sid'];
					$captcha_word = $_POST['CAPTCHA'];
					if (!$APPLICATION->CaptchaCheckCode($captcha_word, $captcha_sid))
					{
						$arResult["FORM_ERRORS"]["CAPTCHA"] = GetMessage("INTEO_FORM_C_WRONG_CAPTCHA");
					}
				}
			}
			if (count($arResult["FORM_ERRORS"]) <= 0)
			{
				if ($ajax)
					$_REQUEST = \Bitrix\Main\Text\Encoding::convertEncoding($_REQUEST, 'utf-8', LANG_CHARSET);
				foreach ($_REQUEST as $code => $value)
				{
					if ($arResult["DISPLAY_FIELDS"][$code]["USER_TYPE"] == "HTML")
					{
						$_REQUEST[$code] = array("VALUE" => array("TEXT" => $value, "TYPE" => 'text'));
					}
				}
				if (isset($arParams['BASKET_FIELD']))
				{
					\Bitrix\Main\Loader::includeModule('inteo.corporation');
					$_REQUEST[$arParams['BASKET_FIELD']] = array("VALUE" => array("TEXT" => \Inteo\Corporation\Basket::getProductsString(), "TYPE" => 'html'));
				}
				$arResult["arrVALUES"] = $_REQUEST;
				if (isset($_FILES)) 
				{
					foreach ($_FILES as $key => $arFiles)
					{
						if (is_array($arFiles['name']))
						{
							$arResult["arrVALUES"][$key] = reArrayFiles($arFiles);
						}
						else 
						{
							$arResult["arrVALUES"][$key][] = $arFiles;
						}
					}
				}
				$el = new CIBlockElement;

				$arFields = array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"NAME" => GetMessage("INTEO_FORM_C_MESSAGE_NAME").ConvertTimeStamp(),
					"PROPERTY_VALUES" => $arResult["arrVALUES"],
				);

				if ($RESULT_ID = $el->Add($arFields))
				{
					$arResult["FORM_RESULT"] = "ADDOK";

					$eventTypeName = "INTEO_SEND_FORM_".$arParams["IBLOCK_ID"];

					$arEvent = CEventType::GetByID($eventTypeName, $arResult["SITE"]["LANGUAGE_ID"])->Fetch();
					if (!is_array($arEvent))
					{
						//$description = GetMessage("INTEO_FORM_C_EVENT_DESCRIPTION", Array ("#NAME#" => $arResult["FORM_TITLE"]));
						$description = "#MESSAGE# - ".GetMessage("INTEO_FORM_C_MESSAGE")."\n";
						foreach ($arResult["DISPLAY_FIELDS"] as $fieldCode => $arField)
						{
							if ($arField["TYPE"] != "file")
							{
								$description.= "#".$fieldCode."# - ".$arField["NAME"]."\n";
							}
						}
						$description.= "#SITE_URL# - ".GetMessage("INTEO_FORM_C_SITE_URL")."\n";
						$eventType = new CEventType;
						$arEventTypeFields = array(
							"LID" => $arResult["SITE"]["LANGUAGE_ID"],
							"EVENT_NAME" => $eventTypeName,
							"NAME" => $arResult["FORM_TITLE"],
							"DESCRIPTION" => $description,
						);
						$eventType->Add($arEventTypeFields);
					}

					$arMessage = CEventMessage::GetList($arResult["SITE"]["ID"], $order = "desc", array("TYPE_ID" => $eventTypeName))->Fetch();
					if (!is_array($arMessage))
					{
						$eventMessage = new CEventMessage;
						$arMessage = array();
						$arMessage["ID"] = $eventMessage->Add(array(
							"ACTIVE" => "Y",
							"EVENT_NAME" => $eventTypeName,
							"LID" => array($arResult["SITE"]["LID"]),
							"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
							"EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
							"CC" => "#CC#",
							"BCC" => "#BCC#",
							"SUBJECT" => "#FORM_NAME#",
							"BODY_TYPE" => "html",
							"REPLY_TO" => "#REPLY_TO#",
							"MESSAGE" => "#MESSAGE#",
							"ADDITIONAL_FIELD" => array(
								array(
									"NAME"  => "X-FORM-FILES",
									"VALUE" => "#FILES#"
								)
							)
						));
					}

					$arMailFields = array(
						"FORM_NAME" => $arResult["FORM_TITLE"],
						"CC" => "",
						"BCC" => "",
						"REPLY_TO" => "",
						"FILES" => "",
					);
					$messageHtml = $messageFiles = '';
					$arFileList = array();
					foreach ($arResult["DISPLAY_FIELDS"] as $fieldCode => $arField)
					{
						if ($arField["TYPE"] != "file")
						{
							if (isset($_REQUEST[$fieldCode]) && ((is_array($_REQUEST[$fieldCode]) && count($_REQUEST[$fieldCode]) > 0) || $_REQUEST[$fieldCode]!=''))
							{
								$preparedText = '';
								if ($arField["TYPE"] == "textarea")
								{
									// not implemented
									/*
									if ($arField["MULTIPLE"] == 'Y') 
									{
									}
									*/
									if ($arResult["DISPLAY_FIELDS"][$fieldCode]["USER_TYPE"] == "HTML")
									{
										$preparedText = nl2br(htmlspecialcharsBack($_REQUEST[$fieldCode]["VALUE"]["TEXT"]));
									}
									else
									{
										$preparedText = nl2br(htmlspecialcharsBack($_REQUEST[$fieldCode]));
									}
								}
								elseif ($arField["PROPERTY_TYPE"] == "L")
								{
									if (is_array($_REQUEST[$fieldCode])) 
									{
										$arValues = array();
										foreach ($_REQUEST[$fieldCode] as $value)
										{
											$arValues[] = htmlspecialcharsBack($arField["VALUES"][$value]["VALUE"]);
										}
										$preparedText = htmlspecialcharsBack(implode(', ',$arValues));
									}
									else
									{
										$preparedText = htmlspecialcharsBack($arField["VALUES"][$_REQUEST[$fieldCode]]["VALUE"]);
									}
								}
								else 
								{
									if (is_array($_REQUEST[$fieldCode])) 
									{
										$preparedText = htmlspecialcharsBack(implode(', ',$_REQUEST[$fieldCode]));
									}
									else
									{
										$preparedText = htmlspecialcharsBack($_REQUEST[$fieldCode]);
										if ($fieldCode == 'EMAIL' && preg_match("/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z\-\.]+$/Du", trim($_REQUEST[$fieldCode])) > 0)
										{
											$arMailFields["REPLY_TO"] = trim($_REQUEST[$fieldCode]);
										}
									}
								}
								$messageHtml.= $arField["NAME"].': <b>'.$preparedText.'</b><br />';
								$arMailFields[$fieldCode] = $preparedText;
							}
							else
							{
								$messageHtml.= $arField["NAME"].': <i>'.GetMessage("INTEO_FORM_C_EMPTY_FIELD").'</i><br />';
								$arMailFields[$fieldCode] = GetMessage("INTEO_FORM_C_EMPTY_FIELD");
							}
						}
						else 
						{
							if (isset($_FILES[$fieldCode]))
							{
								$messageFiles = '';
								if (is_array($_FILES[$fieldCode]['name']) && count($_FILES[$fieldCode]['name']) > 0)
								{
									$messageFiles.= implode(', ',$_FILES[$fieldCode]['name']);
								}
								else 
								{
									if ($_FILES[$fieldCode]['name']!='')
									{
										$messageFiles.= $_FILES[$fieldCode]['name'];
									}
								}
								if (strcasecmp(LANG_CHARSET, 'windows-1251') == 0)
								{
									$messageFiles = Bitrix\Main\Text\Encoding::convertEncodingToCurrent($messageFiles);
								}
								$dbFileProps = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $RESULT_ID, array("sort" => "asc"), Array("CODE" => $fieldCode));
								while ($arMessageFile = $dbFileProps->GetNext())
								{
									$arFileList[] = $arMessageFile['VALUE'];
								}
								if ($messageFiles!='')
								{
									$messageHtml.= $arField["NAME"].': <b>'.$messageFiles.'</b> '.GetMessage("INTEO_FORM_C_FILE_FIELD").'<br />';
								}
							}
						}
					}
					$arMailFields["MESSAGE"] = $messageHtml;
					$arMailFields["MESSAGE"].= GetMessage("INTEO_FORM_C_FORM_ADMIN_FOOTER", Array ("#LINK#" => $_SERVER['HTTP_REFERER']));
					$arMailFields["MESSAGE"].= GetMessage("INTEO_FORM_C_FORM_ADMIN_LINK", Array ("#LINK#" => (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http').'://'.$_SERVER['SERVER_NAME'].'/bitrix/admin/iblock_list_admin.php?IBLOCK_ID='.$arParams['IBLOCK_ID'].'&type='.$arParams["IBLOCK_TYPE"].'&lang='.$arResult["SITE"]["LANGUAGE_ID"].'&find_section_section=0'));
					if ($arParams["ADD_EMAIL"] != '')
					{
						$arMailFields["CC"] = trim($arParams["ADD_EMAIL"]);
					}
					$arMailFields["FILES"] = implode(',',$arFileList);
					$arMailFields["SITE_URL"] = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http').'://'.$_SERVER['SERVER_NAME'];
					$event = new \Bitrix\Main\Event(
						'inteo.corporation',
						'onBeforeMailSend',
						array(
							'fields' => $arMailFields
						)
					);
					$event->send();
					CEvent::SendImmediate($eventTypeName, $arResult["SITE"]["LID"], $arMailFields, "Y", $arMessage["ID"], $arFileList);
					if ($ajax)
					{
						$APPLICATION->RestartBuffer();
						if (isset($arParams['BASKET_FIELD']))
						{
							\Inteo\Corporation\Basket::deleteAllItems();
						}
						echo \Bitrix\Main\Web\Json::encode(array("success" => htmlspecialcharsBack($arParams["SUCCESS_MESSAGE"])));
					}
					else
					{
						LocalRedirect(
							$APPLICATION->GetCurPageParam(
								"IBLOCK_ID=".$arParams["IBLOCK_ID"]
								."&RESULT_ID=".$RESULT_ID
								."&formresult=".urlencode($arResult["FORM_RESULT"]),
								array('formresult', 'strFormNote', 'IBLOCK_ID', 'RESULT_ID')
							)
						);
					}
					die();

				}
				else
				{
					$arResult["FORM_ERRORS"] = $el->LAST_ERROR;
				}
			}
		}

		if (!empty($_REQUEST["formresult"]) && strtoupper($_REQUEST["formresult"]) == "ADDOK")
		{
			$arResult['FORM_NOTE'] = (!empty($arParams["SUCCESS_MESSAGE"]) ? $arParams["SUCCESS_MESSAGE"] : GetMessage('INTEO_FORM_C_FORM_NOTE_ADDOK'));
		}
		
		$arResult["isFormErrors"] = ((is_array($arResult["FORM_ERRORS"]) && count($arResult["FORM_ERRORS"]) > 0) ? "Y" : "N");
		if ($arParams["USE_CAPTCHA"] == "Y") 
		{
			if (COption::GetOptionString("inteo.corporation", "recaptcha", "N") == "Y")
			{
				if (strlen($recaptchaKey = COption::GetOptionString("inteo.corporation", "recaptcha_key", "")) > 0)
				{
					$arResult["RECAPTCHA"]["KEY"] = $recaptchaKey;
					$arResult["RECAPTCHA"]["TYPE"] = COption::GetOptionString("inteo.corporation", "recaptcha_type", "v2");
					if ($arResult["RECAPTCHA"]["TYPE"] == 'v2')
					{
						$arResult["RECAPTCHA"]["SIZE"] = COption::GetOptionString("inteo.corporation", "recaptcha_size", "default");
						$arResult["RECAPTCHA"]["COLOR"] = COption::GetOptionString("inteo.corporation", "recaptcha_color", "light");
					}
				}
			}
			else
			{
				$arResult["CAPTCHACode"] = $APPLICATION->CaptchaGetCode();
			}
		}
		$arResult = array_merge(
			$arResult,
			array(
				"isFormNote" => strlen($arResult["FORM_NOTE"]) ? "Y" : "N",
				"FORM_HEADER" => sprintf(
					"<div class=\"form-wrap\"><form name=\"%s\" action=\"%s\" method=\"%s\" id=\"web_form_%s\" enctype=\"multipart/form-data\">",
					$arResult["FORM_CODE"], SITE_DIR.'ajax/form.php', "POST", $arParams["IBLOCK_ID"]
				)
				.'<input type="hidden" name="FORM_ID" value="'.$arParams['IBLOCK_ID'].'">'
				.'<input type="hidden" name="form_submit" value="Y">'
				.'<div class="hide"><input type="text" name="'.reset($arSpamFields).'" value="" autocomplete="off"></div>',
				"FORM_DESCRIPTION" =>
					$arResult["FORM_DESCRIPTION_TYPE"] == "html" ?
					trim($arResult["FORM_DESCRIPTION"]) :
					nl2br(htmlspecialcharsbx(trim($arResult["FORM_DESCRIPTION"]))),
				"isIblockTitle" => $arParams["SHOW_TITLE"] == "Y" ? "Y" : "N",
				"isUseCaptcha" => $arParams["USE_CAPTCHA"] == "Y" ? "Y" : "N",
				"DATE_FORMAT" => CLang::GetDateFormat("SHORT"),
				"FORM_FOOTER" => "</form></div>"
			)
		);
		if ($arResult["isFormErrors"] == "Y")
		{
			ob_start();
			if (is_array($arResult["FORM_ERRORS"]))
			{
				ShowError(implode( '<br>', $arResult["FORM_ERRORS"]));
			}
			else 
			{
				ShowError(strval($arResult["FORM_ERRORS"]));
			}
			$arResult["FORM_ERRORS_TEXT"] = ob_get_contents();
			ob_end_clean();
		}
		if ($ajax)
		{
			$APPLICATION->RestartBuffer();
			if (is_array($arResult["FORM_ERRORS"]) && count($arResult["FORM_ERRORS"]) > 0)
			{
				echo \Bitrix\Main\Web\Json::encode(array("errors" => $arResult["FORM_ERRORS"]));
				die();
			}
			else {
				if ($arResult["FORM_ERRORS"] != '')
				{
					echo \Bitrix\Main\Web\Json::encode(array("errortext" => $arResult["FORM_ERRORS"]));
					die();
				}
			}
		}
		$arResult["FORM_URL"] = SITE_DIR.'ajax/form.php';
		$this->IncludeComponentTemplate();
	}
	else
	{
		ShowError(GetMessage($arResult["ERROR"]));
	}
}
else
{
	ShowError(GetMessage("IB_MODULE_NOT_INSTALLED"));
}
?>
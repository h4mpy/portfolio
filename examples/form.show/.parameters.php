<?if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();

if( !CModule::IncludeModule("iblock") ) return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arPhoneMask = array(
	"N" => GetMessage("INTEO_FORM_SHOW_P_PHONE_MASK_N"),
	"RU" => GetMessage("INTEO_FORM_SHOW_P_PHONE_MASK_RU"),
);

$arTemplates = array(
	"1" => GetMessage("INTEO_FORM_SHOW_P_FORM_TEMPLATE_DEFAULT"),
	"2" => GetMessage("INTEO_FORM_SHOW_P_FORM_TEMPLATE_2COL"),
	"3" => GetMessage("INTEO_FORM_SHOW_P_FORM_TEMPLATE_3COL"),
);

$arPopup = array(
	"SMALL" => GetMessage("INTEO_FORM_SHOW_P_POPUP_SIZE_SMALL"),
	"MEDIUM" => GetMessage("INTEO_FORM_SHOW_P_POPUP_SIZE_MEDIUM"),
	"BIG" => GetMessage("INTEO_FORM_SHOW_P_POPUP_SIZE_BIG"),
);

$arErrorsFormat = array(
	"N" => GetMessage("INTEO_FORM_SHOW_P_ERROR_TYPE_N"),
	"COMPACT" => GetMessage("INTEO_FORM_SHOW_P_ERROR_TYPE_COMPACT"),
	"STANDART" => GetMessage("INTEO_FORM_SHOW_P_ERROR_TYPE_STANDART"),
);

$arComponentParameters = array(
	"GROUPS" => array(
		"IBLOCK_PARAMS" => array(
			"SORT" => "110",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SELECT"),
		),
		"POPUP_PARAMS" => array(
			"SORT" => "120",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_POPUP_PARAMS"),
		),
		"FORM_PARAMS" => array(
			"SORT" => "130",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_PARAMS"),
		),
		"BUTTON_PARAMS" => array(
			"SORT" => "140",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_BUTTON_PARAMS"),
		),
		"SEND_PARAMS" => array(
			"SORT" => "150",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SEND_PARAMS"),
		),
		"CUSTOM_ELEMENTS" => array(
			"SORT" => "160",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_CUSTOM_ELEMENTS"),
		),
		"SET_FIELDS" => array(
			"SORT" => "170",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SET_FIELDS"),
		),
		"ADDITIONAL_PARAMS" => array(
			"SORT" => "180",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_ADDITIONAL_PARAMS"),
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "IBLOCK_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "IBLOCK_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
			"ADDITIONAL_VALUES" => "Y",
		),
		"POPUP" => array(
			"PARENT" => "POPUP_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_POPUP"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"SHOW_TITLE" => array(
			"PARENT" => "FORM_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SHOW_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SHOW_REQUIRED" => array(
			"PARENT" => "FORM_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SHOW_REQUIRED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"FORM_TEMPLATE" => array(
			"PARENT" => "FORM_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_FORM_TEMPLATE"),
			"TYPE" => "LIST",
			"VALUES" => $arTemplates,
			"DEFAULT" => "1",
		),
		"ERROR_MODE" => array(
			"PARENT" => "FORM_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_ERROR_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arErrorsFormat,
			"DEFAULT" => "COMPACT",
		),
		"PHONE_MASK" => array(
			"PARENT" => "FORM_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_PHONE_MASK"),
			"TYPE" => "LIST",
			"VALUES" => $arPhoneMask,
			"DEFAULT" => "RU",
		),
		"SUCCESS_MESSAGE" => array(
			"PARENT" => "FORM_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SUCCESS_MESSAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("INTEO_FORM_SHOW_P_DEFAULT_SUCCESS_MESSAGE"),
		),
		"USE_CAPTCHA" => array(
			"PARENT" => "FORM_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_USE_CAPTCHA"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SHOW_AGREEMENT" => array(
			"PARENT" => "FORM_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SHOW_AGREEMENT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SEND_BUTTON_TEXT" => array(
			"PARENT" => "BUTTON_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SEND_BUTTON_TEXT"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("INTEO_FORM_SHOW_P_DEFAULT_SEND_BUTTON_TEXT"),
		),
		"SEND_BUTTON_CLASS" => array(
			"PARENT" => "BUTTON_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SEND_BUTTON_CLASS"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("INTEO_FORM_SHOW_P_DEFAULT_SEND_BUTTON_CLASS"),
		),
		"ADD_EMAIL" => array(
			"PARENT" => "SEND_PARAMS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_ADD_EMAIL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"SET_FIELD" => array(
			"PARENT" => "SET_FIELDS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_SET_FIELD"),
			"TYPE" => "STRING",
			"MULTIPLE" => "Y",
			"DEFAULT" => "",
		),
		"CUSTOM_INPUTFILE" => array(
			"PARENT" => "CUSTOM_ELEMENTS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_CUSTOM_INPUTFILE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CUSTOM_BOXES" => array(
			"PARENT" => "CUSTOM_ELEMENTS",
			"NAME" => GetMessage("INTEO_FORM_SHOW_P_CUSTOM_BOXES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CACHE_TIME" => array("DEFAULT"=>36000000),
	)
);
if($arCurrentValues["POPUP"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["POPUP_TITLE"] = array(
		"PARENT" => "POPUP_PARAMS",
		"NAME" => GetMessage("INTEO_FORM_SHOW_P_POPUP_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("INTEO_FORM_SHOW_P_POPUP_TITLE_DEFAULT"),
	);
	$arComponentParameters["PARAMETERS"]["POPUP_BUTTON_CSS"] = array(
		"PARENT" => "POPUP_PARAMS",
		"NAME" => GetMessage("INTEO_FORM_SHOW_P_POPUP_BUTTON_CSS"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("INTEO_FORM_SHOW_P_POPUP_BUTTON_CSS_DEFAULT"),
	);
	$arComponentParameters["PARAMETERS"]["POPUP_SIZE"] = array(
		"PARENT" => "POPUP_PARAMS",
		"NAME" => GetMessage("INTEO_FORM_SHOW_P_POPUP_SIZE"),
		"TYPE" => "LIST",
		"VALUES" => $arPopup,
		"DEFAULT" => "SMALL",
	);
}
if($arCurrentValues["USETYPE"] == "FORM")
{
	$arComponentParameters["PARAMETERS"]["ADDRESS"] = array(
		"PARENT" => "IBLOCK_PARAMS",
		"NAME" => GetMessage("INTEO_FORM_ADDRESS"),
		"TYPE" => "STRING",
		"DEFAULT" => "/form/",
	);
}
?>
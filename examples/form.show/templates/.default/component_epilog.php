<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
CJSCore::Init(array('validate','inputmask','bootstrap'));
if ($arParams["POPUP"] == "Y")
{
	CJSCore::Init(array('fancybox'));
}
if ($arParams["CUSTOM_INPUTFILE"] == "Y")
{
	CJSCore::Init(array('multifile'));
}
?>
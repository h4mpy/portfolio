<?
namespace Inteo\Corporation;

use \Bitrix\Main\Data\Cache;
use \Bitrix\Main\Application;

class SubCache
{
	public static $arIBlock = NULL;
	public static function CIBlock_GetList()
	{
		list($cacheTime, $cachePath) = self::getCacheParams("iblock", __FUNCTION__);
		$cacheId = __FUNCTION__;
		$cache = Cache::createInstance(); 
		if (\Bitrix\Main\Config\Option::get("main", "component_cache_on", "Y") == "Y" && $cache->initCache($cacheTime, $cacheId, $cachePath))
		{
			$result = $cache->getVars();
		}
		else {
			\Bitrix\Main\Loader::IncludeModule("iblock");
			$dbIblocks = \Bitrix\Iblock\IblockTable::getList(array(
				'select' => array('CODE', 'ID')
			));
			while ($arIblock = $dbIblocks->fetch())
			{
				$result[$arIblock['CODE']] = $arIblock['ID'];
			}
			self::saveCache($cache, $result, self::getTagByIblockId(), $cacheTime, $cacheId, $cachePath);
		}
		return $result;
	}
	public static function CIBlockElement_GetList($arOrder = Array("SORT" => "ASC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = Array(), $extraTag = "")
	{
		list($cacheTime, $cachePath) = self::getCacheParams("iblock", __FUNCTION__, $extraTag);
		$cacheId = __FUNCTION__."_".md5(serialize(array_merge((array)$arOrder, (array)$arFilter, (array)$arGroupBy, (array)$arNavStartParams, (array)$arSelectFields, (array)$extraTag)));
		$cache = Cache::createInstance(); 
		if (\Bitrix\Main\Config\Option::get("main", "component_cache_on", "Y") == "Y" && $cache->initCache($cacheTime, $cacheId, $cachePath))
		{
			$result = $cache->getVars();
		}
		else {
			\Bitrix\Main\Loader::IncludeModule("iblock");

			/* old Api */
			$arElements = \CIBlockElement::GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
			while ($arElement = $arElements->GetNext())
			{
				$result[$arElement["ID"]] = $arElement;
			}
			
			self::saveCache($cache, $result, $extraTag, $cacheTime, $cacheId, $cachePath);
		}
		return $result;
	}
	public static function CIBlockSection_GetList($arOrder = Array("SORT" => "ASC"), $arFilter = Array(), $bIncCnt = false, $arSelect = Array(), $arNavStartParams = Array(), $extraTag = "")
	{
		list($cacheTime, $cachePath) = self::getCacheParams("iblock", __FUNCTION__, $extraTag);
		$cacheId = __FUNCTION__."_".md5(serialize(array_merge((array)$arOrder, (array)$arFilter, (array)$bIncCnt, (array)$arSelect, (array)$arNavStartParams, (array)$extraTag)));
		$cache = Cache::createInstance(); 
		if (\Bitrix\Main\Config\Option::get("main", "component_cache_on", "Y") == "Y" && $cache->initCache($cacheTime, $cacheId, $cachePath))
		{
			$result = $cache->getVars();
		}
		else {
			\Bitrix\Main\Loader::IncludeModule("iblock");

			/* old Api */
			$arSections = \CIBlockSection::GetList($arOrder, $arFilter, $bIncCnt, $arSelect, $arNavStartParams);
			while ($arSection = $arSections->GetNext())
			{
				$result[$arSection["ID"]] = $arSection;
			}
			self::saveCache($cache, $result, $extraTag, $cacheTime, $cacheId, $cachePath);
		}
		return $result;
	}
	private static function getCacheParams($module = "nomodule", $function = "nofunction", $tag = "", $arCache = Array())
	{
		$cacheTime = $arCache["TIME"] > 0 ? 
			$arCache["TIME"] : 36000000;
		$cacheTag = empty($tag) ? 
			"notag" : $tag;
		$cachePath = !empty($arCache["PATH"]) ? 
			$arCache["PATH"] : '/Inteo/'.$module.'/'.$function.'/'.$cacheTag.'/';
		return array($cacheTime, $cachePath);
	}
	private static function saveCache($cache, $result, $tag, $cacheTime, $cacheId, $cachePath)
	{
		if (\Bitrix\Main\Config\Option::get("main", "component_cache_on", "Y") == "Y" && $cache->startDataCache($cacheTime, $cacheId, $cachePath))
		{
			if (strlen($tag))
			{
				$cacheManager = Application::getInstance()->getTaggedCache();
				$cacheManager->startTagCache($cachePath);
				$cacheManager->registerTag($tag);
				$cacheManager->endTagCache();
			}
			$cache->endDataCache($result);
		}
	}
	public static function getTagByIblockId($iblockId = 0)
	{
		$tag = $iblockId == 0 ?
			'iblocks' : 'iblock_'.intval($iblockId);
		return $tag;
	}
	public static function clearByIblockId($iblockId = 0)
	{
		$tag = self::getTagByIblockId($iblockId);
		$taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
		$taggedCache->clearByTag($tag);
		return true;
	}
}
if (\Inteo\Corporation\SubCache::$arIBlock === NULL)
{
	\Inteo\Corporation\SubCache::$arIBlock = \Inteo\Corporation\SubCache::CIBlock_GetList();
}
?>
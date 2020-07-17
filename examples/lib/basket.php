<?
namespace Inteo\Corporation;

use Inteo\Corporation\Siteuser;
use Inteo\Corporation\Internals\BasketTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;

Loc::loadMessages(__FILE__);

class Basket
{
	/** @var string */
	const MODULE_ID = 'inteo.corporation';
	const DELETE_LIMIT = 2000;

	function __construct()
	{

	}

	public static function getProducts($tag = 'basket')
	{

		$r = BasketTable::getList(array(
			'select' => array('ITEM_ID', 'DETAIL_PAGE_URL', 'NAME', 'IMAGE_ID', 'DESCRIPTION', 'PRICE', 'OLD_PRICE', 'QUANTITY', 'SUMMARY_PRICE'),
			'filter' => array(
				'=USER_ID' => Siteuser::getId(), 
				'=SITE_ID' => SITE_ID, 
				'=TAG' => $tag, 
			)
		));

		$arBasketItems = array();
		while ($arItem = $r->fetch())
		{
			$arItem['PRICE_FORMATED'] = '';
			if (intval($arItem['PRICE']) > 0)
			{
				$arItem['PRICE_FORMATED'] = number_format($arItem['PRICE'], 0, '.', ' ').' &#8381;';
			}
			$arItem['FULL_PRICE_FORMATED'] = '';
			if (intval($arItem['OLD_PRICE']) > 0)
			{
				$arItem['FULL_PRICE_FORMATED'] = number_format($arItem['OLD_PRICE'], 0, '.', ' ').' &#8381;';
			}
			$arItem['QUANTITY'] += 0;
			$arItem['SUMMARY_PRICE'] = intval($arItem['SUMMARY_PRICE']);
			$arItem['SUM'] = '';
			if ($arItem['SUMMARY_PRICE'] > 0)
			{
				$arItem['SUM'] = number_format($arItem['SUMMARY_PRICE'], 0, '.', ' ').' &#8381;';
			}
			$arBasketItems[] = $arItem;
		}

		$arResult = array(
			'TOTAL_PRICE' => 0
		);

		if ($arBasketItems)
		{
			foreach ($arBasketItems as $arItem)
			{
				$arResult["BASKET_ITEMS"][$arItem["ITEM_ID"]] = $arItem;
				$arResult["TOTAL_PRICE"]+=$arItem["SUMMARY_PRICE"];
			}
		}
		return array(
			'NUM_PRODUCTS' => (isset($arResult["BASKET_ITEMS"]))?count($arResult["BASKET_ITEMS"]):0,
			'TOTAL_PRICE' => $arResult["TOTAL_PRICE"],
			'TOTAL_PRICE_FORMATED' => (intval($arResult["TOTAL_PRICE"]) > 0)?number_format($arResult["TOTAL_PRICE"], 0, '.', ' ').' &#8381;':'',
			'BASKET_ITEMS' => $arResult["BASKET_ITEMS"],
		);
	}

	public static function getProductsString($tag = 'basket')
	{
		$products = self::getProducts($tag);
		$arProducts = array();
		foreach ($products["BASKET_ITEMS"] as $arItem)
		{
			$arProducts[] = $arItem["NAME"].' ('.$arItem['PRICE_FORMATED'].' - '.$arItem["QUANTITY"].' '.Loc::getMessage('INTEO_BASKET_QUANTITY').')';
		}
		$arProducts[] = '<b>'.Loc::getMessage('INTEO_BASKET_SUM').' '.$products["TOTAL_PRICE_FORMATED"].'</b>';
		return implode('<br>', $arProducts);
	}

	public static function addItem($itemId, $quantity = 1, $tag = 'basket', $description = false, $siteId = false)
	{
		$itemId = intval($itemId);
		if ($itemId == 0)
		{
			return false;
		}
		if (!$siteId)
		{
			$siteId = SITE_ID;
		}
		if (!$description)
		{
			$description = '';
		}
		$userId = Siteuser::getId();
		$result = BasketTable::getList(
			array(
				'select' => array("ID", "QUANTITY", "NAME", "IMAGE_ID"),
				'filter' => array("ITEM_ID" => $itemId, "DESCRIPTION" => $description, "USER_ID" => $userId, "TAG" => $tag),
				'limit' => 1,
			)
		);
		if ($row = $result->Fetch())
		{
			return self::updateItemQuantity($row, ($row["QUANTITY"] + $quantity));
		}
		\CModule::IncludeModule('iblock');
		$r = \CIBlockElement::GetList(
			array(),
			array(
				"ID" => $itemId,
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y"
			),
			false,
			false,
			array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "DETAIL_PICTURE", "PREVIEW_PICTURE", "PROPERTY_PRICE", "PROPERTY_OLD_PRICE", "PROPERTY_MORE_PHOTO")
		);

		if ($product = $r->GetNext())
		{
			$fields = array(
				'USER_ID' => $userId,
				'DATE_INSERT' => new Main\Type\DateTime(),
				'DATE_UPDATE' => new Main\Type\DateTime(),
				'SITE_ID' => $siteId,
				'ITEM_ID' => $itemId,
				'DETAIL_PAGE_URL' => $product["DETAIL_PAGE_URL"],
				'NAME' => $product["NAME"],
				'TAG' => $tag,
				'DESCRIPTION' => $description,
				'QUANTITY' => $quantity,
			);

			//Very basic cart properties
			//!Upgrade

			//Image
			if ($product["DETAIL_PICTURE"] > 0)
			{
				$fields["IMAGE_ID"] = $product["DETAIL_PICTURE"];
			}
			elseif ($product["PREVIEW_PICTURE"] > 0)
			{
				$fields["IMAGE_ID"] = $product["PREVIEW_PICTURE"];
			}
			elseif ($product["PROPERTY_MORE_PHOTO_VALUE"] > 0)
			{
				$fields["IMAGE_ID"] = $product["PROPERTY_MORE_PHOTO_VALUE"];
			}
			else
			{
				$fields["IMAGE_ID"] = 0;
			}
			//Price
			$fields["PRICE"] = intval(str_replace(' ', '', (is_array($product["PROPERTY_PRICE_VALUE"])?$product["PROPERTY_PRICE_VALUE"]["TEXT"]:$product["PROPERTY_PRICE_VALUE"])));

			//Old price
			$fields["OLD_PRICE"] = intval(str_replace(' ', '', (is_array($product["PROPERTY_OLD_PRICE_VALUE"])?$product["PROPERTY_OLD_PRICE_VALUE"]["TEXT"]:$product["PROPERTY_OLD_PRICE_VALUE"])));

			$result = BasketTable::add($fields);
			if ($result->isSuccess())
			{
			 	return \Bitrix\Main\Web\Json::encode(
					array(
						'NAME' => $fields['NAME'],
						'IMAGE' => ($fields["IMAGE_ID"] > 0)?\CFile::GetPath($fields["IMAGE_ID"]):SITE_TEMPLATE_PATH.'/images/no_photo.png'
					)
				);
			}
			else
			{
				return array('error' => $res->getErrors());
			}
		}
		else
		{
			return array('error' => Loc::getMessage('INTEO_BASKET_NOTFOUND'));
		}
	}

	public static function updateItemQuantity($row, $quantity)
	{
		if(intval($quantity) < 1) 
		{
			$quantity = 1;
		}

		$result = BasketTable::update(
			$row["ID"],
			array("DATE_UPDATE"=> new Main\Type\DateTime(), "QUANTITY" => $quantity)
		);
		if ($result->isSuccess())
		{
			$row["IMAGE"] = ($row["IMAGE_ID"] > 0)?\CFile::GetPath($row["IMAGE_ID"]):SITE_TEMPLATE_PATH.'/images/no_photo.png';
			return \Bitrix\Main\Web\Json::encode($row);
		}
		else
		{
			return array('error' => $res->getErrors());
		}

		return $id;
	}

	public static function deleteItem($itemId, $tag = 'basket', $id = false)
	{
		if ($id) 
		{
			$result = BasketTable::delete($id);
			return true;
		}
		$itemId = intval($itemId);
		if ($itemId == 0)
		{
			return false;
		}
		$userId = Siteuser::getId();
		
		$entity = BasketTable::getEntity();

		$connection = Main\Application::getConnection();
		$tableName = $entity->getDBTableName();

		$query = "DELETE FROM ".$tableName." WHERE USER_ID=".$userId." AND TAG='".$tag."' AND ITEM_ID=".$itemId;
		
		$connection->queryExecute($query);

		return true;
	}

	public static function deleteAllItems($tag = 'basket')
	{
		$userId = Siteuser::getId();
		
		$entity = BasketTable::getEntity();

		$connection = Main\Application::getConnection();
		$tableName = $entity->getDBTableName();

		$query = "DELETE FROM ".$tableName." WHERE USER_ID=".$userId." AND TAG='".$tag."'";
		
		$connection->queryExecute($query);

		return true;
	}


	public static function deleteOld($days)
	{
		$expired = new Main\Type\DateTime();
		$expired->add('-'.$days.'days');
		$expiredValue = $expired->format('Y-m-d H:i:s');

		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$sqlExpiredDate = $sqlHelper->getDateToCharFunction("'" . $expiredValue . "'");

		if ($connection instanceof Main\DB\MysqlCommonConnection)
		{
			$query = "DELETE FROM inteo_corporation_basket	WHERE
									USER_ID IN (
												SELECT inteo_corporation_user.id FROM inteo_corporation_user WHERE
													inteo_corporation_user.DATE_UPDATE < ".$sqlExpiredDate."
											) LIMIT " . static::DELETE_LIMIT;
			$connection->queryExecute($query);
		}
		elseif ($connection instanceof Main\DB\MssqlConnection)
		{
			$query = "DELETE TOP (" . static::DELETE_LIMIT . ") FROM inteo_corporation_basket WHERE
									USER_ID IN (
											SELECT inteo_corporation_user.id FROM inteo_corporation_user WHERE
	 												inteo_corporation_user.DATE_UPDATE < ".$sqlExpiredDate."
											)";
			$connection->queryExecute($query);
		}
		elseif($connection instanceof Main\DB\OracleConnection)
		{
			$query = "DELETE FROM inteo_corporation_basket WHERE
									FUSER_ID IN (
											SELECT inteo_corporation_user.id FROM inteo_corporation_user WHERE
	 												inteo_corporation_user.DATE_UPDATE < ".$sqlExpiredDate."
											) AND ROWNUM <= ".static::DELETE_LIMIT;
			$connection->queryExecute($query);
		}

		return true;
	}

	public static function deleteOldAgent($days)
	{
		if (!isset($GLOBALS["USER"]) || !is_object($GLOBALS["USER"]))
		{
			$tmpUser = True;
			$GLOBALS["USER"] = new \CUser();
		}

		static::deleteOld($days);

		Siteuser::deleteOld($days);
		$speed = intval($speed);
		$result = "\\Inteo\\Corporation\\Basket::deleteOldAgent(30);";

		if (isset($tmpUser))
		{
			unset($GLOBALS["USER"]);
		}

		return $result;
	}
}
?>
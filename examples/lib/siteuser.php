<?php
namespace Inteo\Corporation;

use Inteo\Corporation\Internals\SiteuserTable;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Siteuser
{
	const COOKIE_NAME = 'INTEO_CORPORATION';

	function __construct()
	{

	}

	public static function getId()
	{

		global $USER;

		$id = null;
		$filter = array();

		if (isset($_SESSION["INTEO_USER_ID"]) && intval($_SESSION["INTEO_USER_ID"]) > 0)
		{
			$id = intval($_SESSION["INTEO_USER_ID"]);
		}
		if (intval($id) <= 0 && isset($_COOKIE[self::COOKIE_NAME."_USER"]))
		{
			$code = (string)$_COOKIE[self::COOKIE_NAME."_USER"];
			$filter = (array("=CODE" => $code));
		}
		if (intval($id) <= 0)
		{
			if (!empty($filter))
			{
				$row = SiteuserTable::getList(array(
					'select' => array('ID'),
					'filter' => $filter,
					'order' => array('ID' => "DESC")
				))->fetch();

				if(!empty($row))
				{
					$id = (int)$row['ID'];
					static::updateUser($id);
				}
				else
				{
					if ($USER && $USER->IsAuthorized())
					{
						$id = static::getIdByUserId((int)$USER->GetID());
					}
					if (intval($id) <= 0)
					{
						$id = static::createUser();
					}
				}
			}
			else
			{
				$id = static::createUser();
			}
		}
		static::updateSession($id);
		return $id;
	}

	protected static function createUser()
	{
		global $USER;
		$fields = array(
			'DATE_INSERT' => new Main\Type\DateTime(),
			'DATE_UPDATE' => new Main\Type\DateTime(),
			'USER_ID' => (is_object($USER) && $USER->IsAuthorized() ? intval($USER->GetID()) : false),
			'CODE' => md5(time().randString(10)),
		);
		$r = SiteuserTable::add($fields);
		if ($r->isSuccess())
		{
			$id = $r->getId();
			$_COOKIE[self::COOKIE_NAME."_USER"] = $id;

			$secure = false;
			if(Main\Context::getCurrent()->getRequest()->isHttps())
			{
				$secure = 1;
			}
			$row = SiteuserTable::getList(array(
				'select' => array('ID', 'CODE'),
				'filter' => array("ID" => $id),
				'order' => array('ID' => "DESC"),
			))->fetch();

			if (!empty($row))
			{
				$GLOBALS["APPLICATION"]->set_cookie("USER", $row["CODE"], false, "/", false, $secure, "Y", self::COOKIE_NAME);
				$_COOKIE[self::COOKIE_NAME."_USER"] = $row["CODE"];
			}
			return $id;
		}
		return false;
	}

	protected static function updateUser($id)
	{
		global $USER;

		$fields = array(
			"DATE_UPDATE" => new Main\Type\DateTime(),
		);
		if ($USER && $USER->IsAuthorized())
		{
			$fields["USER_ID"] = intval($USER->GetID());
		}

		$r = SiteuserTable::update($id, $fields);
		if ($r->isSuccess()) 
		{
			$_COOKIE[self::COOKIE_NAME."_USER"] = $id;
			$secure = false;
			if(Main\Context::getCurrent()->getRequest()->isHttps())
			{
				$secure = 1;
			}

			$row = SiteuserTable::getList(array(
				'select' => array('ID', 'CODE'),
				'filter' => array("ID" => $id),
				'order' => array('ID' => "DESC"),
			))->fetch();

			if (!empty($row))
			{
				if (strval($row["CODE"]) == "")
				{
					$row["CODE"] = md5(time().randString(10));
					$result = SiteuserTable::update($row["ID"], array("CODE" => $row["CODE"]));
				}
				$GLOBALS["APPLICATION"]->set_cookie("USER", $row["CODE"], false, "/", false, $secure, "Y", self::COOKIE_NAME);
				$_COOKIE[self::COOKIE_NAME."_USER"] = $row["CODE"];
			}
			return true;
		}
		return false;
	}

	protected static function updateSession($id)
	{
		if (!isset($_SESSION['INTEO_USER_ID']) || (string)$_SESSION['INTEO_USER_ID'] == '' || $_SESSION['INTEO_USER_ID'] === 0)
		{
			$_SESSION['INTEO_USER_ID'] = $id;
		}
	}

	public static function getIdByUserId($userId)
	{
		$res = SiteuserTable::getList(array(
			'filter' => array(
				'USER_ID' => $userId
			),
			'select' => array(
				'ID'
			),
			'order' => array('ID' => "DESC")
		));
		if ($siteuserData = $res->fetch())
		{
			return intval($siteuserData['ID']);
		}
		else
		{
			$r = static::createForUserId($userId);
			if ($r->isSuccess())
			{
				return $r->getId();
			}
		}
		return false;
	}

	protected static function createForUserId($userId)
	{
		$fields = array(
			'DATE_INSERT' => new Main\Type\DateTime(),
			'DATE_UPDATE' => new Main\Type\DateTime(),
			'USER_ID' => $userId,
			'CODE' => md5(time().randString(10)),
		);
		return SiteuserTable::add($fields);
	}

	public static function deleteOld($days)
	{
		$expired = new Main\Type\DateTime();
		$expired->add('-'.$days.'days');
		$expiredValue = $expired->format('Y-m-d H:i:s');

		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$query = "DELETE FROM inteo_corporation_user WHERE
			inteo_corporation_user.DATE_UPDATE < ".$sqlHelper->getDateToCharFunction("'".$expiredValue."'")."
			AND inteo_corporation_user.ID NOT IN (select USER_ID from inteo_corporation_basket)";
		$connection->queryExecute($query);
	}
}
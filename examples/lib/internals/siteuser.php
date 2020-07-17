<?php
namespace Inteo\Corporation\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class SiteuserTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'inteo_corporation_user';
	}

	public static function getMap()
	{
		global $DB;

		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				)
			),
			new Entity\IntegerField('USER_ID'),
			new Entity\DatetimeField('DATE_INSERT'),
			new Entity\DatetimeField('DATE_UPDATE'),
			new Entity\StringField(
				'CODE', 
				array(
					'size' => 32
				)),
		);
	}
}
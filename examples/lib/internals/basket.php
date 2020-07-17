<?php
namespace Inteo\Corporation\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class BasketTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'inteo_corporation_basket';
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
			new Entity\IntegerField('USER_ID', array(
				'required' => true,
				)
			),
			new Entity\ReferenceField(
				'SITEUSER',
				'Inteo\Corporation\Internals\Siteuser',
				array('=this.USER_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
			new Entity\DatetimeField('DATE_INSERT'),
			new Entity\DatetimeField('DATE_UPDATE'),
			new Entity\StringField(
				'SITE_ID', 
				array(
					'validation' => function(){
						return array(
							new Entity\Validator\Length(null, 2),
						);
					}
				)
			),
			new Entity\IntegerField('ITEM_ID'),
			new Entity\StringField(
				'DETAIL_PAGE_URL', 
				array(
					'validation' => function(){
						return array(
							new Entity\Validator\Length(null, 255),
						);
					}
				)
			),
			new Entity\StringField(
				'NAME', 
				array(
					'validation' => function(){
						return array(
							new Entity\Validator\Length(null, 255),
						);
					}
				)
			),
			new Entity\IntegerField('IMAGE_ID'),
			new Entity\StringField(
				'DESCRIPTION', 
				array(
					'validation' => function(){
						return array(
							new Entity\Validator\Length(null, 255),
						);
					}
				)
			),
			new Entity\FloatField('PRICE'),
			new Entity\FloatField(
				'OLD_PRICE', 
				array(
					'default_value' => '0.00'
				)
			),
			new Entity\FloatField(
				'QUANTITY', 
				array(
					'required' => true
				)
			),
			new Entity\ExpressionField(
				'SUMMARY_PRICE', 
				'(%s * %s)', 
				array('QUANTITY', 'PRICE')
			),
			new Entity\StringField(
				'TAG', 
				array(
					'default_value' => 'basket',
					'validation' => function(){
						return array(
							new Entity\Validator\Length(null, 255),
						);
					}
				)
			),
		);
	}
}
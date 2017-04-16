<?php

use Bitrix\Main\Entity;

class SaleBasketEntity extends Entity\Base
{
	protected function __construct() {}

	public function Initialize()
	{
		$this->className = __CLASS__;
		$this->filePath = __FILE__;

		$this->dbTableName = 'b_sale_basket';

		global $DB;

		$this->fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'FUSER_ID' => array(
				'data_type' => 'integer'
			),
			'FUSER' => array(
				'data_type' => 'SaleFuser',
				'reference' => array(
					'=this.FUSER_ID' => 'ref.ID'
				)
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime'
			),
			'DATE_INS' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->DatetimeToDateFunction('%s'), 'DATE_INSERT'
				)
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime'
			),
			'DATE_UPD' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->DatetimeToDateFunction('%s'), 'DATE_UPDATE'
				)
			),
			'PRODUCT_ID' => array(
				'data_type' => 'integer'
			),
			'PRODUCT' => array(
				'data_type' => 'SaleProduct',
				'reference' => array(
					'=this.PRODUCT_ID' => 'ref.ID'
				)
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'ORDER_ID' => array(
				'data_type' => 'integer'
			),
			'ORDER' => array(
				'data_type' => 'SaleOrder',
				'reference' => array(
					'=this.ORDER_ID' => 'ref.ID'
				)
			),
			'PRICE' => array(
				'data_type' => 'float'
			),
			'QUANTITY' => array(
				'data_type' => 'float'
			),
			'SUMMARY_PRICE' => array(
				'data_type' => 'float',
				'expression' => array(
					'%s * %s', 'QUANTITY', 'PRICE'
				)
			),
			'SUBSCRIBE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'N_SUBSCRIBE' => array(
				'data_type' => 'integer',
				'expression' => array(
					'CASE WHEN %s = \'Y\' THEN 1 ELSE 0 END', 'SUBSCRIBE'
				)
			)
		);
	}
}

?>
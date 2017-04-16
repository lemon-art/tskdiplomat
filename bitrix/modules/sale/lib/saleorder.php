<?php

use Bitrix\Main\Entity;

class SaleOrderEntity extends Entity\Base
{
	protected function __construct() {}

	public function Initialize()
	{
		$this->className = __CLASS__;
		$this->filePath = __FILE__;

		$this->dbTableName = 'b_sale_order';

		global $DB;

		$this->fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
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
			'PRODUCTS_QUANT' => array(
				'data_type' => 'float',
				'expression' => array(
					'(SELECT  SUM(b_sale_basket.QUANTITY)
						FROM b_sale_basket
						WHERE b_sale_basket.ORDER_ID = %s)', 'ID'
				)
			),
			'TAX_VALUE' => array(
				'data_type' => 'float'
			),
			'PRICE_DELIVERY' => array(
				'data_type' => 'float'
			),
			'DISCOUNT_VALUE' => array(
				'data_type' => 'float'
			),
			'DISCOUNT_ALL' => array(
				'data_type' => 'float',
				'expression' => array(
					'%s + (SELECT  SUM(b_sale_basket.DISCOUNT_PRICE)
						FROM b_sale_basket
						WHERE b_sale_basket.ORDER_ID = %s)', 'DISCOUNT_VALUE', 'ID'
				)
			),
			'PRICE' => array(
				'data_type' => 'float'
			),
			'PAYED' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'SUM_PAID' => array(
				'data_type' => 'float'
			),
			'SUM_PAID_FORREP' => array(
				'data_type' => 'float',
				'expression' => array(
					'CASE WHEN %s = \'Y\' THEN %s ELSE %s END', 'PAYED', 'PRICE', 'SUM_PAID'
				)
			),
			'LID' => array(
				'data_type' => 'string'
			),
			'USER_ID' => array(
				'data_type' => 'integer'
			),
			'BUYER' => array(
				'data_type' => 'User',
				'reference' => array(
					'=this.USER_ID' => 'ref.ID'
				)
			)/*,
			'FUSER_ID' => array(
				'data_type' => 'integer'
			),
			'FUSER' => array(
				'data_type' => 'SaleFuser',
				'reference' => array(
					'=this.FUSER_ID' => 'ref.ID'
				)
			)*//*,
			'DATE_INSERT' => array(
				'data_type' => 'datetime'
			),*/

			/*'DATE_INSERT_EXP' => array(
				'data_type' => 'datetime',
				'expression' => array(
					str_replace('%%ss', '%s', str_replace('%','%%',$DB->DateToCharFunction('%ss','SHORT'))),
					'DATE_INSERT'
				)
			),*/
			/*'N_PRODUCTS' => array(
				'data_type' => 'integer',
				'expression' => array(
					'SUM(%s)', 'SaleBasket:ORDER.QUANTITY'
				)
			),*/
			/*'N_PRODUCTS_RAW' => array(
				'data_type' => 'integer',
				'expression' => array(
					'CASE WHEN %s IS NOT NULL THEN 1 ELSE 0 END', 'SaleBasket:ORDER.QUANTITY'
				)
			),*/
			/*'DISCOUNT_VALUE' => array(
				'data_type' => 'float'
			),*/
			// общая скидка RAW - поле для избавления от двойной агрегации
			/*'DISCOUNT_ALL_RAW' => array(
				'data_type' => 'float',
				'expression' => array(
					'%s + %s', 'DISCOUNT_VALUE', 'SaleBasket:ORDER.DISCOUNT_PRICE'
				)
			),*/
			// общая скидка
			/*'DISCOUNT_ALL' => array(
				'data_type' => 'float',
				'expression' => array(
					'%s + SUM(%s)', 'DISCOUNT_VALUE', 'SaleBasket:ORDER.DISCOUNT_PRICE'
				)
			),*/
			/*'SUM_PAID' => array(
				'data_type' => 'float'
			),*/
			/*'SUM_PAID_FORREP' => array(
				'data_type' => 'float',
				'expression' => array(
					'CASE WHEN %s = \'Y\' THEN %s ELSE %s END',
					'PAYED', 'PRICE', 'SUM_PAID'
				)
			),*/
		);
	}
}
?>
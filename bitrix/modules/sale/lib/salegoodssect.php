<?php

use Bitrix\Main\Entity;

class SaleGoodsSectionEntity extends Entity\Base
{
	protected function __construct() {}

	public function Initialize()
	{
		$this->className = __CLASS__;
		$this->filePath = __FILE__;

		$this->dbTableName = 'b_iblock_section_element';

		$this->fieldsMap = array(
			'IBLOCK_ELEMENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'PRODUCT' => array(
				'data_type' => 'SaleProduct',
				'reference' => array(
					'=this.IBLOCK_ELEMENT_ID' => 'ref.ID'
				)
			),
			'IBLOCK_SECTION_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'SECT' => array(
				'data_type' => 'SaleSection',
				'reference' => array(
					'=this.IBLOCK_SECTION_ID' => 'ref.ID'
				)
			)
		);
	}
}

?>
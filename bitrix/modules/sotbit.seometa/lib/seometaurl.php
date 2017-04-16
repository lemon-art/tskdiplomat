<?php
namespace Sotbit\Seometa;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class SeometaUrlTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ID_CONDITION int mandatory
 * <li> ENABLE bool optional default 'Y'
 * <li> REAL_URL string mandatory
 * <li> NEW_URL string mandatory
 * </ul>
 *
 * @package Bitrix\Sotbit
 **/

class SeometaUrlTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_sotbit_seometa_chpu';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('SEOMETA_URL_ENTITY_ID_FIELD'),
            ),     
            'CONDITION_ID' => array(
                'data_type' => 'integer',   
                'title' => Loc::getMessage('SEOMETA_URL_ENTITY_ÑONDITION_ID_FIELD'),
            ),  
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SEOMETA_URL_ENTITY_ENABLE_FIELD'),
            ),
            'REAL_URL' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('SEOMETA_URL_ENTITY_REAL_URL_FIELD'),
            ),
            'NEW_URL' => array(
                'data_type' => 'text',   
                'title' => Loc::getMessage('SEOMETA_URL_ENTITY_NEW_URL_FIELD'),
            ),
            'CATEGORY_ID' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SEOMETA_CATEGORY_ID'),
            ),    
            'NAME' => array(
                'data_type' => 'string',
                'title' => Loc::getMessage('SEOMETA_NAME'),
            ),  
            'PROPERTIES' => array(
                'data_type' => 'string',
                'title' => Loc::getMessage('SEOMETA_PROPERTIES'),
            ), 
            'iblock_id' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SEOMETA_IBLOCK_ID'),
            ),    
            'section_id' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SEOMETA_SECTION_ID'),
            ),     
            'DATE_CHANGE' => array(
                'data_type' => 'datetime',
                'required' => true,
                'title' => Loc::getMessage('SEOMETA_SECTION_CHPU_ENTITY_DATE_CHANGE_FIELD'),
            ), 
            'PRODUCT_COUNT' => array(
                'data_type' => 'integer',    
                'title' => Loc::getMessage('SEOMETA_SECTION_CHPU_ENTITY_PRODUCT_COUNT_FIELD'),
            ),
        );
    }
    
    public static function deleteByConditionId($id){
        $arr = self::getList(array(
            'select' => array('ID'),
            'filter' => array('CONDITION_ID' => $id),
            'order'  => array('ID'), ));
            while($one = $arr->fetch()){
                self::delete($one['ID']);
            }
    }
    
    public static function getByCondition($id){
       $res = self::getList(array(
            'select' => array('ID', 'REAL_URL', 'NEW_URL', 'DATE_CHANGE', 'NAME'),
            'filter' => array('CONDITION_ID' => $id),
            'order'  => array('ID'),      
        ));            
        $resAll = array();
        while($one = $res->fetch()){     
            $resAll[$one['ID']] = $one;
        }
        return $resAll; 
    }
    
    public static function getByRealUrl($url){
        $res = self::getList(array(
            'select' => array('*'),
            'filter' => array('ACTIVE' => 'Y', '=REAL_URL' => $url),
            'order'  => array('ID'),
            'limit'  => 1
        ));                  
        return $res->fetch();
    }
    
    public static function getByRealUrlGenerate($url){
        $res = self::getList(array(
            'select' => array('*'),
            'filter' => array('=REAL_URL' => $url),
            'order'  => array('ID'),
            'limit'  => 1
        ));                  
        return $res->fetch();
    }
    
    public static function getByNewUrl($url){
        $res = self::getList(array(
            'select' => array('*'),
            'filter' => array('ACTIVE' => 'Y', '=NEW_URL' => $url),
            'order'  => array('ID'),
            'limit'  => 1
        ));  
        return $res->fetch();
    }
    
    public static function getAll(){
        $res = self::getList(array(
            'select' => array('ID', 'REAL_URL', 'NEW_URL', 'DATE_CHANGE'),
            'filter' => array('ACTIVE' => 'Y', '>PRODUCT_COUNT'=>'0'),
            'order'  => array('ID'),      
        ));            
        $resAll = array();
        while($one = $res->fetch()){     
            $resAll[$one['ID']] = array(
                'REAL_URL' =>$one['REAL_URL'],
                'NEW_URL' =>$one['NEW_URL'],
                'DATE_CHANGE' => $one['DATE_CHANGE'],
            );
        }
        return $resAll;        
    }
}
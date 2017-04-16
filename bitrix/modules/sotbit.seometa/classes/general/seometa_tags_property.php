<?
use Bitrix\Main;

class CSeoMetaTagsProperty extends CSeoMetaTags
{

	public function calculate($parameters)
	{
		$return='';
		$Property=$parameters[0];
		$codes = array();
		foreach(parent::$FilterResult['ITEMS'] as $key=>$elements)
		{
			if($Property==$elements['CODE'] && !isset($codes[$elements['CODE']]))
			{
				$codes[$elements['CODE']] = "Y";
				foreach($elements['VALUES'] as $key_element=>$element)
					if($element['CHECKED']==1)
					{
						if(isset($element['LIST_VALUE']))
							$return[]=$element['LIST_VALUE'];
						else
							$return[]=$element['VALUE'];
					}
			}
		}
			return $return;
	}
}
?>
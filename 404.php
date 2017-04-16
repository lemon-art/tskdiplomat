<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("404 Not Found - Запрашиваемая Вами страница не найдена.");

//$GLOBALS['APPLICATION']->RestartBuffer();

?>
<script type="text/javascript">
    jQuery(document).ready(function() {
      document.title = '<?=$APPLICATION->ShowTitle(false);?>';
    });
</script>
 <style type="text/css">
   #maket {
    width: 100%; /* Ширина всей таблицы */
   }
   TD {
    vertical-align: top; /* Вертикальное выравнивание в ячейках */
    padding: 5px; /* Поля вокруг ячеек */
   }
   TD#leftcol {
    width: 50%; /* Ширина левой колонки */
   }
   TD#rightcol {
    width: 50%; /* Ширина правой колонки */
   }
   .FOUR_N li{
   list-style-type: circle
   }
  </style>
  
  <table cellspacing="0" id="maket">
   <tr> 
    <td id="leftcol">
			<img src="http://tskdiplomat.ru/upload/page_404.jpg" style="width: 100%;">
			
		
			
			
	</td>
    <td id="rightcol">
		<div style="margin-left: 30px;">
	<h1>Запрашиваемая Вами страница не найдена </h1>
		<br>
		<p><b>В чём может быть причина?</b></p>
		<ul class="FOUR_N">
		<li>Если вы набрали URL вручную, убедитесь, что не было опечаток.</li>
		<li>Если вы попали сюда, перейдя по ссылке, скорее всего, ссылка устарела.</li> 
		</ul>
		<br>
		<p>Есть несколько способов вернуться к покупкам в нашем интернет-магазине:</p>
		<ul  class="FOUR_N">
		<li><u><a onclick="history.back(); return false;" href="#">Вернуться к предыдущей странице</a></u></li>
		<li>Использовать строку поиска в верхней части страницы, чтобы найти интересующий Вас продукт.</li>
		<li>Перейти по следующим ссылкам<br>
		<p><u><a href="http://tskdiplomat.ru/catalog/stroymaterialy/">Стройматериалы</a></u> | <u><a href="http://tskdiplomat.ru/catalog/otdelka/">Отделочные материалы</a></u> | <u><a href="http://tskdiplomat.ru/catalog/instrument/">Инструмент и оборудование</a></u> | <u><a href="http://tskdiplomat.ru/catalog/izolyatsionnye_materialy/">Изоляционные материалы</a></u></li> </ul>
	<div>
	</td>
   </tr>
  </table>
  
  






<?//$GLOBALS['APPLICATION']->RestartBuffer();

/*
$APPLICATION->IncludeComponent("bitrix:main.map", ".default", Array(
	"LEVEL"	=>	"3",
	"COL_NUM"	=>	"2",
	"SHOW_DESCRIPTION"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"CACHE_TIME"	=>	"36000000"
	)
);*/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
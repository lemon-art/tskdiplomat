<?
error_reporting(E_ALL & ~E_NOTICE);

if(version_compare(phpversion(), '5.0.0') == -1)
	die('PHP 5.0.0 or higher is required!');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
//header("Content-type: text/html; charset=cp1251");
echo '<html><head><title>Конвертация сайта в UTF8</title></head><body>';

$STEP = intval($_REQUEST['step']);
if (!$STEP)
	$STEP = 1;
$strRes = '';
define('LIMIT', 10); // time limit

define('START_TIME', time()); // засекаем время старта

		define('START_PATH', $_SERVER['DOCUMENT_ROOT'].'/local/templates/utf/'); // стартовая папка для поиска
                
                echo START_PATH.'<br/>';
                

		Search(START_PATH);

function Search($path)
{
    
    echo $path;

	if (is_dir($path)) // dir
	{
		$dir = opendir($path);
		while($item = readdir($dir))
		{
			if ($item == '.' || $item == '..')
				continue;

			Search($path.'/'.$item);
		}
		closedir($dir);
	}
	else // file
	{
			if ((substr($path,-3) == '.js' || substr($path,-4) == '.php' || basename($path) == 'trigram') && $path != __FILE__)
				Process($path);
	}
        
        echo 'end searsh <br>';
}

function Process($file)
{
    echo 'Pocess';
    
		$content = file_get_contents($file);

		if (GetStringCharset($content) != 'cp1251')
			return;

		if ($content === false)
			Error('Не удалось прочитать файл: '.$file);

		if (file_put_contents($file, mb_convert_encoding($content, 'utf8', 'cp1251')) === false)
			Error('Не удалось сохранить файл: '.$file);
		
}

function GetStringCharset($str)
{ 
	global $APPLICATION;
	if (preg_match("/[\xe0\xe1\xe3-\xff]/",$str))
		return 'cp1251';
	$str0 = $APPLICATION->ConvertCharset($str, 'utf8', 'cp1251');
	if (preg_match("/[\xe0\xe1\xe3-\xff]/",$str0,$regs))
		return 'utf8';
	return 'ascii';
}

function Error($text)
{
	die('<font color=red>'.$text.'</font>');
}
?>

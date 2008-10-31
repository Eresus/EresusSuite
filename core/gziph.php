<?php
/**
 * Eresus 2.10.1
 *
 * GZIP-сжатие файлов
 *
 * @copyright		2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright		2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо (по вашему выбору) с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 */

error_reporting(0);
set_magic_quotes_runtime(0);

/**
 * Отправляет заголовок кода результата
 *
 */
function HttpError($code)
{
	$message = array(
		403 => '403 Access Not Alowed',
		404 => '404 Not Found',
	);
	header('HTTP/'.$_SERVER['PROTOCOL_VERSION'].' '.$message[$code], true, $code);
	die(
		"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n".
		"<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n".
		"<head>\n".
		"  <title>".$message[$code]."</title>\n".
		"</head>\n".
		"<body>\n".
		"	<h1>".$message[$code]."</h1>\n".
		"	<address>{$_SERVER['REQUEST_URI']}</address>\n".
		"	<hr />".
		$_SERVER['SERVER_SIGNATURE'].
		"</body>\n".
	"</html>"
	);
}

/**
 * Отправляет заголовок Content-Length
 *
 * @param string $content
 * @return string
 */
function ContentLength($content)
{
	header("Content-Length: ".strlen($content));
	return $content;
}

$filesRoot = __FILE__;
$filesRoot = str_replace('\\','/',$filesRoot);
$filesRoot = substr($filesRoot, 0, strpos($filesRoot, '/core/')+1);
$httpPath = substr($filesRoot, strpos($filesRoot, $_SERVER['DOCUMENT_ROOT'])+strlen($_SERVER['DOCUMENT_ROOT'])-($_SERVER['DOCUMENT_ROOT']{strlen($_SERVER['DOCUMENT_ROOT'])-1} == '/'?1:0));
if ($filesRoot{1} == ':') $filesRoot = substr($filesRoot, 2);
$httpRoot = 'http://'.$_SERVER['HTTP_HOST'].$httpPath;
$styleRoot = $httpRoot.'style/';
$dataRoot = $httpRoot.'data/';
$dataFiles = $filesRoot.'data/';

$type = isset($_REQUEST['type'])?$_REQUEST['type']:'text/plain';
$file = isset($_REQUEST['file'])?$_REQUEST['file']:'';
if (empty($file)) HttpError(404);
if (!preg_match('/\.(js|css|html)$/i',$file)) HttpError(403);
$filename = AddSlashes($filesRoot.$file);
if (is_file($filename)) {
	ob_start('ContentLength');
	ob_start('ob_gzhandler');
	header('Content-type: '.$type.(isset($_GET['charset'])?'; charset='.$_GET['charset']:''));
	header('Cache-Control: '.(isset($_GET['cache'])?$_GET['cache']:'public'));
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
	$text = file_get_contents($filename);
	$text = str_replace(array(
		'$(httpHost)',
		'$(httpPath)',
		'$(httpRoot)',
		'$(styleRoot)',
		'$(dataRoot)',
		'$(dataFiles)',
	), array(
		$_SERVER['HTTP_HOST'],
		$httpPath,
		$httpRoot,
		$styleRoot,
		$dataRoot,
		$dataFiles,
	), $text);
	echo $text;
	ob_end_flush();
} else NotFound();
?>
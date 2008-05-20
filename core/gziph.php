<?php
/**
 * Eresus 2.10
 * 
 * GZIP-сжатие файлов
 * 
 * Система управления контентом Eresus™ 2
 * © 2004-2007, ProCreat Systems, http://procreat.ru/
 * © 2007-2008, Eresus Group, http://eresus.ru/
 * 
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */

error_reporting(0);
set_magic_quotes_runtime(0);

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

/**
 * Отправляет заголовок 404 Not Found
 *
 */
function NotFound()
{
	header('HTTP/'.$_SERVER['PROTOCOL_VERSION'].' 404 Not Found', true, 404);
	die(
		"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n".
		"<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n".
		"<head>\n".
		"  <title>Not found</title>\n".
		"</head>\n".
		"<body>\n".
		"	<h1>Resource not found</h1>\n".
		"	<address>{$_SERVER['REQUEST_URI']}</address>\n".
		"	<hr />".
		$_SERVER['SERVER_SIGNATURE'].
		"</body>\n".
	"</html>"
	);		
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
if (empty($file)) NotFound();
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
<?php
/**
 * Eresus {$M{VERSION}}
 *
 * Проксирование запросов к сторонним расширениям через коннекторы
 *
{$M{COPYTIGHTS}}
 * @license     {$M{LICENSE_URI}}  {$M{LICENSE}}
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
 *
{$M{LICENSE_TEXT}}
 *
 */

require '../core/kernel.php';

$extension = next($Eresus->request['params']);
$filename = $Eresus->froot.'ext-3rd/'.$extension.'/eresus-connector.php';
if ($extension && is_file($filename)) {
	include($filename);
	$className = $extension.'Connector';
	$connector = new $className;
	$connector->proxy();
} else {
	header('404 Not Found', true, 404);
	echo '404';
}
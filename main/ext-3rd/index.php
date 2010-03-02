<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * Проксирование запросов к сторонним расширениям через коннекторы
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
 *
 * $Id: index.php 703 2010-01-23 09:58:25Z mk $
 */

require dirname(__FILE__) . '/../core/kernel-legacy.php';

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
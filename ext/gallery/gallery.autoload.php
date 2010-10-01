<?php
/**
 * Галерея изображений
 *
 * Таблица авторзагрузки классов
 *
 * @version 2.00
 *
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author Михаил Красильников <mk@3wstyle.ru>
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
 * @package Gallery
 *
 * $Id: gallery.autoload.php 970 2010-09-29 11:17:35Z mk $
 */

$dir = dirname(__FILE__);

return array(
	'GalleryAdminXHRController' => $dir . '/controllers/AdminXHR.php',
	'GalleryEresusAdminXHRController' => $dir . '/prototype/AdminXHR.php',
	'GalleryUnsupportedFormatException' => $dir . '/classes/Exceptions.php',
	'GalleryUploadException' => $dir . '/classes/Exceptions.php',
	'GalleryGroup' => $dir . '/classes/Group.php',
	'GalleryAbstractActiveRecord' => $dir . '/classes/AbstractActiveRecord.php',
	'GalleryGroup' => $dir . '/classes/Group.php',
	'GalleryImage' => $dir . '/classes/Image.php'
);
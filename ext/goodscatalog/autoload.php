<?php
/**
 * Каталог товаров
 *
 * Таблица автозагрузки классов
 *
 * @version 1.00
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
 * @package GoodsCatalog
 *
 * $Id: autoload.php 520 2010-09-16 15:02:52Z mk $
 */

$dir = dirname(__FILE__);

return array(
	'GoodsCatalogAbstractActiveRecord' => $dir . '/classes/AbstractActiveRecord.php',
	'GoodsCatalogAbstractAdminUI' => $dir . '/classes/AbstractAdminUI.php',
	'GoodsCatalogAbstractUI' => $dir . '/classes/AbstractUI.php',
	'GoodsCatalogBrand' => $dir . '/classes/Brand.php',
	'GoodsCatalogBrandsAdminUI' => $dir . '/classes/BrandsAdminUI.php',
	'GoodsCatalogHelper' => $dir . '/classes/Helper.php',
	'GoodsCatalogGood' => $dir . '/classes/Good.php',
	'GoodsCatalogGoodsAdminUI' => $dir . '/classes/GoodsAdminUI.php',
	'GoodsCatalogGoodsClientUI' => $dir . '/classes/GoodsClientUI.php',
	'GoodsCatalogPhoto' => $dir . '/classes/Photo.php',

	'GoodsCatalogTemplateService' => $dir . '/classes/TemplateService.php',
	'GoodsCatalogTemplateException' => $dir . '/classes/TemplateService.php',
	'GoodsCatalogTemplateInvalidPathException' => $dir . '/classes/TemplateService.php',
	'GoodsCatalogTemplatePathExistsException' => $dir . '/classes/TemplateService.php',
);

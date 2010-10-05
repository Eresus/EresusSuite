<?php
/**
 * Каталог товаров
 *
 * Класс-помощник
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
 * $Id: Helper.php 520 2010-09-16 15:02:52Z mk $
 */


/**
 * Класс-помощник
 *
 * Класс содержит вспомогательный функционал
 *
 * @package GoodsCatalog
 */
class GoodsCatalogHelper
{
	/**
	 * Объект плагина
	 *
	 * @var GoodsCatalog
	 */
	private $plugin;

	/**
	 * Конструктор
	 *
	 * @param GoodsCatalog $plugin
	 *
	 * @return GoodsCatalogHelper
	 */
	public function __construct(GoodsCatalog $plugin)
	{
		$this->plugin = $plugin;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Метод возвращает имя для временного файла в области, доступной для безопасного чтения
	 * и записи.
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	public function getTempFileName()
	{
		return $this->plugin->getDataDir() . 'tmp_upload.bin';
	}
	//-----------------------------------------------------------------------------

	/**
	 * Подключает библиотеку jQuery
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function linkJQuery()
	{
		global $Eresus, $page;

		$page->linkScripts($Eresus->root . 'core/jquery/jquery.min.js');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Подключает библиотеку jQueryUI
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function linkJQueryUI()
	{
		global $Eresus, $page;

		$page->linkScripts($Eresus->root . 'core/jquery/jquery-ui.min.js');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает экземпляр шаблона АИ с указанным именем
	 *
	 * @param string $name  Имя файла шаблона относительно директории шаблонов плагина
	 *
	 * @return Template
	 *
	 * @since 1.00
	 */
	public function getAdminTemplate($name)
	{
		$tmpl = new Template('ext/' . $this->plugin->name . '/templates/' . $name);
		return $tmpl;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает экземпляр шаблона КИ с указанным именем
	 *
	 * @param string $name  Имя файла шаблона относительно директории шаблонов плагина
	 *
	 * @return Template
	 *
	 * @since 1.00
	 */
	public function getClientTemplate($name)
	{
		$ts = GoodsCatalogTemplateService::getInstance();
		$tmpl = $ts->getTemplate($name, $this->plugin->name);

		return $tmpl;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает массив данных для шаблона.
	 *
	 * Массив предварительно наполняется частоиспользуемыми переменными.
	 *
	 * @return array
	 *
	 * @since 1.00
	 */
	public function prepareTmplData()
	{
		$data = array();
		$data['this'] = $this->plugin;
		$data['page'] = $GLOBALS['page'];
		$data['Eresus'] = $GLOBALS['Eresus'];
		return $data;
	}
	//-----------------------------------------------------------------------------
}

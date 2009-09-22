<?php
/**
 * Eresus 2.11
 *
 * Система управления контентом Eresus 2
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt GPL License 3
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
 * $Id$
 */

define('AJAXUI', true);

# Подключаем ядро системы #
$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'kernel.php';
if (is_file($filename)) include_once($filename); else {
	# TODO: Заменить на JavaScript с сообщением об ошибке.
	echo "<h1>Fatal error</h1>\n<strong>Kernel not available!</strong><br />\nThis error can take place during site update.<br />\nPlease try again later.";
	exit;
}

define('AJAX_ANSWER_TEXT', 'text/plain; charset='.CHARSET);
define('AJAX_ANSWER_XML',  'text/xml; charset='.CHARSET);
define('AJAX_ANSWER_JS',   'text/javascript; charset='.CHARSET);


/**
 * Класс серверного AJAX-интерфейса
 *
 */
class AjaxUI extends WebPage {
 /**
	* Имя запрошенного плагина
	* @var string
	*/
	var $plugin;
	//------------------------------------------------------------------------------
 /**
	* Конструктор
	*
	* @access  public
	*/
	function AjaxUI()
	{
		global $plugins;

		parent::WebPage();
		$plugins->preload(array('client'),array('ondemand'));
		$plugins->clientOnStart();

	}
	//------------------------------------------------------------------------------
 /**
	* Обработка запроса
	*/
	function process()
	{
		global $Eresus, $plugins;

		$plugin = next($Eresus->request['params']);
		$plugins->load($plugin);
		$plugins->ajaxOnRequest();
		$plugins->items[$plugin]->ajaxProcess();
	}
	//-----------------------------------------------------------------------------
 /**
	* Отправка ответа
	*
	* @param string  Тип ответа
	* @param string  Данные ответа
	*/
	function answer($type, $data)
	{
		header("Content-type: $type", true);
		die($data);
	}
	//-----------------------------------------------------------------------------
}

$page = new AjaxUI;
$page->process();

<?php
/**
 * Eresus 2.10
 *
 * AJAX-интерфейс
 *
 * Система управления контентом Eresus™ 2
 * © 2007, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
?>

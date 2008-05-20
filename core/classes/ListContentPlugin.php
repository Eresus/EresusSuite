<?php
/**
 * Eresus 2.10
 *
 * Родительский класс для плагинов, реализующих контент в виде списка
 *
 * Система управления контентом Eresus™ 2
 * © 2004-2007, ProCreat Systems, http://procreat.ru/
 * © 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */

/**
 * Родительский класс для плагинов, реализующих контент в виде списка
 *
 * @var  string  $name        Имя плагина
 * @var  string  $version	    Версия плагина
 * @var  string  $kernel      Необходимая версия Eresus
 * @var  string  $title       Название плагина
 * @var  string  $description	Описание плагина
 * @var  string  $type        Тип плагина, перечисленые через запятую ключевые слова:
 *                              client   - Загружать плагин в КИ
 *                              admin    - Загружать плагин в АИ
 *                              content  - Плагин предоставляет тип контента
 *                              ondemand - Не загружать плагин автоматически
 * @var  array   $settings    Настройки плагина
 */

class ListContentPlugin extends ContentPlugin {
	var $condition = '';
	var $total = 0;
 /**
  * Конструктор
  *
  * @return ListContentPlugin
  */
	function ListContentPlugin()
	{
		parent::ContentPlugin();
		if (!isset($this->settings['perpage'])) $this->settings['perpage'] = 0;
	}
	//-----------------------------------------------------------------------------
 /**
  * Отрисовка клиентского контента
  *
  * @return string
  */
  function clientRenderContent()
  {
		$result = $this->clientListView();
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * Режим отображения списка
  *
  * @return string
  */
  function clientListView()
  {
  	$result = '';

  	$view = array(
  		'items' => $this->clientRenderList($this->clientListItems()),
  		'pages' => $this->clientListPages(),
  		'add' => $this->clientAddItemControl(),
  	);
  	$result = $this->replaceMacros($this->settings['tmplListView'], $view);
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * Отрисовка сипска элементов
  *
  * @param array $items  Список элементов
  *
  * @return string
  */
  function clientRenderList($items)
  {
		$result = '';
  	for($i = 0; $i < count($items); $i++)
			$result .= $this->replaceMacros($this->settings['tmplListItem'], $items[$i]);
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * Получение элементов списка
  *
  * @return array
  */
  function clientListItems()
  {
  	$this->condition = $this->clientListCondition();
  	$result = $this->dbSelect('', $this->condition, $this->settings['sort'], '', $this->settings['perpage'], 0);
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * Отрисовка переключателя страниц
  *
  * @return string
  */
  function clientListPages()
  {
  	$result = '';
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * Отрисовка ЭУ добавления элемента
  *
  * @return string
  */
  function clientAddItemControl()
  {
  	return '';
  }
  //-----------------------------------------------------------------------------
 /**
  * Построение условия для выборки элементов списка
  *
  * @return string
  */
  function clientListCondition()
  {
  	global $page;

  	$result = "`section` = {$page->id} AND `active` = 1";
  	return $result;
  }
  //-----------------------------------------------------------------------------
}
?>

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

/**
 * Родительский класс веб-интерфейсов
 *
 */
class WebPage {
	/**
	 * Идентификатор текущего раздела
	 *
	 * @var int
	 */
	var $id = 0;
	/**
	 * HTTP-заголовки ответа
	 *
	 * @var array
	 */
	var $headers = array();
	/**
	 * Описание секции HEAD
	 * 	meta-http - мета-теги HTTP-заголовков
	 * 	meta-tags - мета-теги
	 * 	link - подключение внешних ресурсов
	 * 	style - CSS
	 * 	script - Скрипты
	 * 	content - прочее
	 *
	 * @var array
	 */
	var $head = array (
		'meta-http' => array(),
		'meta-tags' => array(),
		'link' => array(),
		'style' => array(),
		'script' => array(),
		'content' => '',
	);
 /**
	* Значения по умолчанию
	* @var array
	*/
	var $defaults = array(
		'pageselector' => array(
			'<div class="pages">$(pages)</div>',
			'&nbsp;<a href="$(href)">$(number)</a>&nbsp;',
			'&nbsp;<b>$(number)</b>&nbsp;',
			'<a href="$(href)">&larr;</a>',
			'<a href="$(href)">&rarr;</a>',
		),
	);
 /**
	* Конструктор
	* @return WebPage
	*/
	function WebPage()
	{
	}
	//-----------------------------------------------------------------------------
 /**
	* Установка мета-тега HTTP-заголовка
	*
	* @param string $httpEquiv  Имя тега
	* @param string $content  	Значение тега
	*/
	function setMetaHeader($httpEquiv, $content)
	{
		$this->head['meta-http'][$httpEquiv] = $content;
	}
	//------------------------------------------------------------------------------
 /**
	* Установка мета-тега
	*
	* @param string $name  		Имя тега
	* @param string $content  Значение тега
	*/
	function setMetaTag($name, $content)
	{
		$this->head['meta-tags'][$name] = $content;
	}
	//------------------------------------------------------------------------------
 /**
	* Подключение CSS-файла
	*
	* @param string $url    URL файла
	* @param string $media  Тип носителя
	*/
	function linkStyles($url, $media = '')
	{
		for($i=0; $i<count($this->head['link']); $i++) if ($this->head['link'][$i]['href'] == $url) return;
		$item = array('rel' => 'StyleSheet', 'href' => $url, 'type' => 'text/css');
		if (!empty($media)) $item['media'] = $media;
		$this->head['link'][] = $item;
	}
	//------------------------------------------------------------------------------
 /**
	* Встраивание CSS
	*
	* @param string $content  Стили CSS
	* @param string $media 	  Тип носителя
	*/
	function addStyles($content, $media = '')
	{
		$content = preg_replace(array('/^(\s)+/m', '/^(\S)/m'), array('		', '	\1'), $content);
		$content = rtrim($content);
		$item = array('content' => $content);
		if (!empty($media)) $item['media'] = $media;
		$this->head['style'][] = $item;
	}
	//------------------------------------------------------------------------------
 /**
	* Подключение клиентского скрипта
	*
	* @param string $url   URL скрипта
	* @param string $type  Тип скрипта
	*/
	function linkScripts($url, $type = 'javascript')
	{
		for($i=0; $i<count($this->head['script']); $i++) if (isset($this->head['script'][$i]['src']) && $this->head['script'][$i]['src'] == $url) return;
		if (strpos($type, '/') === false) switch (strtolower($type)) {
			case 'emca': $type = 'text/emcascript'; break;
			case 'javascript': $type = 'text/javascript'; break;
			case 'jscript': $type = 'text/jscript'; break;
			case 'vbscript': $type = 'text/vbscript'; break;
			default: return;
		}
		$this->head['script'][] = array('type' => $type, 'src' => $url);
	}
	//------------------------------------------------------------------------------
 /**
	* Добавление клиентских скриптов
	*
	* @param string $content  Код скрипта
	* @param string $type     Тип скрипта
	*/
	function addScripts($content, $type = 'javascript')
	{
		if (strpos($type, '/') === false) switch (strtolower($type)) {
			case 'emca': $type = 'text/emcascript'; break;
			case 'javascript': $type = 'text/javascript'; break;
			case 'jscript': $type = 'text/jscript'; break;
			case 'vbscript': $type = 'text/vbscript'; break;
			default: return;
		}
		$content = preg_replace(array('/^(\s)+/m', '/^(\S)/m'), array('		', '	\1'), $content);
		$this->head['script'][] = array('type' => $type, 'content' => $content);
	}
	//------------------------------------------------------------------------------
 /**
	* Отрисовка секции <head>
	*
	* @return string  Отрисованная секция <head>
	*/
	function renderHeadSection()
	{
		$result = array();
		# <meta> теги
		if (count($this->head['meta-http'])) foreach($this->head['meta-http'] as $key => $value)
			$result[] = '	<meta http-equiv="'.$key.'" content="'.$value.'" />';
		if (count($this->head['meta-tags'])) foreach($this->head['meta-tags'] as $key => $value)
			$result[] = '	<meta name="'.$key.'" content="'.$value.'" />';
		# <link>
		if (count($this->head['link'])) foreach($this->head['link'] as $value)
			$result[] = '	<link rel="'.$value['rel'].'" href="'.$value['href'].'" type="'.$value['type'].'"'.(isset($value['media'])?' media="'.$value['media'].'"':'').' />';
		# <script>
		if (count($this->head['script'])) foreach($this->head['script'] as $value) {
			if (isset($value['content'])) {
				$value['content'] = trim($value['content']);
				$result[] = "	<script type=\"".$value['type']."\">\n	//<!-- <![CDATA[\n		".$value['content']."\n	//]] -->\n	</script>";
			} elseif (isset($value['src'])) $result[] = '	<script src="'.$value['src'].'" type="'.$value['type'].'"></script>';
		}
		# <style>
		if (count($this->head['style'])) foreach($this->head['style'] as $value)
			$result[] = '	<style type="text/css"'.(isset($value['media'])?' media="'.$value['media'].'"':'').'>'."\n".$value['content']."\n  </style>";

		$this->head['content'] = trim($this->head['content']);
		if (!empty($this->head['content'])) $result[] = $this->head['content'];

		$result = implode("\n" , $result);
		return $result;
	}
	//------------------------------------------------------------------------------
 /**
	* Построение GET-запроса
	*
	* @param array $args      Установить аргументы
	* @return string
	*/
	function url($args = array())
	{
		global $Eresus;

		$args = array_merge($Eresus->request['arg'], $args);
		foreach($args as $key => $value) if (is_array($value)) $args[$key] = implode(',', $value);

		$result = array();
		foreach($args as $key => $value) if ($value !== '') $result []= "$key=$value";
		$result = implode('&amp;', $result);
		$result = $Eresus->request['path'].'?'.$result;
		return $result;
	}
	//-----------------------------------------------------------------------------
 /**
	* Клиентский URL страницы с идентификатором $id
	*
	* @param int $id  Идентификатор страницы
	* @return string URL страницы или NULL если раздела $id не существует
	*/
	function clientURL($id)
	{
		global $Eresus;

		$parents = $Eresus->sections->parents($id);

		if (is_null($parents)) return null;

		array_push($parents, $id);
		$items = $Eresus->sections->get( $parents);

		$list = array();
		for($i = 0; $i < count($items); $i++) $list[array_search($items[$i]['id'], $parents)-1] = $items[$i]['name'];
		$result = $Eresus->root;
		for($i = 0; $i < count($list); $i++) $result .= $list[$i].'/';

		return $result;
	}
	//-----------------------------------------------------------------------------

 /**
	* Отрисовка переключателя страниц
	*
	* @param int     $total      Общее количество страниц
	* @param int     $current    Номер текущей страницы
	* @param string  $url        Шаблон адреса для перехода к подстранице.
	* @param array   $templates  Шаблоны оформления
	* @return string
	*/
	function pageSelector($total, $current, $url = null, $templates = null)
	{
		global $Eresus;

		$result = '';
		# Загрузка шаблонов
		if (!is_array($templates)) $templates = array();
		for ($i=0; $i < 5; $i++) if (!isset($templates[$i])) $templates[$i] = $this->defaults['pageselector'][$i];

		if (is_null($url)) $url = $Eresus->request['path'].'p%d/';

		$pages = array(); # Отображаемые страницы
		# Определяем номера первой и последней отображаемых страниц
		$visible = option('clientPagesAtOnce'); # TODO: Изменить переменную или сделать учёт client/admin
		if ($total > $visible) {
			# Будут показаны НЕ все страницы
			$from = floor($current - $visible / 2); # Начинаем показ с текущей минус половину видимых
			if ($from < 1) $from = 1; # Страниц меньше 1-й не существует
			$to = $from + $visible - 1; # мы должны показать $visible страниц
			if ($to > $total) { # Но если это больше чем страниц всего, вносим исправления
				$to = $total;
				$from = $to - $visible + 1;
			}
		} else {
			# Будут показаны все страницы
			$from = 1;
			$to = $total;
		}
		for($i = $from; $i <= $to; $i++) {
			$src['href'] = sprintf($url, $i);
			$src['number'] = $i;
			$pages[] = replaceMacros($templates[$i != $current ? 1 : 2], $src);
		}

		$pages = implode('', $pages);
		if ($from != 1) $pages = replaceMacros($templates[3], array('href' => sprintf($url, 1))).$pages;
		if ($to != $total) $pages .= replaceMacros($templates[4], array('href' => sprintf($url, $total)));
		$result = replaceMacros($templates[0], array('pages' => $pages));

		return $result;
	}
	//------------------------------------------------------------------------------

}

/**
 * Работа с плагинами
 */
class Plugins {
	var $list = array(); # Список всех плагинов
	var $items = array(); # Массив плагинов
	var $events = array(); # Таблица обработчиков событий
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function Plugins()
	{
		global $Eresus;

		$items = $Eresus->db->select('`plugins`', '', '`position`');
		if (count($items)) foreach($items as $item) $this->list[$item['name']] = $item;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function install($name)
	# Установка нового плагина
	{
		global $Eresus;

		$filename = filesRoot.'ext/'.$name.'.php';
		if (file_exists($filename)) {
			include_once($filename);
			$ClassName = $name;
			if (!class_exists($ClassName) && class_exists('T'.$ClassName)) $ClassName = 'T'.$ClassName; # FIX: Обратная совместимость с версиями до 2.10b2
			if (class_exists($ClassName)) {
				$this->items[$name] = new $ClassName;
				$this->items[$name]->install();
				$Eresus->db->insert('plugins', $this->items[$name]->__item());
			} else FatalError(sprintf(errClassNotFound, $ClassName));
		}
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function uninstall($name)
	# Удаление плагина
	{
		global $Eresus;

		if (!isset($this->items[$name])) $this->load($name);
		if (isset($this->items[$name])) $this->items[$name]->uninstall();
		$item = $Eresus->db->selectItem('plugins', "`name`='".$name."'");
		if (!is_null($item)) {
			$Eresus->db->delete('plugins', "`name`='".$name."'");
			$Eresus->db->update('plugins', "`position` = `position`-1", "`position` > '".$item['position']."'");
		}
		$filename = filesRoot.'ext/'.$name.'.php';
		#if (file_exists($filename)) unlink($filename);
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function preload($include, $exclude)
	{
		if (count($this->list)) foreach($this->list as $item) if ($item['active']) {
			$type = explode(',', $item['type']);
			if (count(array_intersect($include, $type)) && count(array_diff($exclude, $type))) $this->load($item['name']);
		}
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function load($name)
	{
		$result = isset($this->items[$name]) ? $this->items[$name] : false;
		if (isset($this->list[$name]) && !$result) {
			$filename = filesRoot.'ext/'.$name.'.php';
			if (file_exists($filename)) {
				include_once($filename);
				$ClassName = $name;
				if (!class_exists($ClassName) && class_exists('T'.$ClassName)) $ClassName = 'T'.$ClassName; # FIX: Обратная совместимость с версиями до 2.10b2
				if (class_exists($ClassName)) {
					$this->items[$name] = new $ClassName;
					$result = $this->items[$name];
				} else FatalError(sprintf(errClassNotFound, $name));
			} else $result = false;
		}
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	/**
	 * Отрисовка контента раздела
	 *
	 * @return stirng  Контент
	 */
	function clientRenderContent()
	{
		global $Eresus, $page;

		$result = '';
		switch ($page->type) {
			case 'default':
				$plugin = new ContentPlugin;
				$result = $plugin->clientRenderContent();
			break;
			case 'list':
				if ($page->topic) $page->httpError(404);
				$subitems = $Eresus->db->select('pages', "(`owner`='".$page->id."') AND (`active`='1') AND (`access` >= '".($Eresus->user['auth'] ? $Eresus->user['access'] : GUEST)."')", "`position`");
				if (empty($page->content)) $page->content = '$(items)';
				$template = loadTemplate('std/SectionListItem');
				if ($template === false) $template['html'] = '<h1><a href="$(link)" title="$(hint)">$(caption)</a></h1>$(description)';
				$items = '';
				foreach($subitems as $item) {
					$items .= str_replace(
						array(
							'$(id)',
							'$(name)',
							'$(title)',
							'$(caption)',
							'$(description)',
							'$(hint)',
							'$(link)',
						),
						array(
							$item['id'],
							$item['name'],
							$item['title'],
							$item['caption'],
							$item['description'],
							$item['hint'],
							$Eresus->request['url'].($page->name == 'main' && !$page->owner ? 'main/' : '').$item['name'].'/',
						),
						$template['html']
					);
					$result = str_replace('$(items)', $items, $page->content);
				}
			break;
			case 'url':
				goto($page->replaceMacros($page->content));
			break;
			default:
			if ($this->load($page->type)) {
				if (method_exists($this->items[$page->type], 'clientRenderContent'))
					$result = $this->items[$page->type]->clientRenderContent();
				else ErrorMessage(sprintf(errMethodNotFound, 'clientRenderContent', get_class($this->items[$page->type])));
			} else ErrorMessage(sprintf(errContentPluginNotFound, $page->type));
		}
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function clientOnStart()
	{
		if (isset($this->events['clientOnStart'])) foreach($this->events['clientOnStart'] as $plugin) $this->items[$plugin]->clientOnStart();
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function clientOnURLSplit($item, $url)
	{
		if (isset($this->events['clientOnURLSplit'])) foreach($this->events['clientOnURLSplit'] as $plugin) $this->items[$plugin]->clientOnURLSplit($item, $url);
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function clientOnTopicRender($text, $topic = null, $buttonBack = true)
	{
	global $page;
		if (isset($this->events['clientOnTopicRender'])) foreach($this->events['clientOnTopicRender'] as $plugin) $text = $this->items[$plugin]->clientOnTopicRender($text, $topic);
		if ($buttonBack) $text .= '<br /><br />'.$page->buttonBack();
		return $text;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function clientOnContentRender($text)
	{
		if (isset($this->events['clientOnContentRender']))
			foreach($this->events['clientOnContentRender'] as $plugin) $text = $this->items[$plugin]->clientOnContentRender($text);
		return $text;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function clientOnPageRender($text)
	{
		if (isset($this->events['clientOnPageRender']))
			foreach($this->events['clientOnPageRender'] as $plugin) $text = $this->items[$plugin]->clientOnPageRender($text);
		return $text;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function clientBeforeSend($text)
	{
		if (isset($this->events['clientBeforeSend']))
			foreach($this->events['clientBeforeSend'] as $plugin) $text = $this->items[$plugin]->clientBeforeSend($text);
		return $text;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	/* function clientOnFormControlRender($formName, $control, $text)
	{
		if (isset($this->events['clientOnFormControlRender'])) foreach($this->events['clientOnFormControlRender'] as $plugin) $text = $this->items[$plugin]->clientOnFormControlRender($formName, $control, $text);
		return $text;
	}*/
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function adminOnMenuRender()
	{
		if (isset($this->events['adminOnMenuRender'])) foreach($this->events['adminOnMenuRender'] as $plugin)
			if (method_exists($this->items[$plugin], 'adminOnMenuRender')) $this->items[$plugin]->adminOnMenuRender();
			else ErrorMessage(sprintf(errMethodNotFound, 'adminOnMenuRender', $plugin));
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
 /**
	* Событие ajaxOnRequest
	*/
	function ajaxOnRequest()
	{
		if (isset($this->events['ajaxOnRequest']))
			foreach($this->events['ajaxOnRequest'] as $plugin)
				$this->items[$plugin]->ajaxOnRequest();
	}
	//-----------------------------------------------------------------------------
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

/* * * * * * * * * * * * * * * * * * * * * * * *
*
*     Классы-предки для создания плагинов
*
* * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Родительский класс для всех плагинов
 *
 * @var  string  $name        Имя плагина
 * @var  string  $version	   	Версия плагина
 * @var  string  $kernel      Необходимая версия Eresus
 * @var  string  $title       Название плагина
 * @var  string  $description	Описание плагина
 * @var  string  $type        Тип плагина, перечисленые через запятую ключевые слова:
 *                            	client   - Загружать плагин в КИ
 *                              admin    - Загружать плагин в АИ
 *                              content  - Плагин предоставляет тип контента
 *                              ondemand - Не загружать плагин автоматически
 * @var  array   $settings    Настройки плагина
 */
class Plugin {
	var $name;
	var $version = '0.00';
	var $kernel = '2.10b2';
	var $title = 'no title';
	var $description = '';
	var $type;
	var $settings = array();
	var $dirData; # Директория данных (/data/имя_плагина)
	var $urlData; # URL данных
	var $dirCode; # Директория скриптов (/ext/имя_плагина)
	var $urlCode; # URL скриптов
	var $dirStyle; # Директория оформления (style/имя_плагина)
	var $urlStyle; # URL оформления
/**
 * Конструктор
 *
 * Производит чтение настроек плагина и подключение языковых файлов
 */
function Plugin()
{
	global $Eresus, $locale;
	$this->name = strtolower(get_class($this));
	if (!empty($this->name) && isset($Eresus->plugins->list[$this->name])) {
		$this->settings = decodeOptions($Eresus->plugins->list[$this->name]['settings'], $this->settings);
		# Если установлена версия плагина отличная от установленной ранее
		# то необходимо произвести обновление информации о плагине в БД
		if ($this->version != $Eresus->plugins->list[$this->name]['version']) $this->resetPlugin();
	}
	$this->dirData = $Eresus->fdata.$this->name.'/';
	$this->urlData = $Eresus->data.$this->name.'/';
	$this->dirCode = $Eresus->froot.'ext/'.$this->name.'/';
	$this->urlCode = $Eresus->root.'ext/'.$this->name.'/';
	$this->dirStyle = $Eresus->fstyle.$this->name.'/';
	$this->urlStyle = $Eresus->style.$this->name.'/';
	$filename = filesRoot.'lang/'.$this->name.'/'.$locale['lang'].'.php';
	if (is_file($filename)) include_once($filename);
}
//------------------------------------------------------------------------------
/**
 * Возвращает информацию о плагине
 *
 * @param  array  $item  Предыдущая версия информации (по умолчанию null)
 *
 * @return  array  Массив информации, пригодный для записи в БД
 */
function __item($item = null)
{
	global $Eresus;

	$result['name'] = $this->name;
	$result['type'] = $this->type;
	$result['active'] = is_null($item)? true : $item['active'];
	$result['position'] = is_null($item) ? $Eresus->db->count('plugins') : $item['position'];
	$result['settings'] = $Eresus->db->escape(is_null($item) ? encodeOptions($this->settings) : $item['settings']);
	$result['title'] = $this->title;
	$result['version'] = $this->version;
	$result['description'] = $this->description;
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Чтение настроек плагина из БД
 *
 * @return  bool  Результат выполнения
 */
function loadSettings()
{
	global $Eresus;
	$result = $Eresus->db->selectItem('plugins', "`name`='".$this->name."'");
	if ($result) $this->settings = decodeOptions($result['settings'], $this->settings);
	return (bool)$result;
}
//------------------------------------------------------------------------------
/**
 * Сохранение настроек плагина в БД
 *
 * @return  bool  Результат выполнения
 */
function saveSettings()
{
	global $Eresus;

	$result = $Eresus->db->selectItem('plugins', "`name`='{$this->name}'");
	$result = $this->__item($result);
	$result['settings'] = $Eresus->db->escape(encodeOptions($this->settings));
	$result = $Eresus->db->updateItem('plugins', $result, "`name`='".$this->name."'");

	return $result;
}
//------------------------------------------------------------------------------
/**
 * Обновление данных о плагине в БД
 */
function resetPlugin()
{
	$this->loadSettings();
	$this->saveSettings();
}
//------------------------------------------------------------------------------
/**
 * Действия, выполняемые при инсталляции плагина
 */
function install() {}
//------------------------------------------------------------------------------
/**
 * Действия, выполняемые при деинсталляции плагина
 */
function uninstall()
{
	global $Eresus;

	# TODO: Перенести в IDataSource
	$tables = $Eresus->db->query_array("SHOW TABLES LIKE '{$Eresus->db->prefix}{$this->name}_%'");
	$tables = array_merge($tables, $Eresus->db->query_array("SHOW TABLES LIKE '{$Eresus->db->prefix}{$this->name}'"));
	for ($i=0; $i < count($tables); $i++)
		$this->dbDropTable(substr(current($tables[$i]), strlen($this->name)+1));
}
//------------------------------------------------------------------------------
/**
 * Действия при изменении настроек
 */
function onSettingsUpdate() {}
//------------------------------------------------------------------------------
/**
 * Сохраняет в БД изменения настроек плагина
 */
function updateSettings()
{
	global $Eresus;

	foreach ($this->settings as $key => $value) if (!is_null(arg($key))) $this->settings[$key] = arg($key, 'dbsafe');
	$this->onSettingsUpdate();
	$this->saveSettings();
}
//------------------------------------------------------------------------------
/**
 * Замена макросов
 *
 * @param  string  $template  Строка в которой требуется провести замену макросов
 * @param  mixed   $item      Ассоциативный массив со значениями для подстановки вместо макросов
 *
 * @return  string  Обработанная строка
 */
function replaceMacros($template, $item)
{
	$result = replaceMacros($template, $item);
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Создание новой директории
 *
 * @param string $name Имя директории
 * @return bool Результат
 */
function mkdir($name = '')
{
	$result = true;
	$umask = umask(0000);
	# Проверка и создание корневой директории данных
	if (!is_dir($this->dirData)) $result = mkdir($this->dirData);
	if ($result) {
		# Удаляем директории вида "." и "..", а также финальный и лидирующий слэши
		$name = preg_replace(array('!\.{1,2}/!', '!^/!', '!/$!'), '', $name);
		if ($name) {
			$name = explode('/', $name);
			$root = substr($this->dirData, 0, -1);
			for($i=0; $i<count($name); $i++) if ($name[$i]) {
				$root .= '/'.$name[$i];
				if (!is_dir($root)) $result = mkdir($root);
				if (!$result) break;
			}
		}
	}
	umask($umask);
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Удаление директории и файлов
 *
 * @param string $name Имя директории
 * @return bool Результат
 */
function rmdir($name = '')
{
	$result = true;
	$name = preg_replace(array('!\.{1,2}/!', '!^/!', '!/$!'), '', $name);
	$name = $this->dirData.$name;
	if (is_dir($name)) {
		$files = glob($name.'/{.*,*}', GLOB_BRACE);
		for ($i = 0; $i < count($files); $i++) {
			if (substr($files[$i], -2) == '/.' || substr($files[$i], -3) == '/..') continue;
			if (is_dir($files[$i])) $result = $this->rmdir(substr($files[$i], strlen($this->dirData)));
			elseif (is_file($files[$i])) $result = filedelete($files[$i]);
			if (!$result) break;
		}
		if ($result) $result = rmdir($name);
	}
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Возвращает реальное имя таблицы
 *
 * @param string $table  Локальное имя таблицы
 * @return string Реальное имя таблицы
 */
function __table($table)
{
	return $this->name.(empty($table)?'':'_'.$table);
}
//------------------------------------------------------------------------------
/**
 * Создание таблицы в БД
 *
 * @param string $SQL Описание таблицы
 * @param string $name Имя таблицы
 *
 * @return bool Результат выполенения
 */
function dbCreateTable($SQL, $name = '')
{
	global $Eresus;

	$result = $Eresus->db->create($this->__table($name), $SQL);
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Удаление таблицы БД
 *
 * @param string $name Имя таблицы
 *
 * @return bool Результат выполенения
 */
function dbDropTable($name = '')
{
	global $Eresus;

	$result = $Eresus->db->drop($this->__table($name));
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Производит выборку из таблицы БД
 *
 * @param string	$table				Имя таблицы (пустое значение - таблица по умолчанию)
 * @param string	$condition		Условие выборки
 * @param string	$order				Порядок выборки
 * @param string	$fields				Список полей
 * @param int			$limit				Вернуть не больше полей чем limit
 * @param int			$offset				Смещение выборки
 * @param bool		$distinct			Только уникальные результаты
 *
 * @return array	Список записей
 */
function dbSelect($table = '', $condition = '', $order = '', $fields = '', $limit = 0, $offset = 0, $group = '', $distinct = false)
{
	global $Eresus;

	if (is_bool($fields) || $fields == '1' || $fields == '0' || !is_numeric($limit)) {
		# Обратная совместимость
		$desc = $fields;
 		$fields = $limit;
 		$limit = $offset;
 		$offset = $group;
 		$group = $distinct;
 		$distinct = func_num_args() == 9 ? func_get_arg(8) : false;
		$result = $Eresus->db->select($this->__table($table), $condition, $order, $desc, $fields, $limit, $offset, $group, $distinct);
	} else $result = $Eresus->db->select($this->__table($table), $condition, $order, $fields, $limit, $offset, $group, $distinct);

	return $result;
}
//------------------------------------------------------------------------------
/**
 * Получение записи из БД
 *
 * @param string $table  Имя таблицы
 * @param mixed  $id   	 Идентификатор элемента
 * @param string $key    Имя ключевого поля
 *
 * @return array Элемент
 */
function dbItem($table, $id, $key = 'id')
{
	global $Eresus;

	$result = $Eresus->db->selectItem($this->__table($table), "`$key` = '$id'");

	return $result;
}
//------------------------------------------------------------------------------
/**
 * Вставка в таблицу БД
 *
 * @param string $table  Имя таблицы
 * @param array  $item   Вставляемый элемент
 */
function dbInsert($table, $item)
{
	global $Eresus;

	$result = $Eresus->db->insert($this->__table($table), $item);
	$result = $this->dbItem($table, $Eresus->db->getInsertedId());

	return $result;
}
//------------------------------------------------------------------------------
/**
 * Изменение данных в БД
 *
 * @param string $table      Имя таблицы
 * @param mixed  $data       Изменяемый эелемент / Изменения
 * @param string $condition  Ключевое поле / Условие для замены
 *
 * @return bool Результат
 */
function dbUpdate($table, $data, $condition = '')
{
	global $Eresus;

	if (is_array($data)) {
		if (empty($condition)) $condition = 'id';
		$result = $Eresus->db->updateItem($this->__table($table), $data, "`$condition` = '{$data[$condition]}'");
	} elseif (is_string($data)) {
		$result = $Eresus->db->update($this->__table($table), $data, $condition);
	}

	return $result;
}
//------------------------------------------------------------------------------
/**
 * Удаление элемента из БД
 *
 * @param string $table  Имя таблицы
 * @param mixed  $item   Удаляемый элемент / Идентификатор
 * @param string $key    Ключевое поле
 *
 * @return bool Результат
 */
function dbDelete($table, $item, $key = 'id')
{
	global $Eresus;

	$result = $Eresus->db->delete($this->__table($table), "`$key` = '".(is_array($item)? $item[$key] : $item)."'");

	return $result;
}
//------------------------------------------------------------------------------
/**
 * Подсчёт количества записей в БД
 *
 * @param string $table      Имя таблицы
 * @param string $condition  Условие для включения в подсчёт
 *
 * @return int Количество записей, удовлетворяющих условию
 */
function dbCount($table, $condition = '')
{
	global $Eresus;

	$result = $Eresus->db->count($this->__table($table), $condition);

	return $result;
}
//------------------------------------------------------------------------------
/**
 * Получение информации о таблицах
 *
 * @param string $table  Маска имени таблицы
 * @param string $param  Вернуть только указанный парамер
 *
 * @return mixed
 */
function dbTable($table, $param = '')
{
	global $Eresus;

	$result = $Eresus->db->tableStatus($this->__table($table), $param);

	return $result;
}
//------------------------------------------------------------------------------
/**
 * Регистрация обработчиков событий
 *
 * @param string $event1  Имя события1
 * ...
 * @param string $eventN  Имя событияN
 */
function listenEvents()
{
	global $Eresus;

	for($i=0; $i < func_num_args(); $i++)
		$Eresus->plugins->events[func_get_arg($i)][] = $this->name;
}
//------------------------------------------------------------------------------
}

/**
* Базовый класс для плагинов, предоставляющих тип контента
*
*
*/
class ContentPlugin extends Plugin {
/**
* Конструктор
*
* Устанавливает плагин в качестве плагина контента и читает локальные настройки
*/
function ContentPlugin()
{
	global $page;

	parent::Plugin();
	if (isset($page)) {
		$page->plugin = $this->name;
		if (isset($page->options) && count($page->options)) foreach ($page->options as $key=>$value) $this->settings[$key] = $value;
	}
}
//------------------------------------------------------------------------------
/**
 * Действия при удалении раздела данного типа
 * @param int     $id     Идентификатор удаляемого раздела
 * @param string  $table  Имя таблицы
 */
function onSectionDelete($id, $table = '')
{
	if (count($this->dbTable($table)))
		$this->dbDelete($table, $id, 'section');
}
//-----------------------------------------------------------------------------
/**
* Обновляет контент страницы в БД
*
* @param  string  $content  Контент
*/
function updateContent($content)
{
	global $Eresus, $page;

	$item = $Eresus->db->selectItem('pages', "`id`='".$page->id."'");
	$item['content'] = $content;
	$Eresus->db->updateItem('pages', $item, "`id`='".$page->id."'");
}
//------------------------------------------------------------------------------
/**
* Обновляет контент страницы
*/
function adminUpdate()
{
	$this->updateContent(arg('content', 'dbsafe'));
	goto(arg('submitURL'));
}
//------------------------------------------------------------------------------
/**
* Отрисовка клиентской части
*
* @return  string  Контент
*/
function clientRenderContent()
{
	global $page;

	return $page->content;
}
//------------------------------------------------------------------------------
/**
 * Отрисовка административной части
 *
 * @return  string  Контент
 */
function adminRenderContent()
{
	global $page, $Eresus;

	if (arg('action') == 'update') $this->adminUpdate();
	$item = $Eresus->db->selectItem('pages', "`id`='".$page->id."'");
	$form = array(
		'name' => 'content',
		'caption' => $page->title,
		'width' => '100%',
		'fields' => array (
			array ('type'=>'hidden','name'=>'action', 'value' => 'update'),
			array ('type' => 'memo', 'name' => 'content', 'label' => strEdit, 'height' => '30'),
		),
		'buttons' => array('apply', 'reset'),
	);

	$result = $page->renderForm($form, $item);
	return $result;
}
//------------------------------------------------------------------------------
}

/**
 * Базовый класс коннектора сторонних расширений
 *
 */
class EresusExtensionConnector {
	var $root;
	var $froot;
 /**
	* Конструктор
	*
	* @return EresusExtensionConnector
	*/
	function EresusExtensionConnector()
	{
		global $Eresus;

		$name = strtolower(substr(get_class($this), 0, -9));
		$this->root = $Eresus->root.'ext-3rd/'.$name.'/';
		$this->froot = $Eresus->froot.'ext-3rd/'.$name.'/';
	}
	//-----------------------------------------------------------------------------
	/**
	 * Метод вызывается при проксировании прямых запросов к расширению
	 *
	 */
	function proxy()
	{
		global $Eresus;

		if(!UserRights(EDITOR))	die;

		$ext = strtolower(substr($Eresus->request['file'], strrpos($Eresus->request['file'], '.')+1));
		$filename = $Eresus->request['path'] . $Eresus->request['file'];
		$filename = $Eresus->froot.substr($filename, strlen($Eresus->root));
		switch (true) {
			case in_array($ext, array('png', 'jpg', 'jpeg', 'gif')):
				$info = getimagesize($filename);
				header('Content-type: '.$info['mime']);
				echo file_get_contents($filename);
			break;
			case $ext == 'js':
				header('Content-type: text/javascript');
				echo file_get_contents($filename);
			break;
			case $ext == 'css':
				header('Content-type: text/css');
				echo file_get_contents($filename);
			break;
			case $ext == 'html':
			case $ext == 'htm':
				header('Content-type: text/html');
				echo file_get_contents($filename);
			break;
			case $ext == 'php':
				$Eresus->conf['debug']['enable'] = false;
				restore_error_handler();
				chdir(dirname($filename));
				require $filename;
			break;
		}
	}
	//-----------------------------------------------------------------------------
}

/**
 * Класс для работы с расширениями системы
 */
class EresusExtensions {
 /**
	* Загруженные расширения
	*
	* @var array
	*/
	var $items = array();
 /**
	* Определение имени расширения
	*
	* @param string $class     Класс расширения
	* @param string $function  Расширяемая функция
	* @param string $name      Имя расширения
	*
	* @return mixed  Имя расширения или false если подходящего расширения не найдено
	*/
	function get_name($class, $function, $name = null)
	{
		global $Eresus;

		$result = false;
		if (isset($Eresus->conf['extensions'])) {
			if (isset($Eresus->conf['extensions'][$class])) {
				if (isset($Eresus->conf['extensions'][$class][$function])) {
					$items = $Eresus->conf['extensions'][$class][$function];
					reset($items);
					$result = isset($items[$name]) ? $name : key($items);
				}
			}
		}

		return $result;
	}
	//-----------------------------------------------------------------------------
 /**
	* Загрузка расширения
	*
	* @param string $class     Класс расширения
	* @param string $function  Расширяемая функция
	* @param string $name      Имя расширения
	*
	* @return mixed  Экземпляр класса EresusExtensionConnector или false если не удалось загрузить расширение
	*/
	function load($class, $function, $name = null)
	{
		global $Eresus;

		$result = false;
		$name = $this->get_name($class, $function, $name);

		if (isset($this->items[$name])) {
			if ($Eresus->PHP5) $result = $this->items[$name]; else $result =& $this->items[$name];
		} else {
			$filename = $Eresus->froot.'ext-3rd/'.$name.'/eresus-connector.php';
			if (is_file($filename)) {
				include_once $filename;
				$class = $name.'Connector';
				if (class_exists($class)) {
					$this->items[$name] = new $class();
					if ($Eresus->PHP5) $result = $this->items[$name]; else $result =& $this->items[$name];
				}
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------
}

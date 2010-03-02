<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
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
 * $Id: ListContentPlugin.php 703 2010-01-23 09:58:25Z mk $
 */


/**
 * Родительский класс, описывающий элемент контента
 */
class ListContentItem {
	/**
	 * Владелец
	 *
	 * @var object ListContentPlugin
	 */
	var $owner;
	/**
	 * Описания свойств объекта
	 *
	 * @var array
	 */
	var $properties = array();
	/**
	 * Имя ключевого поля
	 *
	 * @var string
	 */
	var $key = 'id';
	/**
	 * Данные объекта
	 *
	 * @var array
	 * @access protected
	 */
	var $source = array();
	/**
	 * Конструктор
	 *
	 * @param array $source
	 * @return ListContentItem
	 */
	function ListContentItem($owner, $source = null)
	{
		global $Eresus;

		if ($Eresus->PHP5) $this->owner = $owner; else $this->owner =& $owner;
		if ($source) $this->source = $source;
		if (isset($this->owner->objects['properties'])) $this->properties = $this->owner->objects['properties'];
		if (isset($this->owner->objects['indexes']['primary'])) $this->key = $this->owner->objects['indexes']['primary'];
	}
	//-----------------------------------------------------------------------------
	/**
	 * Возвращает значение свойства
	 *
	 * @param string $property
	 * @return mixed
	 */
	function get($property)
	{
		global $Eresus;

		$value = isset($this->source[$property]) ? $this->source[$property] : false;
		if (!$value && strpos($property, '-')) {
			list($name, $mode) = explode('-', $property);
			if (isset($this->source[$name])) switch (true) {
				case $mode == 'uri' && $this->properties[$name]['type'] == 'file':
					$value = $Eresus->request['link'].'download='.$this->source[$this->key].'&amp;name='.$name;
				break;
				case $mode == 'local' && $this->properties[$name]['type'] == 'file':
					$value = $this->filename($name);
				break;
			}
		}

		return $value;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Возвращает значение свойства для списка АИ
	 *
	 * @param string $property
	 * @return mixed
	 */
	function getAdminListValue($property)
	{
		return $this->get($property);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Возвращает элемент управления для списка АИ
	 *
	 * @param string $control  Название ЭУ
	 * @param array  $params   Параметры
	 * @return string
	 */
	function getAdminListControl($control, $params)
	{
		global $page;

		$result = '';
		$id = $this->source[$this->key];
		switch ($control) {
			case 'delete': $result = $page->control('delete', $page->url(array('action' => 'delete', 'id' => $id))); break;
			case 'toggle': $result = $page->control('toggle', $page->url(array('action' => 'toggle', 'id' => $id)), array('active' => isset($this->source['active']) ? $this->source['active'] : false)); break;
			case 'edit': $result = $page->control('edit', $page->url(array('id' => $id))); break;
			case 'position':
				if ($this->owner->settings['order'] == 'position')
					$result = $page->control('position', array($page->url(array('action'=>'up', 'id'=>$id)),$page->url(array('action'=>'down', 'id'=>$id))));
			break;
		}
			/*
			$page->control('position', array($root.'action=up&amp;id=%d',$root.'action=down&amp;id=%d')).' '.
			*/

		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Возвращает элементы управления для списков АИ
	 *
	 * @param array $controls
	 * @return string
	 */
	function getAdminListControls($controls)
	{
		$result = '';

		foreach($controls as $control => $params) {
			if (is_numeric($control) && is_string($params)) {
				$control = $params;
				$params = array();
			}
			$result .= $this->getAdminListControl($control, $params);
		}

		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Устновить свойства объекта из запроса
	 *
	 */
	function setFromRequest()
	{
		foreach ($this->properties as $name => $params) {
			if (!isset($params['add']) && !isset($params['edit'])) continue;
			if (is_null(arg($name)) && $params['type'] != 'file') continue;
			switch ($params['type']) {
				case 'string':
				case 'text':
				case 'longtext': $filter = 'dbsafe'; break;
				case 'bool': $filter = 'int'; break;
				case 'file':
					if (!isset($_FILES[$name])) continue;
					$this->source[$name] = $_FILES[$name]['name'];
				break;
				default: $filter = $params['type'];
			}
			if ($params['type'] != 'file') $this->source[$name] = arg($name, $filter);
		}
	}
	//-----------------------------------------------------------------------------
	/**
	 * Фозвращает имя файла
	 *
	 * @param string $name  Имя свойства
	 */
	function filename($name)
	{
		$filename = $this->owner->dirData.$this->source[$this->key].'.'.$name.'.bin';
		return $filename;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Загружает файлы
	 *
	 */
	function upload()
	{
		foreach ($this->properties as $name => $params) {
			if (!isset($params['add']) && !isset($params['edit'])) continue;
			if ($params['type'] != 'file') continue;
			if ($this->source[$name]) {
				$filename = $this->filename($name);
				upload($name, $filename);
			}
		}
	}
	//-----------------------------------------------------------------------------
	/**
	 * Загружает объект из БД
	 *
	 */
	function load($id)
	{
		$this->source = $this->owner->dbItem('', $id);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Вставляет объект в БД
	 *
	 */
	function insert()
	{
		if (array_key_exists('position', $this->properties)) {
			$condition = '';
			if (array_key_exists('section', $this->properties) && isset($this->source['section']))
				$condition = '`section` = '.$this->source['section'];
			$items = $this->owner->dbSelect('', $condition, 'position');
			$this->source['position'] = count($items) ? $items[count($items)-1]['position'] + 1 : 0;
		}
		$this->source = $this->owner->dbInsert('', $this->source);
		$this->upload();
	}
	//-----------------------------------------------------------------------------
	/**
	 * Изменяет объект в БД
	 *
	 */
	function update()
	{
		$this->owner->dbUpdate('', $this->source);
		$this->upload();
	}
	//-----------------------------------------------------------------------------
	/**
	 * Переключает активность объекта в БД
	 *
	 */
	function toggle()
	{
		if (array_key_exists('active', $this->properties)) {
			$this->source['active'] = !$this->source['active'];
			$this->update();
		}
	}
	//-----------------------------------------------------------------------------
	/**
	 * Удаляет объект из БД
	 *
	 */
	function delete()
	{
		foreach($this->properties as $name => $params) if ($params['type'] == 'file') {
			$filename = $this->filename($name);
			if (!filedelete($filename)) ErrorMessage(sprintf(errFileDelete, $filename));
		}
		$this->owner->dbDelete('', $this->source[$this->key]);
		if (array_key_exists('position', $this->properties)) {
			$condition = '`position` > '.$this->source['position'];
			if (array_key_exists('section', $this->properties) && isset($this->source['section']))
				$condition .= ' AND `section` = '.$this->source['section'];
			$this->owner->dbUpdate('', "`position` = `position` - 1", $condition);
		}
	}
	//-----------------------------------------------------------------------------
	/**
	 * Изменение позиции в списке
	 *
	 * @param int $value
	 */
	function position($value)
	{
		if (array_key_exists('position', $this->properties)) {
			$condition = '';
			if (array_key_exists('section', $this->properties) && isset($this->source['section']))
				$condition = '`section` = '.$this->source['section'].' AND ';
			if ($value == -1 && $this->source['position'] == 0) return;
			if ($value == +1 && $this->source['position'] == $this->owner->dbCount('', $condition.' 1')-1) return;
			$condition .= '`position` = '.($this->source['position']+$value);
			$this->owner->dbUpdate('', "`position` = ".$this->source['position'], $condition);
			$this->source['position'] += $value;
			$this->update();
		}
	}
	//-----------------------------------------------------------------------------
	/**
	 * Создаёт шаблон по умолчанию
	 *
	 * @return string
	 */
	function clientDefaultTemplate()
	{
		$result = '';
		foreach($this->properties as $name => $params) {
			switch($params['type']) {
				default: $result .= $params['label'].': $('.$name.')<br />';
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Замена макросов
	 *
	 * @param string $template
	 * @return string
	 */
	function replaceMacros($template)
	{
		$result = replaceMacros($template, $this);
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовка клиентского представления
	 *
	 * @param string $template
	 */
	function clientRender($template = null)
	{
		if (is_null($template)) $template = $this->clientDefaultTemplate();
		$result = $this->replaceMacros($template);
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отправка файла ПА
	 *
	 * @param string $name
	 */
	function download($name)
	{
		global $page;

		$filename = $this->get($name.'-local');
		if (is_file($filename)) {
			@$fp = fopen($filename, 'rb');
			if ($fp) {
				$size = filesize($filename);
				$time = date("D, d M Y H:i:s T", filemtime($filename));
				# Если запрашивающий агент поддерживает докачку
				if (isset($_SERVER['HTTP_RANGE'])) {
					$range = $_SERVER['HTTP_RANGE'];
					$range = str_replace(array('bytes=', '-'), '', $range);
					if ($range) fseek($fp, $range);
				} else $range = 0;
				$content = fread($fp, filesize($filename));
				fclose($fp);
				if ($range) header('HTTP/1.1 206 Partial Content', true);
				else header('HTTP/1.1 200 OK', true);
				header('Content-Disposition: attachment; filename='.$this->get($name));
				header('Last-Modified: '.$time);
				header('Accept-Ranges: bytes');
				header('Content-Length: '.($size-$range));
				header('Content-Range: bytes '.$range.'-'.($size -1).'/'.$size);
				header('Content-type: application/octet-stream');
				die($content);
			} else $page->httpError(403);
		} else $page->httpError(404);
	}
	//-----------------------------------------------------------------------------
}

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
	/*var $condition = '';
	var $total = 0;*/
	var $ItemClassName;
	/**
	 * Описание объектов
	 * @var array
	 */
	var $objects = array(
		/**
		 * Список свойств объекта
		 *
		 * Каждое свойств записывается в виде ассоциативного массива в котором ключём выступает имя поля, а значение - тип:
		 *   bool          - логическое значение
		 *   int, integer  - целое число
		 * 	 float         - вещественное число
		 *   string        - строка (до 255 символов)
		 *   text          - текст (до 65535 символов
		 *   longtext      - текст больше 65535 символов
		 *   file          - файл
		 *
		 * Дополнительно, через точку с заяпятой, могут быть указаны в виде пар 'свойство=значение' параметры:
		 *   default       - значение по умолчанию
		 * 	 autoincrement - флаг, автоинкрементируемое поле
		 *   size          - размер поля
		 *
		 * @var array
		 * @access public
		 */
		'properties' => array(
		/*
		 * 'name1' => 'type1',
		 * 'name2' => 'type2',
		 * ...
		 */
		),
		/**
		 * Список индексируемых полей
		 *
		 * @var array
		 * @access public
		 */
		'indexes' => array(
			/*
			 * 'name1' => 'fieldA','fieldB',...
			 * 'name2' => 'type2',
			 * ...
			 */
		),
	);
	/**
	 * Текущая страница списка
	 *
	 * @var int
	 */
	var $listpage = 1;

 /**
	* Конструктор
	*
	* @return ListContentPlugin
	*/
	function ListContentPlugin()
	{
		parent::ContentPlugin();

		$this->ItemClassName = "{$this->name}Item";
		if (!class_exists($this->ItemClassName)) $this->ItemClassName = 'ListContentItem';
		if (!isset($this->settings['perpage'])) $this->settings['perpage'] = 0;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Действия при установке плагина
	 *
	 */
	function install()
	{
		parent::install();

		$sql = '';
		$needDirectory = false;
		foreach($this->objects['properties'] as $name => $field) {
			$type = $field['type'];
			$default = isset($field['default']) ? $field['default'] : 'NULL';
			$autoincrement = isset($field['auto_increment']) ? ' auto_increment' : '';
			$size = isset($field['size']) ? $field['size'] : '';
			switch($field['type']) {
				case 'string':
					$type = 'varchar('.($size?$size:'255').')';
					$default = "''";
				break;
				case 'file':
					$type = 'varchar(255)';
					$default = "''";
					$needDirectory = true;
				break;
			}
			$sql .= "`$name` $type default $default$autoincrement,\n";
		}
		foreach($this->objects['indexes'] as $name => $params) {
			if ($name == 'primary') $sql .= "PRIMARY KEY";
			else $sql .= "KEY `$name`";
			$sql .= " ($params),\n";
		}
		$sql = substr($sql, 0, -2);
		$this->dbCreateTable($sql, '');
		if ($needDirectory) $this->mkdir();
	}
	//-----------------------------------------------------------------------------
	/**
	 * Определение запрошенного действия
	 *
	 * @return string
	 */
	function adminGetAction()
	{
		$action = 'ListView';
		if (arg('action')) $action = arg('action', 'word');
		elseif (arg('id')) $action = 'Edit';
		return $action;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Загрузка элементов из БД
	 *
	 * @return array
	 */
	function adminLoadItems()
	{
		$items = false;

		$records = $this->dbSelect('', '', $this->settings['order'], '', $this->settings['perpage'], ($this->listpage-1)*$this->settings['perpage']);
		if ($records) {
			for($i = 0; $i < count($records); $i++) $items[] = new $this->ItemClassName($this, $records[$i]);
		}
		return $items;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовка контента АИ
	 *
	 * @return string
	 */
	function adminRenderContent()
	{
		global $page;

		$result = '';

		if (property_exists($this, 'adminTabs')) $result .= $page->renderTabs($this->adminTabs);

		$action = $this->adminGetAction();
		$method = "adminAction$action";
		if (method_exists($this, $method)) $result .= $this->$method();
		else FatalError(sprintf(errMethodNotFound, $method, get_class($this)));
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовка списка элементов
	 *
	 * @param array $items  Отрисовываемые элементы
	 * @return string
	 */
	function adminRenderList($items)
	{
		useLib('admin/lists');
		$list = new AdminList;
		$header = array();
		foreach($this->adminList['columns'] as $name => $params) {
			switch ($name) {
				case '%controls': $column['text'] = admControls; break;
				default: $column['text'] = $params['caption'];
			}

			$header[] = $column;
		}
		call_user_func_array(array($list, 'setHead'), $header);
		if ($items) {
			for($i = 0; $i < count($items); $i++) {
				$row = array();
				foreach($this->adminList['columns'] as $name => $params) {
					switch($name) {
						case '%controls': $column['text'] = $items[$i]->getAdminListControls($params); $column['align'] = 'center'; break;
						default: $column['text'] = $items[$i]->getAdminListValue($name);
					}
					$row[] = $column;
				}
				$list->addRow($row);
			}
		}
		$result = $list->render();
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовывает переключаетль страниц
	 *
	 * @return string
	 */
	function adminRenderPageSelector()
	{
		global $page;

		$total = ceil($this->dbCount('') /  $this->settings['perpage']);
		$result = $total > 1 ? $page->pageSelector($total, $this->listpage, $page->url(array('pg' => '%d'))) : '';
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовка списка в АИ
	 *
	 * @return string
	 */
	function adminActionListView()
	{
		$this->listpage = arg('pg', 'int') ? arg('pg', 'int') : 1;
		$items = $this->adminLoadItems();
		$result = $this->adminRenderList($items);
		$result .= $this->adminRenderPageSelector();
		return $result;
	}
	//-----------------------------------------------------------------------------
	function adminDialog($type, $action, $buttons = array('ok', 'cancel'), $caption = '')
	{
		$Type = strtoupper(substr($type, 0, 1)).substr($type, 1);
		$form = array(
			'name' => $Type.'Form',
			'caption' => empty($caption) ? constant('str'.$Type) : $caption,
			'fields' => array(
				array('type' => 'hidden', 'name' => 'action', 'value' => $action),
			),
			'buttons' => $buttons,
		);
		if (array_key_exists('section', $this->objects['properties']))
			$form['fields'][] = array('type' => 'hidden', 'name' => 'section', 'value' => arg('section', 'int'));
		foreach ($this->objects['properties'] as $name => $params) {
			if (!isset($params[$type])) continue;
			$field = $params;
			$field['name'] = $name;
			switch ($params['type']) {
				case 'string':
					$field['type'] = 'edit';
					$field['width'] = '100%';
				break;
				case 'bool':
					$field['type'] = 'checkbox';
				break;
			}
			$form['fields'][] = $field;
		}
		return $form;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Возвращает диалог добавления объекта
	 *
	 * @return object
	 */
	function adminAddDialog()
	{
		$form = $this->adminDialog('add', 'Insert');
		return $form;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Возвращает диалог изменения объекта
	 *
	 * @return object
	 */
	function adminEditDialog()
	{
		$form = $this->adminDialog('edit', 'Update', array('ok', 'apply', 'cancel'));
		return $form;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Диалог добавления объекта
	 *
	 * @return string
	 */
	function adminActionAdd()
	{
		global $page;

		$form = $this->adminAddDialog();
		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Добавление объекта в БД
	 *
	 */
	function adminActionInsert()
	{
		$item = new $this->ItemClassName($this);
		$item->setFromRequest();
		$item->insert();
		HTTP::redirecto(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------
	/**
	 * Диалог изменения объекта
	 *
	 * @return string
	 */
	function adminActionEdit()
	{
		global $page;

		$item = $this->dbItem('', arg('id', 'int'));
		$form = $this->adminEditDialog();
		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Изменение объекта в БД
	 *
	 */
	function adminActionUpdate()
	{
		$item = new $this->ItemClassName($this);
		$item->load(arg('id', 'int'));
		$item->setFromRequest();
		$item->update();
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------
	/**
	 * Переключение актовности объекта
	 *
	 */
	function adminActionToggle()
	{
		global $Eresus;

		$item = new $this->ItemClassName($this);
		$item->load(arg('id', 'int'));
		$item->toggle();
		HTTP::redirect($Eresus->request['referer']);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Удаление объекта
	 *
	 */
	function adminActionDelete()
	{
		global $Eresus;

		$item = new $this->ItemClassName($this);
		$item->load(arg('id', 'int'));
		$item->delete();
		HTTP::redirect($Eresus->request['referer']);
	}
	//-----------------------------------------------------------------------------
	function adminActionUp()
	{
		global $Eresus;

		$item = new $this->ItemClassName($this);
		$item->load(arg('id', 'int'));
		$item->position(-1);
		HTTP::redirect($Eresus->request['referer']);
	}
	//-----------------------------------------------------------------------------
	function adminActionDown()
	{
		global $Eresus;

		$item = new $this->ItemClassName($this);
		$item->load(arg('id', 'int'));
		$item->position(+1);
		HTTP::redirect($Eresus->request['referer']);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Определение запрошенного действия
	 *
	 * @return string
	 */
	function clientGetAction()
	{

		switch (true) {
			case !is_null(arg('action')): $action = arg('action', 'word'); break;
			case !is_null(arg('download')): $action = 'Download'; break;
			default: $action = 'ListView';
		}
		return $action;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовка клиентского контента
	 *
	 * @return string
	 */
	function clientRenderContent()
	{
		global $page;

		$result = '';

		$action = $this->clientGetAction();
		$method = "clientAction$action";
		if (method_exists($this, $method)) $result .= $this->$method();
		else FatalError(sprintf(errMethodNotFound, $method, get_class($this)));
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Скачивание файла
	 *
	 */
	function clientActionDownload()
	{
		$item = new $this->ItemClassName($this);
		$item->load(arg('download', 'int'));
		$item->download(arg('name', 'word'));
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовка списка КИ
	 *
	 * @return string
	 */
	function clientActionListView()
	{
		global $page;

		$this->listpage = $page->subpage ? $page->subpage : 1;
		$this->condition = '`active` = 1';
		$items = $this->clientLoadItems();
		$macros = array(
			'items' => $this->clientRenderList($items),
			'pages' => $this->clientRenderPageSelector(),
		);
		$template = isset($this->settings['tmplList']) ? $this->settings['tmplList'] : '$(items)<br />$(pages)';
		$result = $this->replaceMacros($template, $macros);
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Загрузка элементов из БД
	 *
	 * @return array
	 */
	function clientLoadItems()
	{
		$items = false;

		$records = $this->dbSelect('', $this->condition, $this->settings['order'], '', $this->settings['perpage'], ($this->listpage-1)*$this->settings['perpage']);
		if ($records) {
			for($i = 0; $i < count($records); $i++) $items[] = new $this->ItemClassName($this, $records[$i]);
		}
		return $items;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовка списка элементов
	 *
	 * @param array $items  Отрисовываемые элементы
	 * @return string
	 */
	function clientRenderList($items)
	{
		$result = '';
		$template = isset($this->settings['tmplListItem']) ? $this->settings['tmplListItem'] : null;
		if ($items) {
			for($i = 0; $i < count($items); $i++)
				$result .= $items[$i]->clientRender($template);
		}
		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Отрисовывает переключаетль страниц
	 *
	 * @return string
	 */
	function clientRenderPageSelector()
	{
		global $page;

		$total = ceil($this->dbCount('', $this->condition) /  $this->settings['perpage']);
		$result = $page->pageSelector($total, $this->listpage);
		return $result;
	}
	//-----------------------------------------------------------------------------
}

<?php
/**
* Родительский класс для всех плагинов
*
* @var  string  $name         Имя плагина
* @var  string  $version	    Версия плагина
* @var  string  $kernel       Необходимая версия Eresus
* @var  string  $title        Название плагина
* @var  string  $description	Описание плагина
* @var  string  $type         Тип плагина, перечисленые через запятую ключевые слова:
*                               client   - Загружать плагин в КИ
*                               admin    - Загружать плагин в АИ
*                               content  - Плагин предоставляет тип контента
*                               ondemand - Не загружать плагин автоматически
* @var  array   $settings     Настройки плагина
*/
class TPlugin {
	var $name;
	var $version;
	var $title;
	var $description;
	var $type;
	var $settings = array();

/**
* Конструктор
*
* Производит чтение настроек плагина и подключение языковых файлов
*/
function TPlugin()
{
	global $plugins, $locale;

	if (!empty($this->name) && isset($plugins->list[$this->name])) {
		$this->settings = decodeOptions($plugins->list[$this->name]['settings'], $this->settings);
		# Если установлена версия плагина отличная от установленной ранее
		# то необходимо произвести обновление информации о плагине в БД
		if ($this->version != $plugins->list[$this->name]['version']) $this->resetPlugin();
	}
	$filename = filesRoot.'lang/'.$this->name.'/'.$locale['lang'].'.inc';
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
# Обратная совместимость
function createPluginItem($item = null) {return $this->__item($item);}
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
	
	$item = $Eresus->db->selectItem('plugins', "`name`='{$this->name}'");
	$item = $this->__item($item);
	$item['settings'] = $Eresus->db->escape(encodeOptions($this->settings));
	$result = $Eresus->db->updateItem('plugins', $item, "`name`='".$this->name."'");
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
function uninstall() {}
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

	foreach ($this->settings as $key => $value) if (!is_null(arg($key))) $this->settings[$key] = arg($key);
	$this->onSettingsUpdate();
	$this->saveSettings();
}
//------------------------------------------------------------------------------
/**
* Замена макросов
*
* @param  string  $template  Строка в которой требуется провести замену макросов
* @param  arrya   $item      Ассоциативный массив со значениями для подстановки вместо макросов
*
* @return  string  Метод возвращает строку, в которой заменены все макросы, совпадающие с полями массива item
*/
function replaceMacros($template, $item)
{
	preg_match_all('/\$\(([^(]+)\)/U', $template, $matches);
	if (count($matches[1])) foreach($matches[1] as $macros)
		if (isset($item[$macros])) $template = str_replace('$('.$macros.')', $item[$macros], $template);
	return $template;
}
//------------------------------------------------------------------------------
}

?>
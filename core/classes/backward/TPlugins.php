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

class TPlugins {
	var $list = array();   # Список всех плагинов
	var $items = array();  # Массив плагинов
	var $events = array(); # Таблица обработчиков событий
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function  TPlugins()
  {
  	global $Eresus;

    $items = $Eresus->db->select('`plugins`', '', '`position`');
    if (count($items)) foreach($items as $item) $this->list[$item['name']] = $item;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	/**
	 * Установка нового плагина
	 *
	 * @param string $name
	 */
  function install($name)
  {
  	global $Eresus;

    $filename = filesRoot.'ext/'.$name.'.php';
    if (file_exists($filename)) {
      include_once($filename);
      $Class = 'T'.$name;
      $this->items[$name] = new $Class;
      $this->items[$name]->install();
      $Eresus->db->insert('plugins', $this->items[$name]->createPluginItem());
    }
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	/**
	 * Удаление плагина
	 *
	 * @param string $name
	 */
  function uninstall($name)
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
        $Class = 'T'.$name;
        $this->items[$name] = new $Class;
        $result = $this->items[$name];
      } else $result = false;
    }
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function clientRenderContent()
  {
  	global $Eresus, $page;

    $result = '';
    switch ($page->type) {
      case 'default':
        $plugin = new TContentPlugin;
        $result = $plugin->clientRenderContent();
      break;
      case 'list':
        if (isset($page->topic)) $page->httpError('404');
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
        else EerrorMessage(sprintf(errMethodNotFound, 'clientRenderContent', get_class($this->items[$page->type])));
      }
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
  function adminOnMenuRender()
  {
    if (isset($this->events['adminOnMenuRender'])) foreach($this->events['adminOnMenuRender'] as $plugin) $this->items[$plugin]->adminOnMenuRender();
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}

?>
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
 * $Id: TContentPlugin.php 703 2010-01-23 09:58:25Z mk $
 */

useClass('backward/TPlugin');
/**
* Базовый класс для плагинов, предоставляющих тип контента
*
*
*/
class TContentPlugin extends TPlugin {
/**
* Конструктор
*
* Устанавливает плагин в качестве плагина контента и читает локальные настройки
*/
function TContentPlugin()
{
	global $page;

  parent::TPlugin();
  if (isset($page)) {
    $page->plugin = $this->name;
    if (count($page->options)) foreach ($page->options as $key=>$value) $this->settings[$key] = $value;
  }
}
//------------------------------------------------------------------------------
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
function update()
{
	$this->updateContent(arg('content', 'dbsafe'));
  HTTP::redirect(arg('submitURL'));
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

  $item = $Eresus->db->selectItem('pages', "`id`='".$page->id."'");
  $form = array(
    'name' => 'content',
    'caption' => $page->title,
    'width' => '100%',
    'fields' => array (
      array ('type'=>'hidden','name'=>'update'),
      array ('type' => 'memo', 'name' => 'content', 'label' => strEdit, 'height' => '30'),
    ),
    'buttons' => array('apply', 'reset'),
  );

  $result = $page->renderForm($form, $item);
  return $result;
}
//------------------------------------------------------------------------------
}
?>
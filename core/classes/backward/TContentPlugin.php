<?php
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
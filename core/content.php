<?php
/**
 * Eresus 2.10.1
 *
 * Редактирование контента
 *
 * @copyright		2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright		2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */

class TContent {
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function adminRender()
  {
  	global $Eresus, $page;

    if (UserRights(EDITOR)) {
      $item = $Eresus->db->selectItem('pages', "`id`='".arg('section', 'int')."'");
      $page->id = $item['id'];
      if (!array_key_exists($item['type'], $Eresus->plugins->list)) {
        switch ($item['type']) {
          case 'default':
            $editor = new ContentPlugin;
            if (arg('update')) $editor->update();
            else $result = $editor->adminRenderContent();
          break;
          case 'list':
            if (arg('update')) {
              $original = $item['content'];
              $item['content'] = arg('content', 'dbsafe');
              $Eresus->db->updateItem('pages', $item, "`id`='".$item['id']."'");
              sendNotify(admUpdated.': <a href="'.$page->url().'">'.$item['caption'].'</a><br />'.$item['content']);
              goto(arg('submitURL'));
            } else {
              $form = array(
                'name' => 'editURL',
                'caption' => admEdit,
                'width' => '100%',
                'fields' => array (
                  array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
                  array ('type' => 'html', 'name' => 'content', 'label' => admTemplListLabel, 'height' => '300px', 'value'=>isset($item['content'])?$item['content']:'$(items)'),
                ),
                'buttons' => array('apply', 'cancel'),
              );
              $result = $page->renderForm($form);
            }
          break;
          case 'url':
            if (arg('update')) {
              $original = $item['content'];
              $item['content'] = arg('url', 'dbsafe');
              $Eresus->db->updateItem('pages', $item, "`id`='".$item['id']."'");
              sendNotify(admUpdated.': <a href="'.$page->url().'">'.$item['caption'].'</a><br />'.$original.' &rarr; '.$item['content']);
              goto(arg('submitURL'));
            } else {
              $form = array(
                'name' => 'editURL',
                'caption' => admEdit,
                'width' => '100%',
                'fields' => array (
                  array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
                  array ('type' => 'edit', 'name' => 'url', 'label' => 'URL:', 'width' => '100%', 'value'=>isset($item['content'])?$item['content']:''),
                ),
                'buttons' => array('apply', 'cancel'),
              );
              $result = $page->renderForm($form);
            }
          break;
          default:
          $result = $page->box(sprintf(errContentPluginNotFound, $item['type']), 'errorBox', errError);
        }
      } else {
        $Eresus->plugins->load($item['type']);
        $page->module = $Eresus->plugins->items[$item['type']];
        $result = $Eresus->plugins->items[$item['type']]->adminRenderContent();
      }
      return $result;
    }
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
?>
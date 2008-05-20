<?php
/**
 * Eresus 2.10
 *
 * Редактирование контента
 *
 * Система управления контентом Eresus™ 2
 * © 2004-2007, ProCreat Systems, http://procreat.ru/
 * © 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
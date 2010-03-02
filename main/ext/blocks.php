<?php
/**
 * Blocks
 *
 * Eresus 2
 *
 * ���������� ���������� �������
 *
 * @version 2.04
 *
 * @copyright   2005-2006, ProCreat Systems, http://procreat.ru/
 * @copyright   2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� �� ������ ������ � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 */
class TBlocks extends TListContentPlugin {
  var $name = 'blocks';
  var $title = '�����';
  var $type = 'client,admin';
  var $version = '2.04';
  var $description = '������� ���������� ���������� �������';
  var $table = array (
    'name' => 'blocks',
    'key'=> 'id',
    'sortMode' => 'id',
    'sortDesc' => false,
    'columns' => array(
      array('name' => 'caption', 'caption' => '��������'),
      array('name' => 'block', 'caption' => '����', 'align'=> 'right'),
      array('name' => 'priority', 'caption' => '<span title="���������" style="cursor: default;">&nbsp;&nbsp;*</span>', 'align'=>'center'),
    ),
    'controls' => array (
      'delete' => '',
      'edit' => '',
      'toggle' => '',
    ),
    'tabs' => array(
      'width'=>'180px',
      'items'=>array(
       array('caption'=>'�������� ����', 'name'=>'action', 'value'=>'create')
      ),
    ),
    'sql' => "(
      `id` int(10) unsigned NOT NULL auto_increment,
      `caption` varchar(255) default NULL,
      `active` tinyint(1) unsigned default NULL,
      `section` varchar(255) default NULL,
      `priority` int(10) unsigned default NULL,
      `block` varchar(31) default NULL,
      `target` varchar(63) default NULL,
      `content` text,
      PRIMARY KEY  (`id`),
      KEY `active` (`active`),
      KEY `section` (`section`),
      KEY `block` (`block`),
      KEY `target` (`target`)
    ) TYPE=MyISAM COMMENT='Content blocks';",
  );

  /**
   * �����������
   *
   * @return TBlocks
   */
  function TBlocks()
  {
  	global $plugins;

    parent::TListContentPlugin();
    if (defined('CLIENTUI')) {
      $plugins->events['clientOnContentRender'][] = $this->name;
      $plugins->events['clientOnPageRender'][] = $this->name;
    } else $plugins->events['adminOnMenuRender'][] = $this->name;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  # ���������� �������
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function menuBranch($owner = 0, $level = 0)
  {
  	global $db;

    $result = array(array(), array());
    $items = $db->select('`pages`', "(`access`>='".USER."')AND(`owner`='".$owner."') AND (`active`='1')", "`position`", false, "`id`,`caption`");
    if (count($items)) foreach($items as $item) {
      $result[0][] = str_repeat('- ', $level).$item['caption'];
      $result[1][] = $item['id'];
      $sub = $this->menuBranch($item['id'], $level+1);
      if (count($sub[0])) {
        $result[0] = array_merge($result[0], $sub[0]);
        $result[1] = array_merge($result[1], $sub[1]);
      }
    }
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function insert()
  {
  	global $db, $request;

    $item = GetArgs($db->fields($this->table['name']));
    if (isset($item['section'])) $item['section'] = ($item['section'] != 'all')?':'.implode(':', $request['arg']['section']).':':'all';
    $item['content'] = arg('content', 'dbsafe');
    $item['active'] = true;
    $db->insert($this->table['name'], $item);
    sendNotify('�������� ����: '.$item['caption']);
    goto($request['arg']['submitURL']);
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function update()
  {
  global $db, $request;

    $item = $db->selectItem($this->table['name'], "`id`='".$request['arg']['update']."'");
    $item = GetArgs($item);
    $item['section'] = ($item['section'] != 'all')?':'.implode(':', $request['arg']['section']).':':'all';
    $item['content'] = arg('content', 'dbsafe');
    $db->updateItem($this->table['name'], $item, "`id`='".$request['arg']['update']."'");
    $item = $db->selectItem($this->table['name'], "`id`='".$request['arg']['update']."'");
    sendNotify('������� ����: '.$item['caption']);
    goto($request['arg']['submitURL']);
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  # ���������������� �������
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function create()
  {
  global $page, $db;

    $sections = array(array(), array());
    $sections = $this->menuBranch();
    array_unshift($sections[0], '��� �������');
    array_unshift($sections[1], 'all');
    $form = array(
      'name' => 'formCreate',
      'caption' => '�������� ����',
      'width' => '95%',
      'fields' => array (
        array ('type'=>'hidden','name'=>'action', 'value'=>'insert'),
        array ('type' => 'edit', 'name' => 'caption', 'label' => '���������', 'width' => '100%', 'maxlength' => '255', 'pattern'=>'/.+/', 'errormsg'=>'��������� �� ����� ���� ������!'),
        array ('type' => 'listbox', 'name' => 'section', 'label' => '�������', 'height'=> 5,'items'=>$sections[0], 'values'=>$sections[1]),
        array ('type' => 'edit', 'name' => 'priority', 'label' => '���������', 'width' => '20px', 'comment' => '������� �������� - ������� ���������', 'value'=>0, 'pattern'=>'/\d+/', 'errormsg'=>'��������� �������� ������ �������!'),
        array ('type' => 'edit', 'name' => 'block', 'label' => '����', 'width' => '100px', 'maxlength' => 31),
        array ('type' => 'select', 'name' => 'target', 'label' => '�������', 'items' => array('������������ ��������','������ ��������'), 'values' => array('page','template')),
        array ('type' => 'html', 'name' => 'content', 'label' => '����������', 'height' => '300px'),
      ),
      'buttons' => array('ok', 'cancel'),
    );

    $result = $page->renderForm($form);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function edit()
  {
  global $page, $db, $request;

    $item = $db->selectItem($this->table['name'], "`id`='".$request['arg']['id']."'");
    $item['section'] = explode(':', $item['section']);
    $sections = array(array(), array());
    $sections = $this->menuBranch();
    array_unshift($sections[0], '��� �������');
    array_unshift($sections[1], 'all');
    $form = array(
      'name' => 'formEdit',
      'caption' => '�������� ����',
      'width' => '95%',
      'fields' => array (
        array ('type' => 'hidden','name'=>'update', 'value'=>$item['id']),
        array ('type' => 'edit', 'name' => 'caption', 'label' => '���������', 'width' => '100%', 'maxlength' => '255', 'pattern'=>'/.+/', 'errormsg'=>'��������� �� ����� ���� ������!'),
        array ('type' => 'listbox', 'name' => 'section', 'label' => '�������', 'height'=> 5,'items'=>$sections[0], 'values'=>$sections[1]),
        array ('type' => 'edit', 'name' => 'priority', 'label' => '���������', 'width' => '20px', 'comment' => '������� �������� - ������� ���������', 'default'=>0, 'pattern'=>'/\d+/', 'errormsg'=>'��������� �������� ������ �������!'),
        array ('type' => 'edit', 'name' => 'block', 'label' => '����', 'width' => '100px', 'maxlength' => 31),
        array ('type' => 'select', 'name' => 'target', 'label' => '�������', 'items' => array('������������ ��������','������ ��������'), 'values' => array('page','template')),
        array ('type' => 'html', 'name' => 'content', 'label' => '����������', 'height' => '300px'),
        array ('type' => 'checkbox', 'name' => 'active', 'label' => '������������'),
      ),
      'buttons' => array('ok', 'apply', 'cancel'),
    );

    $result = $page->renderForm($form, $item);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function adminRender()
  {
  global $db, $page, $user, $request, $session;

    $result = '';
    if (isset($request['arg']['id'])) {
      $item = $db->selectItem($this->table['name'], "`".$this->table['key']."` = '".$request['arg']['id']."'");
      $page->title .= empty($item['caption'])?'':' - '.$item['caption'];
    }
    if (isset($request['arg']['update']) && isset($this->table['controls']['edit'])) {
      if (method_exists($this, 'update')) $result = $this->update(); else $session['errorMessage'] = sprintf(errMethodNotFound, 'update', get_class($this));
    } elseif (isset($request['arg']['toggle']) && isset($this->table['controls']['toggle'])) {
      if (method_exists($this, 'toggle')) $result = $this->toggle($request['arg']['toggle']); else $session['errorMessage'] = sprintf(errMethodNotFound, 'toggle', get_class($this));
    } elseif (isset($request['arg']['delete']) && isset($this->table['controls']['delete'])) {
      if (method_exists($this, 'delete')) $result = $this->delete($request['arg']['delete']); else $session['errorMessage'] = sprintf(errMethodNotFound, 'delete', get_class($this));
    } elseif (isset($request['arg']['id']) && isset($this->table['controls']['edit'])) {
      if (method_exists($this, 'edit')) $result = $this->edit(); else $session['errorMessage'] = sprintf(errMethodNotFound, 'edit', get_class($this));
    } elseif (isset($request['arg']['action'])) switch ($request['arg']['action']) {
      case 'create': $result = $this->create(); break;
      case 'insert':
        if (method_exists($this, 'insert')) $result = $this->insert();
        else $session['errorMessage'] = sprintf(errMethodNotFound, 'insert', get_class($this));
      break;
    } else {
      $result = $page->renderTable($this->table);
    }
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function renderBlocks($source, $target)
  {
    global $db, $page, $request;

    preg_match_all('/\$\(Blocks:([^\)]+)\)/', $source, $blocks);
    foreach($blocks[1] as $block) {
      $sql = "(`active`=1) AND (`section` LIKE '%:".$page->id.":%' OR `section` = ':all:') AND (`block`='".$block."') AND (`target` = '".$target."')";
      $item = $db->select($this->name, $sql, '`priority`', true);
      if (count($item)) $source = str_replace('$(Blocks:'.$block.')', trim($item[0]['content']), $source);
    }
    return $source;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  # ����������� �������
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function adminOnMenuRender()
  {
    global $page;

    $page->addMenuItem(admExtensions, array ('access'  => EDITOR, 'link'  => $this->name, 'caption'  => $this->title, 'hint'  => $this->description));
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function clientOnContentRender($text)
  {
    global $page;
    $page->template = $this->renderBlocks($page->template, 'template');
    return $text;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function clientOnPageRender($text)
  {
    $text = $this->renderBlocks($text, 'page');
    return $text;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}

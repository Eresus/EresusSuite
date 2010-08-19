<?php
/**
 * Banners
 *
 * ������� ������ ��������.
 *
 * @version: 2.00
 *
 * @copyright 2005, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 * @author ����� <bersz@procreat.ru>
 * @author dkDimon <dkdimon@mail.ru>
 * @author ghost
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
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 *
 * @package Banners
 *
 * $Id: banners.php 484 2010-08-17 09:38:03Z mk $
 */

/**
 * ����� �������
 *
 * @package Banners
 */
class Banners extends Plugin
{
	/**
	 * ��������� ������ ����
	 * @var string
	 */
	public $kernel = '2.13';

	/**
	 * �������� �������
	 * @var string
	 */
	public $title = '�������';

	/**
	 * ���
	 * @var string
	 */
	public $type = 'client,admin';

	/**
	 * ������
	 * @var string
	 */
	public $version = '2.00';

	/**
	 * ��������
	 * @var string
	 */
	public $description = '������� ������ ��������';

	/**
	 * ������� ��
	 * @var array
	 */
	private $table = array (
		'name' => 'banners',
		'key'=> 'id',
		'sortMode' => 'id',
		'sortDesc' => false,
		'columns' => array(
			array('name' => 'caption', 'caption' => '��������'),
			array('name' => 'block', 'caption' => '����', 'align'=> 'right'),
			array('name' => 'priority', 'caption' => '<span title="���������" style="cursor: default;">&nbsp;&nbsp;*</span>', 'align'=>'center'),
			array('name' => 'showTill', 'caption' => '�� ����', 'replace'=> array('0000-00-00'=> '��� �������.')),
			array('name' => 'showCount', 'caption' => '����.�����.', 'align'=>'right', 'replace' => array('0'=> '��� �������.')),
			array('name' => 'shows', 'caption' => '�������', 'align'=>'right'),
			array('name' => 'clicks', 'caption' => '������', 'align'=>'right'),
			//array('name' => 'mail', 'caption' => '��������', 'value' => '<a href="mailto:$(mail)">$(mail)</a>', 'macros' => true),
		),
		'controls' => array (
			'delete' => '',
			'edit' => '',
			'toggle' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
			 array('caption'=>'�������� ������', 'name'=>'action', 'value'=>'create')
			),
		),
		'sql' => "(
			`id` int(10) unsigned NOT NULL auto_increment,
			`caption` varchar(255) default NULL,
			`active` tinyint(1) unsigned default NULL,
			`section` varchar(255) default NULL,
			`priority` int(10) unsigned default NULL,
			`block` varchar(31) default NULL,
			`showFrom` date default NULL,
			`showTill` date default NULL,
			`showCount` int(10) unsigned default NULL,
			`html` text,
			`image` varchar(255) default NULL,
			`width` varchar(15) default NULL,
			`height` varchar(15) default NULL,
			`url` varchar(255) default NULL,
			`target` tinyint(1) unsigned default NULL,
			`shows` bigint(20) unsigned default NULL,
			`clicks` bigint(20) unsigned default NULL,
			PRIMARY KEY	(`id`),
			KEY `active` (`active`),
			KEY `priority` (`priority`),
			KEY `showFrom` (`showFrom`),
			KEY `showTill` (`showTill`),
			KEY `showCount` (`showCount`),
			KEY `shows` (`shows`)
		) TYPE=MyISAM COMMENT='Banner system';",
	);

	/**
	 * �����������
	 *
	 * ���������� ����������� ������������ �������.
	 */
	function __construct()
	{
		parent::__construct();

		if (defined('CLIENTUI'))
		{
			$this->listenEvents('clientOnPageRender');
		}
		else
		{
			$this->listenEvents('adminOnMenuRender');
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ���� � ���������� ������ �������
	 *
	 * @return string
	 *
	 * @since 2.00
	 */
	public function getDataDir()
	{
		return $this->dirData;
	}
	//-----------------------------------------------------------------------------

	/**
	 * �������� ��� ��������� �������
	 */
	function install()
	{
		parent::install();

		$this->createTable($this->table);

		$this->mkdir();
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ����� �������� �����
	 *
	 * @param int $owner[optional]
	 * @param int $level[optional]
	 * @return array
	 */
	private function menuBranch($owner = 0, $level = 0)
	{
		global $Eresus;

		$result = array(array(), array());
		$items = $Eresus->db->select('`pages`',
			"(`access` >= '" . USER . "') AND (`owner` = '" . $owner . "') AND (`active` = '1')",
			"-position", "`id`,`caption`");
		if (count($items)) foreach($items as $item)
		{
			$result[0][] = str_repeat('- ', $level).$item['caption'];
			$result[1][] = $item['id'];
			$sub = $this->menuBranch($item['id'], $level+1);
			if (count($sub[0]))
			{
				$result[0] = array_merge($result[0], $sub[0]);
				$result[1] = array_merge($result[1], $sub[1]);
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������ � ��
	 *
	 * @return void
	 */
	private function insert()
	{
		global $Eresus, $request;

		$item = GetArgs($Eresus->db->fields($this->table['name']));

		if (arg('section'))
		{
			$item['section'] = '|'.implode('|', arg('section')).'|';
		}

		if ($item['showTill'] == '') unset($item['showTill']);

		$Eresus->db->insert($this->table['name'], $item);

		$item['id'] = $Eresus->db->getInsertedID();
		if (is_uploaded_file($_FILES['image']['tmp_name']))
		{
			$filename = 'banner'.$item['id'].substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.'));
			upload('image', filesRoot.'data/'.$this->name.'/'.$filename);
			$item['image'] = $filename;
			$Eresus->db->updateItem($this->table['name'], $item, "`id`='".$item['id']."'");
		}
		HTTP::redirect($request['arg']['submitURL']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������ � ��
	 * @return void
	 */
	private function update()
	{
		global $Eresus, $request;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".$request['arg']['update']."'");
		$old_file = $item['image'];
		$item = GetArgs($item);

		if (arg('section'))
		{
			$item['section'] = '|'.implode('|', arg('section')).'|';
		}
		if ($item['showTill'] == '')
		{
			unset($item['showTill']);
		}
		if (arg('flushShowCount'))
		{
			$item['shows'] = 0;
		}
		if (is_uploaded_file($_FILES['image']['tmp_name']))
		{
			$path = filesRoot.'data/'.$this->name.'/';
			if (is_file($path.$old_file))
			{
				unlink($path.$old_file);
			}
			$filename = 'banner' . $item['id'] .
				substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.'));
			upload('image', $path.$filename);
			$item['image'] = $filename;
		}

		$Eresus->db->updateItem($this->table['name'], $item, "`id`='".$item['id']."'");

		HTTP::redirect($request['arg']['submitURL']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * ����������� ���������� �������
	 *
	 * @param int $id
	 * @return void
	 */
	private function toggle($id)
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".$id."'");
		$item['active'] = !$item['active'];

		$item = $Eresus->db->escape($item);
		$Eresus->db->updateItem($this->table['name'], $item, "`id`='".$id."'");

		HTTP::redirect($page->url());
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������� ������
	 *
	 * @param int $id
	 * @return void
	 */
	private function delete($id)
	{
		global $page, $Eresus;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".$id."'");
		$path = dataFiles.$this->name.'/';
		if (
			!empty($item['image']) &&
			file_exists($path.$item['image'])
		)
		{
			unlink($path.$item['image']);
		}
		$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."`='".$id."'");
		$Eresus->db->delete($this->table['name'], "`".$this->table['key']."`='".$id."'");
		HTTP::redirect(str_replace('&amp;', '&', $page->url()));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ������ ���������� �������
	 *
	 * @return string  HTML
	 */
	function create()
	{
		global $page;

		$sections = array(array(), array());
		$sections = $this->menuBranch();
		array_unshift($sections[0], '��� �������');
		array_unshift($sections[1], 'all');
		$form = array(
			'name' => 'formCreate',
			'caption' => '�������� ������',
			'width' => '600px',
			'fields' => array (
				array ('type'=>'hidden','name'=>'action', 'value'=>'insert'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '<b>���������</b>',
					'width' => '100%', 'maxlength' => '255', 'pattern'=>'/.+/',
					'errormsg'=>'��������� �� ����� ���� ������!'),
				array ('type' => 'listbox', 'name' => 'section', 'label' => '<b>�������</b>', 'height'=> 5,
					'items'=>$sections[0], 'values'=>$sections[1]),
				array ('type' => 'edit', 'name' => 'block', 'label' => '<b>���� �������</b>',
					'width' => '100px', 'maxlength' => 31,
					'comment' => '��� ������� ������� ����������� ������ <b>$(Banners:���_�����)</b>',
					'pattern'=>'/.+/', 'errormsg'=>'�� ������ ���� �������!'),
				array ('type' => 'edit', 'name' => 'priority', 'label' => '���������', 'width' => '20px',
					'comment' => '���� ��� ������ ������� � ������ ����� ������ ��������� ��������, ����� ������� � ������� �����������',
					'default'=>0, 'pattern'=>'/\d+/', 'errormsg'=>'��������� �������� ������ �������!'),
				array ('type' => 'edit', 'name' => 'showFrom', 'label' => '������ �������',
					'width' => '100px', 'comment' => '����-��-��', 'default'=>gettime('Y-m-d'),
					'pattern'=>'/[12]\d{3,3}-[01]\d-[0-3]\d/', 'errormsg'=>'������������ ������ ����!'),
				array ('type' => 'edit', 'name' => 'showTill', 'label' => '����� �������',
					'width' => '100px', 'comment' => '����-��-��; ������ - ��� �����������',
					'pattern'=>'/([12]\d{3,3}-[01]\d-[0-3]\d)|(^$)/',
					'errormsg'=>'������������ ������ ����!'),
				array ('type' => 'edit', 'name' => 'showCount', 'label' => '����. ���-�� �������',
					'width' => '100px', 'comment' => '0 - ��� �����������', 'default'=>0,
					'pattern'=>'/(\d+)|(^$)/', 'errormsg'=>'���-�� ������� �������� ������ �������!'),
				/*array ('type' => 'edit', 'name' => 'mail', 'label' => 'e-mail ���������',
					'width' => '200px', 'maxlength' => '63'),*/
				array ('type' => 'checkbox', 'name' => 'active', 'label' => '������������',
					'default' => true),
				array ('type' => 'header', 'value' => '�������� �������'),
				array ('type' => 'file', 'name' => 'image', 'label' => '�������� ��� Flash', 'width'=>'50'),
				array ('type' => 'edit', 'name' => 'width', 'label' => '������', 'width' => '100px',
					'comment'=>'������ ��� Flash'),
				array ('type' => 'edit', 'name' => 'height', 'label' => '������', 'width' => '100px',
					'comment'=>'������ ��� Flash'),
				array ('type' => 'edit', 'name' => 'url', 'label' => 'URL ��� ������', 'width' => '100%',
					'maxlength' => '255'),
				array ('type' => 'select', 'name' => 'target', 'label' => '���������',
					'items'=>array('� ����� ����', '� ��� �� ����')),
				array ('type' => 'header', 'value' => 'HTML-��� �������'),
				array ('type' => 'memo', 'name' => 'html',
					'label' => 'HTML-��� (���� ����� HTML-���, �� ���������� �������� ������������ � ����� �� �����������)',
					'height' => '4'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ������ ��������� �������
	 *
	 * @return string  HMTL
	 */
	function edit()
	{
		global $page, $Eresus, $request;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".$request['arg']['id']."'");
		$item['section'] = explode('|', $item['section']);
		$sections = array(array(), array());
		$sections = $this->menuBranch();
		array_unshift($sections[0], '��� �������');
		array_unshift($sections[1], 'all');
		$form = array(
			'name' => 'formEdit',
			'caption' => '�������� ������',
			'width' => '95%',
			'fields' => array (
				array ('type' => 'hidden','name'=>'update', 'value'=>$item['id']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '<b>���������</b>', 'width' => '100%', 'maxlength' => '255', 'pattern'=>'/.+/', 'errormsg'=>'��������� �� ����� ���� ������!'),
				array ('type' => 'listbox', 'name' => 'section', 'label' => '<b>�������</b>', 'height'=> 5,'items'=>$sections[0], 'values'=>$sections[1]),
				array ('type' => 'edit', 'name' => 'block', 'label' => '<b>���� �������</b>', 'width' => '100px', 'maxlength' => 15, 'comment' => '��� ������� ������� ����������� ������ <b>$(Banners:���_�����)</b>','pattern'=>'/.+/', 'errormsg'=>'�� ������ ���� �������!'),
				array ('type' => 'edit', 'name' => 'priority', 'label' => '���������', 'width' => '20px', 'comment' => '���� ��� ������ ������� � ������ ����� ������ ��������� ��������, ����� ������� � ������� �����������', 'default'=>0, 'pattern'=>'/\d+/', 'errormsg'=>'��������� �������� ������ �������!'),
				array ('type' => 'edit', 'name' => 'showFrom', 'label' => '������ �������', 'width' => '100px', 'comment' => '����-��-��', 'default'=>gettime('Y-m-d'), 'pattern'=>'/[12]\d{3,3}-[01]\d-[0-3]\d/', 'errormsg'=>'������������ ������ ����!'),
				array ('type' => 'edit', 'name' => 'showTill', 'label' => '����� �������', 'width' => '100px', 'comment' => '����-��-��; ������ - ��� �����������', 'pattern'=>'/(\d{4,4}-[01]\d-[0-3]\d)|(^$)/', 'errormsg'=>'������������ ������ ����!'),
				array ('type' => 'edit', 'name' => 'showCount', 'label' => '����. ���-�� �������', 'width' => '100px', 'comment' => '0 - ��� �����������', 'default'=>0, 'pattern'=>'/(\d+)|(^$)/', 'errormsg'=>'���-�� ������� �������� ������ �������!'),
				//array ('type' => 'edit', 'name' => 'mail', 'label' => 'e-mail ���������', 'width' => '200px', 'maxlength' => '63'),
				array ('type' => 'checkbox', 'name' => 'active', 'label' => '������������'),
				array ('type' => 'header', 'value' => '�������� �������'),
				array ('type' => 'file', 'name' => 'image', 'label' => '�������� ��� Flash', 'width'=>'50', 'comment' => '<a></a>'),
				array ('type' => 'edit', 'name' => 'width', 'label' => '������', 'width' => '100px', 'comment'=>'������ ��� Flash'),
				array ('type' => 'edit', 'name' => 'height', 'label' => '������', 'width' => '100px', 'comment'=>'������ ��� Flash'),
				array ('type' => 'edit', 'name' => 'url', 'label' => 'URL ��� ������', 'width' => '100%', 'maxlength' => '255'),
				array ('type' => 'select', 'name' => 'target', 'label' => '���������', 'items'=>array('� ����� ����', '� ��� �� ����')),
				array ('type' => 'header', 'value' => 'HTML-��� �������'),
				array ('type' => 'memo', 'name' => 'html', 'label' => 'HTML-��� (���� ����� HTML-���, �� ���������� �������� ������������ � ����� �� �����������)', 'height' => '4'),
				array ('type' => 'divider'),
				array ('type' => 'checkbox', 'name' => 'flushShowCount', 'label' => '�������� ���-�� �������'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);

		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� �������� ������ ��������
	 *
	 * @return string  HTML
	 */
	function adminRender()
	{
		global $Eresus, $page, $request, $session;

		$result = '';
		if (isset($request['arg']['id'])) {
			$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."` = '".$request['arg']['id']."'");
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
	//-----------------------------------------------------------------------------


	function adminRenderContent()
	{
	global $Eresus, $page;

		$result = '';
		if (!is_null(arg('id'))) {
			$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."` = '".arg('id', 'dbsafe')."'");
			$page->title .= empty($item['caption'])?'':' - '.$item['caption'];
		}
		switch (true) {
			case !is_null(arg('update')) && isset($this->table['controls']['edit']):
				if (method_exists($this, 'update')) $result = $this->update(); else ErrorMessage(sprintf(errMethodNotFound, 'update', get_class($this)));
			break;
			case !is_null(arg('toggle')) && isset($this->table['controls']['toggle']):
				if (method_exists($this, 'toggle')) $result = $this->toggle(arg('toggle', 'dbsafe')); else ErrorMessage(sprintf(errMethodNotFound, 'toggle', get_class($this)));
			break;
			case !is_null(arg('delete')) && isset($this->table['controls']['delete']):
				if (method_exists($this, 'delete')) $result = $this->delete(arg('delete', 'dbsafe')); else ErrorMessage(sprintf(errMethodNotFound, 'delete', get_class($this)));
			break;
			case !is_null(arg('up')) && isset($this->table['controls']['position']):
				if (method_exists($this, 'up')) $result = $this->table['sortDesc']?$this->down(arg('up', 'dbsafe')):$this->up(arg('up', 'dbsafe')); else ErrorMessage(sprintf(errMethodNotFound, 'up', get_class($this)));
			break;
			case !is_null(arg('down')) && isset($this->table['controls']['position']):
				if (method_exists($this, 'down')) $result = $this->table['sortDesc']?$this->up(arg('down', 'dbsafe')):$this->down(arg('down', 'dbsafe')); else ErrorMessage(sprintf(errMethodNotFound, 'down', get_class($this)));
			break;
			case !is_null(arg('id')) && isset($this->table['controls']['edit']):
				if (method_exists($this, 'adminEditItem')) $result = $this->adminEditItem(); else ErrorMessage(sprintf(errMethodNotFound, 'adminEditItem', get_class($this)));
			break;
			case !is_null(arg('action')):
				switch (arg('action')) {
					case 'create': if (isset($this->table['controls']['edit']))
						if (method_exists($this, 'adminAddItem')) $result = $this->adminAddItem();
						else ErrorMessage(sprintf(errMethodNotFound, 'adminAddItem', get_class($this)));
					break;
					case 'insert':
						if (method_exists($this, 'insert')) $result = $this->insert();
						else ErrorMessage(sprintf(errMethodNotFound, 'insert', get_class($this)));
					break;
				}
			break;
			default:
				if (!is_null(arg('section'))) $this->table['condition'] = "`section`='".arg('section', 'int')."'";
				$result = $page->renderTable($this->table);
		}
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#

	/**
	 *
	 * @return unknown_type
	 */
	function adminOnMenuRender()
	{
		global $page;

		$page->addMenuItem(admExtensions, array ('access'	=> EDITOR, 'link'	=> $this->name, 'caption'	=> $this->title, 'hint'	=> $this->description));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� �������� � ��������� ������
	 *
	 * @param string $text  HTML ��������
	 * @return string  HTML ��������
	 */
	public function clientOnPageRender($text)
	{
		global $Eresus, $page;

		if (arg('banners-click'))
		{
			/*
			 * ���� ������� �������� banners-click, ���� ������������� ������������ �� URL �������
			 */
			if (count($Eresus->request['arg']) != 1)
			{
				$page->httpError(404);
			}

			$id = arg('banners-click', 'int');
			if ($id == '' | $id != arg('banners-click'))
			{
				$page->httpError(404);
			}

			$this->processClick($id);

		}
		else
		{
			// ���� ��� ����� ������ ��������
			preg_match_all('/\$\(Banners:([^)]+)\)/', $text, $blocks, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
			$delta = 0;
			foreach ($blocks as $block)
			{
				$sql = "(`active`=1) AND (`section` LIKE '%|" . $page->id .
					"|%' OR `section` LIKE '%|all|%') AND (`block`='" . $block[1][0 ] .
					"') AND (`showFrom`<='" . gettime() . "') AND (`showCount`=0 OR (`shows` < `showCount`)) AND (`showTill` = '0000-00-00' OR `showTill` IS NULL OR `showTill` > '" .
					gettime() . "')";

				// �������� ������� ��� ����� ����� � ������� ���������� ����������
				$items = $this->dbSelect('', $sql, '-priority');
				if (count($items))
				{
					/* �������� ������� � ������ ����������� */
					$priority = $items[0]['priority'];
					for ($i = 0; $i < count($items); $i++)
					{
						if ($items[$i]['priority'] != $priority)
						{
							$items = array_slice($items, 0, $i);
							break;
						}
					}

					// �������� ��������� ������
					$item = $items[mt_rand(0, count($items)-1)];
					$item['shows']++;
					$banner = BannersFactory::createFromArray($item);

					$Eresus->db->updateItem($this->name, $Eresus->db->escape($item), "`id`='".$item['id']."'");

					$code = $banner->render();
					$text = substr_replace($text, $code, $block[0][1]+$delta, strlen($block[0][0]));
					$delta += strlen($code) - strlen($block[0][0]);
				}
			}
			$items = $Eresus->db->select($this->table['name'],
				"(`showCount` != 0 AND `shows` > `showCount`) AND ((`showTill` < '" . gettime() .
				"') AND (`showTill` != '0000-00-00'))");
			if (count($items))
			{
				foreach($items as $item)
				{
					//sendMail($item['mail'], '��� ������ �������������', '��� ������ "'.$item['caption'].' ��� ��������, �.�. ��� ��� ��������� ���������� ������� ���� ���� ������."');
					sendMail(getOption('sendNotifyTo'), '������ �������������', '������ "'.$item['caption'].' ��� �������� �������� ���������� ������."');
				}
				$Eresus->db->update($this->table['name'], "`active`='0'", "(`showCount` != 0 AND `shows` > `showCount`) AND ((`showTill` < '".gettime()."') AND (`showTill` != '0000-00-00'))");
			}
		}
		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * �������������� ���������� �� URL, �������� ��������
	 *
	 * @param int $id  ������������� �������
	 */
	private function processClick($id)
	{
		$item = $this->dbItem('', $id);
		if ($item)
		{
			$item['clicks']++;
			$this->dbUpdate('', $item);

			HTTP::redirect($item['url']);
		}
		else
		{
			$GLOBALS['page']->httpError(404);
		}
	}
	//-----------------------------------------------------------------------------

	protected function createTable($table)
	{
		global $Eresus;

		$Eresus->db->query('CREATE TABLE IF NOT EXISTS `'.$Eresus->db->prefix.$table['name'].'`'.$table['sql']);
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}



/**
 * ������
 *
 * @package Banners
 */
abstract class AbstractBanner
{
	/**
	 * �������� �������
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * ����������� �������
	 *
	 * @param array $data
	 * @return AbstractBanner
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ����� ������ ���������� �������� ������� ��� ���������� �� ��������
	 *
	 * @return string  HTML
	 */
	abstract public function render();
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ������ ������� Banners
	 *
	 * @return Banners
	 */
	protected function getPlugin()
	{
		$plugin = $GLOBALS['Eresus']->plugins->load('banners');
		return $plugin;
	}
	//-----------------------------------------------------------------------------
}



/**
 * ��������� ������
 *
 * @package Banners
 */
class TextBanner extends AbstractBanner
{
	/**
	 * ���������� ���� ������� ��� ������� �� ��������
	 *
	 * @return string  HTML
	 * @see AbstractBanner::render()
	 */
	public function render()
	{
		return $this->data['html'];
	}
	//-----------------------------------------------------------------------------
}



/**
 * ����������� ������
 *
 * @package Banners
 */
class ImageBanner extends AbstractBanner
{
	/**
	 * ���������� ���� ������� ��� ������� �� ��������
	 *
	 * @return string  HTML
	 * @see AbstractBanner::render()
	 */
	public function render()
	{
		global $Eresus;

		$plugin = $this->getPlugin();

		$html = img($plugin->getDataDir() . $this->data['image']);

		if (!empty($this->data['url']))
		{
			$template = '<a href="%s"%s>%s</a>';

			$url = $Eresus->request['path'] . '?banners-click=' .	$this->data['id'];
			$target = $this->data['target'] ? '' : ' target="_blank"';

			$html = sprintf($template, $url, $target, $html);
		}

		return $html;
	}
	//-----------------------------------------------------------------------------
}



/**
 * Flash-������
 *
 * @package Banners
 */
class FlashBanner extends AbstractBanner
{
	/**
	 * ���������� ���� ������� ��� ������� �� ��������
	 *
	 * @return string  HTML
	 * @see AbstractBanner::render()
	 */
	public function render()
	{
		global $Eresus, $page;

		$plugin = $this->getPlugin();

		$template =
			'<object type="application/x-shockwave-flash" data="%s" width="%d" height="%d">' .
				'<param name="movie" value="%1$s" />' .
				'<param name="quality" value="high" />' .
				'<param name="wmode" value="opaque" />' .
			'</object>';

		$swf = $plugin->urlData . $this->data['image'];
		$width = $this->data['width'];
		$height = $this->data['height'];

		$html = sprintf($template, $swf, $width, $height);

		if (!empty($this->data['url']))
		{
			$page->linkStyles($plugin->urlCode . 'main.css');

			$template =
				'<div class="banners-swf-container">' .
					'<div class="banners-swf-overlay">' .
						'<a href="%1$s"%2$s><img src="%4$s" alt="" width="%5$d" height="%6$d" /></a>' .
					'</div>' .
					'%3$s' .
				'</div>';

			$url = $Eresus->request['path'] . '?banners-click=' .	$this->data['id'];
			$target = $this->data['target'] ? '' : ' target="_blank"';
			$stubImage = $Eresus->root . 'style/dot.gif';

			$html = sprintf($template, $url, $target, $html, $stubImage, $width, $height);
		}

		return $html;
	}
	//-----------------------------------------------------------------------------
}



/**
 * ������� ��������
 *
 * ����� ������������ ��� �������� �������� ��������
 *
 * @package Banners
 */
class BannersFactory
{
	/**
	 * ������ ������ ������� �� ������� ��� �������
	 *
	 * @param array $data
	 * @return Banner  ������ �������
	 */
	public static function createFromArray($data)
	{
		switch (true)
		{
			case $data['html'] != '':
				return new TextBanner($data);

			case preg_match('/\.swf$/i', $data['image']):
				return new FlashBanner($data);

			default:
				return new ImageBanner($data);
		}
	}
	//-----------------------------------------------------------------------------
}


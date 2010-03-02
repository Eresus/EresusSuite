<?php
/**
 * �������
 *
 * Eresus 2
 *
 * ����� ��������
 *
 * @version 2.07
 *
 * @copyright   2005-2007, ProCreat Systems, http://procreat.ru/
 * @copyright   2007-2009, Eresus Project, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
 * @author      bersz <anton@procreat.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 2 ���� �� ������ ������ � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * @package Plugins
 * @subpackage News
 *
 * $Id$
 */

/**
 * ����� �������
 *
 * @package Plugins
 * @subpackage News
 *
 */
class TNews extends TListContentPlugin {

	/**
	 * ��� �������
	 * @var string
	 */
	var $name = 'news';

	/**
	 * ��� �������
	 * @var string
	 */
	var $type = 'client,content';

	/**
	 * �������� �������
	 * @var string
	 */
	var $title = '�������';

	/**
	 * ������ �������
	 * @var string
	 */
	var $version = '2.07';

	/**
	 * �������� �������
	 * @var string
	 */
	var $description = '���������� ��������';

	/**
	 * ��������� �������
	 * @var array
	 */
	var $settings = array(
			'itemsPerPage' => 10,
			'tmplListItem' => '
				<div class="NewsListItem">
					<div class="caption">$(caption) ($(posted))</div>
					$(preview)
					<br />
					<a href="$(link)">������ �����...</a>
				</div>
			',
			'tmplItem' => '<h3>$(caption)</h3>$(posted)<br /><br />$(text)',
			'tmplLastNews' => '<b>$(posted)</b><br /><a href="$(link)">$(caption)</a><br />',
			'previewMaxSize' => 500,
			'previewSmartSplit' => true,
			'dateFormatPreview' => DATE_SHORT,
			'dateFormatFullText' => DATE_LONG,
			'lastNewsMode' => 0,
			'lastNewsCount' => 5,
		);

	/**
	 * ������� ������ ��������
	 * @var array
	 */
	var $table = array (
		'name' => 'news',
		'key'=> 'id',
		'sortMode' => 'posted',
		'sortDesc' => true,
		'columns' => array(
			array('name' => 'caption', 'caption' => '���������', 'wrap' => false),
			array('name' => 'posted', 'align'=>'center', 'value' => templPosted, 'macros' => true),
			array('name' => 'preview', 'caption' => '������'),
		),
		'controls' => array (
			'delete' => '',
			'edit' => '',
			'toggle' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
				array('caption'=>'�������� �������', 'name'=>'action', 'value'=>'create')
			),
		),
		'sql' => "(
			`id` int(10) unsigned NOT NULL auto_increment,
			`section` int(10) unsigned default NULL,
			`posted` datetime default NULL,
			`caption` varchar(100) NOT NULL default '',
			`active` tinyint(1) unsigned NOT NULL default '1',
			`preview` text NOT NULL,
			`text` longtext NOT NULL,
			PRIMARY KEY	(`id`),
			KEY `section` (`section`),
			KEY `posted` (`posted`)
			) TYPE=MyISAM COMMENT='News';",
	);

	/**
	 * �����������
	 *
	 * ���������� ����������� ������������ �������
	 */
	function TNews()
	{
		global $plugins;

		parent::TListContentPlugin();

		switch ($this->settings['lastNewsMode']) {
			case 1:
				$plugins->events['clientOnPageRender'][] = $this->name;
			break;
		}

	}
	//-----------------------------------------------------------------------------

	/**
	 * �������� �������� ������
	 *
	 * @param string $text
	 * @return string
	 */
	function createPreview($text)
	{

		$text = trim(preg_replace('/<[^>]+?>/Us',' ',$text));

		if ($this->settings['previewSmartSplit']) {

			if (preg_match("/\A.{1,".$this->settings['previewMaxSize']."}([\.;]|$)/s", $text, $result)) {

				$result = str_replace(array("\n","\r"),' ',$result[0]);

			} else {

				$this->settings['previewSmartSplit'] = false;
				$result = $this->createPreview($text);

			}

		} else {

			$result = substr($text, 0, $this->settings['previewMaxSize']);
			if (strlen($text)>$this->settings['previewMaxSize']) $result .= '...';

		}

		return $result;

	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ������� � ��
	 *
	 */
	function insert()
	{
		global $db, $request, $page;

		$item['section'] = arg('section', 'int');
		$item['posted'] = gettime();
		if (empty($item['posted'])) $item['posted'] = gettime();
		$item['caption'] = arg('caption', 'dbsafe');
		$item['active'] = true;
		$item['text'] = arg('text', 'dbsafe');
		$item['preview'] = arg('preview', 'dbsafe');
		if (empty($item['preview'])) $item['preview'] = $this->createPreview($item['text']);

		$db->insert($this->table['name'], $item);
		$item['id'] = $db->getInsertedID();
		sendNotify(admAdded.': <a href="'.httpRoot.'admin.php?mod=content&section='.$item['section'].'&id='.$item['id'].'">'.$item['caption'].'</a><br />'.$item['text'], array('editors'=>defined('CLIENTUI_VERSION')));
		goto($request['arg']['submitURL']);

	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ������� � ��
	 */
	function update()
	{
		global $db, $page, $request;

		$item = $db->selectItem($this->table['name'], "`id`='".$request['arg']['update']."'");
		$item['section'] = arg('section', 'int');
		if ( ! is_null(arg('section')) )
			$item['active'] = arg('active', 'int');

		$item['posted'] = arg('posted', 'dbsafe');
		$item['caption'] = arg('caption', 'dbsafe');
		$item['text'] = arg('text', 'dbsafe');
		$item['preview'] = arg('preview', 'dbsafe');
		if (empty($item['preview']) || arg('updatePreview')) $item['preview'] = $this->createPreview($item['text']);

		$db->updateItem($this->table['name'], $item, "`id`='".$request['arg']['update']."'");
		sendNotify(admUpdated.': <a href="'.$page->url().'">'.$item['caption'].'</a><br />'.$item['text']);
		goto($request['arg']['submitURL']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ��������
	 *
	 * @param string $template
	 * @param array $item
	 * @param string $dateFormat
	 * @return string
	 */
	function replaceMacros($template, $item, $dateFormat)
	{
		global $page;

		$item['preview'] = '<p>'.str_replace("\n", "</p>\n<p>", $item['preview']).'</p>';
		$item['posted'] = FormatDate($item['posted'], $dateFormat);
		$item['link'] = $page->clientURL($item['section']).$item['id'].'/';
		$result = parent::replaceMacros($template, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ���������� �������
	 *
	 * @return string
	 */
	function adminAddItem()
	{
	global $page, $request;

		$form = array(
			'name' => 'newNews',
			'caption' => '�������� �������',
			'width' => '95%',
			'fields' => array (
				array ('type'=>'hidden','name'=>'action', 'value'=>'insert'),
				array ('type' => 'hidden', 'name' => 'section', 'value' => $request['arg']['section']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '���������', 'width' => '100%', 'maxlength' => '100'),
				array ('type' => 'html', 'name' => 'text', 'label' => '������ �����', 'height' => '200px'),
				array ('type' => 'memo', 'name' => 'preview', 'label' => '������� ��������', 'height' => '10'),
				array ('type' => 'edit', 'name'=>'posted', 'label'=>'��������'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ��������� �������
	 *
	 * @return string
	 */
	function adminEditItem()
	{
	global $db, $page, $request;

		$item = $db->selectItem($this->table['name'], "`id`='".$request['arg']['id']."'");
		$form = array(
			'name' => 'editNews',
			'caption' => '�������� �������',
			'width' => '95%',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '���������', 'width' => '100%', 'maxlength' => '100'),
				array ('type' => 'html', 'name' => 'text', 'label' => '������ �����', 'height' => '200px'),
				array ('type' => 'memo', 'name' => 'preview', 'label' => '������� ��������', 'height' => '5'),
				array ('type' => 'checkbox', 'name'=>'updatePreview', 'label'=>'�������� ������� �������� �������������'),
				array ('type' => 'divider'),
				array ('type' => 'edit', 'name' => 'section', 'label' => '������', 'access'=>ADMIN),
				array ('type' => 'edit', 'name'=>'posted', 'label'=>'��������'),
				array ('type' => 'checkbox', 'name'=>'active', 'label'=>'�������'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ������ ��������
	 *
	 * @return string
	 */
	function settings()
	{
	global $page;

		$form = array(
			'name' => 'settings',
			'caption' => $this->title.' '.$this->version,
			'width' => '500px',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$this->name),
				array('type'=>'edit','name'=>'itemsPerPage','label'=>'�������� �� ��������','width'=>'50px', 'maxlength'=>'2'),
				array('type'=>'memo','name'=>'tmplListItem','label'=>'������ �������� ������','height'=>'5'),
				array('type'=>'edit','name'=>'dateFormatPreview','label'=>'������ ����', 'width'=>'200px'),
				array('type'=>'edit','name'=>'previewMaxSize','label'=>'����. ������ ��������','width'=>'50px', 'maxlength'=>'4', 'comment'=>'��������'),
				array('type'=>'checkbox','name'=>'previewSmartSplit','label'=>'"�����" �������� ��������'),
				array('type'=>'divider'),
				array('type'=>'memo','name'=>'tmplItem','label'=>'������ ��������������� ���������','height'=>'5'),
				array('type'=>'edit','name'=>'dateFormatFullText','label'=>'������ ����', 'width'=>'200px'),
				array('type'=>'header', 'value' => '��������� �������'),
				array('type'=>'memo','name'=>'tmplLastNews','label'=>'������ ��������� ��������','height'=>'3'),
				array('type'=>'select','name'=>'lastNewsMode','label'=>'�����', 'items'=>array('���������', '�������� ������ $(NewsLast)')),
				array('type'=>'edit','name'=>'lastNewsCount','label'=>'���������� ��������', 'width'=>'100px'),
		),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $this->settings);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���� ��������� ��������
	 *
	 * @return string
	 */
	function renderLastNews()
	{
		global $db;

		$result = '';
		$items = $db->select($this->table['name'], "`active`='1'", 'posted', true, '', $this->settings['lastNewsCount']);
		if (count($items)) foreach($items as $item) $result .= $this->replaceMacros($this->settings['tmplLastNews'], $item, $this->settings['dateFormatPreview']);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������� � ������ � ��
	 *
	 * @param array $item
	 * @return string
	 */
	function clientRenderListItem($item)
	{
		$result = $this->replaceMacros($this->settings['tmplListItem'], $item, $this->settings['dateFormatPreview']);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������� ������ �������
	 *
	 * @return string
	 */
	function clientRenderItem()
	{
		global $db, $page, $plugins, $request;

		$item = $db->selectItem($this->table['name'], "(`id`='".$page->topic."')AND(`active`='1')");
		if (is_null($item)) $page->httpError('404');
		$result = $this->replaceMacros($this->settings['tmplItem'], $item, $this->settings['dateFormatFullText']).$page->buttonBack();
		$page->section[] = $item['caption'];
		$item['access'] = $page->access;
		$item['name'] = $item['id'];
		$item['title'] = $item['caption'];
		$item['hint'] = $item['description'] = $item['keywords'] = '';
		$plugins->clientOnURLSplit($item, $request['path']);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ����������� ����� ��������� ��������
	 *
	 * @param string $text
	 * @return string
	 */
	function clientOnPageRender($text)
	{
	global $page;

		$text = str_replace('$(NewsLast)', $this->renderLastNews(), $text);
		return $text;
	}
	//-----------------------------------------------------------------------------
}

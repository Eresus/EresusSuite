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
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� (�� ������ ������) � ��������� ����� �������
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
 * $Id: themes.php 703 2010-01-23 09:58:25Z mk $
 */

#TODO: ��������� ��� �� ������� � ������� �����������

/**
 * ��������� ������ ����������
 *
 * @package EresusCMS
 * @author mekras
 */
class TThemes
{
	/**
	 * ???
	 * @var unknown_type
	 */
	public $access = ADMIN;

	/**
	 * ???
	 * @var unknown_type
	 */
	public $tabs = array(
		'width' => admThemesTabWidth,
		'items' => array(
			array('caption' => admThemesTemplates),
			array('caption' => admThemesStandard),
			array('caption' => admThemesStyles),
		),
	);

	/**
	 * ???
	 * @var unknown_type
	 */
	public $stdTemplates = array(
		'SectionListItem' => array('caption' => admTemplList, 'hint' => admTemplListItemLabel),
		'PageSelector' => array('caption' => admTemplPageSelector, 'hint' => admTemplPageSelectorLabel),
		'400' => array('caption' => 'HTTP 400 - Bad Request'),
		'401' => array('caption' => 'HTTP 401 - Unauthorized'),
		'402' => array('caption' => 'HTTP 402 - Payment Required'),
		'403' => array('caption' => 'HTTP 403 - Forbidden'),
		'404' => array('caption' => 'HTTP 404 - Not Found'),
		'405' => array('caption' => 'HTTP 405 - Method Not Allowed'),
		'406' => array('caption' => 'HTTP 406 - Not Acceptable'),
		'407' => array('caption' => 'HTTP 407 - Proxy Authentication Required'),
		'408' => array('caption' => 'HTTP 408 - Request Timeout'),
		'409' => array('caption' => 'HTTP 409 - Conflict'),
		'410' => array('caption' => 'HTTP 410 - Gone'),
		'411' => array('caption' => 'HTTP 411 - Length Required'),
		'412' => array('caption' => 'HTTP 412 - Precondition Failed'),
		'413' => array('caption' => 'HTTP 413 - Request Entity Too Large'),
		'414' => array('caption' => 'HTTP 414 - Request-URI Too Long'),
		'415' => array('caption' => 'HTTP 415 - Unsupported Media Type'),
		'416' => array('caption' => 'HTTP 416 - Requested Range Not Satisfiable'),
		'417' => array('caption' => 'HTTP 417 - Expectation Failed'),
	);

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionTemplatesInsert()
	{
		useLib('templates');
		$templates = new Templates();
		$templates->add(arg('name'), '', arg('code'), arg('desc'));
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionTemplatesUpdate()
	{
		useLib('templates');
		$templates = new Templates();
		$templates->update(arg('name'), '', arg('code'), arg('desc'));
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionTemplatesDelete()
	{
		global $page;

		useLib('templates');
		$templates = new Templates();
		$templates->delete(arg('delete'));
		HTTP::redirect($page->url());
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionTemplatesAdd()
	{
		global $page;

		$form = array(
			'name' => 'addForm',
			'caption' => $page->title.admTDiv.admAdd,
			'width' => '100%',
			'fields' => array (
				array('type'=>'hidden','name'=>'action', 'value'=>'insert'),
				array('type'=>'hidden','name'=>'section', 'value'=>arg('section')),
				array('type'=>'edit','name'=>'name','label'=>admThemesFilenameLabel, 'width'=>'200px', 'comment'=>'.html'),
				array('type'=>'edit','name'=>'desc','label'=>admThemesDescriptionLabel, 'width'=>'100%'),
				array('type'=>'memo','name'=>'code', 'height'=>'30', 'syntax' => 'html'),
			),
			'buttons' => array('ok','cancel'),
		);
		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionTemplatesEdit()
	{
		global $page;

		useLib('templates');
		$templates = new Templates();
		$item = $templates->get(arg('id'), '', true);
		$form = array(
			'name' => 'editForm',
			'caption' => $page->title.admTDiv.admEdit,
			'width' => '100%',
			'fields' => array (
				array('type'=>'hidden','name'=>'action', 'value'=>'update'),
				array('type'=>'hidden','name'=>'section', 'value'=>arg('section')),
				array('type'=>'hidden','name'=>'name'),
				array('type'=>'edit','name'=>'filename','label'=>admThemesFilenameLabel, 'width'=>'200px', 'comment'=>'.html', 'disabled' => true, 'value' => $item['name']),
				array('type'=>'edit','name'=>'desc','label'=>admThemesDescriptionLabel, 'width'=>'100%'),
				array('type'=>'memo','name'=>'code', 'height'=>'30', 'syntax' => 'html'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionTemplatesList()
	{
		global $page;

		$table = array(
			'name' => 'templates',
			'key'=> 'filename',
			'sortMode' => 'filename',
			'sortDesc' => false,
			'columns' => array(
				array('name' => 'description', 'caption' => '��������'),
				array('name' => 'filename', 'caption' => '��� �����'),
			),
			'controls' => array (
				'delete' => '',
				'edit' => '',
			),
			'tabs' => array(
				'width'=>'120px',
				'items'=>array(
					array('caption'=>admAdd, 'name'=>'action', 'value'=>'add'),
				)
			),
		);
		useLib('templates');
		$templates = new Templates();
		$list = $templates->enum();
		$items = array();
		foreach($list as $key=>$value) $items[] = array('filename' => $key, 'description' => $value);
		$result = $page->renderTable($table, $items);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionTemplates()
	{
	global $Eresus, $page;

		$page->title .= admTDiv.admThemesTemplates;

		switch(arg('action')) {
			case 'update': $result = $this->sectionTemplatesUpdate(); break;
			case 'insert': $result = $this->sectionTemplatesInsert(); break;
			case 'add': $result = $this->sectionTemplatesAdd(); break;
			default:
				if (arg('delete')) $result = $this->sectionTemplatesDelete();
				elseif (arg('id')) $result = $this->sectionTemplatesEdit();
				else $result = $this->sectionTemplatesList();
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStdInsert()
	{
		useLib('templates');
		$templates = new Templates();
		$templates->add(arg('name'), 'std', arg('code'), $this->stdTemplates[arg('name')]['caption']);
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStdUpdate()
	{
		$this->sectionStdInsert();
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStdDelete()
	{
		global $page;

		useLib('templates');
		$templates = new Templates();
		$templates->delete(arg('delete'), 'std');
		HTTP::redirect($page->url());
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStdAdd()
	{
		global $page;

		$values = array();
		$items = array();
		$jsArray = "var aTemplates = Array();\n";
		foreach($this->stdTemplates as $key => $item) {
			if (!isset($hint)) $hint = isset($item['hint'])?$item['hint']:'';
			$values[] = $key;
			$items[] = $item['caption'];
			$jsArray .= "aTemplates['".$key."'] = '".(isset($item['hint'])?$item['hint']:'')."'\n";
		}

		$page->scripts .= $jsArray."
			function onTemplateNameChange()
			{
				document.getElementById('templateHint').innerHTML = aTemplates[document.addForm.elements.namedItem('name').value];
			}
		";
		$form = array(
			'name' => 'addForm',
			'caption' => $page->title.admTDiv.admAdd,
			'width' => '100%',
			'fields' => array (
				array('type'=>'hidden','name'=>'action', 'value'=>'insert'),
				array('type'=>'hidden','name'=>'section', 'value'=>arg('section')),
				array('type'=>'select','name'=>'name','label'=>admThemesTemplate, 'values'=>$values, 'items'=>$items, 'extra' => 'onChange="onTemplateNameChange()"'),
				array('type'=>'text','name'=>'hint', 'value' => $hint, 'extra' => 'id="templateHint"'),
				array('type'=>'memo','name'=>'code', 'height'=>'30', 'syntax' => 'html'),
			),
			'buttons' => array('ok','cancel'),
		);
		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStdEdit()
	{
		global $page;

		useLib('templates');
		$templates = new Templates();
		$item = $templates->get(arg('id'), 'std', true);
		$form = array(
			'name' => 'editForm',
			'caption' => $page->title.admTDiv.admEdit,
			'width' => '100%',
			'fields' => array (
				array('type'=>'hidden','name'=>'action', 'value'=>'update'),
				array('type'=>'hidden','name'=>'section', 'value'=>arg('section')),
				array('type'=>'hidden','name'=>'name'),
				array('type'=>'edit','name'=>'_name','label'=>admThemesFilenameLabel, 'width'=>'200px', 'comment'=>'.tmpl ('.$this->stdTemplates[$item['name']]['caption'].')', 'disabled' => true, 'value'=>$item['name']),
				array('type'=>'text','name'=>'hint', 'value' => isset($this->stdTemplates[$item['name']]['hint'])?$this->stdTemplates[$item['name']]['hint']:'', 'extra' => 'id="templateHint"'),
				array('type'=>'memo','name'=>'code', 'height'=>'30', 'syntax' => 'html'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStdList()
	{
		global $page;

		$table = array(
			'name' => 'templates',
			'key'=> 'filename',
			'sortMode' => 'filename',
			'sortDesc' => false,
			'columns' => array(
				array('name' => 'description', 'caption' => '��������'),
				#array('name' => 'filename', 'caption' => '��� �����'),
			),
			'controls' => array (
				'delete' => '',
				'edit' => '',
			),
			'tabs' => array(
				'width'=>'120px',
				'items'=>array(
					array('caption'=>admAdd, 'name'=>'action', 'value'=>'add'),
				)
			),
		);
		useLib('templates');
		$templates = new Templates();
		$list = $templates->enum('std');
		$items = array();
		foreach($list as $key=>$value) $items[] = array('filename' => $key, 'description' => $value);
		$result = $page->renderTable($table, $items);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStd()
	{
		global $page;

		$page->title .= admTDiv.admThemesStandard;

		switch(arg('action')) {
			case 'update': $result = $this->sectionStdUpdate(); break;
			case 'insert': $result = $this->sectionStdInsert(); break;
			case 'add': $result = $this->sectionStdAdd(); break;
			default:
				if (arg('delete')) $result = $this->sectionStdDelete();
				if (arg('id')) $result = $this->sectionStdEdit();
				else $result = $this->sectionStdList();
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStylesInsert()
	{
		$file = "/* ".arg('description')." */\r\n\r\n".arg('html');
		$fp = fopen(filesRoot.'style/'.arg('filename').'.css', 'w');
		fwrite($fp, $file);
		fclose($fp);
		SendNotify((arg('update')?admUpdated:admAdded).': '.arg('filename').'.css');
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 */
	public function sectionStylesUpdate()
	{
		global $Eresus;
		$Eresus->request['update'] = true;
		$this->sectionStylesInsert();
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStylesDelete()
	{
		global $page;

		$filename = filesRoot.'style/'.arg('delete');
		if (file_exists($filename)) unlink($filename);
		SendNotify(admDeleted.': '.arg('delete'));
		HTTP::redirect($page->url());
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStylesAdd()
	{
		global $page;

		$form = array(
			'name' => 'addForm',
			'caption' => $page->title.admTDiv.admAdd,
			'width' => '100%',
			'fields' => array (
				array('type'=>'hidden','name'=>'action', 'value'=>'insert'),
				array('type'=>'hidden','name'=>'section', 'value'=>arg('section')),
				array('type'=>'edit','name'=>'filename','label'=>admThemesFilenameLabel, 'width'=>'200px', 'comment'=>'.css'),
				array('type'=>'edit','name'=>'description','label'=>admThemesDescriptionLabel, 'width'=>'100%'),
				array('type'=>'memo','name'=>'html', 'height'=>'30', 'syntax' => 'css'),
			),
			'buttons' => array('ok','cancel'),
		);
		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStylesEdit()
	{
		global $page;

		$item['filename'] = arg('id');
		$item['html'] = trim(file_get_contents(filesRoot.'style/'.$item['filename']));
		preg_match('|/\*(.*?)\*/|', $item['html'], $item['description']);
		$item['description'] = trim($item['description'][1]);
		$item['filename'] = substr($item['filename'], 0, strrpos($item['filename'], '.'));
		$item['html'] = trim(substr($item['html'], strpos($item['html'], "\n")));
		$form = array(
			'name' => 'editForm',
			'caption' => $page->title.admTDiv.admEdit,
			'width' => '100%',
			'fields' => array (
				array('type'=>'hidden','name'=>'action', 'value'=>'update'),
				array('type'=>'hidden','name'=>'section', 'value'=>arg('section')),
				array('type'=>'hidden','name'=>'filename'),
				array('type'=>'edit','name'=>'_filename','label'=>admThemesFilenameLabel, 'width'=>'200px', 'comment'=>'.css', 'disabled' => true, 'value' => $item['filename']),
				array('type'=>'edit','name'=>'description','label'=>admThemesDescriptionLabel, 'width'=>'100%'),
				array('type'=>'memo','name'=>'html', 'height'=>'30', 'syntax' => 'css'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStylesList()
	{
		global $page;

		$table = array(
			'name' => 'Styles',
			'key'=> 'filename',
			'sortMode' => 'filename',
			'sortDesc' => false,
			'columns' => array(
				array('name' => 'description', 'caption' => '��������'),
				array('name' => 'filename', 'caption' => '��� �����'),
			),
			'controls' => array (
				'delete' => '',
				'edit' => '',
			),
			'tabs' => array(
				'width'=>'120px',
				'items'=>array(
					array('caption'=>admAdd, 'name'=>'action', 'value'=>'add'),
				)
			),
		);
		# ��������� ������ ��������
		$dir = filesRoot.'style/';
		$hnd = opendir($dir);
		while (($filename = readdir($hnd))!==false) if (preg_match('/.*\.css$/', $filename)) {
			$description = file_get_contents($dir.$filename);
			preg_match('|/\*(.*?)\*/|', $description, $description);
			$description = trim($description[1]);
			$items[] = array(
				'filename' => $filename,
				'description' => $description,
			);
		}
		closedir($hnd);
		$result = $page->renderTable($table, $items);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function sectionStyles()
	{
	global $page;

		$page->title .= admTDiv.admThemesStyles;
		switch(arg('action')) {
			case 'update': $result = $this->sectionStylesUpdate(); break;
			case 'insert': $result = $this->sectionStylesInsert(); break;
			case 'add': $result = $this->sectionStylesAdd(); break;
			default:
				if (arg('delete')) $result = $this->sectionStylesDelete();
				elseif (arg('id')) $result = $this->sectionStylesEdit();
				else $result = $this->sectionStylesList();
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ???
	 * @return unknown_type
	 */
	public function adminRender()
	{
		global $page;

		$result = '';
		if (UserRights($this->access)) {
			#FIXME: ��������� ������� #0000163
			$this->tabs['items'][0]['url'] = $page->url(array('id' => '', 'section' => 'templates'));
			$this->tabs['items'][1]['url'] = $page->url(array('id' => '', 'section' => 'std'));
			$this->tabs['items'][2]['url'] = $page->url(array('id' => '', 'section' => 'css'));
			$result .= $page->renderTabs($this->tabs);
			switch (arg('section')) {
				case 'css': $result .= $this->sectionStyles(); break;
				case 'std': $result .= $this->sectionStd(); break;
				case 'themes': default: $result .= $this->sectionTemplates(); break;
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------
}

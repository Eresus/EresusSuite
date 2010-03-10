<?php
/**
 * Новости
 *
 * Eresus 2.12
 *
 * Публикация новостей
 *
 * @version 2.08
 *
 * @copyright 2005, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Project, http://eresus.ru/
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 * @author bersz <anton@procreat.ru>
 * @author Ghost <ghost@dvaslona.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 2 либо по вашему выбору с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * @package News
 *
 * $Id$
 */

/**
 * Класс плагина
 *
 * @package News
 *
 */
class TNews extends TListContentPlugin
{

	/**
	 * Имя плагина
	 * @var string
	 */
	public $name = 'news';

	/**
	 * Требуемая версия ядра
	 * @var string
	 */
	public $kernel = '2.12b';

	/**
	 * Тип плагина
	 * @var string
	 */
	public $type = 'client,content';

	/**
	 * Название плагина
	 * @var string
	 */
	public $title = 'Новости';

	/**
	 * Версия плагина
	 * @var string
	 */
	public $version = '2.08b';

	/**
	 * Описание плагина
	 * @var string
	 */
	public $description = 'Публикация новостей';

	/**
	 * Настройки плагина
	 * @var array
	 */
	public $settings = array(
		'itemsPerPage' => 10,
		'tmplListItem' => '
			<div class="NewsListItem">
				<div class="caption">$(caption) ($(posted))</div>
				$(preview)
				<br />
				<a href="$(link)">Полный текст...</a>
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
	 * Таблица списка объектов
	 * @var array
	 */
	public $table = array (
		'name' => 'news',
		'key'=> 'id',
		'sortMode' => 'posted',
		'sortDesc' => true,
		'columns' => array(
			array('name' => 'caption', 'caption' => 'Заголовок', 'wrap' => false),
			array('name' => 'posted', 'align'=>'center', 'value' => templPosted, 'macros' => true),
			array('name' => 'preview', 'caption' => 'Кратко'),
		),
		'controls' => array (
			'delete' => '',
			'edit' => '',
			'toggle' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
				array('caption'=>'Добавить новость', 'name'=>'action', 'value'=>'create')
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
	 * Конструктор
	 *
	 * Производит регистрацию обработчиков событий
	 */
	function __construct()
	{
		global $plugins;

		parent::__construct();

		switch ($this->settings['lastNewsMode'])
		{
			case 1:
				$plugins->events['clientOnPageRender'][] = $this->name;
			break;
		}

	}
	//-----------------------------------------------------------------------------

	/**
	 * Создание краткого текста
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
	 * Запись новости в БД
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
		HTTP::redirect($request['arg']['submitURL']);

	}
	//-----------------------------------------------------------------------------

	/**
	 * Обновление новости в БД
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
		HTTP::redirect($request['arg']['submitURL']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Замена макросов
	 *
	 * @param string $template  Шаблон
	 * @param array $item       Замены
	 * @return string  HTML
	 */
	function replaceMacros($template, $item)
	{
		global $page;

		$item['preview'] = '<p>'.str_replace("\n", "</p>\n<p>", $item['preview']).'</p>';
		$item['link'] = $page->clientURL($item['section']).$item['id'].'/';
		$result = parent::replaceMacros($template, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диалог добавления новости
	 *
	 * @return string
	 */
	function adminAddItem()
	{
	global $page, $request;

		$form = array(
			'name' => 'newNews',
			'caption' => 'Добавить новость',
			'width' => '95%',
			'fields' => array (
				array ('type'=>'hidden','name'=>'action', 'value'=>'insert'),
				array ('type' => 'hidden', 'name' => 'section', 'value' => $request['arg']['section']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Заголовок', 'width' => '100%', 'maxlength' => '100'),
				array ('type' => 'html', 'name' => 'text', 'label' => 'Полный текст', 'height' => '200px'),
				array ('type' => 'memo', 'name' => 'preview', 'label' => 'Краткое описание', 'height' => '10'),
				array ('type' => 'edit', 'name'=>'posted', 'label'=>'Написано'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диалог изменения новости
	 *
	 * @return string
	 */
	function adminEditItem()
	{
	global $db, $page, $request;

		$item = $db->selectItem($this->table['name'], "`id`='".$request['arg']['id']."'");
		$form = array(
			'name' => 'editNews',
			'caption' => 'Изменить новость',
			'width' => '95%',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Заголовок', 'width' => '100%', 'maxlength' => '100'),
				array ('type' => 'html', 'name' => 'text', 'label' => 'Полный текст', 'height' => '200px'),
				array ('type' => 'memo', 'name' => 'preview', 'label' => 'Краткое описание', 'height' => '5'),
				array ('type' => 'checkbox', 'name'=>'updatePreview', 'label'=>'Обновить краткое описание автоматически'),
				array ('type' => 'divider'),
				array ('type' => 'edit', 'name' => 'section', 'label' => 'Раздел', 'access'=>ADMIN),
				array ('type' => 'edit', 'name'=>'posted', 'label'=>'Написано'),
				array ('type' => 'checkbox', 'name'=>'active', 'label'=>'Активно'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диалог настроек
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
				array('type'=>'edit','name'=>'itemsPerPage','label'=>'Новостей на страницу','width'=>'50px', 'maxlength'=>'2'),
				array('type'=>'memo','name'=>'tmplListItem','label'=>'Шаблон краткого текста','height'=>'5'),
				array('type'=>'edit','name'=>'dateFormatPreview','label'=>'Формат даты', 'width'=>'200px'),
				array('type'=>'edit','name'=>'previewMaxSize','label'=>'Макс. размер описания','width'=>'50px', 'maxlength'=>'4', 'comment'=>'симовлов'),
				array('type'=>'checkbox','name'=>'previewSmartSplit','label'=>'"Умное" создание описания'),
				array('type'=>'divider'),
				array('type'=>'memo','name'=>'tmplItem','label'=>'Шаблон полнотекстового просмотра','height'=>'5'),
				array('type'=>'edit','name'=>'dateFormatFullText','label'=>'Формат даты', 'width'=>'200px'),
				array('type'=>'header', 'value' => 'Последние новости'),
				array('type'=>'memo','name'=>'tmplLastNews','label'=>'Шаблон последних новостей','height'=>'3'),
				array('type'=>'select','name'=>'lastNewsMode','label'=>'Режим', 'items'=>array('отключить', 'Заменять макрос $(NewsLast)')),
				array('type'=>'edit','name'=>'lastNewsCount','label'=>'Показывать новостей', 'width'=>'100px'),
		),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $this->settings);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Формирование контента
	 *
	 * @return string
	 */
	function clientRenderContent()
	{
		global $Eresus, $page;

		if ($page->topic) {
			$acceptUrl = $Eresus->request['path'] .
				($page->subpage !== 0 ? 'p' . $page->subpage . '/' : '') .
				($page->topic !== false ? $page->topic . '/' : '');
			if ($acceptUrl != $Eresus->request['url']) {
				$page->httpError(404);
			}
		} else {
			$acceptUrl = $Eresus->request['path'] .
				($page->subpage !== 0 ? 'p' . $page->subpage . '/' : '');
			if ($acceptUrl != $Eresus->request['url']) {
				$page->httpError(404);
			}
		}

		return parent::clientRenderContent();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Блок последних новостей
	 *
	 * @return string
	 */
	function renderLastNews()
	{
		global $db;

		$result = '';
		$items = $db->select($this->table['name'], "`active`='1'", 'posted', true, '', $this->settings['lastNewsCount']);
		if (count($items))
			foreach($items as $item)
			{
				$item['posted'] = FormatDate($item['posted'], $this->settings['dateFormatPreview']);
				$result .= $this->replaceMacros($this->settings['tmplLastNews'], $item);
			}

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отрисовка новости в списке в КИ
	 *
	 * @param array $item
	 * @return string
	 */
	function clientRenderListItem($item)
	{
		$item['posted'] = FormatDate($item['posted'], $this->settings['dateFormatPreview']);
		$result = $this->replaceMacros($this->settings['tmplListItem'], $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отрисовка полного текста новости
	 *
	 * @return string
	 */
	function clientRenderItem()
	{
		global $db, $page, $plugins, $request;

		if ($page->topic != (string)((int)($page->topic))) {
			$page->httpError(404);
		}

		$item = $db->selectItem($this->table['name'], "(`id`='".$page->topic."')AND(`active`='1')");
		if (is_null($item)) $page->httpError('404');
		$item['posted'] = FormatDate($item['posted'], $this->settings['dateFormatFullText']);
		$result = $this->replaceMacros($this->settings['tmplItem'], $item).$page->buttonBack();
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
	 * Подстановка блока последних новостей
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

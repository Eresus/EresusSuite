<?php
/**
 * Галерея изображений
 *
 * Плагин позволяет публиковать на сайте картинки.
 *
 * @version 2.00
 *
 * @copyright 2008, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Михаил Красильников <mk@3wstyle.ru>
 * @author Ghost
 * @author Olex
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо по вашему выбору с условиями более поздней
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
 * @package Gallery
 *
 * $Id$
 */

/**
 * Класс плагина
 *
 * @package Gallery
 */
class Gallery extends ContentPlugin
{
	/**
	 * Название плагина
	 *
	 * @var string
	 */
	public $title = 'Галерея изображений';

	/**
	 * Версия плагина
	 *
	 * @var string
	 */
	public $version = '2.00';


	/**
	 * Требуемая версия CMS
	 *
	 * @var string
	 */
	public $kernel = '2.14';

	/**
	 * Описание плагина
	 *
	 * @var string
	 */
	public $description = 'Создание галерей изображений';

	/**
	 * Параметры плагина
	 *
	 * @var array
	 */
	public $settings = array(

		/* Свойства изображений */
		// Ширина изображения
		'imageWidth' => 800,
		// Высота изображения
		'imageHeight' => 600,

		/* Свойства миниатюр */
		// Ширина миниатюры
		'thumbWidth' => 120,
		// Высота миниатюры
		'thumbHeight' => 90,

		/* Список изображений */
		// Сортировка: date_asc, date_desc, manual
		'sort' => 'date_asc',
		// изображений на страницу
		'itemsPerPage' => 20,
		// Режим отображения изображений: normal, popup
		'showItemMode' => 'normal',

		/* Группы изображений */
		// Использовать группы
		'useGroups' => false,
		// Групп на страницу
		'groupsPerPage' => 10,

		// Накладывать логотип
		'logoEnable' => false,
		// Положение логотипа
		'logoPosition' => 'BR',
		// Вертикальный отступ
		'logoVPadding' => 10,
		// Горизонтальный отступ
		'logoHPadding' => 10,
	);

	public function __construct()
	{
		parent::__construct();
		EresusClassAutoloader::add($this->dirCode . 'gallery.autoload.php');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает свойство $urlData
	 *
	 * @return string
	 *
	 * @since 2.00
	 */
	public function getDataURL()
	{
		return $this->urlData;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает свойство $dirData
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
	 * Возвращает диалог настроек
	 *
	 * @return string  HTML
	 */
	public function settings()
	{
		global $Eresus, $page;

		$page->linkStyles($this->urlCode . 'admin.css');
		$this->linkJQuery();

		// Данные для подстановки в шаблон
		$data = array();
		$data['this'] = $this;
		$data['page'] = $page;
		$data['logo_exists'] = file_exists($this->dirData . 'logo.png');

		$tmplDir = $Eresus->froot . 'templates/' . $this->name;
		$this->settings['tmplImageList'] = file_get_contents($tmplDir . '/image-list.html');
		$this->settings['tmplImageGroupedList'] =
			file_get_contents($tmplDir . '/image-grouped-list.html');
		$this->settings['tmplImage'] = file_get_contents($tmplDir . '/image.html');

		// Создаём экземпляр шаблона
		$tmpl = new Template('ext/' . $this->name . '/templates/settings.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обновление настроек
	 *
	 * Загрузка рамок
	 *
	 * @return void
	 */
	public function updateSettings()
	{
		global $Eresus;

		/*
		 * Загрузка логотипа
		 */
		if (isset($_FILES['logoImage']['tmp_name']) &&
			is_uploaded_file($_FILES['logoImage']['tmp_name']))
		{
			if (substr($_FILES['logoImage']['name'], -3) == 'png')
			{
				upload('logoImage', $this->dirData .'logo.png');
			}
			else
			{
				ErrorMessage('Логотип должен быть в формате PNG');
			}
		}

		$tmplDir = $Eresus->froot . 'templates/' . $this->name;
		@file_put_contents($tmplDir . '/image-list.html', arg('tmplImageList'));
		@file_put_contents($tmplDir . '/image-grouped-list.html', arg('tmplImageGroupedList'));
		@file_put_contents($tmplDir . '/image.html', arg('tmplImage'));

		parent::updateSettings();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Действия при установке
	 *
	 * Метод создаёт необходимые таблицы в БД и директорию данных.
	 *
	 * @return void
	 */
	public function install()
	{
		global $Eresus;

		parent::install();

		/*
		 * Создаём таблицу изображений
		 */
		$sql = "
			`id` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентифкатор',
			`section` int(10) unsigned default NULL COMMENT 'Привязка к разделу сайта',
			`title` varchar(255) default NULL COMMENT 'Название картинки',
			`image` varchar(128) default NULL COMMENT 'Имя файла исходной картинки',
			`thumb` varchar(128) default NULL COMMENT 'Имя файла миниатюры',
			`posted` datetime default NULL COMMENT 'Дата и время добавления',
			`groupId` int(10) unsigned NOT NULL default '0' COMMENT 'Привязка к группе',
			`cover` tinyint(1) unsigned default '0' COMMENT 'Обложка альбома',
			`active` tinyint(1) unsigned default '0' COMMENT 'Активность',
			`position` int(10) unsigned NOT NULL default '0' COMMENT 'Порядковый номер',
			PRIMARY KEY  (`id`),
			KEY `section` (`section`),
			KEY `position` (`position`),
			KEY `active` (`active`),
			KEY `posted` (`posted`),
			KEY `findCovers` (`section`, `active`, `cover`),
			KEY `imagesByTime` (`section`, `active`, `posted`),
			KEY `imagesByPosition` (`section`, `active`, `position`)
			";
		$this->dbCreateTable($sql, 'images');

		/*
		 * Создаём таблицу групп
		 */
		$sql = "
			`id` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентифкатор',
			`section` int(10) unsigned default NULL COMMENT 'Привязка к разделу сайта',
			`title` varchar(255) default '' COMMENT 'Название группы',
			`description` text default '' COMMENT 'Описание',
			`position` int(10) unsigned NOT NULL default '0' COMMENT 'Порядковый номер',
			PRIMARY KEY  (`id`),
			KEY `list` (`section`, `position`)
		";
		$this->dbCreateTable($sql, 'groups');

		// Создаём директорию данных
		$this->mkdir();

		/* Создаём директорию шаблонов */
		$tmplDir = $Eresus->froot . 'templates/' . $this->name;
		$umask = umask(0000);
		@mkdir($tmplDir, 0777);
		umask($umask);

		/* Копируем шаблоны */
		$files = glob($this->dirCode . 'distrib/*.html');
		foreach ($files as $file)
		{
			$target = $tmplDir . '/' . basename($file);
			copy($file, $target);
			chmod($target, 0666);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Действия при удалении плагина
	 *
	 * Метод удалаяет файлы данных плагина.
	 *
	 * @return void
	 */
	public function uninstall()
	{
		global $Eresus;

		// Удаляем директорию данных
		$this->rmdir();

		/* Удаляем шаблоны */
		$tmplDir = $Eresus->froot . 'templates/' . $this->name;
		$files = glob($tmplDir . '/*');
		foreach ($files as $file)
		{
			filedelete($file);
		}

		/* Удаляем директорию шаблонов */
		@rmdir($tmplDir);

		parent::uninstall();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Формирование HTML-кода АИ
	 *
	 * @return string  HTML
	 */
	public function adminRenderContent()
	{
		global $page;

		switch (true)
		{
			/*
			 * Управление группами
			 * Этот блок должен располагаться до блока управления изображениями из-за того что иначе
			 * наличие аргумента id может быть ошибочно расценено как запрос на диалог изменения
			 * изображения.
			 */

			case arg('action') == 'group':
				$result = $this->adminRenderGroupsList();
			break;

			case arg('group_id') !== null:
				$result = $this->adminEditGroupDialog();
			break;

			case arg('action') == 'group_create':
				$result = $this->adminAddGroupDialog();
			break;

			case arg('action') == 'group_insert':
				$this->adminInsertGroup();
			break;

			case arg('action') == 'group_update':
				$this->adminUpdateGroup();
			break;

			case arg('group_up') !== null:
				$this->adminMoveUpGroup();
			break;

			case arg('group_down') !== null:
				$this->adminMoveDownGroup();
			break;

			case arg('group_delete') !== null:
				$this->adminDeleteGroup();
			break;

			/* Управление изображениями */

			case arg('action') == 'create':
				$result = $this->adminAddItem();
			break;

			case arg('action') == 'insert':
				$this->adminInsertImage();
			break;

			case arg('id') !== null:
				$result = $this->adminEditItem();
			break;

			case arg('update') !== null:
				$this->adminUpdateImage();
			break;

			case arg('toggle') !== null:
				$this->adminImageToggle(arg('toggle', 'int'));
			break;

			case arg('cover') !== null:
				$this->coverAction();
			break;

			case arg('delete') !== null:
				$this->delete(arg('delete', 'int'));
			break;

			case arg('up') !== null:
				$this->up(arg('up', 'int'));
			break;

			case arg('down') !== null:
				$this->down(arg('down', 'int'));
			break;

			/* Управление свойствами галереи */

			case arg('action') == 'props':
				$result = $this->adminRenderProperties();
			break;

			case arg('action') == 'props_update':
				$result = $this->updateProperties();
			break;

			/* Действие по умолачнию */

			default:
				$result = $this->adminRenderImagesList();
			break;
		}

		/*
		 * Кнопки-"вкладки"
		 */
		$tabs = array('width' => '14em', 'items' => array());
		$tabs['items'] []= array(
			'caption' => 'Изображения',
			'url' => preg_replace('/&(id|pg)=\d+/', '', $page->url(null, array('action'))),
		);

		if ($this->settings['useGroups'])
		{
			$tabs['items'] []= array(
				'caption' => 'Группы',
				'url' => preg_replace('/&(id|pg)=\d+/', '', $page->url(array('action' => 'group'))),
			);
		}

		$tabs['items'] []= array(
			'caption' => 'Свойства галереи',
			'url' => preg_replace('/&(id|pg)=\d+/', '', $page->url(array('action' => 'props'))),
		);

		$result =
			$page->renderTabs($tabs) .
			$result;

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обработчик XHR-запросов
	 *
	 * @return void
	 */
	public function adminRender()
	{
		$ctl = new GalleryAdminXHRController;
		$ctl->execute(arg('args'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Формирование контента
	 *
	 * @return string
	 */
	public function clientRenderContent()
	{
		global $page;

		$this->clientCheckRequest();

		$result = '';

		if ($page->topic)
		{
			$result = $this->clientRenderItem();
		}
		else
		{
			$result = $this->clientRenderList();
		}

		return $result;

	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает URL корня клиентского раздела
	 *
	 * @return string
	 */
	public function clientURL()
	{
		global $page;

		return $page->clientURL($page->id);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает URL текущей страницы списка
	 *
	 * @return string
	 */
	public function clientListURL()
	{
		global $page;

		$url = $this->clientURL();

		if ($page->subpage)
		{
			$url .= 'p' . $page->subpage . '/';
		}

		return $url;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отрисовывает интерфейс списка изображений
	 *
	 * @return string  HTML
	 */
	private function adminRenderImagesList()
	{
		global $page;

		// Определяем текущую страницу списка
		$pg = arg('pg') ? arg('pg', 'int') : 1;

		$section = arg('section', 'int');

		$maxCount = $this->settings['useGroups'] ?
			$this->settings['groupsPerPage'] :
			$this->settings['itemsPerPage'];

		$startFrom = ($pg - 1) * $maxCount;

		if ($this->settings['useGroups'])
		{
			$items = GalleryGroup::find($section, $maxCount, $startFrom);
		}
		else
		{
			$items = GalleryImage::find($section, $maxCount, $startFrom);
		}

		// Данные для подстановки в шаблон
		$data = array();
		$data['this'] = $this;
		$data['page'] = $page;
		$data['Eresus'] = $GLOBALS['Eresus'];
		$data['sectionId'] = arg('section', 'int');
		$data['items'] = $items;

		/* Шаблоны адресов действий */
		$data['urlEdit'] = str_replace('&', '&amp;', $page->url(array('id' => '%s')));
		$data['urlToggle'] = str_replace('&', '&amp;', $page->url(array('toggle' => '%s')));
		$data['urlCover'] = str_replace('&', '&amp;', $page->url(array('cover' => '%s')));
		$data['urlUp'] = str_replace('&', '&amp;', $page->url(array('up' => '%s')));
		$data['urlDown'] = str_replace('&', '&amp;', $page->url(array('down' => '%s')));
		$data['urlDelete'] = str_replace('&', '&amp;', $page->url(array('delete' => '%s')));

		if ($this->settings['useGroups'])
		{
			$totalPages = ceil(GalleryGroup::count($section) / $maxCount);
		}
		else
		{
			$totalPages = ceil(GalleryImage::count($section) / $maxCount);
		}

		if ($totalPages > 1)
		{
			$pager = new PaginationHelper($totalPages, $pg, $page->url(array('pg' => '%s')));
			$data['pager'] = $pager->render();
		}
		else
		{
			$data['pager'] = '';
		}

		$page->linkStyles($this->urlCode . 'admin.css');
		$page->linkScripts($this->urlCode . 'admin.js');

		/* Создаём экземпляр шаблона */
		if ($this->settings['useGroups'])
		{
			$tmpl = new Template('ext/' . $this->name . '/templates/image-grouped-list.html');
			// Изображения вне групп
			$data['orphans'] = GalleryImage::findOrphans($section);
		}
		else
		{
			$tmpl = new Template('ext/' . $this->name . '/templates/image-list.html');
		}

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог добавления изображения
	 *
	 * @return string  HTML
	 */
	private function adminAddItem()
	{
		global $page;

		$this->linkJQuery();
		$page->linkStyles($this->urlCode . 'admin.css');

		// Данные для подстановки в шаблон
		$data = array();
		$data['this'] = $this;
		$data['page'] = $page;
		$data['sectionId'] = arg('section', 'int');
		$data['defaultGroup'] = isset($_SESSION['gallery_default_group']) ?
			$_SESSION['gallery_default_group'] : null;

		if ($this->settings['useGroups'])
		{
			$data['groups'] = $this->dbSelect('groups', "`section` = " . arg('section', 'int'),
				'position');
		}

		// Создаём экземпляр шаблона
		$tmpl = new Template('ext/' . $this->name . '/templates/add-image.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог изменения изображения
	 *
	 * @return string  HTML
	 */
	private function adminEditItem()
	{
		global $page;

		$image = new GalleryImage(arg('id', 'int'));

		$page->linkStyles($this->urlCode . 'admin.css');
		$page->linkScripts($this->urlCode . 'admin.js');

		// Данные для подстановки в шаблон
		$data = array();
		$data['this'] = $this;
		$data['page'] = $page;
		$data['pg'] = arg('pg', 'int');
		$data['image'] = $image;
		$data['sections'] = $this->buildGalleryList(0);

		if ($this->settings['useGroups'])
		{
			$data['groups'] = $this->dbSelect('groups', "`section` = {$image->section}", 'position');
		}

		// Создаём экземпляр шаблона
		$tmpl = new Template('ext/' . $this->name . '/templates/edit-image.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет изображение
	 *
	 * @return void
	 */
	private function adminInsertImage()
	{
		if (empty($_FILES['image']['name']))
		{
			ErrorMessage(isset($form['message']) ? $form['message'] : 'Поле "файл" не заполнено');
			HTTP::goback();
		}

		$item = new GalleryImage();
		$item->section = arg('section');
		$item->group = arg('group');
		$_SESSION['gallery_default_group'] = arg('group');
		$item->title = arg('title');
		$item->cover = arg('cover');
		$item->active = arg('active');
		$item->posted = gettime();
		$item->image = 'image'; // $_FILES['image'];

		$item->save();

		$url = 'admin.php?mod=content&section=' . $item->section;
		if (arg('pg'))
		{
			$url .= '&pg=' . arg('pg', 'int');
		}
		HTTP::redirect($url);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обновляет изображение
	 *
	 * @return void
	 * @see adminEditItem
	 */
	private function adminUpdateImage()
	{
		global $page;

		$image = new GalleryImage(arg('update', 'int'));

		$new_section = arg('section', 'int');
		if ($new_section == $image->section)
		{
			$image->group = arg('group', 'int');
		}
		else
		{
			$image->section = $new_section;
			$image->group = arg('new_group', 'int');
		}
		$image->title = arg('title', 'dbsafe');
		$image->posted = arg('posted', 'dbsafe');
		$image->cover = arg('cover', 'int');
		$image->active = arg('active', 'int');
		$image->image = 'image'; // $_FILES['image'];

		$image->save();

		HTTP::redirect($page->url());
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отрисовывает интерфейс списка групп
	 *
	 * @return string  HTML
	 */
	private function adminRenderGroupsList()
	{
		global $page;

		/*
		 * Описание таблицы групп
		 */
		$table = array(
			'name' => $this->__table('groups'),
			'key'=> 'id',
			'sortMode' => 'position',
			'sortDesc' => false,
			'columns' => array(
				array('name' => 'title', 'caption' => 'Название'),
			),
			'condition' => "`section`='" . arg('section', 'int') . "'",
			'controls' => array(
				'delete' => '',
				'edit' => '',
				'position' => '',
			),
			'tabs' => array(
				'width' => '180px',
				'items' => array(
					array('caption' => 'Добавить группу', 'name' => 'action', 'value' => 'group_create'),
				),
			),
		);

		$result = $page->renderTable($table, null, 'group_');
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог добавления группы
	 *
	 * @return string  HTML
	 */
	private function adminAddGroupDialog()
	{
		global $page;

		$form = array(
			'name' => 'AddForm',
			'caption' => 'Добавление группы',
			'width' => '600px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'action', 'value' => 'group_insert'),
				array ('type' => 'hidden', 'name' => 'section', 'value' => arg('section', 'int')),
				array ('type' => 'edit', 'name' => 'title', 'label' => 'Название',
					'width' => '100%', 'maxlength' => '255'),
				array('type' => 'html', 'name' => 'description', 'height' => '400px',
					'label' => 'Описание'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог изменения группы
	 *
	 * @return string  HTML
	 */
	private function adminEditGroupDialog()
	{
		global $page;

		$item = $this->dbItem('groups', arg('group_id', 'int'));

		$form = array(
			'name' => 'editGroup',
			'caption' => 'Изменение группы',
			'width' => '600px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'action', 'value' => 'group_update'),
				array ('type' => 'hidden', 'name' => 'section', 'value' => arg('section', 'int')),
				array ('type' => 'hidden', 'name' => 'id', 'value' => $item['id']),
				array ('type' => 'edit', 'name' => 'title', 'label' => 'Название',
					'width' => '100%', 'maxlength' => '255'),
				array('type' => 'html', 'name' => 'description', 'height' => '400px',
					'label' => 'Описание'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);

		$result = $page->renderForm($form, $item);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет группу
	 *
	 * @return void
	 */
	private function adminInsertGroup()
	{
		$item = new GalleryGroup();
		$item->section = arg('section');
		$item->title = arg('title');
		$item->description = arg('description');

		// Код определения позиции желательно перенести в GalleryGroup
		$maxPosition = $this->dbSelect('groups', "`section` = '{$item->section}'", null,
			'MAX(`position`) AS `value`');
		$item->position = intval($maxPosition[0]['value']) + 1;

		$item->save();

		HTTP::redirect(arg('submitURL') . '&action=group');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обновляет группу
	 *
	 * @return void
	 */
	private function adminUpdateGroup()
	{
		$item = $this->dbItem('groups', arg('id', 'int'));

		$item['title'] = arg('title', 'dbsafe');
		$item['description'] = arg('description', 'dbsafe');

		$this->dbUpdate('groups', $item);

		$url = arg('submitURL');
		if (strpos($url, '=group') === false)
		{
			$url .= '&action=group';
		}

		HTTP::redirect($url);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещает группу вверх по списку
	 *
	 * @return void
	 */
	private function adminMoveUpGroup()
	{
		$group = new GalleryGroup(arg('group_up', 'int'));
		$group->moveUp();
		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещает группу вниз по списку
	 *
	 * @return void
	 */
	private function adminMoveDownGroup()
	{
		$group = new GalleryGroup(arg('group_down', 'int'));
		$group->moveDown();
		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет группу
	 *
	 * @return void
	 */
	private function adminDeleteGroup()
	{
		$id = arg('group_delete', 'int');
		$group = new GalleryGroup($id);
		$group->delete();

		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обновление свойств галереи
	 */
	private function updateProperties()
	{
		global $Eresus;

		$item = $Eresus->db->selectItem('pages', '`id` = "'.arg('section').'"');
		$item = GetArgs($item);

		$Eresus->db->updateItem('pages', $item, "`id` = '".$item['id']."'");
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаление записи
	 *
	 * @param int id Номер записи
	 */
	private function delete($id)
	{
		global $page;

		$image = new GalleryImage($id);
		$image->delete();

		HTTP::redirect($page->url());
	}
	//-----------------------------------------------------------------------------

	/**
	 * Проверяет правильность запроса
	 *
	 * В случае если запрос содержит лишние элементы, возврщает HTTP/404
	 *
	 * @return void
	 */
	private function clientCheckRequest()
	{
		global $Eresus, $page;

		/* Собираем вместе ожидаемые эелементы URL */
		$acceptUrl = $Eresus->request['path'] .
			($page->subpage !== 0 ? 'p' . $page->subpage . '/' : '');

		if ($page->topic)
		{
			$acceptUrl .= $page->topic !== false ? $page->topic . '/' : '';
		}

		/* Сравниваем с переданным URL */
		if ($acceptUrl != $Eresus->request['url'])
		{
			$page->httpError(404);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает контент для страницы списка изображений
	 *
	 * @return string  HTML
	 */
	private function clientRenderList()
	{
		global $page;

		if ($page->subpage == 0)
		{
			$page->subpage = 1;
		}

		$maxCount = $this->settings['itemsPerPage'];
		$startFrom = ($page->subpage - 1) * $maxCount;
		if ($this->settings['useGroups'])
		{
			$items = GalleryGroup::find($page->id, $maxCount, $startFrom);
		}
		else
		{
			$items = GalleryImage::find($page->id, $maxCount, $startFrom, true);
		}

		// Данные для подстановки в шаблон
		$data = array();
		$data['this'] = $this;
		$data['page'] = $page;
		$data['Eresus'] = $GLOBALS['Eresus'];
		$data['items'] = $items;

		/* Ищем обложку альбома */
		$data['cover'] = GalleryImage::findCover($page->id);

		if ($this->settings['useGroups'])
		{
			$totalPages = ceil(GalleryGroup::count($page->id) / $maxCount);
		}
		else
		{
			$totalPages = ceil(GalleryImage::count($page->id, true) / $maxCount);
		}

		if ($totalPages > 1)
		{
			$pager = new PaginationHelper($totalPages, $page->subpage);
			$data['pager'] = $pager->render();
		}
		else
		{
			$data['pager'] = '';
		}

		/* Создаём экземпляр шаблона */
		if ($this->settings['useGroups'])
		{
			$tmpl = new Template('templates/' . $this->name . '/image-grouped-list.html');
		}
		else
		{
			$tmpl = new Template('templates/' . $this->name . '/image-list.html');
		}

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		if ($this->settings['showItemMode'] == 'popup')
		{
			$this->linkJQuery();
			$page->linkScripts($this->urlCode . 'lightbox/jquery.lightbox-0.5.pack.js');
			$page->linkStyles($this->urlCode . 'lightbox/jquery.lightbox-0.5.css');
			$page->linkScripts($this->urlCode . 'gallery.js');
		}

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отрисовка представления "Просмотр изображения"
	 *
	 * @return string  HTML
	 */
	private function clientRenderItem()
	{
		global $page;

		$id = intval($page->topic);

		if ($id != $page->topic)
		{
			$page->httpError(404);
		}

		$image = new GalleryImage($id);

		if (!$image || !$image->active)
		{
			$page->HttpError(404);
		}

		// Данные для подстановки в шаблон
		$data = array();
		$data['this'] = $this;
		$data['page'] = $page;
		$data['Eresus'] = $GLOBALS['Eresus'];
		$data['image'] = $image;
		$data['images'] = GalleryImage::find($page->id, null, null, true);

		// Создаём экземпляр шаблона
		$tmpl = new Template('templates/' . $this->name . '/image.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Создаёт список разделов типа "Галерея изображений" для использования в шаблонах
	 *
	 * @param int $root             Идентификатор корневого раздела
	 * @param int $level[optional]  Уровень вложенности ветки
	 *
	 * @return array  Описания разделов
	 */
	private function buildGalleryList($root, $level = 0)
	{
		useLib('sections');
		$lib = new Sections();

		$sections = $lib->children($root);

		// Описания разделов выбранной ветки
		$branch = array();

		/*
		 * Отбираем только разделы типа "Галерея изображений" или разделы,
		 * содержащие дочерние разделы этого же типа.
		 */
		foreach ($sections as $section)
		{
			$item = array(
				'id' => $section['id'],
				'selectable' => false,
				'caption' => $section['caption'],
				'level' => $level
			);

			if ($section['type'] == $this->name)
			{
				$item['selectable'] = true;
			}

			$subitems = $this->buildGalleryList($section['id'], $level + 1);

			if ($item['selectable'] || $subitems)
			{
				$branch []= $item;
			}

			$branch = array_merge($branch, $subitems);
		}

		return $branch;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог свойств галереи
	 *
	 * @return string  HTML
	 */
	private function adminRenderProperties()
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem('pages', '`id`="' . arg('section', 'int') . '"');

		$form = array(
			'name' => 'contentEditor',
			'caption' => 'Текст на странице',
			'width' => '800px',
			'fields' => array(
				array('type' => 'hidden', 'name' => 'action', 'value' => 'props_update'),
				array('type' => 'hidden', 'name' => 'textupdate', 'value' => '1'),
				array('type' => 'edit', 'name' => 'title', 'label' => 'Название', 'width' => '150px'),
				array('type' => 'edit', 'name' => 'created', 'label' => 'Дата создания',
					'width' => '150px', 'comment' => 'ГГГГ-ММ-ДД ЧЧ:ММ:СС'),
				array('type' => 'checkbox', 'name' => 'active', 'label' => 'Активна'),
				array('type' => 'html', 'name' => 'content', 'height' => '400px', 'label' => 'Описание'),
			),
			'buttons'=> array('ok', 'apply', 'cancel'),
		);

		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещение изображения вверх по списку
	 *
	 * @param int $id  Идентификатор изображения
	 *
	 * @return void
	 */
	private function up($id)
	{
		$image = new GalleryImage($id);
		$image->moveUp();
		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещение изображения вниз по списку
	 *
	 * @param int $id  Идентификатор изображения
	 *
	 * @return void
	 */
	private function down($id)
	{
		$image = new GalleryImage($id);
		$image->moveDown();
		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Включает или отключает изображение
	 *
	 * @param int $id  Идентификатор изображения
	 */
	private function adminImageToggle($id)
	{
		$image = new GalleryImage($id);
		$image->active = ! $image->active;
		$image->save();

		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Делает указанное в запросе Изображение обложкой альбома
	 *
	 * @return void
	 */
	private function coverAction()
	{
		$id = arg('cover', 'int');

		$image = new GalleryImage($id);
		$image->cover = true;
		$image->save();

		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Подключает библиотеку jQuery
	 *
	 * @return void
	 */
	private function linkJQuery()
	{
		global $Eresus, $page;

		$page->linkScripts($Eresus->root . 'core/jquery/jquery.min.js');
	}
	//-----------------------------------------------------------------------------

}

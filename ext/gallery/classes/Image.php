<?php
/**
 * Галерея изображений
 *
 * Изображение
 *
 * @version 2.00
 *
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author Михаил Красильников <mk@3wstyle.ru>
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
 * @package Gallery
 *
 * $Id: Image.php 970 2010-09-29 11:17:35Z mk $
 */


/**
 * Изображение
 *
 * @property       int               $id
 * @property       int               $section      Идентификатор раздела
 * @property       string            $title        Название изображения
 * @property-read  string            $image        Имя файла картинки
 * @property-write array             $image        Элемент $_FILES соответсвующий загружаемому файлу
 * @property       string            $thumb        Имя файла миниатюры
 * @property       string            $posted       Дата и время добавления изображения
 * @property-read  GalleryGroup      $group        Группа изображения
 * @property-write int               $group        Идентификатор группы
 * @property       bool              $cover        Является ли обложкой альбома
 * @property       bool              $active       Является ли активным
 * @property       int               $position     Порядковый номер
 * @property-read  string            $thumbURL     URL файла миниатюры
 * @property-read  string            $imageURL     URL файла изображения
 * @property-read  string            $showURL      URL или JavaScript для показа картинки
 * @property-read  GalleryImage      $nextSibling  Модель следующего по порядку Изображения.
 *                                                 Для КИ - следующего активного Изображения.
 * @property-read  GalleryImage      $prevSibling  Модель предыдущего по порядку Изображения.
 *                                                 Для КИ - предыдущего активного Изображения.
 *
 * @package Gallery
 */
class GalleryImage extends GalleryAbstractActiveRecord
{
	/**
	 * Список поддерживаемых форматов
	 * @var array
	 */
	private $supportedFormats = array(
		'image/jpeg',
		'image/jpg',
		'image/pjpeg',
		'image/png',
		'image/gif',
	);

	/**
	 * Кэш геттеров
	 *
	 * @var array
	 */
	private $gettersCache = array();

	/**
	 * Описание файла для загрузки
	 *
	 * @var array
	 */
	private $upload;

	/**
	 * Указывает на то что это изображение при сохранении надо сделать обложкой
	 *
	 * @var bool
	 */
	private $setAsCover = false;

	/**
	 * Сохраняет исходное состояние свойства $section
	 *
	 * @var int
	 */
	private $origSection = null;

	/**
	 * Сохраняет исходное состояние свойства $cover
	 *
	 * @var bool
	 */
	private $origCover = null;

	/**
	 * Сохраняет исходное состояние свойства $active
	 *
	 * @var bool
	 */
	private $origActive = null;

	/**
	 * Возвращает имя таблицы БД
	 *
	 * @return string  Имя таблицы БД
	 *
	 * @since 2.00
	 */
	public function getTableName()
	{
		return 'images';
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает список полей записи и их атрибуты
	 *
	 * @return array
	 *
	 * @since 2.00
	 */
	public function getAttrs()
	{
		return array(
			'id' => array('type' => PDO::PARAM_INT),
			'section' => array('type' => PDO::PARAM_INT),
			'title' => array('type' => PDO::PARAM_STR, 'maxlength' => 255),
			'image' => array('type' => PDO::PARAM_STR, 'maxlength' => 128),
			'thumb' => array('type' => PDO::PARAM_STR, 'maxlength' => 128),
			'posted' => array('type' => PDO::PARAM_STR),
			'groupId' => array('type' => PDO::PARAM_INT),
			'cover' => array('type' => PDO::PARAM_BOOL),
			'active' => array('type' => PDO::PARAM_BOOL),
			'position' => array('type' => PDO::PARAM_INT)
		);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сохраняет изменения в БД
	 *
	 * @return void
	 *
	 * @uses serveUpload
	 * @since 2.00
	 */
	public function save()
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');

		$this->serveCoverChanges();

		if ($this->isNew())
		{
			/* Вычисляем порядковый номер */
			$q = DB::getHandler()->createSelectQuery();
			$e = $q->expr;
			$q->select($q->alias($e->max('position'), 'maxval'))
				->from($this->getDbTable())
				->where($e->eq('section', $q->bindValue($this->section, null, PDO::PARAM_INT)));
			$result = DB::fetch($q);
			$this->position = $result['maxval'] + 1;
		}

		// Запоминаем состояние isNew, потому что флаг будет сброшен в parent::save()
		$wasNew = $this->isNew();
		// Записываем в БД чтобы получить идентификатор для использования в имени файла
		parent::save();

		if ($this->upload)
		{
			try
			{
				$this->serveUpload();
			}
			catch (EresusRuntimeException $e)
			{
				if ($wasNew)
				{
					$this->delete();
				}
				throw $e;
			}
		}

		/* Сохраняем значения для сравнения при сохранении */
		$this->origSection = $this->getProperty('section');
		$this->origCover = $this->getProperty('cover');
		$this->origActive = $this->getProperty('active');

	}
	//-----------------------------------------------------------------------------

	/**
	 * (non-PHPdoc)
	 * @see src/gallery/classes/GalleryAbstractActiveRecord::delete()
	 */
	public function delete()
	{
		$filename = self::plugin()->getDataDir() . $this->image;

		if (is_file($filename))
		{
			@$result = unlink($filename);
			if (!$result)
			{
				ErrorMessage("Can not delete file $filename");
			}
		}

		$filename = self::plugin()->getDataDir() . $this->thumb;

		if (is_file($filename))
		{
			@$result = unlink($filename);
			if (!$result)
			{
				ErrorMessage("Can not delete file $filename");
			}
		}

		/*
		 * Если удаляется изображение-обложка, то обложкой должно стать другое активное
		 * изображение в том же разделе.
		 */
		if ($this->cover == true)
		{
			self::autoSetCover($this->section);
		}

		parent::delete();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Считает количество изображений в разделе
	 *
	 * @param int  $section               Идентификатор раздела
	 * @param bool $activeOnly[optional]  Считать только активные или все
	 *
	 * @return int
	 *
	 * @since 2.00
	 */
	public static function count($section, $activeOnly = false)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%d, %d)', $section, $activeOnly);

		$q = DB::getHandler()->createSelectQuery();
		$q->select('count(DISTINCT id) as `count`')
			->from(self::getDbTableStatic(__CLASS__));

		$e = $q->expr;
		$condition = $e->eq('section', $q->bindValue($section, null, PDO::PARAM_INT));
		if ($activeOnly)
		{
			$condition = $e->lAnd(
				$condition,
				$e->eq('active', $q->bindValue(true, null, PDO::PARAM_BOOL))
			);
		}

		$q->where($condition);

		$result = DB::fetch($q);
		return $result['count'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Ищёт обложку для указанного раздела
	 *
	 * @param int $section
	 *
	 * @return GalleryImage|false  Возвращает изображение или FALSE, если обложка отсутствует
	 *
	 * @since 2.00
	 */
	public static function findCover($section)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');

		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;
		$q->select('*')->from(self::getDbTableStatic(__CLASS__))
			->where($e->lAnd(
				$e->eq('section', $q->bindValue($section, null, PDO::PARAM_INT)),
				$e->eq('active', $q->bindValue(true, null, PDO::PARAM_BOOL)),
				$e->eq('cover', $q->bindValue(true, null, PDO::PARAM_BOOL))
			));

		$raw = DB::fetch($q);
		if ($raw == false)
		{
			return false;
		}

		$image = new GalleryImage();
		$image->loadFromArray($raw);
		return $image;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбирает изображения из БД
	 *
	 * @param int|GalleryGroup $owner                 Идентификатор раздела или группа
	 * @param int              $limit[optional]       Вернуть не более $limit изображений
	 * @param int              $offset[optional]      Пропустить $offset первых изображений
	 * @param bool             $activeOnly[optional]  Искать только активные изображения
	 *
	 * @return array(GalleryImage)
	 *
	 * @since 2.00
	 */
	public static function find($owner, $limit = null, $offset = null, $activeOnly = false)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%s, %s, %s, %s)', $owner, $limit, $offset, $activeOnly);

		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;

		/* Строим условие выборки */
		if ($owner instanceof GalleryGroup)
		{
			$cond = $e->eq('groupId', $q->bindValue($owner->id, null, PDO::PARAM_INT));
		}
		else
		{
			$cond = $e->eq('section', $q->bindValue($owner, null, PDO::PARAM_INT));
		}

		if ($activeOnly)
		{
			$cond = $e->lAnd($cond, $e->eq('active', $q->bindValue(true, null, PDO::PARAM_BOOL)));
		}

		if (self::plugin()->settings['useGroups'])
		{
			// Отбираем только изображения, привязанные к группам
			$cond = $e->lAnd($cond, $e->neq('groupId', 0));
		}

		$q->where($cond);

		$result = self::load($q, $limit, $offset);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбирает изображения вне групп
	 *
	 * @param int  $section               Идентификатор раздела
	 *
	 * @return array(GalleryImage)
	 *
	 * @since 2.00
	 */
	public static function findOrphans($section)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%d)', $section);

		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;
		$q->where($e->lAnd(
			$e->eq('section', $q->bindValue($section, null, PDO::PARAM_INT)),
			$e->eq('groupId', 0)
		));

		$result = self::load($q);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сеттер свойства $image
	 *
	 * @param string $value
	 * //param array $value
	 */
	protected function setImage($value)
	{
		$this->upload = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $group
	 *
	 * @return GalleryGroup
	 */
	protected function getGroup()
	{
		return new GalleryGroup($this->getProperty('groupId'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сеттер свойства $group
	 *
	 * @param int $value
	 */
	protected function setGroup($value)
	{
		$this->groupId = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сеттер свойства $cover
	 *
	 * @param int $value
	 */
	protected function setCover($value)
	{
		$this->setProperty('cover', $value);
		$this->setAsCover = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $thumbnailURL
	 *
	 * @return string
	 */
	protected function getThumbURL()
	{
		return self::plugin()->getDataURL() . $this->thumb;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $imageURL
	 *
	 * @return string
	 */
	protected function getImageURL()
	{
		return self::plugin()->getDataURL() . $this->image;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $showURL
	 *
	 * @return string
	 */
	protected function getShowURL()
	{
		if (self::plugin()->settings['showItemMode'] == 'popup')
		{
			$url = $this->imageURL . '#gallery-popup';
		}
		else
		{
			$url = self::plugin()->clientListURL() . $this->id . '/';
		}

		return $url;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $nextSibling
	 *
	 * @return GalleryImage
	 */
	protected function getNextSibling()
	{
		if (!isset($this->gettersCache['nextSibling']))
		{
			$q = DB::getHandler()->createSelectQuery();
			$e = $q->expr;

			$q->select('*')
				->from($this->getDbTable())
				->limit(1);

			$where = $e->eq('section', $q->bindValue($this->section, null, PDO::PARAM_INT));

			if ($GLOBALS['page'] instanceof TClientUI)
			{
				$where = $e->lAnd($where, $e->eq('active', $q->bindValue(true, null, PDO::PARAM_INT)));
			}

			switch (self::plugin()->settings['sort'])
			{
				case 'date_asc':
					$where = $e->lAnd($where, $e->gt('posted', $q->bindValue($this->posted)));
				break;

				case 'date_desc':
					$where = $e->lAnd($where, $e->lt('posted', $q->bindValue($this->posted)));
				break;

				case 'manual':
					$where = $e->lAnd($where,
						$e->gt('position', $q->bindValue($this->position, null, PDO::PARAM_INT)));
				break;
			}

			$q->where($where);
			self::setOrderBy($q);

			$raw = DB::fetch($q);

			if ($raw)
			{
				$image = new GalleryImage();
				$image->loadFromArray($raw);
				$this->gettersCache['nextSibling'] = $image;
			}
			else
			{
				$this->gettersCache['nextSibling'] = null;
			}
		}
		return $this->gettersCache['nextSibling'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $prevSibling
	 *
	 * @return GalleryImage
	 */
	protected function getPrevSibling()
	{
		if (!isset($this->gettersCache['prevSibling']))
		{
			$q = DB::getHandler()->createSelectQuery();
			$e = $q->expr;

			$q->select('*')
				->from($this->getDbTable())
				->limit(1);

			$where = $e->eq('section', $q->bindValue($this->section, null, PDO::PARAM_INT));

			if ($GLOBALS['page'] instanceof TClientUI)
			{
				$where = $e->lAnd($where, $e->eq('active', $q->bindValue(true, null, PDO::PARAM_INT)));
			}

			switch (self::plugin()->settings['sort'])
			{
				case 'date_asc':
					$where = $e->lAnd($where, $e->lt('posted', $q->bindValue($this->posted)));
					$q->orderBy('posted', ezcQuerySelect::DESC);
				break;

				case 'date_desc':
					$where = $e->lAnd($where, $e->gt('posted', $q->bindValue($this->posted)));
					$q->orderBy('posted');
				break;

				case 'manual':
					$where = $e->lAnd($where,
						$e->lt('position', $q->bindValue($this->position, null, PDO::PARAM_INT)));
					$q->orderBy('position', ezcQuerySelect::DESC);
				break;
			}

			$q->where($where);
			$raw = DB::fetch($q);

			if ($raw)
			{
				$image = new GalleryImage();
				$image->loadFromArray($raw);
				$this->gettersCache['prevSibling'] = $image;
			}
			else
			{
				$this->gettersCache['prevSibling'] = null;
			}
		}
		return $this->gettersCache['prevSibling'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * (non-PHPdoc)
	 * @see src/gallery/classes/GalleryAbstractActiveRecord::loadById()
	 */
	protected function loadById($id)
	{
		parent::loadById($id);

		/* Сохраняем значения для сравнения при сохранении */
		$this->origSection = $this->getProperty('section');
		$this->origCover = $this->getProperty('cover');
		$this->origActive = $this->getProperty('active');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает в запросе порядок сортировки
	 *
	 * @param ezcQuerySelect $query
	 * @return void
	 */
	private static function setOrderBy($query)
	{
		switch (self::plugin()->settings['sort'])
		{
			case 'date_asc':
				$query->orderBy('posted', ezcQuerySelect::DESC);
			break;

			case 'date_desc':
				$query->orderBy('posted', ezcQuerySelect::ASC);
			break;

			case 'manual':
				$query->orderBy('position');
			break;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбирает изображения из БД
	 *
	 * @param string $condition         Условие выборки
	 * @param int    $limit[optional]   Вернуть не более $limit изображений
	 * @param int    $offset[optional]  Пропустить $offset первых изображений
	 *
	 * @return array(GalleryImage)
	 *
	 * @since 2.00
	 */
	private static function load($query, $limit = null, $offset = null)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '("%s", %s, %s)', $query, $limit, $offset);

		$query->select('*')->from(self::getDbTableStatic(__CLASS__));

		self::setOrderBy($query);

		if ($limit !== null)
		{
			if ($offset !== null)
			{
				$query->limit($limit, $offset);
			}
			else
			{
				$query->limit($limit);
			}

		}

		$raw = DB::fetchAll($query);
		$result = array();
		if (count($raw))
		{
			foreach ($raw as $item)
			{
				$image = new GalleryImage();
				$image->loadFromArray($item);
				$result []= $image;
			}
		}

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сбрасывает флаг "Обложка альбома" у всех изображений в указанном разделе
	 *
	 * @param int $section
	 * @return void
	 *
	 * @since 2.00
	 */
	private static function clearCovers($section)
	{
		$q = DB::getHandler()->createUpdateQuery();
		$q->update(self::getDbTableStatic(__CLASS__))
			->set('cover', $q->bindValue(false, null, PDO::PARAM_BOOL))
			->where($q->expr->eq('section', $q->bindValue($section, null, PDO::PARAM_INT)));
		DB::execute($q);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Автоматически выбирает изображение для обложки в указанном разделе
	 *
	 * @param int  $section  Идентификатор раздела
	 *
	 * @return void
	 *
	 * @since 2.00
	 */
	private static function autoSetCover($section)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%d)', $section);

		$table = self::getDbTableStatic(__CLASS__);
		/*
		 * ez Components не поддерживает LIMIT в запросах UPDATE, так что делаем два запроса
		 */
		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;
		$q->select('id')->from($table)
			->where($e->lAnd(
				$e->eq('section', $q->bindValue($section, null, PDO::PARAM_INT)),
				$e->eq('active', $q->bindValue(true, null, PDO::PARAM_BOOL)),
				$e->neq('cover', $q->bindValue(true, null, PDO::PARAM_BOOL))
			))
			->limit(1);

		self::setOrderBy($q);

		try
		{
			$tmp = DB::fetch($q);
		}
		catch (DBQueryException $e)
		{
			// Нет такой записи. Ничего делать не надо
			return;
		}

		$q = DB::getHandler()->createUpdateQuery();
		$e = $q->expr;
		$q->update($table)->set('cover', true)->
			where($e->eq('id', $q->bindValue($tmp['id'], null, PDO::PARAM_INT)));

		DB::execute($q);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обрабатывает изменения в обложках
	 *
	 * @return void
	 *
	 * @since 2.00
	 */
	private function serveCoverChanges()
	{
		/* При смене раздела флаг "Обложка" должен быть сброшен */
		if ($this->section != $this->origSection && $this->cover)
		{
			$this->cover = false;
			self::autoSetCover($this->origSection);
		}

		/* Если это единственное изображение в разделе, делаем его обложкой */
		if (self::count($this->section) == 0)
		{
			$this->cover = true;
			// Нам не надо сбрасывать флаг у других изображений раздела, потому что их нет
			$this->setAsCover = false;
		}

		if ($this->setAsCover)
		{
			self::clearCovers($this->section);
			$this->setAsCover = false;
		}

		/*
		 * Если отключается изображение-обложка, либо сбрасывается флаг "Обложка", то обложкой должно
		 * стать другое активное изображение в том же разделе.
		 */
		$isDisablingActiveCover = $this->cover == true && $this->active == false &&
			$this->origActive == true;

		$isDroppingCoverFlag = $this->cover == false && $this->origCover == true;

		if ($isDisablingActiveCover || $isDroppingCoverFlag)
		{
			self::autoSetCover($this->section);
			$this->cover = false;
		}

		/*
		 * Если включается изображение НЕ-обложка, то проверяем, не надо ли сделать его обложкой.
		 */
		if ($this->cover == false && $this->active == true && $this->origActive == false)
		{
			$tmp = self::findCover($this->section);
			if ($tmp == false)
			{
				$this->cover = true;
			}
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обслуживает загрузку изображения
	 *
	 * @return void
	 *
	 * @throws GalleryUnsupportedFormatException
	 * @throws GalleryUploadException
	 * @since 2.00
	 */
	private function serveUpload()
	{
		$fileInfo = $_FILES[$this->upload];
		if ($fileInfo['error'] == UPLOAD_ERR_NO_FILE)
		{
			return false;
		}

		$ext = '.' . strtolower(substr(strrchr($fileInfo['name'], '.'), 1));

		if (!in_array($fileInfo['type'], $this->supportedFormats))
		{
			throw new GalleryUnsupportedFormatException($fileInfo['type']);
		}

		$imageFileName = self::plugin()->getDataDir() . $this->id . $ext;
		if (!upload($this->upload, $imageFileName))
		{
			throw new GalleryUploadException();
		}

		useLib('glib');
		$this->setProperty('image', $this->id . $ext);
		$this->setProperty('thumb', $this->id . '-thmb.jpg');

		/*
		 * Если изображение слишком больше - уменьшаем
		 */
		$info = @getimagesize($imageFileName);
		if (
			$info[0] > self::plugin()->settings['imageWidth'] ||
			$info[1] > self::plugin()->settings['imageHeight']
		)
		{
			$oldName = $this->image;
			$this->setProperty('image', $this->id . '.jpg');
			thumbnail(
				self::plugin()->getDataDir() . $oldName,
				self::plugin()->getDataDir() . $this->image,
				self::plugin()->settings['imageWidth'],
				self::plugin()->settings['imageHeight']
			);
			if ($oldName != $this->image)
			{
				filedelete(self::plugin()->getDataDir() . $oldName);
			}
		}

		if (self::plugin()->settings['logoEnable'])
		{
			$this->overlayLogo(self::plugin()->getDataDir() . $this->image);
		}

		thumbnail(
			self::plugin()->getDataDir() . $this->image,
			self::plugin()->getDataDir() . $this->thumb,
			self::plugin()->settings['thumbWidth'],
			self::plugin()->settings['thumbHeight']
		);

		$this->upload = null;

		parent::save();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Накладывает логотип
	 *
	 * @param string $file
	 *
	 * @return void
	 */
	private function overlayLogo($file)
	{
		$logoFile = self::plugin()->getDataDir() . 'logo.png';
		if (!is_file($logoFile))
		{
			return;
		}

		$src = imageCreateFromFile($file);
		imagealphablending($src, true);
		$logo = imageCreateFromPNG($logoFile);
		imagealphablending($logo, true);

		$settings = self::plugin()->settings;

		if ($logo)
		{
			$sw = imageSX($src);
			$sh = imageSY($src);
			$lw = imageSX($logo);
			$lh = imageSY($logo);

			switch ($settings['logoPosition'])
			{
				case 'TL':
					$x = $settings['logoHPadding'];
					$y = $settings['logoVPadding'];
				break;
				case 'TR':
					$x = $sw - $lw - $settings['logoHPadding'];
					$y = $settings['logoVPadding'];
				break;
				case 'BL':
					$x = $settings['logoHPadding'];
					$y = $sh - $lh - $settings['logoVPadding'];
				break;
				case 'BR':
					$x = $sw - $lw - $settings['logoHPadding'];
					$y = $sh - $lh - $settings['logoVPadding'];
				break;
			}
			imagesavealpha($src, true);
			imagecopy ($src, $logo, $x, $y, 0, 0, $lw, $lh);
			imagesavealpha($src, true);
			imageSaveToFile($src, $file, IMG_JPG);
			imageDestroy($logo);
			imageDestroy($src);
		}
	}
	//-----------------------------------------------------------------------------
}

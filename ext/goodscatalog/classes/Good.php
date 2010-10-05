<?php
/**
 * Каталог товаров
 *
 * ActiveRecord товара
 *
 * @version 1.00
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
 * @package GoodsCatalog
 *
 * $Id: Good.php 984 2010-09-30 11:30:38Z mk $
 */


/**
 * ActiveRecord товара
 *
 * @property       int                      $id           Идентификатор
 * @property       int                      $section      Идентификатор раздела сайта
 * @property       bool                     $active       Активность товара
 * @property       int                      $position     Порядковый номер
 * @property       string                   $article      Артикул
 * @property       string                   $title        Название
 * @property       string                   $about        Краткое описание
 * @property       string                   $description  Описание
 * @property       float                    $cost         Цена
 * @property-read  string                   $photoPath    Путь к основной фотографии
 * @property-read  string                   $photoURL     URL основной фотографии
 * @property-write string                   $photo        Свойство для загрузки основной фотографии
 * @property-read  string                   $thumbPath    Путь к миниатюре
 * @property-read  string                   $thumbURL     URL иниатюры
 * @property       bool                     $special      Спецпредложение
 * @property-read  GoodsCatalogBrand        $brand        Бренд или null
 * @property-write GoodsCatalogBrand|int    $brand        Бренд или его идентификатор
 * @property-read  array(GoodsCatalogPhoto) $photos       Дополнительные фотографии
 * @property-read  string                   $clientURL    URL страницы товара в КИ
 *
 * @package GoodsCatalog
 */
class GoodsCatalogGood extends GoodsCatalogAbstractActiveRecord
{
	/**
	 * Свойство для отслеживания изменения раздела
	 *
	 * @var int
	 */
	private $originalSection = null;

	/**
	 * Метод возвращает имя таблицы БД
	 *
	 * @return string  Имя таблицы БД
	 *
	 * @since 1.00
	 */
	public function getTableName()
	{
		return 'goods';
	}
	//-----------------------------------------------------------------------------

	/**
	 * Метод возвращает список полей записи и их атрибуты
	 *
	 * @return array
	 *
	 * @since 1.00
	 */
	public function getAttrs()
	{
		return array(
			'id' => array(
				'type' => PDO::PARAM_INT,
			),
			'section' => array(
				'type' => PDO::PARAM_INT,
			),
			'active' => array(
				'type' => PDO::PARAM_BOOL,
			),
			'position' => array(
				'type' => PDO::PARAM_INT,
			),
			'article' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 255,
			),
			'title' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 255,
			),
			'about' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 65535,
			),
			'description' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 4294967295,
			),
			'cost' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 10,
			),
			'ext' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 4,
			),
			'special' => array(
				'type' => PDO::PARAM_BOOL,
			),
			'brand' => array(
				'type' => PDO::PARAM_INT,
			),
			);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сохраняет изменения в БД
	 *
	 * @return void
	 *
	 * @uses serveUpload
	 * @since 1.00
	 */
	public function save()
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');

		if ($this->isNew() || ! is_null($this->originalSection))
		{
			$this->autoPosition();
		}

		parent::save();
	}
	//-----------------------------------------------------------------------------

	/**
	 * (non-PHPdoc)
	 * @see src/goodscatalog/classes/GoodsCatalogAbstractActiveRecord::delete()
	 */
	public function delete()
	{
		foreach ($this->photos as $photo)
		{
			$photo->delete();
		}

		if (is_file($this->photoPath))
		{
			@$result = unlink($this->photoPath);
			if (!$result)
			{
				ErrorMessage("Can not delete file {$this->photoPath}");
			}
		}

		if (is_file($this->thumbPath))
		{
			@$result = unlink($this->thumbPath);
			if (!$result)
			{
				ErrorMessage("Can not delete file {$this->thumbPath}");
			}
		}

		rmdir(dirname($this->photoPath));

		parent::delete();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Считает количество брендов
	 *
	 * @param int  $section               Идентификатор раздела
	 * @param bool $activeOnly[optional]  Считать только активные или все
	 *
	 * @return int
	 *
	 * @since 1.00
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
	 * Выбирает бренды из БД
	 *
	 * @param int  $section               Идентификатор раздела
	 * @param int  $limit[optional]       Вернуть не более $limit брендов
	 * @param int  $offset[optional]      Пропустить $offset первых брендов
	 * @param bool $activeOnly[optional]  Искать только активные бренды
	 *
	 * @return array(GoodsCatalogBrand)
	 *
	 * @since 1.00
	 */
	public static function find($section, $limit = null, $offset = null, $activeOnly = false)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%d, %d, %d, %d)', $section, $limit, $offset, $activeOnly);

		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;

		$where = $e->eq('section', $q->bindValue($section, null, PDO::PARAM_INT));
		if ($activeOnly)
		{
			$where = $e->lAnd($where, $e->eq('active', $q->bindValue(true, null, PDO::PARAM_BOOL)));
		}
		$q->where($where);

		$result = self::load($q, $limit, $offset);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сеттер свойства $photo
	 *
	 * @param string $value
	 * //param array $value
	 */
	protected function setPhoto($value)
	{
		$this->upload = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $photoPath
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getPhotoPath()
	{
		if ($this->ext)
		{
			return self::plugin()->getDataDir() . 'goods/' . $this->id . '/main.' . $this->ext;
		}
		else
		{
			return null;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $photoURL
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getPhotoURL()
	{
		if ($this->ext)
		{
			return self::plugin()->getDataURL() . 'goods/' . $this->id . '/main.' . $this->ext;
		}
		else
		{
			return null;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $thumbPath
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getThumbPath()
	{
		if ($this->ext)
		{
			return self::plugin()->getDataDir() . 'goods/' . $this->id . '/main-thmb.jpg';
		}
		else
		{
			return null;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $thumbURL
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getThumbURL()
	{
		if ($this->ext)
		{
			return self::plugin()->getDataURL() . 'goods/' . $this->id . '/main-thmb.jpg';
		}
		else
		{
			return null;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $brand
	 *
	 * @return GoodsCatalogBrand
	 *
	 * @since 1.00
	 */
	protected function getBrand()
	{
		try {
			$brand = new GoodsCatalogBrand($this->getProperty('brand'));
		}
		catch (DomainException $e)
		{
			return null;
			$e = $e; // PHPMD hack
		}

		return $brand;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сеттер свойства $brand
	 *
	 * @param int|GoodsCatalogBrand $value
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function setBrand($value)
	{
		if ($value instanceof GoodsCatalogBrand)
		{
			$value = $value->id;
		}
		$this->setProperty('brand', intval($value));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сеттер свойства $section
	 *
	 * @param int $value
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function setSection($value)
	{
		if (is_null($this->originalSection) || $value != $this->section	)
		{
			$this->originalSection = $this->section;
		}
		$this->setProperty('section', $value);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $photos
	 *
	 * @return array
	 *
	 * @since 1.00
	 */
	protected function getPhotos()
	{
		return GoodsCatalogPhoto::find($this->id);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $clientURL
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getClientURL()
	{
		$page = $GLOBALS['page'];

		$url = $page->clientURL($this->section);
		if ($page instanceof TClientUI && $page->subpage)
		{
			$url .= 'p' . $page->subpage . '/';
		}

		return  $url . $this->id . '/';
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбирает товары из БД
	 *
	 * @param ezcQuerySelect $query             Запрос
	 * @param int            $limit[optional]   Вернуть не более $limit брендов
	 * @param int            $offset[optional]  Пропустить $offset первых брендов
	 *
	 * @return array(GoodsCatalogBrand)
	 *
	 * @since 1.00
	 */
	private static function load($query, $limit = null, $offset = null)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '("%s", %d, %d)', $query, $limit, $offset);

		$query->select('*')->from(self::getDbTableStatic(__CLASS__))
			->orderBy('position');

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
			foreach ($raw as $array)
			{
				$item = new self();
				$item->loadFromArray($array);
				$result []= $item;
			}
		}

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обслуживает загрузку изображения
	 *
	 * @return void
	 *
	 * @throws EresusRuntimeException Если формат файла не поддерживается
	 * @throws EresusFsRuntimeException Если загрузка не удалась
	 * @since 1.07
	 */
	protected function serveUpload()
	{
		if (!$this->fileUploaded())
		{
			return;
		}
		$fileInfo = $_FILES[$this->upload];

		$this->ext = strtolower(substr(strrchr($fileInfo['name'], '.'), 1));

		$this->checkFormat($fileInfo['type']);

		$dirCreated = self::plugin()->mkdir('goods/' . $this->id);

		if (!$dirCreated)
		{
			throw new EresusFsRuntimeException('Can not create directory.');
		}

		if (!upload($this->upload, $this->photoPath))
		{
			throw new EresusFsRuntimeException('Upload failed.');
		}

		useLib('glib');

		/*
		 * Если изображение слишком больше - уменьшаем
		 */
		@$info = getimagesize($this->photoPath);
		if (
			$info[0] > self::plugin()->settings['photoMaxWidth'] ||
			$info[1] > self::plugin()->settings['photoMaxHeight']
		)
		{
			$oldName = $this->photoPath;
			$this->ext = 'jpg';
			thumbnail(
				$oldName,
				$this->photoPath,
				self::plugin()->settings['photoMaxWidth'],
				self::plugin()->settings['photoMaxHeight']
			);
			if ($oldName != $this->photoPath)
			{
				filedelete($oldName);
			}
		}

		if (self::plugin()->settings['logoEnabled'])
		{
			$this->overlayLogo($this->photoPath);
		}

		thumbnail(
			$this->photoPath,
			$this->thumbPath,
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

		if (!file_exists($logoFile))
		{
			return;
		}

		$src = imageCreateFromFile($file);
		imagealphablending($src, true);
		imagesavealpha($src, true);

		$logo = imageCreateFromPNG($logoFile);
		imagealphablending($logo, true);
		imagesavealpha($logo, true);

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
			imagecopy ($src, $logo, $x, $y, 0, 0, $lw, $lh);
			imagealphablending($src, true);
			imagesavealpha($src, true);
			imageSaveToFile($src, $file, IMG_JPG);
			imageDestroy($logo);
			imageDestroy($src);
		}
	}
	//-----------------------------------------------------------------------------
}

<?php
/**
 * Каталог товаров
 *
 * ActiveRecord фотографии
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
 * $Id: Photo.php 992 2010-10-01 09:28:21Z mk $
 */


/**
 * ActiveRecord фотографии
 *
 * @property       int                   $id           Идентификатор
 * @property       GoodsCatalogGood      $good         Товар
 * @property       int                   $position     Порядковый номер
 * @property-read  string                $photoPath    Путь к основной фотографии
 * @property-read  string                $photoURL     URL основной фотографии
 * @property-read  string                $clientPopup  URL для показа во всплывающем блоке
 * @property-write string                $photo        Свойство для загрузки основной фотографии
 * @property-read  string                $thumbPath    Путь к миниатюре
 * @property-read  string                $thumbURL     URL миниатюры
 *
 * @package GoodsCatalog
 */
class GoodsCatalogPhoto extends GoodsCatalogAbstractActiveRecord
{
	/**
	 * Конструктор
	 *
	 * @param int $id  Идентификатор
	 *
	 * @return GoodsCatalogPhoto
	 *
	 * @since 1.00
	 */
	public function __construct($id = null)
	{
		$this->ownerProperty = 'good';

		parent::__construct($id);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Метод возвращает имя таблицы БД
	 *
	 * @return string  Имя таблицы БД
	 *
	 * @since 1.00
	 */
	public function getTableName()
	{
		return 'photos';
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
			'active' => array(
				'type' => PDO::PARAM_BOOL,
			),
			'position' => array(
				'type' => PDO::PARAM_INT,
			),
			'good' => array(
				'type' => PDO::PARAM_INT,
			),
			'ext' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 4,
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

		if ($this->isNew())
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

		parent::delete();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Считает количество фотографий
	 *
	 * @param int  $good                  Идентификатор товара
	 * @param bool $activeOnly[optional]  Считать только активные или все
	 *
	 * @return int
	 *
	 * @since 1.00
	 */
	public static function count($good, $activeOnly = false)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%d, %d)', $good, $activeOnly);

		$q = DB::getHandler()->createSelectQuery();
		$q->select('count(DISTINCT id) as `count`')
			->from(self::getDbTableStatic(__CLASS__));

		$e = $q->expr;
		$condition = $e->eq('good', $q->bindValue($good, null, PDO::PARAM_INT));
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
	 * Выбирает фотографии из БД
	 *
	 * @param int  $good                  Идентификатор товара
	 * @param int  $limit[optional]       Вернуть не более $limit фотографий
	 * @param int  $offset[optional]      Пропустить $offset первых фотографий
	 * @param bool $activeOnly[optional]  Искать только активные фотографии
	 *
	 * @return array(GoodsCatalogPhoto)
	 *
	 * @since 1.00
	 */
	public static function find($good, $limit = null, $offset = null, $activeOnly = false)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%d, %d, %d, %d)', $good, $limit, $offset, $activeOnly);

		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;

		$where = $e->eq('good', $q->bindValue($good, null, PDO::PARAM_INT));
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
			return self::plugin()->getDataDir() . 'goods/' . $this->getProperty('good') . '/' .
				$this->id . '.' . $this->ext;
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
			return self::plugin()->getDataURL() . 'goods/' . $this->getProperty('good') . '/' .
				$this->id . '.' . $this->ext;
		}
		else
		{
			return null;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $clientPopup
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getClientPopup()
	{
		if ($this->photoURL)
		{
			return $this->photoURL . '#catalog-popup';
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
			return self::plugin()->getDataDir() . 'goods/' . $this->getProperty('good') . '/' .
				$this->id . '-thmb.jpg';
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
			return self::plugin()->getDataURL() . 'goods/' . $this->getProperty('good') . '/' .
				$this->id . '-thmb.jpg';
		}
		else
		{
			return null;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $good
	 *
	 * @return GoodsCatalogGood
	 *
	 * @since 1.00
	 */
	protected function getGood()
	{
		try {
			$good = new GoodsCatalogGood($this->getProperty('good'));
		}
		catch (DomainException $e)
		{
			return null;
			$e = $e; // PHPMD hack
		}

		return $good;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сеттер свойства $good
	 *
	 * @param int|GoodsCatalogGood $value
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function setGood($value)
	{
		if ($value instanceof GoodsCatalogGood)
		{
			$value = $value->id;
		}
		$this->setProperty('good', intval($value));
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
		if (!isset($_FILES[$this->upload]) || $_FILES[$this->upload]['error'] == UPLOAD_ERR_NO_FILE)
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

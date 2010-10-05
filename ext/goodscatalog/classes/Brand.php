<?php
/**
 * Каталог товаров
 *
 * ActiveRecord бренда
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
 * $Id: Brand.php 995 2010-10-04 11:53:34Z mk $
 */


/**
 * ActiveRecord бренда
 *
 * @property       int    $id           Идентификатор
 * @property       bool   $active       Активность бренда
 * @property       string $title        Название
 * @property       string $description  Описание бренда
 * @property-read  string $logoPath     Путь к файлу логотипа
 * @property-read  string $logoURL      URL файла логотипа
 * @property-write string $logo         Свойство для загрузки нового логотипа
 *
 * @package GoodsCatalog
 */
class GoodsCatalogBrand extends GoodsCatalogAbstractActiveRecord
{
	/**
	 * Метод возвращает имя таблицы БД
	 *
	 * @return string  Имя таблицы БД
	 *
	 * @since 1.00
	 */
	public function getTableName()
	{
		return 'brands';
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
			'title' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 255,
			),
			'description' => array(
				'type' => PDO::PARAM_STR,
			),
			'ext' => array(
				'type' => PDO::PARAM_STR,
				'maxlength' => 4,
			),
		);
	}
	//-----------------------------------------------------------------------------

	/**
	 * (non-PHPdoc)
	 * @see src/goodscatalog/classes/GoodsCatalogAbstractActiveRecord::delete()
	 */
	public function delete()
	{
		$filename = $this->logoPath;

		if (is_file($filename))
		{
			@$result = unlink($filename);
			if (!$result)
			{
				ErrorMessage("Can not delete file $filename");
			}
		}

		parent::delete();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Считает количество брендов
	 *
	 * @param bool $activeOnly[optional]  Считать только активные или все
	 *
	 * @return int
	 *
	 * @since 1.00
	 */
	public static function count($activeOnly = false)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');

		$q = DB::getHandler()->createSelectQuery();
		$q->select('count(DISTINCT id) as `count`')
			->from(self::getDbTableStatic(__CLASS__));

		if ($activeOnly)
		{
			$e = $q->expr;
			$q->where($e->eq('active', $q->bindValue(true, null, PDO::PARAM_BOOL)));
		}

		$result = DB::fetch($q);
		return $result['count'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбирает бренды из БД
	 *
	 * @param int   $limit       Вернуть не более $limit брендов
	 * @param int   $offset      Пропустить $offset первых брендов
	 * @param bool  $activeOnly  Искать только активные бренды
	 *
	 * @return array(GoodsCatalogBrand)  Массив объектов GoodsCatalogBrand
	 *
	 * @uses eresus_log()
	 * @uses DB::getHandler()
	 * @uses load()
	 * @uses PDO
	 * @since 1.00
	 */
	public static function find($limit = null, $offset = null, $activeOnly = false)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%d, %d, %d)', $limit, $offset, $activeOnly);

		$q = DB::getHandler()->createSelectQuery();

		if ($activeOnly)
		{
			$e = $q->expr;
			$q->where($e->eq('active', $q->bindValue(true, null, PDO::PARAM_BOOL)));
		}

		$result = self::load($q, $limit, $offset);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сеттер свойства $logo
	 *
	 * @param string $value
	 * //param array $value
	 */
	protected function setLogo($value)
	{
		$this->upload = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $logoPath
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getLogoPath()
	{
		if ($this->ext)
		{
			return self::plugin()->getDataDir() . 'brands/' . $this->id . '.' . $this->ext;
		}
		else
		{
			return null;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $logoURL
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getLogoURL()
	{
		if ($this->ext)
		{
			return self::plugin()->getDataURL() . 'brands/' . $this->id . '.' . $this->ext;
		}
		else
		{
			return null;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбирает бренды из БД
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
			->orderBy('title');

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
				$image = new GoodsCatalogBrand();
				$image->loadFromArray($item);
				$result []= $image;
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
		$fileInfo = $_FILES[$this->upload];
		if ($fileInfo['error'] == UPLOAD_ERR_NO_FILE)
		{
			return;
		}

		$this->ext = strtolower(substr(strrchr($fileInfo['name'], '.'), 1));

		$this->checkFormat($fileInfo['type']);

		if (!upload($this->upload, $this->logoPath))
		{
			throw new EresusFsRuntimeException();
		}

		useLib('glib');

		/*
		 * Если изображение слишком больше - уменьшаем
		 */
		$info = @getimagesize($this->logoPath);
		if (
			$info[0] > self::plugin()->settings['brandLogoMaxWidth'] ||
			$info[1] > self::plugin()->settings['brandLogoMaxHeight']
		)
		{
			$oldName = $this->logoPath;
			$this->ext = 'jpg';
			thumbnail(
				$oldName,
				$this->logoPath,
				self::plugin()->settings['brandLogoMaxWidth'],
				self::plugin()->settings['brandLogoMaxHeight']
			);
			if ($oldName != $this->logoPath)
			{
				filedelete($oldName);
			}
		}

		$this->upload = null;

		parent::save();
	}
	//-----------------------------------------------------------------------------
}

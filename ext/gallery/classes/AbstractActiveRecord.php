<?php
/**
 * Галерея изображений
 *
 * Абстрактная реализация паттерна ActiveRecord
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
 * $Id: AbstractActiveRecord.php 970 2010-09-29 11:17:35Z mk $
 */


/**
 * Абстрактная реализация паттерна ActiveRecord
 *
 * @package Gallery
 */
abstract class GalleryAbstractActiveRecord
{
	/**
	 * Объект плагина
	 *
	 * @var Gallery
	 */
	private static $plugin;

	/**
	 * Значения полей
	 *
	 * @var array
	 */
	private $rawData = array();

	/**
	 * Кэш значений свойств
	 * @var array
	 */
	private $propertyCache = array();

	/**
	 * Признак новой записи
	 *
	 * @var bool
	 */
	private $isNew = true;

	/**
	 * Конструктор
	 *
	 * @param int $id  Идентификатор
	 *
	 * @return GalleryAbstractActiveRecord
	 */
	public function __construct($id = null)
	{
		eresus_log(array(__METHOD__, get_class($this)), LOG_DEBUG, '(%s)', $id);
		if ($id !== null)
		{
			$this->loadById($id);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Метод должен возвращать имя таблицы БД
	 *
	 * @return string
	 * @since 2.00
	 */
	abstract public function getTableName();
	//-----------------------------------------------------------------------------

	/**
	 * Метод должен возвращать список полей записи и их атрибуты
	 *
	 * Значение должно быть ассоциативным массивом, где ключами выступают имена полей, а значениями
	 * массивы атрибутов этих полей. Возможны следующие атрибуты:
	 *
	 * - type - Тип поля: PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_BOOL
	 * - pattern - PCRE для проверки значения
	 * - maxlength - Для строковых полей, максимальное количество символов
	 *
	 * @return array
	 * @since 2.00
	 */
	abstract public function getAttrs();
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает полное имя таблицы (плагин_таблица)
	 *
	 * @return string
	 * @since 2.00
	 */
	public function getDbTable()
	{
		return self::plugin()->name . '_' . $this->getTableName();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает полное имя таблицы (для статических вызовов)
	 *
	 * @param string $className  Имя класса, потомка GalleryAbstractActiveRecord, для которого
	 *                           надо получить имя таблицы
	 * @return string
	 * @since 2.00
	 */
	public static function getDbTableStatic($className)
	{
		$stub = new $className();

		if (!($stub instanceof GalleryAbstractActiveRecord))
		{
			throw new EresusTypeException();
		}

		return $stub->getDbTable();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает значение поля
	 *
	 * @param string $key  Имя поля
	 *
	 * @return mixed  Значение поля
	 *
	 * @throws EresusPropertyNotExistsException
	 * @since 2.00
	 */
	public function __get($key)
	{
		$getter = 'get' . $key;
		if (method_exists($this, $getter))
		{
			return $this->$getter();
		}

		return $this->getProperty($key);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Задаёт значение поля
	 *
	 * @param string $key    Имя поля
	 * @param mixed  $value  Значение поля
	 *
	 * @return void
	 *
	 * @since 2.00
	 */
	public function __set($key, $value)
	{
		$setter = 'set' . $key;
		if (method_exists($this, $setter))
		{
			$this->$setter($value);
		}
		else
		{
			$this->setProperty($key, $value);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает TRUE если эта запись ещё не добавлена в БД
	 *
	 * @return bool
	 *
	 * @since 2.00
	 */
	public function isNew()
	{
		return $this->isNew;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сохраняет изменения в БД
	 *
	 * @return void
	 *
	 * @since 2.00
	 */
	public function save()
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');

		$db = DB::getHandler();
		if ($this->isNew())
		{
			$q = $db->createInsertQuery();
			$q->insertInto($this->getDbTable());
		}
		else
		{
			$q = $db->createUpdateQuery();
			$q->update($this->getDbTable())
				->where($q->expr->eq('id', $q->bindValue($this->id,null, PDO::PARAM_INT)));
		}

		foreach ($this->attrs as $key => $attrs)
		{
			if (isset($this->rawData[$key]))
			{
				$q->set($key, $q->bindValue($this->rawData[$key], null, $attrs['type']));
			}
		}

		DB::execute($q);

		if ($this->isNew())
		{
			$this->id = $db->lastInsertId();
		}

		$this->isNew = false;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет запись
	 *
	 * @return void
	 *
	 * @since 2.00
	 */
	public function delete()
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');

		$db = DB::getHandler();
		if (!$this->isNew())
		{
			$q = $db->createDeleteQuery();
			$q->deleteFrom($this->getDbTable())
				->where($q->expr->eq('id', $q->bindValue($this->id,null, PDO::PARAM_INT)));

			DB::execute($q);
		}

		$this->isNew = true;
		$this->rawData = array();
		$this->propertyCache = array();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещает объект вверх по списку
	 *
	 * @return void
	 *
	 * @since 2.00
	 */
	public function moveUp()
	{
		if ($this->position == 0)
		{
			return;
		}

		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;
		$q->select('*')->from($this->getDbTable())
			->where($e->lAnd(
				$e->eq('section', $q->bindValue($this->section, null, PDO::PARAM_INT)),
				$e->lt('position', $q->bindValue($this->position, null, PDO::PARAM_INT))
			))
			->orderBy('position', ezcQuerySelect::DESC)
			->limit(1);

		$raw = DB::fetch($q);

		if (!$raw)
		{
			return;
		}

		$class = get_class($this);
		$swap = new $class;
		$swap->loadFromArray($raw);

		$pos = $this->position;
		$this->position = $swap->position;
		$swap->position = $pos;
		$swap->save();
		$this->save();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещает объект вниз по списку
	 *
	 * @return void
	 *
	 * @since 2.00
	 */
	public function moveDown()
	{
		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;
		$q->select('*')->from($this->getDbTable())
			->where($e->lAnd(
				$e->eq('section', $q->bindValue($this->section, null, PDO::PARAM_INT)),
				$e->gt('position', $q->bindValue($this->position, null, PDO::PARAM_INT))
			))
			->orderBy('position', ezcQuerySelect::ASC)
			->limit(1);

		$raw = DB::fetch($q);

		if (!$raw)
		{
			return;
		}

		$class = get_class($this);
		$swap = new $class;
		$swap->loadFromArray($raw);

		$pos = $this->position;
		$this->position = $swap->position;
		$swap->position = $pos;
		$swap->save();
		$this->save();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объект как массив свойств
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->rawData;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает экземпляр основного класса плгина
	 *
	 * @param Gallery $plugin[optional]  Использовать этот экземпляр вместо автоопределения.
	 *                                    Для модульных тестов.
	 * @return Gallery
	 *
	 * @since 2.00
	 */
	protected static function plugin($plugin = null)
	{
		if ($plugin)
		{
			self::$plugin = $plugin;
		}

		if (!self::$plugin)
		{
			self::$plugin = $GLOBALS['Eresus']->plugins->load('gallery');
		}
		return self::$plugin;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает значение свойства
	 *
	 * Метод не инициирует вызов сеттеров, но обрабатывает значение фильтрами
	 *
	 * @param string $key    Имя свойства
	 * @param mixed  $value  Значение
	 * @return void
	 *
	 * @throws EresusPropertyNotExistsException
	 * @since 2.00
	 */
	protected function setProperty($key, $value)
	{
		$attrs = $this->getAttrs();
		if (!isset($attrs[$key]))
		{
			throw new EresusPropertyNotExistsException($key, get_class($this));
		}

		switch ($attrs[$key]['type'])
		{
			case PDO::PARAM_BOOL:
				$value = $this->filterBool($value);
			break;

			case PDO::PARAM_INT:
				$value = $this->filterInt($value);
			break;

			case PDO::PARAM_STR:
				$value = $this->filterString($value, $attrs[$key]);
			break;

			default:
				throw new EresusTypeException();
			break;
		}

		$this->propertyCache[$key] = $this->rawData[$key] = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает значение свойства
	 *
	 * Метод не инициирует вызов геттеров
	 *
	 * @param string $key  Имя свойства
	 * @return mixed
	 *
	 * @throws EresusPropertyNotExistsException
	 * @since 2.00
	 */
	protected function getProperty($key)
	{
		$attrs = $this->getAttrs();
		if (!isset($attrs[$key]))
		{
			throw new EresusPropertyNotExistsException($key, get_class($this));
		}

		if (isset($this->rawData[$key]))
		{
			return $this->rawData[$key];
		}

		return null;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Загружает свойства из массива
	 *
	 * @param array $raw
	 * @return void
	 *
	 * @since 2.00
	 */
	protected function loadFromArray($raw)
	{
		$this->rawData = $raw;
		$this->isNew = false;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Загружает запись из БД по её идентификатору
	 *
	 * @param int $id
	 * @return void
	 *
	 * @throws DBQueryException
	 * @since 2.00
	 */
	protected function loadById($id)
	{
		$db = DB::getHandler();
		$q = $db->createSelectQuery();
		$q->select('*')
			->from($this->getDbTable())
			->where($q->expr->eq('id', $q->bindValue($id,null, PDO::PARAM_INT)))
			->limit(1);

		$this->rawData = DB::fetch($q);

		$this->isNew = false;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Фильтрует значения типа 'bool'
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 *
	 * @since 2.00
	 */
	private function filterBool($value)
	{
		return (boolean) $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Фильтрует значения типа 'int'
	 *
	 * @param mixed $value
	 *
	 * @return int
	 *
	 * @since 2.00
	 */
	private function filterInt($value)
	{
		return intval($value);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Фильтрует значения типа 'string'
	 *
	 * @param mixed $value
	 * @param array $attrs
	 *
	 * @return string
	 *
	 * @since 2.00
	 */
	private function filterString($value, $attrs)
	{
		if (isset($attrs['maxlength']))
		{
			$value = substr($value, 0, $attrs['maxlength']);
		}
		return $value;
	}
	//-----------------------------------------------------------------------------

}

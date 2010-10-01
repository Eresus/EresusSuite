<?php
/**
 * Галерея изображений
 *
 * Группа изображений
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
 * $Id: Group.php 970 2010-09-29 11:17:35Z mk $
 */


/**
 * Группа
 *
 * @property       int    $id           Идентификатор
 * @property-write int    $section      Идентифкатор раздела
 * @property       string $title        Название
 * @property       string $description  Описание
 * @property       string $position     Порядковый номер
 * @property-read  array  $images       Изображения группы
 *
 * @package Gallery
 */
class GalleryGroup extends GalleryAbstractActiveRecord
{
	/**
	 * Возвращает имя таблицы БД
	 *
	 * @return string  Имя таблицы БД
	 *
	 * @since 2.00
	 */
	public function getTableName()
	{
		return 'groups';
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
			'description' => array('type' => PDO::PARAM_STR),
			'position' => array('type' => PDO::PARAM_INT),
		);
	}
	//-----------------------------------------------------------------------------

	/**
	 * (non-PHPdoc)
	 * @see src/gallery/classes/GalleryAbstractActiveRecord::delete()
	 */
	public function delete()
	{
		$images = $this->images;
		foreach ($images as $image)
		{
			$image->delete();
		}
		parent::delete();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Считает количество групп в разделе
	 *
	 * @param int  $section  Идентификатор раздела
	 *
	 * @return int
	 *
	 * @since 2.00
	 */
	public static function count($section)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');

		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;
		$q->select('count(DISTINCT id) as `count`')
			->from(self::getDbTableStatic(__CLASS__))
			->where($e->eq('section', $q->bindValue($section, null, PDO::PARAM_INT)));

		$result = DB::fetch($q);
		return $result['count'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбирает группы из БД
	 *
	 * @param int  $section               Идентификатор раздела
	 * @param int  $limit[optional]       Вернуть не более $limit изображений
	 * @param int  $offset[optional]      Пропустить $offset первых изображений
	 *
	 * @return array(GalleryGroup)
	 *
	 * @since 2.00
	 */
	public static function find($section, $limit = null, $offset = null)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');

		$q = DB::getHandler()->createSelectQuery();
		$e = $q->expr;
		$q->select('*')->from(self::getDbTableStatic(__CLASS__))
			->where($e->eq('section', $q->bindValue($section, null, PDO::PARAM_INT)))
			->orderBy('position');

		if ($limit !== null)
		{
			if ($offset !== null)
			{
				$q->limit($limit, $offset);
			}
			else
			{
				$q->limit($limit);
			}

		}

		$raw = DB::fetchAll($q);
		$result = array();
		if (count($raw))
		{
			foreach ($raw as $item)
			{
				$group = new GalleryGroup();
				$group->loadFromArray($item);
				$result []= $group;
			}
		}

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Геттер свойства $images
	 *
	 * @return array(GalleryImage)
	 */
	protected function getImages()
	{
		return GalleryImage::find($this);
	}
	//-----------------------------------------------------------------------------

}
/*
		/* Строим индекс для поиска группы по её ID * /
		$groupIndex = array();
		foreach ($groups as $index => $group)
		{
			$groupIndex[$group['id']] = $index;
			// Заготовка для списка изображений этой группы
			$groups[$index]['images'] = array();
		}

		$condition2 = $condition;
		if (count($groupIndex))
		{
			$condition2 .= ' AND `group` IN(' .	implode(',', array_keys($groupIndex)) . ')';
		}
		$images = $this->dbSelect('images', $condition2, $this->getSortMode());

		/* Распределяем изображения по группам * /
		if (count($groupIndex))
		{
			foreach ($images as $image)
			{
				$groupId = $groupIndex[$image['group']];
				$groups[$groupId]['images'] []= $image;
			}
		}

 */
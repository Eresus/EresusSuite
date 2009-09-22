<?php
/**
 * Eresus 2.11
 *
 * Система управления контентом Eresus 2
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
 * $Id$
 */


/**
 * Работа с учётными записями пользователей
 *
 */
class EresusAccounts {
	var $table = 'users';
	var $cache = array();
 /**
	* Возвращает список полей
	*
	* @access public
	*
	* @return array Список полей
	*/
	function fields()
	{
		global $Eresus;

		if (isset($this->cache['fields'])) $result = $this->cache['fields']; else {
			$result = $Eresus->db->fields($this->table);
			$this->cache['fields'] = $result;
		}
		return $result;
	}
	//------------------------------------------------------------------------------
 /**
	* Возвращает учётную запись или список записей
	*
	* @access public
	*
	* @param int    $id  ID пользователя
	*	или
	*	@param array  $id  Список идентификаторов
	*	или
	*	@param string $id  SQL-условие
	*
	* @return array
	*/
	function	get($id)
	{
		global	$Eresus;

		if	(is_array($id))	$what	=	"FIND_IN_SET(`id`,	'".implode(',',	$id)."')";
		elseif	(is_numeric($id))	$what	=	"`id`=$id";
		else	$what	=	$id;
		$result	=	$Eresus->db->select($this->table,	$what);
		if	($result)	for($i=0;	$i<count($result);	$i++)	$result[$i]['profile']	=	decodeOptions($result[$i]['profile']);
		if	(is_numeric($id)	&&	$result	&&	count($result))	$result	=	$result[0];
		return	$result;
	}
	//------------------------------------------------------------------------------
	function getByName($name)
	{
		return $this->get("`login` = '$name'");
	}
	//-----------------------------------------------------------------------------
	/**
	*	Добавляет	учётную	запись
	*
	*	@access	public
	*
	*	@param	array	$item	Учётная	запись
	*
	*	@return	mixed	Описание	записи	или	false	в	случае	неудачи
	*/
	function	add($item)
	{
		global	$Eresus;

		$result	=	false;
		if	(isset($item['id']))	unset($item['id']);
		if	(!isset($item['profile']))	$item['profile']	=	array();
		$item['profile']	=	encodeOptions($item['profile']);
		if	($Eresus->db->insert($this->table,	$item))
			$result	=	$this->get($Eresus->db->getInsertedId());
		return	$result;
	}
	//------------------------------------------------------------------------------
	/**
	*	Изменяет	учётную	запись
	*
	*	@access	public
	*
	*	@param	array	$item	Учётная	запись
	*
	*	@return	mixed	Описание	изменённой	записи	или	false	в	случае	неудачи
	*/
	function	update($item)
	{
		global	$Eresus;

		$result	=	false;
		$item['profile']	=	encodeOptions($item['profile']);
		$result	=	$Eresus->db->updateItem($this->table,	$item,	"`id`={$item['id']}");
		return	$result;
	}
	//------------------------------------------------------------------------------
	/**
	*	Удаляет	учётную	запись
	*
	*	@access	public
	*
	*	@param	int	$id	Идентификатор	записи
	*
	*	@return	bool	Результат	операции
	*/
	function	delete($id)
	{
		global	$Eresus;

		$result	=	$Eresus->db->delete($this->table,	"`id`=$id");
		return	$result;
	}
	//------------------------------------------------------------------------------
}

/**
 * @deprecated since Eresus 2.11
 *
 */
class	Accounts extends EresusAccounts {}

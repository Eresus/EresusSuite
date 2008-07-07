<?php
/**
 * Eresus 2.10.1
 *
 * Библиотека для работы с СУБД MySQL
 *
 * @copyright		2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright		2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */

# ФУНКЦИИ ОТЛАДКИ (Работают при установленном флаге $Eresus->conf['debug'])
# Устанавливает глобальные переменные
#  $__MYSQL_QUERY_COUNT - Считает количество запросов к БД
#  $__MYSQL_QUERY_TIME - Считает общее время запросов к БД
#  $__MYSQL_QUERY_LOG - Все сделанные запросы (Необходимо установить флаг TMySQL->logQueries)

class MySQL {
	var $Connection, $name, $prefix;
	var $logQueries = false;
	var $error_reporting = true;
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function init($mysqlHost, $mysqlUser, $mysqlPswd, $mysqlName, $mysqlPrefix='')
	# Открывает соединение с базой данных MySQL и выбирает указанную базу данных.
	{
		$result = false;
		$this->name = $mysqlName;
		$this->prefix = $mysqlPrefix;
		@$this->Connection = mysql_connect($mysqlHost, $mysqlUser, $mysqlPswd, true);
		if ($this->Connection) {
			if (defined('LOCALE_CHARSET')) {
				$version = preg_replace('/[^\d\.]/', '', mysql_get_server_info());
				if (version_compare($version, '4.1') >= 0) $this->query("SET NAMES '".LOCALE_CHARSET."'");
			}
			if (mysql_select_db($this->name, $this->Connection)) $result = true;
			elseif ($this->error_reporting) FatalError(mysql_error($this->Connection));
		} elseif ($this->error_reporting) FatalError("Can not connect to MySQL server. Check login and password");
		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function query($query)
	# Выполняет запрос к базе данных с которой установленно соединение методом init().
	{
		global $Eresus, $__MYSQL_QUERY_COUNT, $__MYSQL_QUERY_TIME, $__MYSQL_QUERY_LOG;

		if ($Eresus->conf['debug']['enable']) {
			$time_start = microtime();
			if ($this->logQueries) $__MYSQL_QUERY_LOG .= $query."\n";
		}
		$result = mysql_query($query, $this->Connection);
		if ($this->error_reporting && !$result) FatalError(mysql_error($this->Connection)."<br />Query \"$query\"");
		if ($Eresus->conf['debug']['enable']) {
			$__MYSQL_QUERY_COUNT++;
			$__MYSQL_QUERY_TIME += microtime() - $time_start;
		}
		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function query_array($query)
	# Выполняет запрос к базе данных и возвращает ассоциативный массив значений
	{
		global $__MYSQL_QUERY_COUNT, $__MYSQL_QUERY_TIME, $__MYSQL_QUERY_LOG;

		$result = $this->query($query);
		$values = Array();
		while($row = mysql_fetch_assoc($result)) {
			if (count($row)) foreach($row as $key => $value) $row[$key] = $value;
			$values[] = $row;
		}
		return $values;
	}
	//------------------------------------------------------------------------------
	/**
	 * Создание новой таблицы
	 *
	 * @param string $name       Имя таблицы
	 * @param string $structure  Описание структуры
	 * @param string $options    Опции
	 *
	 * @return bool Результат
	 */
	function create($name, $structure, $options = '')
	{
		$result = false;
		$query = "CREATE TABLE `{$this->prefix}$name` ($structure) $options";
		$result = $this->query($query);
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	 * Удаление таблицы
	 *
	 * @param string $name       Имя таблицы
	 *
	 * @return bool Результат
	 */
	function drop($name)
	{
		$result = false;
		$query = "DROP TABLE `{$this->prefix}$name`";
		$result = $this->query($query);
		return $result;
	}
	//------------------------------------------------------------------------------
 /**
	* Выполняет запрос SELECT
	*
	* @param string  $tables      Список таблиц, разделённый запятыми
	* @param string  $condition   Условие для выборки (WHERE)
	* @param string  $order       Поля для сортировки (ORDER BY)
	* @param string  $fields      Список полей для получения
	* @param int     $lim_rows    Максимльное количество получаемых записей
	* @param int     $lim_offset  Начальное смещение для выборки
	* @param string  $group       Поле для группировки
	* @param bool    $distinct    Вернуть только уникальные записи
	*
	* @return array
	*/
	function select($tables, $condition = '', $order = '', $fields = '', $lim_rows = 0, $lim_offset = 0, $group = '', $distinct = false)
	{
		if (is_bool($fields) || $fields == '1' || $fields == '0' || !is_numeric($lim_rows)) {
			# Обратная совместимость c 1.2.x
			$desc = $fields;
			$fields = $lim_rows ? $lim_rows : '*';
			$lim_rows = $lim_offset;
			$lim_offset = $group;
			$group = $distinct;
			$distinct = func_num_args() == 9 ? func_get_arg(8) : false;
			$query = 'SELECT ';
			if ($distinct) $query .= 'DISTINCT ';
			if (!strlen($fields)) $fields = '*';
			$tables = str_replace('`' ,'', $tables);
			$tables = preg_replace('/([\w.]+)/i', '`'.$this->prefix.'$1`', $tables);
			$query .= $fields." FROM ".$tables;
			if (strlen($condition)) $query .= " WHERE $condition";
			if (strlen($group)) $query .= " GROUP BY $group";
			if (strlen($order)) {
				$query .= " ORDER BY $order";
				if ($desc) $query .= ' DESC';
			}
			if ($lim_rows) {
				$query .= ' LIMIT ';
				if ($lim_offset) $query .= "$lim_offset, ";
				$query .= $lim_rows;
			}
		} else {
			$query = 'SELECT ';
			if ($distinct) $query .= 'DISTINCT ';
			if (!strlen($fields)) $fields = '*';
			$tables = str_replace('`','',$tables);
			$tables = preg_replace('/([\w.]+)/i', '`'.$this->prefix.'$1`', $tables);
			$query .= $fields." FROM ".$tables;
			if (strlen($condition)) $query .= " WHERE ".$condition;
			if (strlen($group)) $query .= " GROUP BY ".$group."";
			if (strlen($order)) {
				$order = explode(',', $order);
				for($i = 0; $i < count($order); $i++) switch ($order[$i]{0}) {
					case '+': $order[$i] = substr($order[$i], 1); break;
					case '-': $order[$i] = substr($order[$i], 1).' DESC'; break;
				}
				$query .= " ORDER BY ".implode(', ',$order);
			}
			if ($lim_rows) {
				$query .= ' LIMIT ';
				if ($lim_offset) $query .= "$lim_offset, ";
				$query .= $lim_rows;
			}
		}
		$result = $this->query_array($query);

		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
	function insert($table, $item)
	# Выполняет запрос INSERT к базе данных используя метод query().
	#  $table - таблица, в которую надо вставтиь запись
	#  $item - ассоциативный массив значений
	{
		$hnd = mysql_list_fields($this->name, $this->prefix.$table, $this->Connection);
		$cols = '';
		$values = '';
		while (($field = @mysql_field_name($hnd, $i++))) if (isset($item[$field])) {
			$cols .= ", `$field`";
			$values .= " , '{$item[$field]}'";
		}
		$cols = substr($cols, 2);
		$values = substr($values, 2);
		$result = $this->query("INSERT INTO ".$this->prefix.$table." (".$cols.") VALUES (".$values.")");
		return $result;
	}
 /**
	* Выполняет обновление информации в БД
	*
	* @param string $table      Таблица
	* @param string $set        Изменения
	* @param string $condition  Условие
	* @return unknown
	*/
	function update($table, $set, $condition)
	{
		$result = $this->query("UPDATE `".$this->prefix.$table."` SET ".$set." WHERE ".$condition);
		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function delete($table, $condition)
	# Выполняет запрос DELETE к базе данных используя метод query().
	#  $table - таблица, из которой требуется удалить записи
	#  $condition - признаки удаляемых записей
	{
		$result = $this->query("DELETE FROM `".$this->prefix.$table."` WHERE ".$condition);
		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function fields($table)
	# Возвращает список полей таблицы
	#  $table - таблица, для которой надо получить список полей
	{
		$hnd = mysql_list_fields($this->name, $this->prefix.$table, $this->Connection);
		if ($hnd == false) FatalError(mysql_error($this->Connection));
		while (($field = @mysql_field_name($hnd, $i++))) $result[] = $field;
		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function selectItem($table, $condition, $fields = '')
	{
		if ($table{0} != "`") $table = "`".$table."`";
		$tmp = $this->select($table, $condition, '', false, $fields);
		$tmp = isset($tmp[0])?$tmp[0]:null;
		return $tmp;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function updateItem($table, $item, $condition)
	{
		$hnd = mysql_list_fields($this->name, $this->prefix.$table, $this->Connection);
		if ($hnd === false) FatalError(mysql_error($this->Connection));
		$values = array();
		$i = 0;
		while (($field = @mysql_field_name($hnd, $i++))) if (isset($item[$field])) $values[] = "`$field`='{$item[$field]}'";
		$values = implode(', ', $values);
		$result = $this->query("UPDATE `".$this->prefix.$table."` SET ".$values." WHERE ".$condition);
		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function count($table, $condition='', $group='', $rows=false)
	# Возвращает количество записей в таблице используя метод query().
	#  $table - таблица, для которой требуется посчитать кол-во записей
	{
		$result = $this->query("SELECT count(*) FROM `".$this->prefix.$table."`".(empty($condition)?'':'WHERE '.$condition).(empty($group)?'':' GROUP BY `'.$group.'`'));
		if ($rows) {
			$count = 0;
			while (mysql_fetch_row($result)) $count++;
			$result = $count;
		} else {
			$result = mysql_fetch_row($result);
			$result = $result[0];
		}
		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function getInsertedID()
	{
		return mysql_insert_id($this->Connection);
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
	function tableStatus($table, $param='')
	{
		$result = $this->query_array("SHOW TABLE STATUS LIKE '".$this->prefix.$table."'");
		if ($result) {
			$result = $result[0];
			if (!empty($param)) $result = $result[$param];
		}
		return $result;
	}
	#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
 /**
	* Экранирует потенциально опасные символы
	*
	* @param mixed $src  Входные данные
	*
	* @return mixed
	*/
	function escape($src)
	{
		switch (true) {
			case is_string($src): $src = mysql_real_escape_string($src); break;
			case is_array($src): foreach($src as $key => $value) $src[$key] = mysql_real_escape_string($value); break;
		}
		return $src;
	}
	//-----------------------------------------------------------------------------
}
?>
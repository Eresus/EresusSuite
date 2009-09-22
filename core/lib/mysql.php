<?php
/**
 * Eresus 2.11
 *
 * Библиотека для работы с СУБД MySQL
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

class MySQL {
	var $Connection;
	var $name;
	var $prefix;
	var $logQueries = false;
 /**
	* Если TRUE (по умолчанию) в случае ошибки скрипт будет прерван и показано сообщение об ошибке
	*
	* @var  bool  $display_errors
	*/
	var $error_reporting = true;
 /**
	* Открывает соединение сервером данных и выбирает источник
	*
	* @param  string  $server    Сервер данных
	* @param  string  $username  Имя пользователя для доступа к серверу
	* @param  string  $password  Пароль пользователя
	* @param  string  $source    Имя источника данных
	* @param  string  $prefix    Префикс для имён таблиц. По умолчанию ''
	*
	* @return  bool  Результат соединения
	*/
	function init($server, $username, $password, $source, $prefix='')
	{
		$result = false;
		$this->name = $source;
		$this->prefix = $prefix;
		@$this->Connection = mysql_connect($server, $username, $password, true);
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
	//-----------------------------------------------------------------------------
 /**
	* Выполняет запрос к источнику
	*
	* @param  string  $query    Запрос в формате источника
	*
	* @return  mixed  Результат запроса. Тип зависит от источника, запроса и результата
	*/
	function query($query)
	{
		global $Eresus;

		$result = mysql_query($query, $this->Connection);
		if ($this->error_reporting && !$result) FatalError(mysql_error($this->Connection)."<br />Query \"$query\"");
		return $result;
	}
	//-----------------------------------------------------------------------------
 /**
	* Выполняет запрос к источнику и возвращает ассоциативный массив значений
	*
	* @param  string  $query    Запрос в формате источника
	*
	* @return  array|bool  Ответ в виде массива или FALSE в случае ошибки
	*/
	function query_array($query)
	{
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
	* Производит выборку данных из источника
	*
	* @param  string   $tables     Список таблиц из которых проводится выборка
	* @param  string   $condition  Условие для выборки (WHERE)
	* @param  string   $order      Поля для сортировки (ORDER BY)
	* @param  string   $fields     Список полей для получения
	* @param  int      $rows       Максимльное количество получаемых записей
	* @param  int      $offset     Начальное смещение для выборки
	* @param  string   $group      Поле для группировки
	* @param  bool     $distinct   Вернуть только уникальные записи
	*
	* @return  array|bool  Выбранные элементы в виде массива или FALSE в случае ошибки
	*/
	function select($tables, $condition = '', $order = '', $fields = '', $rows = 0, $offset = 0, $group = '', $distinct = false)
	{
		if (is_bool($fields) || $fields == '1' || $fields == '0' || !is_numeric($rows)) {
			# Обратная совместимость c 1.2.x
			$desc = $fields;
			$fields = $rows ? $rows : '*';
			$rows = $offset;
			$offset = $group;
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
			if ($rows) {
				$query .= ' LIMIT ';
				if ($offset) $query .= "$offset, ";
				$query .= $rows;
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
					case '+': $order[$i] = '`'.substr($order[$i], 1).'`'; break;
					case '-': $order[$i] = '`'.substr($order[$i], 1).'` DESC'; break;
				}
				$query .= " ORDER BY ".implode(', ',$order);
			}
			if ($rows) {
				$query .= ' LIMIT ';
				if ($offset) $query .= "$offset, ";
				$query .= $rows;
			}
		}
		$result = $this->query_array($query);

		return $result;
	}
	//-----------------------------------------------------------------------------
 /**
	* Вставка элементов в источник
	*
	* @param  string  $table  Таблица, в которую надо вставтиь элемент
	* @param  array   $item   Ассоциативный массив значений
	*
	* @return  mixed  Результат выполнения операции
	*/
	function insert($table, $item)
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
	//-----------------------------------------------------------------------------
 /**
	* Выполняет обновление информации в источнике
	*
	* @param string $table      Таблица
	* @param mixed  $set        Изменения
	* @param string $condition  Условие
	* @return unknown
	*/
	function update($table, $set, $condition)
	{
		if (is_array($set)) {
			$pairs = array();
			$fields = $this->fields($table, true);
			foreach($set as $field => $value) {
				if (isset($fields[$field])) {
					switch ($fields[$field]) {

					}
					#$pairs[]
				}
			}
		}
		$result = $this->query("UPDATE `".$this->prefix.$table."` SET ".$set." WHERE ".$condition);
		return $result;
	}
	//-----------------------------------------------------------------------------
	function delete($table, $condition)
	# Выполняет запрос DELETE к базе данных используя метод query().
	#  $table - таблица, из которой требуется удалить записи
	#  $condition - признаки удаляемых записей
	{
		$result = $this->query("DELETE FROM `".$this->prefix.$table."` WHERE ".$condition);
		return $result;
	}
	//-----------------------------------------------------------------------------
 /**
	* Получение списка полей таблицы
	*
	* @param string $table  Имя таблицы
	* @return array  Описание полей
	*/
	function fields($table, $info = false)
	{
		global $Eresus;

		$fields = $this->query_array("SHOW COLUMNS FROM `{$this->prefix}$table`");
		if ($fields) {
			$result = array();
			foreach($fields as $item) {
				if ($info) {
					$result[$item['Field']] = array(
						'name' => $item['Field'],
						'type' => $item['Type'],
						'size' => 0,
						'signed' => false,
						'default' => $item['Default'],
					);
					switch (true) {
						case $item['Type'] == 'text':
							$result[$item['Field']]['size'] = 65535;
						break;
						case $item['Type'] == 'longtext':
							$result[$item['Field']]['type'] = 'text';
							$result[$item['Field']]['size'] = 4294967295;
						break;
						case substr($item['Type'], 0, 3) == 'int':
							$result[$item['Field']]['signed'] = strpos($result[$item['Field']]['type'], 'unsigned') === false;
							$item['Type'] = str_replace(' unsigned', '', $item['Type']);
							$result[$item['Field']]['type'] = 'int';
							$result[$item['Field']]['size'] = substr($item['Type'], 4, -1);
						break;
						case substr($item['Type'], 0, 8) == 'smallint':
							$result[$item['Field']]['signed'] = strpos($result[$item['Field']]['type'], 'unsigned') === false;
							$item['Type'] = str_replace(' unsigned', '', $item['Type']);
							$result[$item['Field']]['type'] = 'int';
							$result[$item['Field']]['size'] = substr($item['Type'], 9, -1);
						break;
						case substr($item['Type'], 0, 7) == 'tinyint':
							$result[$item['Field']]['signed'] = strpos($result[$item['Field']]['type'], 'unsigned') === false;
							$item['Type'] = str_replace(' unsigned', '', $item['Type']);
							$result[$item['Field']]['type'] = 'int';
							$result[$item['Field']]['size'] = substr($item['Type'], 8, -1);
						break;
						case substr($item['Type'], 0, 7) == 'varchar':
							$result[$item['Field']]['type'] = 'string';
							$result[$item['Field']]['size'] = substr($item['Type'], 8, -1);
						break;
					}
				} else $result[] = $item['Field'];
			}
		} else FatalError(mysql_error($this->Connection));
		return $result;
	}
	//-----------------------------------------------------------------------------
	function selectItem($table, $condition, $fields = '')
	{
		if ($table{0} != "`") $table = "`".$table."`";
		$tmp = $this->select($table, $condition, '', false, $fields);
		$tmp = isset($tmp[0])?$tmp[0]:null;
		return $tmp;
	}
	//-----------------------------------------------------------------------------
	function updateItem($table, $item, $condition)
	{
		$fields = $this->fields($table, true);

		$values = array();
		foreach($fields as $field => $info) if (isset($item[$field])) {
			switch ($info['type']) {
				case 'int':
					$value = $item[$field];
					if (!$value) $value = 0;
				break;
				default: $value = "'".$item[$field]."'";
			}
			$values[] = "`$field` = $value";
		}
		$values = implode(', ', $values);
		$result = $this->query("UPDATE `".$this->prefix.$table."` SET ".$values." WHERE ".$condition);
		return $result;
	}
	//-----------------------------------------------------------------------------
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
	//-----------------------------------------------------------------------------
	function getInsertedID()
	{
		return mysql_insert_id($this->Connection);
	}
	//-----------------------------------------------------------------------------
	function tableStatus($table, $param='')
	{
		$result = $this->query_array("SHOW TABLE STATUS LIKE '".$this->prefix.$table."'");
		if ($result) {
			$result = $result[0];
			if (!empty($param)) $result = $result[$param];
		}
		return $result;
	}
	//-----------------------------------------------------------------------------
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
<?php
/**
 * Eresus {$M{VERSION}}
 *
 * ������ �����-���������� ��� ����������� � Eresus ���������� ����������
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * ���� ����� ��������� ��������� � ��������� ������ (�������� � ���� ������),
 * ������������ Eresus.
 * �������������� ��� ������ � ��������� ������� �� �������� � ������ � ����
 * ����� ������������� �� SQL-�������� �����.
 */

class IDataSource {
 /**
	* ���� TRUE (�� ���������) � ������ ������ ������ ����� ������� � �������� ��������� �� ������
	*
	* @var  bool  $display_errors
	*/
	var $display_errors = true;

 /**
	* ��������� ���������� �������� ������ � �������� ��������
	*
	* @param  string  $server    ������ ������
	* @param  string  $username  ��� ������������ ��� ������� � �������
	* @param  string  $password  ������ ������������
	* @param  string  $source    ��� ��������� ������
	* @param  string  $prefix    ������� ��� ��� ������. �� ��������� ''
	*
	* @return  bool  ��������� ����������
	*/
	function init($server, $username, $password, $source, $prefix='');

 /**
	* ��������� ������ � ���������
	*
	* @param  string  $query    ������ � ������� ���������
	*
	* @return  mixed  ��������� �������. ��� ������� �� ���������, ������� � ����������
	*/
	function query($query);

 /**
	* ��������� ������ � ��������� � ���������� ������������� ������ ��������
	*
	* @param  string  $query    ������ � ������� ���������
	*
	* @return  array|bool  ����� � ���� ������� ��� FALSE � ������ ������
	*/
	function query_array($query);

	/**
	 * �������� ����� �������
	 *
	 * @param string $name       ��� �������
	 * @param string $structure  �������� ���������
	 * @param string $options    �����
	 *
	 * @return bool ���������
	 */
	function create($name, $structure, $options = '');

	/**
	 * �������� �������
	 *
	 * @param string $name       ��� �������
	 *
	 * @return bool ���������
	 */
	function drop($name);

 /**
	* ���������� ������� ������ �� ���������
	*
	* @param  string   $tables     ������ ������ �� ������� ���������� �������
	* @param  string   $condition  ������� ��� ������� (WHERE)
	* @param  string   $order      ���� ��� ���������� (ORDER BY)
	* @param  string   $fields     ������ ����� ��� ���������
	* @param  int      $rows       ����������� ���������� ���������� �������
	* @param  int      $offset     ��������� �������� ��� �������
	* @param  string   $group      ���� ��� �����������
	* @param  bool     $distinct   ������� ������ ���������� ������
	*
	* @return  array|bool  ��������� �������� � ���� ������� ��� FALSE � ������ ������
	*/
	function select($tables, $condition = '', $order = '', $fields = '', $rows = 0, $offset = 0, $group = '', $distinct = false);

 /**
	* ������� ��������� � ��������
	*
	* @param  string  $table  �������, � ������� ���� �������� �������
	* @param  array   $item   ������������� ������ ��������
	*
	* @return  mixed  ��������� ���������� ��������
	*/
	function insert($table, $item);

 /**
	* ��������� ���������� ���������� � ���������
	*
	* @param string $table      �������
	* @param string $set        ���������
	* @param string $condition  �������
	* @return unknown
	*/
	function update($table, $set, $condition);

	function delete($table, $condition);
	# ��������� ������ DELETE � ���� ������ ��������� ����� query().
	#  $table - �������, �� ������� ��������� ������� ������
	#  $condition - �������� ��������� �������

 /**
	* ��������� ������ ����� �������
	*
	* @param string $table  ��� �������
	* @return array  �������� �����
	*/
	function fields($table);

	function selectItem($table, $condition, $fields = '');

	function updateItem($table, $item, $condition);

	function count($table, $condition='', $group='', $rows=false);
	# ���������� ���������� ������� � ������� ��������� ����� query().
	#  $table - �������, ��� ������� ��������� ��������� ���-�� �������

	function getInsertedID();

	function tableStatus($table, $param='');

 /**
	* ���������� ������������ ������� �������
	*
	* @param mixed $src  ������� ������
	*
	* @return mixed
	*/
	function escape($src);

}

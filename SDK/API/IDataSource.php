<?php
/**
 * Eresus {$M{VERSION}}
 *
 * Пример файла-коннектора для подключения к Eresus стороннего расширения
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Этот класс описывает интерфейс к источнику данных (например к базе данных),
 * используемый Eresus.
 * Предполагается что данные в источнике разбиты по таблицам и работа с ними
 * может производиться на SQL-подобном языке.
 */

class IDataSource {
 /**
	* Если TRUE (по умолчанию) в случае ошибки скрипт будет прерван и показано сообщение об ошибке
	*
	* @var  bool  $display_errors
	*/
	var $display_errors = true;

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
	function init($server, $username, $password, $source, $prefix='');

 /**
	* Выполняет запрос к источнику
	*
	* @param  string  $query    Запрос в формате источника
	*
	* @return  mixed  Результат запроса. Тип зависит от источника, запроса и результата
	*/
	function query($query);

 /**
	* Выполняет запрос к источнику и возвращает ассоциативный массив значений
	*
	* @param  string  $query    Запрос в формате источника
	*
	* @return  array|bool  Ответ в виде массива или FALSE в случае ошибки
	*/
	function query_array($query);

	/**
	 * Создание новой таблицы
	 *
	 * @param string $name       Имя таблицы
	 * @param string $structure  Описание структуры
	 * @param string $options    Опции
	 *
	 * @return bool Результат
	 */
	function create($name, $structure, $options = '');

	/**
	 * Удаление таблицы
	 *
	 * @param string $name       Имя таблицы
	 *
	 * @return bool Результат
	 */
	function drop($name);

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
	function select($tables, $condition = '', $order = '', $fields = '', $rows = 0, $offset = 0, $group = '', $distinct = false);

 /**
	* Вставка элементов в источник
	*
	* @param  string  $table  Таблица, в которую надо вставтиь элемент
	* @param  array   $item   Ассоциативный массив значений
	*
	* @return  mixed  Результат выполнения операции
	*/
	function insert($table, $item);

 /**
	* Выполняет обновление информации в источнике
	*
	* @param string $table      Таблица
	* @param string $set        Изменения
	* @param string $condition  Условие
	* @return unknown
	*/
	function update($table, $set, $condition);

	function delete($table, $condition);
	# Выполняет запрос DELETE к базе данных используя метод query().
	#  $table - таблица, из которой требуется удалить записи
	#  $condition - признаки удаляемых записей

 /**
	* Получение списка полей таблицы
	*
	* @param string $table  Имя таблицы
	* @return array  Описание полей
	*/
	function fields($table);

	function selectItem($table, $condition, $fields = '');

	function updateItem($table, $item, $condition);

	function count($table, $condition='', $group='', $rows=false);
	# Возвращает количество записей в таблице используя метод query().
	#  $table - таблица, для которой требуется посчитать кол-во записей

	function getInsertedID();

	function tableStatus($table, $param='');

 /**
	* Экранирует потенциально опасные символы
	*
	* @param mixed $src  Входные данные
	*
	* @return mixed
	*/
	function escape($src);

}

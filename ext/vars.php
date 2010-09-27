<?php
/**
 * Vars
 *
 * Создание собственных текстовых переменных
 *
 * @version 2.01
 *
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо по вашему выбору с условиями более поздней
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
 * @package Vars
 *
 * $Id: vars.php 552 2010-09-22 12:34:24Z mk $
 */

/**
 * Класс плагина
 * @package Vars
 */
class Vars extends Plugin
{
	/**
	 * Требуемая версия ядра
	 * @var string
	 */
	public $kernel = '2.14';

	var $title = 'Vars';
	var $type = 'client,admin';
	var $version = '2.01';
	var $description = 'Создание собственных текстовых переменных';
	var $settings = array(
			);
	var $table = array (
		'name' => 'vars',
		'key'=> 'name',
		'sortMode' => 'caption',
		'sortDesc' => false,
		'columns' => array(
			array('name' => 'name', 'caption' => 'Имя', 'value' => '&#36;($(name))', 'macros' => true),
			array('name' => 'caption', 'caption' => 'Описание'),
			),
		'controls' => array (
			'delete' => '',
			'edit' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
				array('caption'=>strAdd, 'name'=>'action', 'value'=>'create')
			),
		)
	);

	/**
	 * Конструктор
	 *
	 * @return TVars
	 */
	function __construct()
	{
		parent::__construct();
		$this->listenEvents('clientOnPageRender', 'adminOnMenuRender');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавление
	 *
	 * @return void
	 */
	private function insert()
	{
		$item = array(
			'name' => arg('name', 'word'),
			'caption' => arg('caption', 'dbsafe'),
			'value' => arg('value', 'dbsafe'),
		);

		$tmp = $this->dbItem('', $item['name'], 'name');
		if (!$tmp)
		{
			$this->dbInsert('', $item, 'name');
		}
		else
		{
			ErrorMessage('Переменная с именем "' . $item['name'] .
				'" уже существует. Выберите другое имя.');
			HTTP::goback();
		}

		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Изменение
	 *
	 * @return void
	 */
	private function update()
	{
		$oldName = arg('update', 'word');
		$item = $this->dbItem('', $oldName, 'name');

		$item['name'] = arg('name', 'word');
		$item['caption'] = arg('caption', 'dbsafe');
		$item['value'] = arg('value', 'dbsafe');
		if ($item['name'] != $oldName)
		{
			$tmp = $this->dbItem('', $item['name'], 'name');
			if ($tmp)
			{
				ErrorMessage('Переменная с именем "' . $item['name'] .
					'" уже существует. Выберите другое имя.');
				HTTP::redirect(arg('submitURL'));
			}
		}

		$q = DB::getHandler()->createUpdateQuery();
		$q->update($this->__table(''))
			->where($q->expr->eq('name', $q->bindValue($oldName, null, PDO::PARAM_STR)));

		foreach ($item as $key => $value)
		{
			$q->set($key, $q->bindValue($value));
		}

		DB::execute($q);

		$url = arg('submitURL');

		if ($item['name'] != $oldName)
		{
			$url = str_replace('id=' . $oldName, 'id=' . $item['name'], $url);
		}

		HTTP::redirect($url);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диплог добавления
	 *
	 * @return string
	 */
	private function adminAddItem()
	{
		global $page;

		$form = array(
			'name' => 'AddForm',
			'caption' => strAdd,
			'width'=>'500px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'action', 'value' => 'insert'),
				array ('type' => 'edit', 'name' => 'name', 'label' => 'Имя $(', 'width' => '200px',
					'maxlength' => '31', 'comment' => ')', 'pattern' => '/.+/',
					'errormsg' => 'Не указано имя переменной'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Описание', 'width' => '100%',
					'maxlength' => '63', 'pattern' => '/.+/', 'errormsg' => 'Не указано название переменной'),
				array ('type' => 'memo', 'name' => 'value', 'label' => 'Значение', 'height' => '10'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диалог изменения
	 *
	 * @return string
	 */
	private function adminEditItem()
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem($this->table['name'], "`name`='".arg('id', 'word')."'");
		$form = array(
			'name' => 'EditForm',
			'caption' => strEdit,
			'width' => '500px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'update', 'value' => $item['name']),
				array ('type' => 'edit', 'name' => 'name', 'label' => 'Имя $(', 'width' => '200px',
					'maxlength' => '31', 'comment' => ')', 'pattern' => '/.+/',
					'errormsg' => 'Не указано имя переменной'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Описание', 'width' => '100%',
					'maxlength' => '63', 'pattern' => '/.+/', 'errormsg' => 'Не указано название переменной'),
				array ('type' => 'memo', 'name' => 'value', 'label' => 'Значение', 'height' => '10'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает разметку интерфейса
	 *
	 * @return string  HTML
	 */
	function adminRender()
	{
		global $page;

		$result = '';

		switch (true)
		{
			case !is_null(arg('update')):
				$this->update();
			break;
			case !is_null(arg('delete')):
				$this->delete(arg('delete', 'dbsafe'));
			break;
			case !is_null(arg('id')):
				$result = $this->adminEditItem();
			break;
			case !is_null(arg('action')):
				switch (arg('action'))
				{
					case 'create':
						$result = $this->adminAddItem();
					break;
					case 'insert':
						$this->insert();
					break;
				}
			break;
			default:
				$result = $page->renderTable($this->table);
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 *
	 * @return string
	 */
	function clientOnPageRender($text)
	{
		global $Eresus;

		$items = $Eresus->db->select($this->table['name']);
		if (count($items))
		{
			foreach ($items as $item)
			{
				$text= str_replace('$('.$item['name'].')', $item['value'], $text);
			}
		}
		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * @return void
	 */
	function adminOnMenuRender()
	{
		global $page;

		$page->addMenuItem('Расширения', array ("access"  => EDITOR, "link"  => $this->name,
			"caption"  => 'Переменные', "hint"  => "Управление текстовыми переменными"));
	}
	//-----------------------------------------------------------------------------

	/**
	 * (non-PHPdoc)
	 * @see main/core/Plugin::install()
	 */
	public function install()
	{
		parent::install();

		$sql = "
			`name` varchar(31) NOT NULL,
			`caption` varchar(63) NOT NULL,
			`value` text NOT NULL,
			PRIMARY KEY  (`name`)
		";

		$this->dbCreateTable($sql, '');

	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет переменную
	 *
	 * @param string $name  Имя переменной
	 */
	private function delete($name)
	{
		global $page;

		$item = $this->dbItem('', $name, 'name');
		if ($item)
		{
			$this->dbDelete('', $name, 'name');
		}
		else
		{
			ErrorMessage('Переменной с именем "' . $name . '" не найдено.');
		}
		HTTP::redirect(str_replace('&amp;', '&', $page->url()));
	}
	//-----------------------------------------------------------------------------
}


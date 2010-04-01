<?php
/**
 * Vars
 *
 * Eresus 2
 *
 * Создание собственных текстовых переменных
 *
 * @version 1.07
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
 * $Id$
 */

/**
 * Класс плагина
 * @package Vars
 */
class TVars extends TListContentPlugin
{
	var $name = 'vars';

	/**
	 * Требуемая версия ядра
	 * @var string
	 */
	public $kernel = '2.12b';

	var $title = 'Vars';
	var $type = 'client,admin';
	var $version = '1.07b';
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
		),
		'sql' => "(
			`name` varchar(31) NOT NULL,
			`caption` varchar(63) NOT NULL,
			`value` text NOT NULL,
			PRIMARY KEY  (`name`)
		) TYPE=MyISAM;",
	);

	/**
	 * Конструктор
	 *
	 * @return TVars
	 */
	function __construct()
	{
		global $Eresus;

		parent::__construct();
		$Eresus->plugins->events['clientOnPageRender'][] = $this->name;
		$Eresus->plugins->events['adminOnMenuRender'][] = $this->name;
	}
	//-----------------------------------------------------------------------------
 /**
	* Добавление
	*/
	function insert()
	{
		global $Eresus;

		$item = array(
			'name' => arg('name', 'word'),
			'caption' => arg('caption', 'dbsafe'),
			'value' => arg('value', 'dbsafe'),
		);
		$Eresus->db->insert($this->table['name'], $item);
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------
 /**
	* Изменение
	*/
	function update()
	{
		global $Eresus;

		$item = $Eresus->db->selectItem($this->table['name'], "`name`='".arg('update', 'word')."'");
		$item['name'] = arg('name', 'word');
		$item['caption'] = arg('caption', 'dbsafe');
		$item['value'] = arg('value', 'dbsafe');

		$Eresus->db->updateItem($this->table['name'], $item, "`name`='".arg('update', 'word')."'");
		HTTP::redirect(arg('submitURL'));
	}
	//-----------------------------------------------------------------------------
 /**
	* Диплог добавления
	*
	* @return string
	*/
	function adminAddItem()
	{
		global $page;

		$form = array(
			'name' => 'AddForm',
			'caption' => strAdd,
			'width'=>'500px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'action', 'value' => 'insert'),
				array ('type' => 'edit', 'name' => 'name', 'label' => 'Имя $(', 'width' => '200px', 'maxlength' => '31', 'comment' => ')', 'pattern' => '/.+/', 'errormsg' => 'Не указано имя переменной'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Описание', 'width' => '100%', 'maxlength' => '63', 'pattern' => '/.+/', 'errormsg' => 'Не указано название переменной'),
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
	function adminEditItem()
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem($this->table['name'], "`name`='".arg('id', 'word')."'");
		$form = array(
			'name' => 'EditForm',
			'caption' => strEdit,
			'width' => '500px',
			'fields' => array (
				array ('type' => 'hidden', 'name' => 'update', 'value' => $item['name']),
				array ('type' => 'edit', 'name' => 'name', 'label' => 'Имя $(', 'width' => '200px', 'maxlength' => '31', 'comment' => ')', 'pattern' => '/.+/', 'errormsg' => 'Не указано имя переменной'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => 'Описание', 'width' => '100%', 'maxlength' => '63', 'pattern' => '/.+/', 'errormsg' => 'Не указано название переменной'),
				array ('type' => 'memo', 'name' => 'value', 'label' => 'Значение', 'height' => '10'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------
	function adminRender()
	{
		return $this->adminRenderContent();
	}
	//-----------------------------------------------------------------------------
	function clientOnPageRender($text)
	{
		global $Eresus;

		$items = $Eresus->db->select($this->table['name']);
		if (count($items)) foreach ($items as $item) {
			$text= str_replace('$('.$item['name'].')', $item['value'], $text);
		}
		return $text;
	}
	//-----------------------------------------------------------------------------
	function adminOnMenuRender()
	{
		global $page;

		$page->addMenuItem('Расширения', array ("access"  => EDITOR, "link"  => $this->name, "caption"  => 'Переменные', "hint"  => "Управление текстовыми переменными"));
	}
	//-----------------------------------------------------------------------------
}


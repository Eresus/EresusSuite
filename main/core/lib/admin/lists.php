<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
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
 * $Id: lists.php 594 2008-10-23 14:59:30Z mekras $
 */

class AdminList {
	var $columns = array();
	var $head = array();
	var $body = array();
	var $__controls = array(
		'add'           => array('image' => 'core/img/ctrl_add.gif', 'title' => strAdd, 'alt' => '+'),
		'edit' 	        => array('image' => 'core/img/ctrl_edit.gif', 'title' => strEdit, 'alt' => '&plusmn;'),
		'delete'        => array('image' => 'core/img/ctrl_delete.gif', 'title' => strDelete, 'alt' => 'X', 'onclick' => 'return askdel(this)'),
		'setup'         => array('image' => 'core/img/ctrl_setup.gif', 'title' => strProperties, 'alt' => '*'),
		'move'          => array('image' => 'core/img/ctrl_move.gif', 'title' => strMove, 'alt' => '-&gt;'),
		'on'        	  => array('image' => 'core/img/ctrl_off.gif', 'title' => admActivate, 'alt' => '0'),
		'off'       	  => array('image' => 'core/img/ctrl_on.gif', 'title' => admDeactivate, 'alt' => '1'),
		'position'      => array('image' => 'core/img/ctrl_up.gif', 'title' => admUp, 'alt' => '&uarr;'),
		'position_down' => array('image' => 'core/img/ctrl_down.gif', 'title' => admDown, 'alt' => '&darr;'),
		);
	/**
	 * Отрисовывает элемент управления
	 *
	 * @param string $type    Тип ЭУ (delete,toggle,move,custom...)
	 * @param string $href    Ссылка
	 * @param string $custom  Индивидуальные настройки
	 * @return  string  Отрисованный ЭУ
	 */
	function control($type, $href, $custom = array())
	{
		global $Eresus;

		if (isset($this->__controls[$type])) $control = $this->__controls[$type];
		switch($type) {
			case 'position':
				$s = array_pop($href);
				$href = $href[0];
			break;
		}
		foreach($custom as $key => $value) $control[$key] = $value;
		$result = '<a href="'.$href.'"'.(isset($control['onclick'])?' onclick="'.$control['onclick'].'"':'').'><img src="'.$Eresus->root.$control['image'].'" alt="'.$control['alt'].'" title="'.$control['title'].'" /></a>';
		if ($type == 'position') $result .= ' '.$this->control('position_down', $s, $custom);
		return $result;
	}
	//------------------------------------------------------------------------------
 /**
	* Устанавливает названия и параметры столбцов
	*
	* @access public
	*
	* @param  string  $text  Заголовок столбца
	* или
	* @param  array   $cell  Описание столбца
	*/
	function setHead()
	{
		$this->head = array();
		$items = func_get_args();
		if (count($items)) foreach($items as $item) {
			if (is_string($item)) $this->head[] = array('text' => $item);
			elseif (is_array($item)) $this->head[] = $item;
		}
	}
	//------------------------------------------------------------------------------
	/**
	* Устанавливает параметры столбца
	*
	* Перезаписываются только параметры указанные в $params
	*
	* @access public
	*
	* @param  int    $index  Номер столбца
	* @param  array  $params Описание столбца
	*/
	function setColumn($index, $params)
	{
		if (isset($this->columns[$index])) $this->columns[$index] = array_merge($this->columns[$index], $params);
		else $this->columns[$index] = $params;
	}
	//------------------------------------------------------------------------------
	/**
	* Добавляет строку в таблицу
	*
	* @access public
	*
	* @param  array  $cells  Ячейки строки
	*/
	function addRow($cells)
	{
		for($i=0; $i < count($cells); $i++) {
			if (!is_array($cells[$i])) $cells[$i] = array('text' => $cells[$i]);
			if (!isset($cells[$i]['text']) && isset($cells[$i][0])) {
				$cells[$i]['text'] = $cells[$i][0];
				unset($cells[$i][0]);
			}
		}
		$this->body[] = $cells;
	}
	//------------------------------------------------------------------------------
	/**
	* Добавляет строки в таблицу
	*
	* @access public
	*
	* @param  array  $rows  Строки
	*/
	function addRows($rows)
	{
		for($i=0; $i < count($rows); $i++) $this->addRow($rows[$i]);
	}
	//------------------------------------------------------------------------------
	/**
	* Отрисовывает ячейку таблицы
	*
	* @access  private
	*/
	function renderCell($tag, $cell)
	{
		$style = '';
		$text= isset($cell['text']) ? $cell['text'] : '';
		if (isset($cell['href'])) $text = '<a href="'.$cell['href'].'">'.$text.'</a>';
		if (isset($cell['align'])) $style .= 'text-align: '.$cell['align'].';';
		if (isset($cell['style'])) $style .= $cell['style'];
		$result = '<'.$tag.(empty($style)?'':" style=\"$style\"").'>'.$text.'</'.$tag.'>';
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	* Отрисовывает таблицу
	*
	* @return  string  HTML-код таблицы
	*/
	function render()
	{
		$thead = '';
		foreach($this->head as $cell) $thead .= $this->renderCell('th', $cell);
		$tbody = array();
		foreach($this->body as $row) {
			$cells = '<tr>';
			foreach($row as $cell) $cells .= $this->renderCell('td', $cell);
			$cells .= '</tr>';
			$tbody[] = $cells;
		}
		$table = '<table class="admList">';
		$table .= '<tr>'.$thead.'</tr>';
		$table .= implode("\n", $tbody);
		$table .= '</table>';
		return $table;
	}
	//------------------------------------------------------------------------------
}

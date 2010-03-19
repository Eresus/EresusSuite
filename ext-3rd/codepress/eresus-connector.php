<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * Коннектор к CodePress (Подсветка синтаксиса)
 *
 * @copyright 2004, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Project, http://eresus.ru/
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
 * @package Eresus2
 *
 * $Id$
 */

/**
 * Класс-коннектор
 *
 * @package Eresus2
 */
class CodepressConnector extends EresusExtensionConnector
{
	/**
	 *
	 * @param Form  $form
	 * @param array $field
	 * @return array
	 */
	public function forms_memo_syntax($form, $field)
	{
		global $page;

		if (!$field['id']) $field['id'] = $form->form['name'].'_'.$field['name'];
		$field['class'][] = 'codepress';
		$field['class'] = array_merge($field['class'], explode(' ', $field['syntax']));
    $form->onsubmit .=
        "\n    form.".$field['name'].".value = ".$field['id'].".getCode();\n".
        "    form.".$field['name'].".disabled = false;\n";
    $form->options['codepress'] = true;
    $page->linkScripts($this->root.'codepress.js');

		return $field;
	}
	//-----------------------------------------------------------------------------
}

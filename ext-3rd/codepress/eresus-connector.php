<?php
/**
 * Eresus 2.11
 *
 * Коннектор для Codepress
 *
 * Система управления контентом Eresus™ 2
 * © 2004-2007, ProCreat Systems, http://procreat.ru/
 * © 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */

/**
 * Класс-коннектор
 *
 */
class CodepressConnector extends EresusExtensionConnector {
	function forms_memo_syntax($form, $field)
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

?>
<?php
/**
 * Eresus 2.11
 *
 * Коннектор для TinyMCE
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
class TinyMCEConnector extends EresusExtensionConnector {
	function forms_html($form, $field)
	{
		global $Eresus, $page, $locale;

		$value = isset($form->values[$field['name']]) ? $form->values[$field['name']] : (isset($field['value'])?$field['value']:'');
		$preset = isset($field['preset']) ? $field['preset'] : 'default';
		$result = "\t\t".'<tr><td colspan="2">'.$field['label'].'<br /><textarea name="wyswyg_'.$field['name'].'" class="tinymce_'.$preset.'" cols="80" rows="25" style="width: 100%; height: '.$field['height'].';">'.str_replace('$(httpRoot)', $Eresus->root, EncodeHTML($value)).'</textarea></td></tr>'."\n";

		$page->linkScripts($this->root.'tiny_mce.js');
		$page->linkScripts($this->root.'presets/'.$preset.'.js');

		return $result;
	}
	//-----------------------------------------------------------------------------
}

?>
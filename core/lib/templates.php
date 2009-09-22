<?php
/**
 * Eresus 2.11
 *
 * Система управления контентом Eresus 2
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

class Templates {
	var $pattern = '/^<!--\s*(.+?)\s*-->.*$/s';
	/**
	 * Возвращает список шаблонов
	 *
	 * @param string $type Тип шаблонов (соответствует поддиректории в /templates)
	 * @return array
	 */
	function enum($type = '')
	{
		$result = array();
		$dir = filesRoot.'templates/';
		if ($type) $dir .= "$type/";
		$list = glob("$dir*.html");
		if ($list) foreach($list as $filename) {
			$file = file_get_contents($filename);
			$title = trim(substr($file, 0, strpos($file, "\n")));
			if (preg_match('/^<!-- (.+) -->/', $title, $title)) {
				$file = trim(substr($file, strpos($file, "\n")));
				$title = $title[1];
			} else $title = admNoTitle;
			$result[basename($filename, '.html')] = $title;
		}
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	 * Возвращает шаблон
	 *
	 * @param string $name  Имя шаблона
	 * @param string $type  Тип шаблона (соответствует поддиректории в /templates)
	 * @param bool   $array Вернуть шаблон в виде массива
	 * @return mixed Шаблон
	 */
	function get($name = '', $type = '', $array = false)
	{
		$result = false;
		if (empty($name)) $name = 'default';
		$filename = filesRoot.'templates/';
		if ($type) $filename .= "$type/";
		$filename .= "$name.html";
		$result = fileread($filename);
		if ($result) {
			if ($array) {
				$desc = preg_match($this->pattern, $result);
				$result = array(
					'name' => $name,
					'desc' => $desc ? preg_replace($this->pattern, '$1', $result) : admNA,
					'code' => $desc ? trim(substr($result, strpos($result, "\n"))) : $result,
				);
			} else {
				if (preg_match($this->pattern, $result)) $result = trim(substr($result, strpos($result, "\n")));
			}
		} else {
			if (empty($type) && $name != 'default') $result = $this->get('default', $type);
			#if (!$result) FatalError(sprintf(errTemplateNotFound, $name));
			if (!$result) $result = '';
		}
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	 * Новый шаблон
	 *
	 * @param string $name Имя шаблона
	 * @param string $type Тип шаблона (соответствует поддиректории в /templates)
	 * @param string $code Содержимое шаблона
	 * @param string $desc Описание шаблона (необязательно)
	 * @return bool Результат выполенния
	 */
	function add($name, $type, $code, $desc = '')
	{
		$result = false;
		$filename = filesRoot.'templates/';
		if ($type) $filename .= "$type/";
		$filename .= "$name.html";
		$content = "<!-- $desc -->\n\n$code";
		$result = filewrite($filename, $content);
		if ($result) {
			$message = admAdded.': '.$name;
			InfoMessage($message);
			SendNotify($message);
		} else {
			ErrorMessage(sprintf(errFileWrite, $filename));
		}
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	 * Изменяет шаблон
	 *
	 * @param string $name Имя шаблона
	 * @param string $type Тип шаблона (соответствует поддиректории в /templates)
	 * @param string $code Содержимое шаблона
	 * @param string $desc Описание шаблона (необязательно)
	 * @return bool Результат выполенния
	 */
	function update($name, $type, $code, $desc = null)
	{
		$result = false;
		$filename = filesRoot.'templates/';
		if ($type) $filename .= "$type/";
		$filename .= "$name.html";
		$item = $this->get($name, $type, true);
		$item['code'] = $code;
		if (!is_null($desc)) $item['desc'] = $desc;
		$content = "<!-- {$item['desc']} -->\n\n{$item['code']}";
		$result = filewrite($filename, $content);
		if ($result) {
			$message = admUpdated.': '.$name;
			#InfoMessage($message);
			SendNotify($message);
		} else {
			ErrorMessage(sprintf(errFileWrite, $filename));
		}
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	 * Удаляет шаблон
	 *
	 * @param string $name Имя шаблона
	 * @param string $type Тип шаблона (соответствует поддиректории в /templates)
	 * @return bool Результат выполенния
	 */
	function delete($name, $type = '')
	{
		$result = false;
		$filename = filesRoot.'templates/';
		if ($type) $filename .= "$type/";
		$filename .= "$name.html";
		$result = filedelete($filename);
		if ($result) {
			$message = admDeleted.': '.$name;
			InfoMessage($message);
			SendNotify($message);
		} else {
			ErrorMessage(sprintf(errFileDelete, $filename));
		}
		return $result;
	}
	//------------------------------------------------------------------------------
}

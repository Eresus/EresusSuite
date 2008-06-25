<?php
/**
 * Call
 *
 * Eresus 2
 *
 * Вызов других плагинов посредством макросов.
 *
 * $(call:плагин::метод{аргументы})
 *
 * @version 2.00
 *
 * @copyright   2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */

class Call extends Plugin {
	var $version = '2.00';
	var $kernel = '2.10b3';
	var $title = 'Call';
	var $description = 'Вызов плагинов из шаблонов';
	var $type = 'client';

 /**
	* Конструктор
	*
	* @return Call
	*/
	function Call()
	{
		parent::Plugin();
		$this->listenEvents('clientOnPageRender');
	}
	//-----------------------------------------------------------------------------
 /**
	* Обработчик события clientOnPageRender
	*
	* @param string $text
	* @return string
	*/
	function clientOnPageRender($text)
	{
		global $Eresus;

		preg_match_all('/\$\(call:(.*)(::(.*)({(.*)})?)?\)/Usi', $text, $calls, PREG_SET_ORDER);
		foreach($calls as $call) {

			$name = strtolower($call[1]);
			if (isset($Eresus->plugins->list[$name])) {

				$plugin = isset($Eresus->plugins->items[$name]) ? $Eresus->plugins->items[$name] : $Eresus->plugins->load($name);

				$method = count($call) > 3 ? strtolower($call[3]) : null;
				if ($method) {

					if (method_exists($plugin, $method)) {

						$args = count($call) > 5 ? $call[5] : null;
						$result = call_user_func(array($plugin, $method), $args);
						if (is_string($result)) $text = str_replace($call[0], $result, $text);

					} else ErrorMessage("Method '$method' not found in plugin '$name'");

				} else ErrorMessage("No method specified for plugin '$name'");

			} else ErrorMessage("Plugin '$name' not installed or disabled");
		}
		return $text;
	}
	//-----------------------------------------------------------------------------
}
?>
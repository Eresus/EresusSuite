<?php
/**
* HostRewrite, Eresus 2
*
* В некоторых сложных сетевых конфигурациях возможна ситуация когда значение
* $_SERVER['HTTP_HOST'] отличается от того, которое запросил ПА. В этом случае
* может понадобиться замена хоста в соответствии с определёнными правилами.
*
* УСТАНОВКА
* Скопируйте этот файл в любую директорию Eresus, например в /core/mod/
* В файл /cfg/main.inc добавьте строку:
*   require('../core/mod/hostrewrite.php');
*
* НАСТРОЙКА
* Скрипт читает правила из файла /cfg/hostrewrite
* Каждое правило записывается на новой строке
* Пустые строки и комментарии (все символы правее сивмола '#') игнорируются.
* Правила имеют следующий формат:
*   <маска> <замена>
* Количество пробельных сиволов до, после и между ними роли не играют.
* Маска может быть точным названием хоста или регулярным выражением PCRE,
* в этом случае оно должно быть записано в формате:
*   /выражение/модификаторы
*
* Применяется первое правило в списке, где маска совпадает с текущим хостом.
*
* ПРИМЕР
*  host123.example.com       example.org         # Простое изменения
*  /host(\d+)\.localhost/U   user$1.example.org  # С использованием PCRE
*
 * @copyright		2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */

$filename = '../cfg/hostrewrite';
if (is_file($filename)) {
	@$rules = file($filename);
	if ($rules) foreach($rules as $rule) {
		# Remove comments
		$rule = preg_replace('/#.*/', '', $rule);
		# Remove spaces from sides
		$rule = trim($rule);
		# Skip empty lines
		if (empty($rule)) continue;
		# Parse rule
		preg_match('/^(\S+)\s+(\S+)$/', $rule, $rule);
		if ($rule[1]{0} == '/') {
			# PCRE rewrite
			if (preg_match($rule[1], $_SERVER['HTTP_HOST'])) {
				$_SERVER['HTTP_HOST'] = preg_replace($rule[1], $rule[2], $_SERVER['HTTP_HOST']);
				break;
			}
		} else {
			# Simple change
			if ($_SERVER['HTTP_HOST'] == $rule[1]) {
				$_SERVER['HTTP_HOST'] = $rule[2];
				break;
			}
		}
	} else error_log('mod_hostrewrite: Can not read rules file "'.realpath($filename).'".');
} else error_log('mod_hostrewrite: rules file "'.realpath($filename).'" not found.');

unset($filename);
unset($rules);
unset($rule);

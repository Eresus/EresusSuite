<?php
/**
 * IIS, Eresus 2
 *
 * Исправляет переменные окружения для совместимости с Apache.
 *
 * УСТАНОВКА
 * Скопируйте этот файл в любую директорию Eresus, например в /core/mod/
 * В файл /cfg/main.inc добавьте строку:
 *   require_once(dirname(__FILE__).'\\..\\core\\mod\\iis.php');
 *
 * НАСТРОЙКА
 * Для правильной работы под IIS необходимо выставить в /cfg/main.inc
 * значение параметра $Eresus->path. Обычное значение '/'.
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

if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'off') unset($_SERVER['HTTPS']);
$_SERVER['DOCUMENT_ROOT'] = str_replace('\\\\', '\\', dirname($_SERVER['PATH_TRANSLATED']).'\\\\core');
$_SERVER['DOCUMENT_ROOT'] = preg_replace('!\\\\core$!i', '', $_SERVER['DOCUMENT_ROOT']);
if (isset($_SERVER['HTTP_X_REWRITE_URL'])) $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];

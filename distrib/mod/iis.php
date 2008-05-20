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
* @author Mikhail Krasilnikov <mk@procreat.ru>
* @version 0.0.1
* @modified 2007-07-30
*/

if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'off') unset($_SERVER['HTTPS']);
$_SERVER['DOCUMENT_ROOT'] = str_replace('\\\\', '\\', dirname($_SERVER['PATH_TRANSLATED']).'\\\\core');
$_SERVER['DOCUMENT_ROOT'] = preg_replace('!\\\\core$!i', '', $_SERVER['DOCUMENT_ROOT']);
if (!isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
die($_SERVER['REQUEST_URI']);
?>
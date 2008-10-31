<?php
/**
* IIS, Eresus 2
*
* ���������� ���������� ��������� ��� ������������� � Apache.
*
* ���������
* ���������� ���� ���� � ����� ���������� Eresus, �������� � /core/mod/
* � ���� /cfg/main.inc �������� ������:
*   require_once(dirname(__FILE__).'\\..\\core\\mod\\iis.php');
*
* ���������
* ��� ���������� ������ ��� IIS ���������� ��������� � /cfg/main.inc
* �������� ��������� $Eresus->path. ������� �������� '/'.
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
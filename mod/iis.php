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
 * @copyright		2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� (�� ������ ������) � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 */

if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'off') unset($_SERVER['HTTPS']);
$_SERVER['DOCUMENT_ROOT'] = str_replace('\\\\', '\\', dirname($_SERVER['PATH_TRANSLATED']).'\\\\core');
$_SERVER['DOCUMENT_ROOT'] = preg_replace('!\\\\core$!i', '', $_SERVER['DOCUMENT_ROOT']);
if (isset($_SERVER['HTTP_X_REWRITE_URL'])) $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];

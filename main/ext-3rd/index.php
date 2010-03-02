<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * ������������� �������� � ��������� ����������� ����� ����������
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
 *
 * $Id: index.php 703 2010-01-23 09:58:25Z mk $
 */

require dirname(__FILE__) . '/../core/kernel-legacy.php';

$extension = next($Eresus->request['params']);
$filename = $Eresus->froot.'ext-3rd/'.$extension.'/eresus-connector.php';
if ($extension && is_file($filename)) {
	include($filename);
	$className = $extension.'Connector';
	$connector = new $className;
	$connector->proxy();
} else {
	header('404 Not Found', true, 404);
	echo '404';
}
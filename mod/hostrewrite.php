<?php
/**
* HostRewrite, Eresus 2
*
* � ��������� ������� ������� ������������� �������� �������� ����� ��������
* $_SERVER['HTTP_HOST'] ���������� �� ����, ������� �������� ��. � ���� ������
* ����� ������������ ������ ����� � ������������ � ������������ ���������.
*
* ���������
* ���������� ���� ���� � ����� ���������� Eresus, �������� � /core/mod/
* � ���� /cfg/main.inc �������� ������:
*   require('../core/mod/hostrewrite.php');
*
* ���������
* ������ ������ ������� �� ����� /cfg/hostrewrite
* ������ ������� ������������ �� ����� ������
* ������ ������ � ����������� (��� ������� ������ ������� '#') ������������.
* ������� ����� ��������� ������:
*   <�����> <������>
* ���������� ���������� ������� ��, ����� � ����� ���� ���� �� ������.
* ����� ����� ���� ������ ��������� ����� ��� ���������� ���������� PCRE,
* � ���� ������ ��� ������ ���� �������� � �������:
*   /���������/������������
*
* ����������� ������ ������� � ������, ��� ����� ��������� � ������� ������.
*
* ������
*  host123.example.com       example.org         # ������� ���������
*  /host(\d+)\.localhost/U   user$1.example.org  # � �������������� PCRE
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

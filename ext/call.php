<?php
/**
 * Call
 *
 * Eresus 2
 *
 * ����� ������ �������� ����������� ��������.
 *
 * $(call:������::�����{���������})
 *
 * @version 2.00
 *
 * @copyright   2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� �� ������ ������ � ��������� ����� �������
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
 */

class Call extends Plugin {
	var $version = '2.00';
	var $kernel = '2.10b3';
	var $title = 'Call';
	var $description = '����� �������� �� ��������';
	var $type = 'client';

 /**
	* �����������
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
	* ���������� ������� clientOnPageRender
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
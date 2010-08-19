<?php
/**
 * Call
 *
 * ����� ������ �������� ����������� ��������.
 *
 * $(call:������::�����{���������})
 *
 * @version 2.01
 *
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @copyright 2009, ��� "��� �����", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
 * @package Call
 *
 * $Id$
 */

/**
 * �������� ����� �������
 *
 * @package Call
 */
class Call extends Plugin
{
	/**
	 * ������ �������
	 *
	 * @var string
	 */
	public $version = '2.01';

	/**
	 * ���������� ����������� ������ ����
	 *
	 * @var string
	 */
	public $kernel = '2.10b3';

	/**
	 * ��������
	 *
	 * @var string
	 */
	public $title = 'Call';

	/**
	 * ��������
	 *
	 * @var string
	 */
	public $description = '����� �������� �� ��������';

	/**
	 * ���
	 *
	 * @var string
	 */
	public $type = 'client';

 /**
	* �����������
	*
	* @return Call
	*/
	public function __construct()
	{
		parent::__construct();
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

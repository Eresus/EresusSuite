<?php
/**
 * Eresus CMS / Сборка «Два слона»
 *
 * @version ${product.version}
 *
 * @copyright 2011, ООО «Два слона», http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt GPL License 3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Eresus
 * @subpackage Tests
 * @author Михаил Красильников <mk@3wstyle.ru>
 *
 * $Id: Settings_Test.php 312 2012-04-14 05:28:48Z mk $
 */

require_once __DIR__ . '/../bootstrap.php';

class Admin_Settings_Test extends Eresus_Mink_TestCase
{
	/**
	 * Проверка основных функций
	 */
	public function test_basics()
	{
		$session = $this->getSession();
		$this->openSettingsPage($session);
		$page = $session->getPage();
		$page->find('xpath', '//form[@id="settingsForm"]//a[contains(text(), "Почта")]')->click();
		$this->assertElementOnPage($session, 'css', '#settingsFormTabs-btn-mail.ui-tabs-selected',
			'Не удалось попасть в раздел "Почта"');
		$page->find('xpath', '//form[@id="settingsForm"]//a[contains(text(), "Файлы")]')->click();
		$this->assertElementOnPage($session, 'css', '#settingsFormTabs-btn-files.ui-tabs-selected',
			'Не удалось попасть в раздел "Файлы"');
		$page->find('xpath', '//form[@id="settingsForm"]//a[contains(text(), "Прочее")]')->click();
		$this->assertElementOnPage($session, 'css', '#settingsFormTabs-btn-other.ui-tabs-selected',
			'Не удалось попасть в раздел "Прочее"');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Проверка вкладки "Основное"
	 */
	public function test_main()
	{
		$session = $this->getSession();
		$this->openSettingsPage($session);
		$page = $session->getPage();

		$testString = 'Новый сайт (' . uniqid() . ')';
		$page->fillField('siteName', $testString);
		$page->find('xpath', '//button[contains(text(), "Сохранить")]')->click();
		$this->assertElementOnPage($session, 'xpath',
			'//h1[contains(text(), "' . $testString . '")]');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Открывает страницу настроек
	 */
	public function openSettingsPage($session)
	{
		if (!$this->auth($session, 'root', ''))
		{
			$this->fail('Не удалось авторизоваться в АИ');
		}
		$page = $session->getPage();
		$page->findLink('Конфигурация')->click();
		$this->assertElementOnPage($session, 'named', array('field', 'siteName'),
			'Не удалось попасть в раздел "Конфигурация"');
	}
	//-----------------------------------------------------------------------------

	/* */
}

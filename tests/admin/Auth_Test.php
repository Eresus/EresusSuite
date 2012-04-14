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
 * $Id: Auth_Test.php 312 2012-04-14 05:28:48Z mk $
 */

require_once __DIR__ . '/../bootstrap.php';

class Admin_Auth_Test extends Eresus_Mink_TestCase
{
	/**
	 * Проверка авторизации пользователя root
	 */
	public function test_auth_root()
	{
		$session = $this->getSession();
		$session->visit('/admin/');
		$this->assertPageContainsText($session, 'Пароль:', 'Нет формы авторизации');
		$page = $session->getPage();
		$page->fillField('user', 'root');
		$page->fillField('password', '');
		$page->findField('autologin')->check();
		$page->find('xpath', '//button[@type="submit"]')->click();

		$exitButton = $page->find('css', '.user-box button');
		$this->assertCookieExists($session, 'sid', 'Нет куки sid');
		$this->assertCookieExists($session, 'eresus_login', 'Нет куки eresus_login');
		$this->assertCookieEquals($session, 'eresus_login', 'root',
			'Неправильное значение куки eresus_login');
		$exitButton->click();

		$this->assertPageContainsText($session, 'Пароль:', 'Не удалось выйти из системы');
	}
	//-----------------------------------------------------------------------------
}

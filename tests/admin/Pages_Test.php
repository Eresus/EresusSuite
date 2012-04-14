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
 * $Id: Pages_Test.php 312 2012-04-14 05:28:48Z mk $
 */

require_once __DIR__ . '/../bootstrap.php';

class Admin_Pages_Test extends Eresus_Mink_TestCase
{
	/**
	 * Проверка сохранения опций
	 */
	public function test_optionsSave()
	{
		$session = $this->getSession();
		$this->auth($session, 'root', '');

		$page = $session->getPage();
		$page->findLink('Разделы сайта')->click();
		$this->assertElementOnPage($session, 'xpath', '//h1[contains(text(), "Разделы сайта")]',
			'Не удалось попасть в раздел "Разделы сайта"');

		/* Открываем свойства главной страницы */
		$page->find('xpath',
			'//table[@class="admList"]//td[a[contains(text(), "Главная")]]/' .
			'following-sibling::td/a[1]')->click();
		$this->assertEquals('Главная', $page->findField('title')->getValue(),
			'Не удалось открыть свойства главной страницы');

		$input = $page->findField('options');
		$this->assertNotNull($input);
		$options = "key1=value1\nkey2=value2";
		$input->setValue($options);
		$page->find('xpath', '//button[contains(text(), "Применить")]')->click();
		$elem = $page->findField('options');
		$this->assertNotNull($elem, 'Отсутствует поле «Опции»');
		$this->assertEquals($options, $elem->getValue(), 'Неправильно сохраняются опции раздела');
	}
	//-----------------------------------------------------------------------------
	/* */
}

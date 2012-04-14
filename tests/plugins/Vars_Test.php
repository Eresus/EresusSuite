<?php
require_once __DIR__ . '/../bootstrap.php';

class Vars_Test extends Eresus_Mink_TestCase
{
	/**
	 */
	public function test_simple()
	{
		$admin = $this->getSession();
		$this->resetPlugin($admin, 'vars');
		$admin->getPage()->findLink('Переменные')->click();
		$admin->getPage()->find('xpath', '//a[text()="Добавить"]')->click();
		$admin->getPage()->fillField('name', 'var');
		$admin->getPage()->fillField('value', 'test_var');
		$admin->getPage()->fillField('caption', 'description');
		$admin->getPage()->find('xpath', '//button[contains(text(), "OK")]')->click();
		$this->assertPageContainsText($admin, '$(var)');
	}
	//-----------------------------------------------------------------------------
}

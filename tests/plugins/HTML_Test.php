<?php
require_once __DIR__ . '/../bootstrap.php';

class HTML_Test extends Eresus_Mink_TestCase
{
	/**
	 */
	public function test_simple()
	{
		$admin = $this->getSession();
		$this->resetPlugin($admin, 'html');
	}
	//-----------------------------------------------------------------------------
}

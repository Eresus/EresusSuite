<?php
/**
 * Eresus CMS
 *
 * @version ${product.version}
 *
 * @copyright 2012, ООО «Два слона», http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt GPL License 3
 * @author Михаил Красильников <mk@3wstyle.ru>
 *
 * $Id$
 */

use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\Mink\PHPUnit\TestCase;
use Behat\Mink\Driver\Selenium2Driver;

require_once 'mink/autoload.php';

/**
 * Сессия
 *
 * @package Eresus
 */
class MinkSession extends \Behat\Mink\Session
{
	/**
	 * Базовый URL
	 *
	 * @var string
	 */
	protected $baseURL = '';

	/**
	 * Устанавливает базовый URL
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	public function setBaseURL($url)
	{
		$this->baseURL = $url;
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see Behat\Mink.Session::visit()
	 */
	public function visit($url)
	{
		return parent::visit($this->baseURL . $url);
	}
	//-----------------------------------------------------------------------------
}

/**
 * Базовый набор для тестов
 *
 * @package Eresus
 * @subpackage Tests
 * @since 2.16-4
 */
class Eresus_Mink_TestCase extends TestCase
{
	/**
	 * Корневой URL
	 *
	 * @var string
	 */
	public static $defaultBaseURL = '';

	/**
	 * Папка для снимков экрана
	 *
	 * @var string
	 */
	public static $screenshots = '';

	/**
	 * Папка для исходников страниц
	 *
	 * @var string
	 */
	public static $pageSources = '';

	/**
	 * @see Behat\Mink\PHPUnit.TestCase::getSession()
	 */
	public function getSession($name = null)
	{
		$session = parent::getSession($name);
		$session->setBaseURL(self::$defaultBaseURL);
		return $session;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Compare cookie value with given one
	 *
	 * @param Behat\Mink\Session $session
	 * @param string             $name     cookie name
	 * @param string             $value    cookie value
	 * @param string             $message  message to show on error
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public static function assertCookieEquals(Session $session, $name, $value, $message = '')
	{
		self::assertThat($session->getCookie($name) == $value, self::isTrue(),
			$message ?: 'There is no cookie with name "' . $name. '" and value "' . $value .'"');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Создаёт новую сессию
	 *
	 * @param string $browser
	 *
	 * @return Session
	 */
	protected function createSession($name = 'default', $browser = 'chrome')
	{
		$session = new MinkSession(
			new Selenium2Driver($browser, null,
				'http://' . (getenv('selenium_host') ?: 'localhost') . ':' .
					(getenv('selenium_port') ?: 4444) .'/wd/hub'));
		$session->start();
		$this->getMink()->registerSession($name, $session);
		return $session;
	}
	//-----------------------------------------------------------------------------

	protected static function registerMinkSessions(Mink $mink)
	{
		parent::registerMinkSessions($mink);

		$session = new MinkSession(
			new Selenium2Driver('chrome', null,
				'http://' . (getenv('selenium_host') ?: 'localhost') . ':' .
					(getenv('selenium_port') ?: 4444) .'/wd/hub'));
		$session->start();
		$mink->registerSession('default', $session);
		$mink->setDefaultSessionName('default');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Авторизуется на сайте
	 *
	 * @param SeUnit\WebDriver\Session $session
	 * @param string                   $user
	 * @param string                   $password
	 *
	 * @return bool
	 */
	protected function auth(Session $session, $user, $password)
	{
		$session->setCookie('eresus_login', null);
		$session->setCookie('eresus_key', null);
		$session->visit('/admin/');
		$this->assertPageContainsText($session, 'Пароль:', 'Нет формы авторизации');
		$page = $session->getPage();
		$page->fillField('user', $user);
		$page->fillField('password', $password);
		$page->findField('autologin')->check();
		$page->find('xpath', '//button[@type="submit"]')->click();
		return (boolean) $page->find('css', '.user-box button');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет и заново устанавливает модуль
	 *
	 * @param SeUnit\WebDriver\Session $session
	 * @param string                   $name
	 *
	 * @return void
	 */
	protected function resetPlugin(Session $session, $name)
	{
		$this->auth($session, 'root', '');
		$session->visit('/admin/');
		$page = $session->getPage();
		$page->findLink('Модули расширения')->click();
		$deleteButton = $page->find('xpath', '//a[contains(@href, "delete=' . $name . '")]');
		if ($deleteButton)
		{
			$deleteButton->click();
			$session->acceptAlert();
		}

		$session->visit('/admin.php?mod=plgmgr&action=add');
		$page = $session->getPage();
		$page->find('xpath', '//a[contains(text(), "Снять все отметки")]')->click();
		$page->findField('files[' . $name . ']')->click();
		$page->find('xpath', '//button[contains(text(), "Добавить")]')->click();
		$this->assertPageNotContainsText($session, 'errorBox');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Save page source
	 *
	 * @param Session $session   session
	 * @param string  $filename  filename without directory and extension
	 *
	 * @return void
	 */
	protected function savePageSource(Session $session, $filename)
	{
		$html = $session->getPage()->getContent();
		$filename = (realpath(self::$pageSources) ?: getcwd()) . '/' . $filename;
		$filename = $this->makeArtifactFilename($filename, '.html');
		file_put_contents($filename, $html);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Take actions on exception in session
	 *
	 * @param Session   $session
	 * @param Exception $e
	 *
	 * @return void
	 */
	protected function exceptionThrown(Session $session, Exception $e)
	{
		$filename =
			str_replace('\\', '.', get_class($this)) .
			'::' . $this->getName(false) . '-' .
			str_replace('\\', '.', get_class($e));

		/*if ($this->screenshotOnError)
		{
			try
			{
				$this->saveScreenshot($session, $filename);
			}
			catch (Exception $e)
			{
				// do nothing for now… TODO Think about handle this
			}
		}*/

		/*if ($this->pageSourceOnError)
		{*/
			try
			{
				$this->savePageSource($session, $filename);
			}
			catch (Exception $e)
			{
				// do nothing for now… TODO Think about handle this
			}
		//}
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see PHPUnit_Framework_TestCase::runTest()
	 */
	protected function runTest()
	{
		try
		{
			return parent::runTest();
		}
		catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			foreach ($this->getSessions() as $name => $session)
			{
				if ($session->isStarted())
				{
					$this->exceptionThrown($session, $e);
				}
			}
			throw $e;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает активные сессии
	 *
	 * @return array
	 */
	private function getSessions()
	{
		$sessionsProperty = new ReflectionProperty('Behat\Mink\Mink', 'sessions');
		$sessionsProperty->setAccessible(true);
		$sessions = $sessionsProperty->getValue($this->getMink());
		return $sessions;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Returns unique name for artifact file
	 *
	 * @param string $basePart  directory and base name of file
	 * @param string $suffix    suffix (extension with dot char)
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	private function makeArtifactFilename($basePart, $suffix)
	{
		if (!file_exists($basePart  . $suffix))
		{
			return $basePart  . $suffix;
		}

		$idx = 1;
		while (file_exists($basePart . '-' . $idx . $suffix))
		{
			$idx++;
		}
		return $basePart . '-' . $idx . $suffix;
	}
	//-----------------------------------------------------------------------------
}






if (!getenv('selenium_site_root'))
{
	echo PHP_EOL;
	echo 'You must specify root URL of the test site in the environment variable "selenium_site_root":' . PHP_EOL;
	echo '  export selenium_site_root=http://example.org/;phpunit -c phpunit.xml' . PHP_EOL;
	echo PHP_EOL;
	echo 'Other options:' . PHP_EOL;
	echo '  selenium_host=<hostname> — Selenium Server host' . PHP_EOL;
	echo '  selenium_port=<nnnn> — Selenium Server port' . PHP_EOL;
	echo '  screenshots=<path> — Directory to store screenshots' . PHP_EOL;
	echo '  pagesoucres=<path> — Directory to store page sources' . PHP_EOL;
	echo PHP_EOL;
	die(-1);
}

if ($value = getenv('selenium_site_root'))
{
	Eresus_Mink_TestCase::$defaultBaseURL = $value;
}
/*if ($value = getenv('selenium_host'))
{
	Factory::set('server.host', $value);
}
if ($value = getenv('selenium_port'))
{
	Factory::set('server.port', $value);
}
if ($value = getenv('screenshots'))
{
	Eresus_Selenium_TestCase::$screenshots = realpath($value);
}*/
if ($value = getenv('pagesources'))
{
	Eresus_Mink_TestCase::$pageSources = realpath($value);
}


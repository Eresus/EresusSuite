<?php
/**
 * Eresus Core
 *
 * @version 0.1.2
 *
 * Compiled version
 *
 * @copyright 2007-2009, Eresus Project, http://eresus.ru/
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
 * @package Core
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * $Id: eresus-core.compiled.php 445 2009-12-23 06:43:56Z mk $
 */



/**
 * Eresus application prototype
 *
 * Must be overriden by user application class. See {@link main()} for
 * more details.
 *
 * @see main(), Core::exec()
 *
 * @package Core
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
abstract class EresusApplication {

	/**
	 * Holds path to application root directory
	 *
	 * Setting in {@link initFS()}. Use {@link getFsRoot()} to get this value.
	 *
	 * @var string
	 * @see getFsRoot(), initFS()
	 */
	protected $fsRoot;

	/**
	 * Main application function
	 *
	 * Developer must implement this method in his application.
	 *
	 * This method will be called by {@link Core::exec()}.
	 *
	 * <code>
	 * class MyApp extends EresusApplication {
	 *
	 *   public function main()
	 *   {
	 *     // Main code of your application goes here:
	 *     // 1. You can do some init tasks
	 *     // 2. You can do some usefull job ;-)
	 *     // 3. At the end you can do some finalizing tasks
	 *     return $exitCode;
	 *   }
	 * }
	 * </code>
	 *
	 * @return int  Exit code
	 * @see Core::exec()
	 */
	abstract public function main();
	//-----------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * 1. Inits FS related parts of application
	 * 2. If application has method called 'autoload' registers it through
	 *    {@link Core::registerAutoloader}
	 *
	 * There is no need to call constructor directly. It will be called
	 * automaticly from {@link Core::exec()}
	 *
	 * @return EresusApplication
	 * @see initFS(), Core::exec(), Core::registerAutoloader()
	 */
	function __construct()
	{

		$this->initFS();
		if (method_exists($this, 'autoload'))
			Core::registerAutoloader(array($this, 'autoload'));

	}
	//-----------------------------------------------------------------------------

	/**
	 * Init FS related parts of application
	 *
	 * - Sets {@link fsRoot} by calling {@link detectFsRoot}
	 *
	 * @return void
	 *
	 * @see fsRoot, detectFsRoot()
	 */
	protected function initFS()
	{
		$this->fsRoot = $this->detectFsRoot();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Trying to determine application root directory
	 *
	 * In CLI mode $GLOBALS['argv'][0] used.
	 *
	 * In other modes $_SERVER['SCRIPT_FILENAME'] used.
	 *
	 * @return string
	 * @see fsRoot, getFsRoot()
	 */
	protected function detectFsRoot()
	{
		eresus_log(__METHOD__, LOG_DEBUG, '()');
		$path = false;

		switch (true) {

			case PHP::isCLI():
				$path = reset($GLOBALS['argv']);
				eresus_log(__METHOD__, LOG_DEBUG, 'Using global $argv variable: %s', $path);
				$path = FS::canonicalForm($path);
				$path = FS::dirname($path);
			break;

			default:
				eresus_log(__METHOD__, LOG_DEBUG, 'Using $_SERVER["SCRIPT_FILENAME"]: %s',
					$_SERVER['SCRIPT_FILENAME']);

				$path = FS::canonicalForm($_SERVER['SCRIPT_FILENAME']);
				$path = FS::dirname($path);
				/*
				 * TODO: The CGI SAPI supports CLI SAPI behaviour with a -C switch
				 * when run from the command line.
				 */

		}

		$path = FS::normalize($path);
		eresus_log(__METHOD__, LOG_DEBUG, '"%s"', $path);

		return $path;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get application root directory
	 *
	 * @return string
	 * @see fsRoot
	 */
	public function getFsRoot()
	{
		return $this->fsRoot;
	}
	//-----------------------------------------------------------------------------

}

/**
 * DB module settings
 *
 * @package DB
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class DBSettings implements ezcBaseConfigurationInitializer
{
	/**
	 * DSN
	 * @var string
	 */
	static private $dsn;

	/**
	 * Codepage
	 * @var string
	 */
	static private $codepage = 'UTF8';

	/**
	 * Set DSN
	 *
	 * @param string $dsn
	 */
	public static function setDSN($dsn)
	{
		self::$dsn = $dsn;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Set code page
	 *
	 * @param string $codepage
	 */
	public static function setCodepage($codepage)
	{
		self::$codepage = $codepage;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ezComponents lazy init
	 *
	 * @param unknown $instance
	 * @return unknown_type
	 * @internal
	 * @ignore
	 */
	public static function configureObject($instance)
	{
		eresus_log(__METHOD__, LOG_DEBUG, '(%s)', $instance);

		switch ( $instance ) {

			case false:

				if (self::$dsn) {

					$dsn = self::$dsn;
					$codepage = self::$codepage;

				} else {

					eresus_log(__METHOD__, LOG_DEBUG, 'null');
					return null;

				}

				eresus_log(__METHOD__, LOG_DEBUG, 'Using DSN: %s', $dsn);
				$db = ezcDbFactory::create($dsn);

				#FIXME Next may be valid only for MySQL
				try {

					if ($codepage)
						$db->query('SET NAMES ' . $codepage);

				} catch (Exception $e) {}

				return $db;
		}
	}
	//-----------------------------------------------------------------------------
}



/**
 * DB Runtime Exception
 *
 * @package DB
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class DBRuntimeException extends EresusRuntimeException {
}



/**
 * DB Query Exception
 *
 * @package DB
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class DBQueryException extends DBRuntimeException {

	/**
	 * Creates new exception object
	 *
	 * @param ezcQuery  $query [optional]    Problem query
	 * @param string    $message [optional]  Error message
	 * @param Exception $previous [optional] Previous exception
	 */
	function __construct($query = null, $message = null, $previous = null)
	{
		if ($query instanceof ezcQuery) {

			$insider = new DBQueryInsider;
			$query->doBind($insider);
			$query = $insider->subst($query);
		}

		if (is_null($message))
			$message = 'Database query failed';

		if (!is_null($previous))
			$query = $previous->getMessage() . ': ' . $query;

		parent::__construct($query, $message, $previous);
	}
	//-----------------------------------------------------------------------------

}



/**
 * Database interface
 *
 * @package DB
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 */
class DB {

	/**
	 * Instance to use for testing
	 * @var object
	 */
	private static $testInstance;

	/**
	 * Set test instance
	 * @param object $instance
	 */
	public static function setTestInstance($instance)
	{
		self::$testInstance = $instance;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get connection
	 *
	 * @return object
	 */
	public static function getInstance()
	{
		if (!self::$testInstance) return ezcDbInstance::get();

		return self::$testInstance;
	}
	//-----------------------------------------------------------------------------

	/**
	 * SELECT
	 * @return ezcQuerySelect
	 */
	public static function createSelectQuery()
	{
		$db = self::getInstance();
		return $db->createSelectQuery();
	}
	//-----------------------------------------------------------------------------

	/**
	 * UPDATE
	 * @return ezcQueryUpdate
	 */
	public static function createUpdateQuery()
	{
		$db = self::getInstance();
		return $db->createUpdateQuery();
	}
	//-----------------------------------------------------------------------------

	/**
	 * INSERT
	 * @return ezcQueryInsert
	 */
	public static function createInsertQuery()
	{
		$db = self::getInstance();
		return $db->createInsertQuery();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Delete
	 * @return ezcQueryDelete
	 */
	public static function createDeleteQuery()
	{
		$db = self::getInstance();
		return $db->createDeleteQuery();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Execute query
	 * @param ezQuery $query
	 * @return mixed
	 */
	public static function execute($query)
	{
		try {

			$stmt = $query->prepare();
			if (LOG_DEBUG) {
				$insider = new DBQueryInsider;
				$query->doBind($insider);
				$s = $insider->subst($query);
				eresus_log(__METHOD__, LOG_DEBUG, 'Query "%s"', $s);
			}
			$result = $stmt->execute();

		} catch (Exception $e) {

			throw new DBQueryException($query, null, $e);

		}

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Fetch row from DB response
	 * @param ezcQuery $query
	 * @return array
	 */
	public static function fetch($query)
	{
		if (LOG_DEBUG) {
			$insider = new DBQueryInsider;
			$query->doBind($insider);
			$s = $insider->subst($query);
			eresus_log(__METHOD__, LOG_DEBUG, 'Query "%s"', $s);
		}
		$stmt = $query->prepare();

		try {

			$stmt->execute();

		} catch (Exception $e) {

			throw new DBQueryException($query, null, $e);

		}

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get response rows
	 * @param ezcQuery $query
	 * @return array
	 */
	public static function fetchAll($query)
	{
		if (LOG_DEBUG) {
			$insider = new DBQueryInsider;
			$query->doBind($insider);
			$s = $insider->subst($query);
			eresus_log(__METHOD__, LOG_DEBUG, 'Query "%s"', $s);
		}

		$stmt = $query->prepare();

		try {

			$stmt->execute();

		} catch (Exception $e) {

			throw new DBQueryException($query, null, $e);

		}

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
	//-----------------------------------------------------------------------------

}



/**
 * Query Insider
 *
 * Internal class for substitution in doBind method to get values
 * set with bindValue or bindParam methods.
 *
 * @package DB
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 * @internal
 * @ignore
 *
 */
class DBQueryInsider extends PDOStatement {

	/**
	 * Values
	 * @var array
	 */
	protected $values = array();

	/**
	 * Bind value
	 *
	 * @param paramno
	 * @param param
	 * @param type[optional]
	 */
	public function bindValue($paramno, $param, $type = null)
	{
		switch ($type) {

			case PDO::PARAM_BOOL:
			break;

			case PDO::PARAM_INT:
			break;

			case PDO::PARAM_STR:
				$param = is_null($param) ?
					'NULL' :
					"'" . addslashes($param) . "'";
			break;
		}

		$this->values[$paramno] = $param;

	}
	//-----------------------------------------------------------------------------

	/**
	 * Bind param
	 *
	 * @param paramno
	 * @param param
	 * @param type[optional]
	 * @param maxlen[optional]
	 * @param driverdata[optional]
	 */
	public function bindParam($paramno, &$param, $type = null, $maxlen = null, $driverdata = null)
	{
		$this->bindValue($paramno, $param, $type);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Substitute values in query
	 *
	 * @param string $query
	 * @return string
	 */
	public function subst($query)
	{
		foreach ($this->values as $key => $value)
			$query = preg_replace("/$key(\s|,|$)/", "$value$1", $query);

		return $query;
	}
	//-----------------------------------------------------------------------------

}

ezcBaseInit::setCallback('ezcInitDatabaseInstance', 'DBSettings');


/**
 * Including Dwoo
 */
include_once '3rdparty/dwoo/dwooAutoload.php';

/**
 * Template package settings
 *
 * This class can be used to configure behavor of the Template package.
 *
 * @package Template
 *
 */
class TemplateSettings {

	/**
	 * Global substitution value to be used in all templates
	 * @var array
	 */
	private static $gloablValues = array();

	/**
	 * Set global substitution value to be used in all templates
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public static function setGlobalValue($name, $value)
	{
		self::$gloablValues[$name] = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get global substitution value
	 *
	 * @param string $name
	 * @return null|mixed  Null will be returned if value not set
	 */
	public static function getGlobalValue($name)
	{
		return ecArrayValue(self::$gloablValues, $name);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Remove global substitution value
	 *
	 * @param string $name
	 */
	public static function removeGlobalValue($name)
	{
		if (isset(self::$gloablValues[$name])) unset(self::$gloablValues[$name]);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get all global substitution values
	 *
	 * @return array
	 */
	public static function getGlobalValues()
	{
		return self::$gloablValues;
	}
	//-----------------------------------------------------------------------------
}


/**
 * Template file
 *
 * @package Template
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class TemplateFile extends Dwoo_Template_File {

}


/**
 * Template
 *
 * <b>CONFIGURATION</b>
 * Templte uses Core::getValue to read its configuration:
 *
 * <b>core.template.templateDir</b>
 * Directory where templates located.
 *
 * <b>core.template.compileDir</b>
 * Directory to store compiled templates.
 *
 * <b>core.template.charset</b>
 * Charset of template files.
 *
 * <b>core.template.fileExtension</b>
 * Default extensions of template files.
 *
 * @package Template
 */
class Template
{
	/**
	 * Dwoo object
	 * @var Dwoo
	 */
	protected $dwoo;

	/**
	 * Template file object
	 * @var TemplateFile
	 */
	protected $file;

	/**
	 * Constructor
	 * @var string $filename [optional]  Template file name
	 */
	public function __construct($filename = null)
	{
		$compileDir = $this->detectCompileDir();
		$compileDir = FS::nativeForm($compileDir);
		$this->dwoo = new Dwoo($compileDir);

		if (Core::getValue('core.template.charset'))
			$this->dwoo->setCharset(Core::getValue('core.template.charset'));

		if ($filename) $this->loadFile($filename);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Load template file
	 * @param string $filename  Template file name
	 */
	public function loadFile($filename)
	{
		$templateDir = $this->detectTemplateDir();
		$fileExtension = $this->detectFileExtension();
		$templateDir = FS::normalize($templateDir);
		$template = $templateDir . '/' . $filename . $fileExtension;
		$template = FS::nativeForm($template);
		$this->file = new TemplateFile($template, null, $filename, $filename);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Compile template
	 *
	 * @param array $data [optional]  Data for template
	 *
	 * @return string
	 */
	function compile($data = null)
	{
		if ($data)
			$data = array_merge($data, TemplateSettings::getGlobalValues());
		else
			$data = TemplateSettings::getGlobalValues();

		return $this->dwoo->get($this->file, $data);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Detect directory where templates located
	 *
	 * @return string
	 */
	protected function detectTemplateDir()
	{
		$compileDir = Core::getValue('core.template.templateDir', '');

		return $compileDir;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Detect template files extension
	 *
	 * @return string
	 */
	protected function detectFileExtension()
	{
		$fileExtension = Core::getValue('core.template.fileExtension', '');

		return $fileExtension;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Detect directory where compiled templates will be stored
	 *
	 * @return string
	 */
	protected function detectCompileDir()
	{
		$compileDir = Core::getValue('core.template.compileDir', '');

		return $compileDir;
	}
	//-----------------------------------------------------------------------------
}


/**
 * HTTP Headers
 *
 * @package HTTP
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class HttpHeaders {

	/**
	 * Headers
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Constructor
	 */
	function __construct()
	{
	}
	//-----------------------------------------------------------------------------

	/**
	 * Add header
	 * @param HttpHeader $header
	 */
	function add($header)
	{
		$this->headers []= $header;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get all headers
	 * @return array
	 */
	function getAll()
	{
		return $this->headers;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Send headers to UA
	 */
	public function send()
	{
		$headers = $this->getAll();

		foreach($headers as $header) $header->send();
	}
	//-----------------------------------------------------------------------------
}
//-----------------------------------------------------------------------------

/**
 * HTTP Header
 *
 * @package HTTP
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class HttpHeader {

	/**
	 * Header name
	 * @var string
	 */
	protected $name;

	/**
	 * Header value
	 * @var string
	 */
	protected $value;

	/**
	 * Constructor
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Return header as string
	 * @return string
	 */
	public function __toString()
	{
		return $this->name . ': ' . $this->value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Send header to UA
	 */
	public function send()
	{
		if (!PHP::isCLI()) header($this);
	}
	//-----------------------------------------------------------------------------
}
//-----------------------------------------------------------------------------


/**
 * HTTP Toolkit
 *
 * @package HTTP
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class HTTP {

	/**
	 * HTTP request object
	 * @var HTTPRequest
	 */
	static private $request;

	/**
	 * Sets test instance of HttpRequest
	 *
	 * @param HttpRequest|null $request
	 */
	static public function setTestRequest($request)
	{
		if (Core::testMode())
			self::$request = $request;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Returns an instance of a HttpRequest class
	 *
	 * Object instancing only once
	 *
	 * @return HttpRequest
	 */
	static public function request()
	{
		if (!self::$request) self::$request = new HttpRequest();
		return self::$request;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Redirect UA to another URI and terminate program
	 *
	 * @param string $uri                  New URI
	 * @param bool   $permanent[optional]  Send '301 Moved permanently'
	 */
	static public function redirect($uri, $permanent = false)
	{
		eresus_log(__METHOD__, LOG_DEBUG, $uri);

		$header = 'Location: '.$uri;

		if ($permanent)
			header($header, true, 301);
		else
			header($header);

		if (!Core::testMode()) exit;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Redirect UA to previous URI
	 *
	 * Method uses $_SERVER['HTTP_REFERER'] to determine previous URI. If this
	 * variable not set then method will do nothing. In last case developers can
	 * use next scheme:
	 *
	 * <code>
	 *  # ...Some actions...
	 *
	 * 	HTTP::goback();
	 *  HTTP::redirect('some_uri');
	 * </code>
	 *
	 * So if there is nowhere to go back user will be redirected to some fixed URI.
	 *
	 * @see redirect
	 */
	static public function goback()
	{
		if (isset($_SERVER['HTTP_REFERER']))
			self::redirect($_SERVER['HTTP_REFERER']);
	}
	//-----------------------------------------------------------------------------
}
//-----------------------------------------------------------------------------

/**
 * HTTP Request
 *
 * @package HTTP
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class HttpRequest {
	/**
	 * Parsed HTTP request
	 * @var array
	 */
	protected $request = array();

	/**
	 * Local URI root
	 * @var string
	 * @see getLocal
	 */
	protected $localRoot = '';

	/**
	 * Constructor
	 *
	 * @param string|HTTPRequest $source [optional]  Source for request
	 *
	 * @throws EresusTypeException
	 */
	function __construct($source = null)
	{
		switch (true) {

			case is_object($source) && $source instanceof HttpRequest:
				$this->request = $source->toArray();
			break;

			case is_string($source):
				$this->request = @parse_url($source);
				$this->request['local'] = $this->getPath();
				if ($this->getQuery()) {
					$this->request['local'] .= '?' . $this->getQuery();
					parse_str($this->getQuery(), $this->request['args']);
					if (Core::testModeGet('magic_quotes_gpc') && !get_magic_quotes_gpc()) {
						/* Emulating parse_str behavor... */
						foreach ($this->request['args'] as $key => $value)
							$this->request['args'][$key] = addslashes($value);
					}
					if (
						$this->request['args'] &&
						(get_magic_quotes_gpc() || Core::testModeGet('magic_quotes_gpc'))
					)
						$this->request['args'] = ecStripSlashes($this->request['args']);
				}
			break;

			case is_null($source):
				if (!PHP::isCLI()) {
					if (isset($_SERVER['REQUEST_URI'])) $this->request = @parse_url($_SERVER['REQUEST_URI']);
					$this->request['local'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
					$this->request['args'] = $_POST;
					foreach($_GET as $key => $value)
						if (!isset($this->request['args'][$key]))
							$this->request['args'][$key] = $value;

					if (
						$this->request['args'] &&
						(get_magic_quotes_gpc() || Core::testModeGet('magic_quotes_gpc'))
					)
						$this->request['args'] = ecStripSlashes($this->request['args']);

				}
			break;

			default:
				throw new EresusTypeException($source, 'HttpRequest, string or NULL');
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Return current request as array
	 * @return array
	 * @internal
	 * @ignore
	 */
	public function toArray()
	{
		return $this->request;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get protocol scheme
	 * @return string
	 */
	public function getScheme()
	{
		if (!isset($this->request['scheme'])) {

			$this->request['scheme'] = 'http';

		}

		$result = $this->request['scheme'];

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get request method
	 * @return string
	 */
	public function getMethod()
	{
		if (!isset($this->request['method'])) {

			$this->request['method'] = isset($_SERVER['REQUEST_METHOD']) ?
				strtoupper($_SERVER['REQUEST_METHOD']) :
				'GET';

		}

		$result = $this->request['method'];

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Set request method
	 *
	 * @param string $value
	 */
	public function setMethod($value)
	{
		$this->request['method'] = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get host from request
	 * @return string
	 */
	public function getHost()
	{
		if (!isset($this->request['host'])) {

			$this->request['host'] = isset($_SERVER['HTTP_HOST']) ?
				strtolower($_SERVER['HTTP_HOST']) :
				'localhost';

		}

		$result = $this->request['host'];

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get path (directory and filename) from request
	 * @return string
	 */
	public function getPath()
	{
		if (!isset($this->request['path'])) {

			$this->request['path'] = '/';

		}

		$result = $this->request['path'];

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get directory name from request
	 * @return string
	 */
	public function getDirectory()
	{
		if (!isset($this->request['directory'])) {

			/*
			 * dirname can ommit last directory if path does not contain file name.
			 * To avoid this we can check trailing slash.
			 */
			$path = $this->getPath();
			$this->request['directory'] = substr($path, -1) == '/' ? $path : dirname($path) . '/';

		}

		$result = $this->request['directory'];

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get file name (without directory) from request
	 * @return string
	 */
	public function getFile()
	{
		if (!isset($this->request['file'])) {

			$this->request['file'] = basename($this->getPath());

		}

		$result = $this->request['file'];

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get query (after the question mark "?")
	 * @return string
	 */
	public function getQuery()
	{
		if (!isset($this->request['query'])) {

			$this->request['query'] = '';

		}

		$result = $this->request['query'];

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Return all values of GET or POST arguments
	 * @return array
	 */
	public function getArgs()
	{
		$result = $this->request['args'];

		if (get_magic_quotes_gpc() || Core::testModeGet('magic_quotes_gpc'))
			$result = array_map('stripslashes', $result);

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Return value of GET or POST argument
	 * @param string $arg  Atgument name
	 * @return mixed
	 */
	public function arg($arg)
	{
		$result = isset($this->request['args'][$arg]) ? $this->request['args'][$arg] : null;


		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see function arg
	 */
	public function getArg($arg)
	{
		return $this->arg($arg);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Set value of GET or POST argument
	 *
	 * @param string $arg
	 * @param mixed  $value
	 */
	public function setArg($arg, $value)
	{
		$this->request['args'][$arg] = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get local part of URI
	 * @return string
	 */
	public function getLocal()
	{
		$result = $this->request['local'];

		if ($this->localRoot && strpos($result, $this->localRoot) === 0)
			$result = substr($result, strlen($this->localRoot));

		if ($result === false) return '';
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Return full URI
	 * @return string
	 */
	public function __toString()
	{
		$request = $this->getScheme().'://'.$this->getHost().$this->getPath();
		if ($this->getQuery()) $request .= '?' . $this->getQuery();
		return $request;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Set local root
	 *
	 * Local root is a part of URI after host name which will be cutted from result
	 * of HttpRequest::getLocal.
	 *
	 * <code>
	 * $req = new HttpRequest('http://example.org/some/path/script?query');
	 * echo $req->getLocal(); // '/some/path/script?query'
	 * $req->setLocalRoot('/some');
	 * echo $req->getLocal(); // '/path/script?query'
	 * </code>
	 *
	 * @param string $root
	 */
	public function setLocalRoot($root)
	{
		$this->localRoot = $root;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Get local root
	 * @return string
	 */
	public function getLocalRoot()
	{
		return $this->localRoot;
	}
	//-----------------------------------------------------------------------------

}
//-----------------------------------------------------------------------------




/**
 * HTTP Response
 *
 * @package HTTP
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */
class HttpResponse {

	/**
	 * HTTP headers
	 * @var HttpHeaders
	 */
	private $headers;

	/**
	 * Response body
	 *
	 * Response body must be a string or object with __toString method defined
	 *
	 * @var string|object
	 */
	private $body;

	/**
	 * Constructor
	 *
	 * @param string|object $body
	 */
	function __construct($body = null)
	{
		if (!is_null($body)) $this->body = $body;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Magic property getter
	 * @param string $property
	 * @return mixed
	 */
	public function __get($property)
	{
		switch ($property) {

			case 'headers':
				if (!$this->headers) $this->headers = new HttpHeaders();
				return $this->headers;
			break;

		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Set response body
	 * @param string|object $body
	 */
	public function setBody($body)
	{
		$this->body = $body;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Send reponse
	 */
	public function send()
	{
		if ($this->headers) $this->headers->send();
		echo $this->body;
	}
	//-----------------------------------------------------------------------------

}
//-----------------------------------------------------------------------------


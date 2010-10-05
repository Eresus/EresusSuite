<?php
/**
 * Каталог товаров
 *
 * Прототип службы шаблонов
 *
 * @version 1.00
 *
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author Михаил Красильников <mk@3wstyle.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо (по вашему выбору) с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 *
 * @package GoodsCatalog
 *
 * $Id: TemplateService.php 992 2010-10-01 09:28:21Z mk $
 */


/**
 * Прототип службы шаблонов
 *
 * @package GoodsCatalog
 *
 * @since 1.00
 */
class GoodsCatalogTemplateService
{
	/**
	 * Экземпляр-одиночка
	 * @var GoodsCatalogTemplateService
	 */
	protected static $instance;

	/**
	 * Путь к корневой директории шаблонов
	 * @var string
	 */
	private $rootDir;

	/**
	 * Конструктор
	 *
	 * @return GoodsCatalogTemplateService
	 */
	private function __construct()
	{
		$this->rootDir = $GLOBALS['Eresus']->froot . 'templates';
	}
	//-----------------------------------------------------------------------------

	/**
	 * Заглушка, запрещающая клонирование
	 *
	 * @return GoodsCatalogTemplateService
	 */
	private function __clone()
	{
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает экземпляр класса
	 *
	 * @return GoodsCatalogTemplateService
	 */
	public static function &getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает шаблоны в общую директорию шаблонов
	 *
	 * @param string $sourceDir   Абсолютный путь к директории с устанавливаемым шаблонами
	 * @param string $targetPath  Путь относительно общей директории шаблонов
	 *
	 * @throws GoodsCatalogTemplateInvalidPathException  Если указан неправильный путь
	 * @throws GoodsCatalogTemplatePathExistsException  Если $path уже существует
	 */
	public function installTemplates($sourceDir, $targetPath)
	{
		if (!is_dir($sourceDir))
		{
			throw new GoodsCatalogTemplateInvalidPathException(
				'Source path not exists or not a directory: ' .	$sourceDir);
		}

		$targetPath = $this->rootDir . '/' . $targetPath;
		if (file_exists($targetPath))
		{
			throw new GoodsCatalogTemplatePathExistsException(
				'Target path already exists: ' .	$targetPath);
		}

		try
		{
			$umask = umask(0000);
			mkdir($targetPath, 0777, true);
			umask($umask);
		}
		catch (Exception $e)
		{
			throw new GoodsCatalogTemplateException('Can not create target directory: ' . $targetPath,
				null, $e);
		}

		$templates = new RegexIterator(new DirectoryIterator($sourceDir),
			'/^.*\.html$/', RegexIterator::GET_MATCH);
		/*
		 * Начиная с PHP 5.3.0 можно будет использовать GlobIterator:
		 * GlobIterator($sourceDir . '/*.html', FilesystemIterator::KEY_AS_PATHNAME);
		 */

		foreach ($templates as $template)
		{
			$target = $targetPath . '/' . basename($template[0]);

			copy($sourceDir . '/' . $template[0], $target);
			chmod($target, 0666);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет установленные ранее шаблоны
	 *
	 * @param string $path  Путь шаблону или директории относительно общей директории шаблонов
	 *
	 * @throws GoodsCatalogTemplateInvalidPathException  Если указан неправильный путь
	 */
	public function uninstall($path)
	{
		$path = $this->rootDir . '/' . $path;
		if (!file_exists($path))
		{
			throw new GoodsCatalogTemplateInvalidPathException(
				'Uninstall path not exists: ' .	$path);
		}

		if (is_file($path))
		{
			unlink($path);
		}
		else
		{
			$branch = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path),
				RecursiveIteratorIterator::SELF_FIRST);

			$files = array();
			$dirs = array();
			foreach ($branch as $file)
			{
				if ($file->isDir())
				{
					$dirs []= $file->getPathname();
				}
				else
				{
					$files []= $file->getPathname();
				}
			}

			/* Вначале удаляем все файлы */
			foreach ($files as $file)
			{
				unlink($file);
			}
			/* Теперь удаляем директории, начиная с самых глубоких */
			for ($i = count($dirs) - 1; $i >= 0; $i--)
			{
				rmdir($dirs[$i]);
			}
			rmdir($path);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает содержимое шаблона
	 *
	 * @param string $name              Имя файла шаблона
	 * @param string $prefix[optional]  Опциональный префикс (путь относительно корня шаблонов)
	 *
	 * @return string
	 *
	 * @throws GoodsCatalogTemplateInvalidPathException
	 */
	public function getContents($name, $prefix = '')
	{
		$path = $this->rootDir . '/' . $this->getFilename($name, $prefix);

		if (!is_file($path))
		{
			throw new GoodsCatalogTemplateInvalidPathException('Template not exists: ' .	$path);
		}

		$contents = file_get_contents($path);

		return $contents;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Записывает содержимое шаблона
	 *
	 * @param string $contents          Содержимое шаблона
	 * @param string $name              Имя файла шаблона
	 * @param string $prefix[optional]  Опциональный префикс (путь относительно корня шаблонов)
	 *
	 * @return void
	 *
	 * @throws GoodsCatalogTemplateInvalidPathException
	 */
	public function setContents($contents, $name, $prefix = '')
	{
		$path = $this->rootDir . '/' . $this->getFilename($name, $prefix);

		if (!is_file($path))
		{
			throw new GoodsCatalogTemplateInvalidPathException('Template not exists: ' .	$path);
		}

		@file_put_contents($path, $contents);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает путь к файлу шаблона
	 *
	 * @param string $name              Имя файла шаблона
	 * @param string $prefix[optional]  Опциональный префикс (путь относительно корня шаблонов)
	 *
	 * @return string
	 */
	public function getFilename($name, $prefix = '')
	{
		$path = $name;
		if ($prefix != '')
		{
			$path = $prefix . '/' . $path;
		}

		return $path;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объект шаблона
	 *
	 * @param string $name              Имя файла шаблона
	 * @param string $prefix[optional]  Опциональный префикс (путь относительно корня шаблонов)
	 *
	 * @return Template
	 *
	 * @throws GoodsCatalogTemplateInvalidPathException
	 */
	public function getTemplate($name, $prefix = '')
	{
		$path = $this->getFilename($name, $prefix);

		if (!is_file($this->rootDir . '/' . $path))
		{
			throw new GoodsCatalogTemplateInvalidPathException('Template not exists: ' .	$path);
		}

		$tmpl = new Template('templates/' . $path);

		return $tmpl;
	}
	//-----------------------------------------------------------------------------

}




/**
 * Ошибка при работе с шаблонами
 *
 * @package GoodsCatalog
 */
class GoodsCatalogTemplateException extends EresusRuntimeException {}



/**
 * Неправильный путь к шаблону
 *
 * @package GoodsCatalog
 */
class GoodsCatalogTemplateInvalidPathException extends GoodsCatalogTemplateException {}



/**
 * Путь уже существует
 *
 * @package GoodsCatalog
 */
class GoodsCatalogTemplatePathExistsException extends GoodsCatalogTemplateException {}
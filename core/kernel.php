<?php
/**
 * Eresus 2.11
 *
 * Система управления контентом Eresus 2
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
 * $Id$
 */

define('CMSNAME', 'Eresus'); # Название системы
define('CMSVERSION', '2.11'); # Версия системы
define('CMSLINK', 'http://eresus.ru/'); # Веб-сайт

define('KERNELNAME', 'ERESUS'); # Имя ядра
define('KERNELDATE', '2009-09-21'); # Дата обновления ядра

# Уровни доступа
define('ROOT',   1); # Главный администратор
define('ADMIN',  2); # Администратор
define('EDITOR', 3); # Редактор
define('USER',   4); # Пользователь
define('GUEST',  5); # Гость (не зарегистрирован)

if (!defined('FILE_APPEND')) define('FILE_APPEND', 8);

###cut:start (testing purpose)
/**
 * Функция выводит сообщение о пользовательской ошибке и прекращает работу скрипта.
 *
 * @param string $msg  Текст сообщения
 */
function FatalError($msg)
{
	if (PHP_SAPI == 'cli') {
		$result = strip_tags(preg_replace('!<br(\s/)?>!i', "\n", $msg))."\n";
	} else {
	$result =
		"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n\n".
		"<html>\n".
		"<head>\n".
		"  <title>".errError."</title>\n".
		"  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=".CHARSET."\">\n".
		"</head>\n\n".
		"<body>\n".
		"  <div align=\"center\" style=\"font-family: Arial, Helvetica, sans-serif;\">\n".
		"    <table cellspacing=\"0\" style=\"border-style: solid;  border-color: #e88 #800 #800 #e88; min-width: 500px;\">\n".
		"      <tr><td style=\"border-style: solid; border-width: 2px; border-color: #800 #e88 #e88 #800; background-color: black; color: yellow; font-weight: bold; text-align: center; font-size: 10pt;\">".errError."</td></tr>\n".
		"      <tr><td style=\"border-style: solid; border-width: 2px; border-color: #800 #e88 #e88 #800; background-color: #c00; padding: 10; color: white; font-weight: bold; font-family: verdana, tahoma, Geneva, sans-serif; font-size: 8pt;\">\n".
		"        <p style=\"text-align: center\">".$msg."</p>\n".
		"        <div align=\"center\"><br /><a href=\"javascript:history.back()\" style=\"font-weight: bold; color: black; text-decoration: none; font-size: 10pt; height: 20px; background-color: #aaa; border-style: solid; border-width: 1px; border-color: #ccc #000 #000 #ccc; padding: 0 2em;\">".strReturn."</a></div>\n".
		"      </td></tr>\n".
		"    </table>\n".
		"  </div>\n".
		"</body>\n".
		"</html>";
	}
	die($result);
}
//------------------------------------------------------------------------------
###cut:end (testing purpose)
/**
 * Вывод сообщения о пользовательской ошибке
 *
 * @param string $text     Текст сообщения
 * @param string $caption  Заголовок окна сообщения
 */
function ErrorBox($text, $caption=errError)
{
	$result =
		(empty($caption)?'':"<div class=\"errorBoxCap\">".$caption."</div>\n").
		"<div class=\"errorBox\">\n".
		$text.
		"</div>\n";
	return $result;
}
//------------------------------------------------------------------------------
function InfoBox($text, $caption=strInformation)
# Функция выводит сообщение о пользовательской ошибке, но НЕ прекращает работу скрипта.
{
	$result =
		(empty($caption)?'':"<div class=\"infoBoxCap\">".$caption."</div>\n").
		"<div class=\"infoBox\">\n".
		$text.
		"</div>\n";
	return $result;
}
//------------------------------------------------------------------------------
function ErrorHandler($errno, $errstr, $errfile, $errline)
{
	global $Eresus;

	if (error_reporting()) switch ($errno) {
		case E_NOTICE:
			if ($Eresus->conf['debug']['enable']) ErrorMessage('<b>'.$errstr.'</b> ('.$errfile.', '.$errline.')');
		break;
		case E_WARNING:
			if ($Eresus->conf['debug']['enable'])
				FatalError('WARNING! <b>'.$errstr.'</b> in <b>'.$errfile.'</b> at <b>'.$errline.'</b><br /><br />'.(function_exists('callStack')?callStack():''));
		break;
		default:
			FatalError('Error <b>'.$errno.'</b>: <b>'.$errstr.'</b> in <b>'.$errfile.'</b> at <b>'.$errline.'</b>');
		break;
	}
}
//------------------------------------------------------------------------------
function ErrorMessage($message)
{
	global $Eresus;
	$Eresus->session['msg']['errors'][] = $message;
}
//------------------------------------------------------------------------------
function InfoMessage($message)
{
	global $Eresus;
	$Eresus->session['msg']['information'][] = $message;
}
//------------------------------------------------------------------------------

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# БЕЗОПАСНОСТЬ
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function UserRights($level)
# Функция проверяет права пользователя на соответствие заданной маске
{
	global $Eresus;

	return ((($Eresus->user['auth']) && ($Eresus->user['access'] <= $level) && ($Eresus->user['access'] != 0)) || ($level == GUEST));
}
//------------------------------------------------------------------------------
//------------------------------------------------------------------------------
function resetLastVisitTime($time='', $expand=false)
{
	global $Eresus;

	if ($Eresus->user['auth']) {
		$item = $Eresus->db->selectItem('users', "`id`='".$Eresus->user['id']."'");
		if (empty($time)) $item['lastVisit'] = gettime(); else {
			if ($expand) $time = substr($time,0,4).'-'.substr($time,4,2).'-'.substr($time,6,2).' '.substr($time,8,2).':'.substr($time,10,2);
			$item['lastVisit'] = $time;
		}
		$Eresus->db->updateItem('users', $item,"`id`='".$item['id']."'");
		$Eresus->user['lastVisit'] = $item['lastVisit'];
	}
}
//------------------------------------------------------------------------------

### Подключаемые модули (библиотеки, классы) ###

/**
 * Подключение библиотеки
 *
 * @param  string  $libaray  Имя библиотеки
 *
 * @return  bool  Результат
 */
function useLib($library)
{
	$result = false;
	if (DIRECTORY_SEPARATOR != '/') $library = str_replace('/', DIRECTORY_SEPARATOR, $library);
	$filename = DIRECTORY_SEPARATOR.$library.'.php';
	$dirs = explode(PATH_SEPARATOR, get_include_path());
	foreach ($dirs as $path) if (is_file($path.$filename)) {
		include_once($path.$filename);
		$result = true;
		break;
	}
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Подключает описание класса
 *
 * @access  public
 *
 * @param  string  $className   Имя класса
 *
 * @return  bool  Результат выполнения
 */
function useClass($className)
{
	$result = false;
	if (DIRECTORY_SEPARATOR != '/') $className = str_replace('/', DIRECTORY_SEPARATOR, $className);
	$filename = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.$className.'.php';
	if (is_file($filename)) {
		include_once($filename);
		$result = true;
	}
	return $result;
}
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# ПОЧТОВЫЕ ФУНКЦИИ
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function sendMail($address, $subject, $text, $html=false, $fromName='', $fromAddr='', $fromOrg='', $fromSign='', $replyTo='')
# Функция отсылает письмо по указанному адресу
{
	global $Eresus;

	if (empty($fromName)) $fromName = option('mailFromName');
	if (empty($fromAddr)) $fromAddr = option('mailFromAddr');
	if (empty($fromOrg)) $fromOrg = option('mailFromOrg');
	if (empty($fromSign)) $fromSign = option('mailFromSign');
	if (empty($replyTo)) $replyTo = option('mailReplyTo');
	if (empty($replyTo)) $replyTo = $fromAddr;

	$charset = option('mailCharset');
	if (empty($charset)) $charset = CHARSET;

	$sender = strlen($fromName) ? "=?".$charset."?B?".base64_encode($fromName)."?= <$fromAddr>" : $fromAddr;
	if (strlen($fromOrg)) $sender .= ' (=?'.$charset.'?B?'.base64_encode($fromOrg).'?=)';
	if (strpos($sender, '@') === false) $sender = 'no-reply@'.preg_replace('/^www\./', '', httpHost);
	$fromSign = "\n-- \n".$fromSign;
	if ($html) $fromSign = nl2br($fromSign);
	if (strlen($fromSign)) $text .= $fromSign;

	$headers =
	 "MIME-Version: 1.0\n".
	 "From: $sender\n".
	 "Subject: $subject\n".
	 "Reply-To: $replyTo\n".
	 "X-Mailer: PHP/" . phpversion()."\n";

	if ($html) {

		$text = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n\n<html>\n<head></head>\n<body>\n".$text."\n</body>\n</html>";

		$boundary="=_".md5(uniqid(time()));
		$headers.="Content-Type: multipart/mixed; boundary=$boundary\n";
		$multipart="";
		$multipart.="This is a MIME encoded message.\n\n";

		$multipart.="--$boundary\n";
		$multipart.="Content-Type: text/html; charset=$charset\n";
		$multipart.="Content-Transfer-Encoding: Base64\n\n";
		$multipart.=chunk_split(base64_encode($text))."\n\n";
		$multipart.="--$boundary--\n";
		$text = $multipart;
	} else $headers .= "Content-type: text/plain; charset=$charset\n";

	if ($Eresus->conf['debug']['enable'] && $Eresus->conf['debug']['mail'] !== true) {
		if (is_string($Eresus->conf['debug']['mail'])) {
			$hnd = @fopen($Eresus->conf['debug']['mail'], 'a');
			if ($hnd) {
				fputs($hnd, "\n================================================================================\n$headers\nTo: $address\nSubject: $subject\n\n$text\n");
				fclose($hnd);
			}
			return true;
		}
	} else return (mail($address, $subject, $text, $headers)===0);
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-//
function sendNotify($notify, $params=null)
# Посылает административное или редакторское уведомление по почте
# Возможные параметры
#   subject (string) - заголовок письма, по умолчанию название сайта
#   title (string) - название раздела
#   url (string) - адрес раздела
#   user (string) - имя пользователя
{
	global $Eresus, $page;

	$subject = isset($params['subject'])?$params['subject']:option('siteName');
	$username = isset($params['user'])?$params['user']:(is_null($Eresus->user)?'Guest':$Eresus->user['name']);
	$usermail = !is_null($Eresus->user) && $Eresus->user['auth'] ? $Eresus->user['mail'] : option('mailFormAddr');
	if (defined('ADMINUI')) {
		$editors = isset($params['editors'])?$params['editors']:false;
		$title = isset($params['title'])?$params['title']:$page->title;
		$url = isset($params['url'])?$params['url']:(arg('submitURL')?arg('submitURL'):$Eresus->request['referer']);
	} else {
		$editors = isset($params['editors'])?$params['editors']:true;
		$title = isset($params['title'])?$params['title']:$page->title;
		$url = isset($params['url'])?$params['url']:arg('submitURL');
	}
	$target = sendNotifyTo;
	$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	if ($host != $_SERVER['REMOTE_ADDR']) $host = "$host ({$_SERVER['REMOTE_ADDR']})";
	$notify = sprintf(strNotifyTemplate, $username, $host, $url, $title, $notify);
	sendMail($target, $subject, nl2br($notify), true, $username, $usermail, '', '');
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

//------------------------------------------------------------------------------
# ДАТА/ВРЕМЯ
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function gettime($format = 'Y-m-d H:i:s')
# Возвращает время с учетом смещения
{
	#$delta = (GMT_ZONE * 3600) - date('Z'); // Смещение на нужный часовой пояс
	$delta = 0;
	return date($format , time() + $delta); // Время, со смещением на наш часовой пояс
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

/**
 * Форматирование даты
 *
 * @param string $date    Дата в формате YYYY-MM-DD hh:mm:ss
 * @param string $format  Правила форматирования даты
 *
 * @return string Отформатированная дата
 */
function FormatDate($date, $format=DATETIME_NORMAL)
{
	if (empty($date)) $result = DATETIME_UNKNOWN; else {
		preg_match_all('/(?<!\\\)[hHisdDmMyY]/', $format, $m, PREG_OFFSET_CAPTURE);
		$repl = array(
			'Y' => substr($date, 0, 4),
			'm' => substr($date, 5, 2),
			'd' => substr($date, 8, 2),
			'h' => substr($date, 11, 2),
			'i' => substr($date, 14, 2),
			's' => substr($date, 17, 2)
		);
		$repl['y'] = substr($repl['Y'], 2, 2);
		$repl['M'] = constant('MONTH_'.$repl['m']);
		$repl['D'] = $repl['d']{0} == '0' ? $repl['d']{1} : $repl['d'];
		$repl['H'] = $repl['h']{0} == '0' ? $repl['h']{1} : $repl['h'];

		$delta = 0;
		for($i = 0; $i<count($m[0]); $i++) {
			$format = substr_replace($format, $repl[$m[0][$i][0]], $m[0][$i][1]+$delta, 1);
			$delta += strlen($repl[$m[0][$i][0]]) - 1;
		}
	}
	return $format;
}
//-----------------------------------------------------------------------------

//------------------------------------------------------------------------------
# РАБОТА С ДАННЫМИ

/**
 * Кодирует спецсимволы HTML
 *
 * @param mixed $source
 * @return mixed
 */
function encodeHTML($source)
{
	$translationTable = get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES);
	switch (true) {
		case is_string($source):
			$source = strtr($source, $translationTable);
		break;
		case is_array($source):
			foreach($source as $key => $value)
				$source[$key] = strtr($value, $translationTable);
		break;
	}
	return $source;
}
//-----------------------------------------------------------------------------
function decodeHTML($text)
# Декодирует спецсимволы HTML
{
	$trans_tbl = get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES);
	$trans_tbl = array_flip ($trans_tbl);
	$trans_tbl['%28'] = '(';
	$trans_tbl['%29'] = ')';
	$text = strtr ($text, $trans_tbl);
	$text = preg_replace('/ilo-[^\s>]*/i', '', $text);
	return $text;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
/**
 * Разбивает текст на строки и возвращает массив из них
 *
 * @param string $value
 * @param bool   $assoc[optional]
 * @return array
 */
function text2array($value, $assoc = false)
{
	$result = trim($value);
	if (!empty($result)) {
		$result = str_replace("\r",'',$result);
		$result = explode("\n", $result);
		if ($assoc && count($result)) {
			foreach($result as $item) {
				$item = explode('=', $item);
				$key = trim($item[0]);
				if ($key !== '') {
					$value = isset($item[1]) ? trim($item[1]) : null;
					$items[$key] = $value;
				}
			}
			$result = $items;
		}
	} else $result = array();
	return $result;
}
//-----------------------------------------------------------------------------
/**
 * Собирает текст из массива
 * @param string $value
 * @param bool   $assoc[optional]
 * @return string
 */
function array2text($items, $assoc = false)
{
	$result = '';
	if (count($items)) {
		if ($assoc)
			foreach($items as $key => $value) $result[] = $key.'='.$value;
		else
			$result = $items;
		$result = implode("\n", $result);
	}
	return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function encodeOptions($options)
# Собирает настройки из массива в строку
{
	$result = serialize($options);
	return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function decodeOptions($options, $defaults = array())
# Функция разбивает записанные в строковом виде опции на массив
{
	if (empty($options)) $result = $defaults; else {
		@$result = unserialize($options);
		if (gettype($result) != 'array') $result = $defaults; else {
			if (count($defaults)) foreach($defaults as $key => $value) if (!array_key_exists($key, $result)) $result[$key] = $value;
		}
	}
	return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
/**
 * Замена макросов
 *
 * @param string $template  Шаблон
 * @param mixed  $source    Источник для замены
 * @return Обработанный текст
 *
 * @see __propery
 */
function replaceMacros($template, $source)
{
	# Замена условных макросов
	preg_match_all('/\$\(([^\)\?:]+)\?([^:\)]*):([^\)]*)\)/U', $template, $matches, PREG_SET_ORDER);
	if (count($matches)) foreach($matches as $macros) {
		if (__isset($source, $macros[1])) $template = str_replace($macros[0], __property($source, $macros[1])?$macros[2]:$macros[3], $template);
	}
		# Замена обычных макросов
	preg_match_all('/\$\(([^(]+)\)/U', $template, $matches);
	if (count($matches[1])) foreach($matches[1] as $macros)
		if (__isset($source, $macros)) $template = str_replace('$('.$macros.')', __property($source, $macros), $template);
	return $template;
}
//------------------------------------------------------------------------------

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# Работа с HTTP-запросом
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
/**
 * Заполняет массив значениями из запроса
 *
 * @param array $item        Заполняемый массив или массив ключей
 * @param array $checkboxes  Список аргументов рассматриваемых как чек-боксы
 * @param array $prevent     Список полей массива изменять которые не следует
 *
 * @return array  Заполненный массив
 *
 * @deprecated since 2.10
 */
function GetArgs($item, $checkboxes = array(), $prevent = array())
{
	global $Eresus;

	if ($clear = (key($item) == '0')) $item = array_flip($item);
	foreach ($item as $key => $value) {
		if ($clear) unset($item[$key]);
		if (!in_array($key, $prevent)) {
			if (!is_null(arg($key))) $item[$key] = arg($key, 'dbsafe');
			if (in_array($key, $checkboxes)&& (!arg($key))) $item[$key] = false;
		}
	}
	return $item;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
/**
 * Получение аргумента запроса
 *
 * @param string $arg     Имя аргумента
 * @param mixed  $filter  Фильтр на значение
 *
 * @return mixed
 */
function arg($arg, $filter = null)
{
	global $Eresus;

	$arg = isset($Eresus->request['arg'][$arg])?$Eresus->request['arg'][$arg]:null;
	if ($arg !== false && !is_null($filter)) {
		switch($filter) {
			case 'dbsafe':
				$arg = $Eresus->db->escape($arg);
			break;
			case 'int':
			case 'integer':
					$arg = intval($arg);
			break;
			case 'float':
					$arg = floatval($arg);
			break;
			case 'word':
					$arg = preg_replace('/\W/', '', $arg);
			break;
			default: $arg = preg_replace($filter, '', $arg);
		}
	}
	return $arg;
}
//-----------------------------------------------------------------------------
function saveRequest()
# Функция сохраняет в сессии текущие аргументы
{
	global $Eresus;
	$Eresus->session['request'] = $Eresus->request;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function restoreRequest()
# Функция сохраняет в сессии текущие аргументы
{
	global $Eresus;
	if (isset($Eresus->session['request'])) {
		$Eresus->request = $Eresus->session['request'];
		unset($Eresus->session['request']);
	}
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#


 /*
 	* РАБОТА С БД
	*/

/**
 * Упорядочивание элементов
 *
 * @param string $table      Таблица
 * @param string $condition  Условие
 * @param string $id         Имя ключевого поля
 *
 * @deprecated
 */
function dbReorderItems($table, $condition='', $id='id')
{
	global $Eresus;

	$items = $Eresus->db->select("`".$table."`", $condition, '`position`', $id);
	for($i=0; $i<count($items); $i++) $Eresus->db->update($table, "`position` = $i", "`".$id."`='".$items[$i][$id]."'");
	}
//------------------------------------------------------------------------------
/**
 * Сдвиг позиций элементов
 *
 * @param string $table      Таблица
 * @param string $condition  Условие
 * @param string $delta      Величина сдвига
 *
 * @deprecated
 *  */
function dbShiftItems($table, $condition, $delta, $id='id')
{
global $Eresus;

	$items = $Eresus->db->select("`".$table."`", $condition, '`position`', $id);
	for($i=0; $i<count($items); $i++) $Eresus->db->update($table, "`position` = `position` + $delta", "`".$id."`='".$items[$i][$id]."'");
	}
//------------------------------------------------------------------------------

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# РАБОТА С ФАЙЛАМИ
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
/**
 * Чтение файла
 *
 * @param string $filename Имя файла
 * @return mixed Содержимое файла или false
 */
function fileread($filename)
{
	$result = false;
	if (is_file($filename)) {
		if (is_readable($filename)) {
			$result = file_get_contents($filename);
		}
	}
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Запись в файл
 *
 * @param string $filename Имя файла
 * @param string $content  Содержимое
 * @param int    $flags    Флаги
 * @return bool Результат выполнения
 */
function filewrite($filename, $content, $flags = 0)
{
	$result = false;
	@$fp = fopen($filename, ($flags && FILE_APPEND)?'ab':'wb');
	if ($fp) {
		$result = fwrite($fp, $content) == strlen($content);
		fclose($fp);
	}
	return $result;
}
//------------------------------------------------------------------------------
/**
 * Удаляет файл
 *
 * @param string $filename Имя файла
 * @return bool Результат выполнения
 */
function filedelete($filename)
{
	$result = false;
	if (is_file($filename)) {
		if (is_writeable($filename)) {
			$result = unlink($filename);
		}
	}
	return $result;
}
//------------------------------------------------------------------------------
function upload($name, $filename, $overwrite = true)
{
	$result = false;
	if (substr($filename, -1) == '/') {
		$filename .= option('filesTranslitNames')?Translit($_FILES[$name]['name']):$_FILES[$name]['name'];
		if (file_exists($filename) && ((is_string($overwrite) && $filename != $overwrite ) || (is_bool($overwrite) && !$overwrite))) {
			$i = strrpos($filename, '.');
			$fname = substr($filename, 0, $i);
			$fext = substr($filename, $i);
			$i = 1;
			while (is_file($fname.$i.$fext)) $i++;
			$filename = $fname.$i.$fext;
		}
	}
	switch($_FILES[$name]['error']) {
		case UPLOAD_ERR_OK:
			if (is_uploaded_file($_FILES[$name]['tmp_name'])) {
				$moved = @move_uploaded_file($_FILES[$name]['tmp_name'], $filename);
				if ($moved) {
					if (option('filesOwnerSetOnUpload')) {
						$owner = option('filesOwnerDefault');
						if (empty($owner)) $owner = fileowner(__FILE__);
						@chown($filename, $owner);
					}
					if (option('filesModeSetOnUpload')) {
						$mode = option('filesModeDefault');
						$mode = empty($mode) ? 0666 : octdec($mode);
						@chmod($filename, $mode);
					}
					$result = $filename;
				} else ErrorMessage(sprintf(errFileMove, $_FILES[$name]['name'], $filename));
			}
		break;
		case UPLOAD_ERR_INI_SIZE: ErrorMessage(sprintf(errUploadSizeINI, $_FILES[$name]['name'])); break;
		case UPLOAD_ERR_FORM_SIZE: ErrorMessage(sprintf(errUploadSizeFORM, $_FILES[$name]['name'])); break;
		case UPLOAD_ERR_PARTIAL: ErrorMessage(sprintf(errUploadPartial, $_FILES[$name]['name'])); break;
		case UPLOAD_ERR_NO_FILE: if (strlen($_FILES[$name]['name'])) ErrorMessage(sprintf(errUploadNoFile, $_FILES[$name]['name'])); break;
	}
	return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# TODO: Удалить как устаревшую
/**
 * @deprecated
 */
function loadTemplate($name)
# Считывает указанный шаблон
{
	$filename = filesRoot.'templates/'.$name.(strpos($name, '.html')===false?'.html':'');
	if (file_exists($filename)) {
		$result['html'] = file_get_contents($filename);
		preg_match('/<!--(.*?)-->/', $result['html'], $result['description']);
		$result['description'] = trim($result['description'][1]);
		$result['html'] = trim(substr($result['html'], strpos($result['html'], "\n")));
	} else $result = false;
	return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# TODO: Удалить как устаревшую
/**
 * @deprecated
 */
function saveTemplate($name, $template)
# Сохраняет указанный шаблон
{
	$file = "<!-- ".$template['description']." -->\r\n\r\n".$template['html'];
	$fp = fopen(filesRoot.'templates/'.$name.(strpos($name, '.tmpl')===false?'.html':''), 'w');
	fwrite($fp, $file);
	fclose($fp);
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

### ОБЩИЕ ФУНКЦИИ ###

/**
 * Переадресация на URL
 *
 * @param string $url  Новый URL
 */
function goto($url)
{
	$url = str_replace('&amp;','&',$url);
	if(preg_match('/Apache/i', $_SERVER['SERVER_SOFTWARE'])) header("Location: $url");
	else header("Refresh: 0; URL=$url");
	exit;
}
//------------------------------------------------------------------------------

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function HttpAnswer($answer)
{
	Header('Content-type: text/html; charset='.CHARSET);
	echo $answer;
	exit;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function SendXML($data)
# Отправляет браузеру XML
{
	Header('Content-Type: text/xml');
	echo '<?xml version="1.0" encoding="'.CHARSET.'"?>'."\n<root>".$data."</root>";
	exit;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function option($name)
{
	$result = defined($name)?constant($name):'';
	return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function img($imagename)
# function img($imagename, $alt='', $title='', $width=0, $height=0, $style='')
# function img($imagename, $params=array())
# Функция возвращает заполненный тэг <img>
{
	$argc = func_num_args();
	$argv = func_get_args();
	if ($argc > 1) {
		if (is_array($argv[1])) $p = $argv[1]; else {
			$p['alt'] = $argv[1];
			if ($argc > 2) $p['title'] = $argv[2];
			if ($argc > 3) $p['width'] = $argv[3];
			if ($argc > 4) $p['height'] = $argv[4];
			if ($argc > 5) $p['style'] = $argv[5];
		}
	}
	if (!isset($p['alt']))    $p['alt'] = '';
	if (!isset($p['title']))  $p['title'] = '';
	if (!isset($p['width']))  $p['width'] = '';
	if (!isset($p['height'])) $p['height'] = '';
	if (!isset($p['style']))  $p['style'] = '';
	if (!isset($p['ext']))  $p['ext'] = '';
	if (!isset($p['autosize'])) $p['autosize'] = true;


	if (strpos($imagename, httpRoot) !== false) $imagename = str_replace(httpRoot, '', $imagename);
	if (strpos($imagename, filesRoot) !== false) $imagename = str_replace(filesRoot, '', $imagename);
	if (strpos($imagename, '://') === false) $imagename = httpRoot.$imagename;
	$local = (strpos($imagename, httpRoot) === 0);

	if ($p['autosize'] && $local && empty($p['width']) && empty($p['height'])) {
		$filename = str_replace(httpRoot, filesRoot, $imagename);
		if (is_file($filename)) $info = getimagesize($filename);
	}
	if (isset($info)) {
		$p['width'] = $info[0];
		$p['height'] = $info[1];
	};

	$result = '<img src="'.$imagename.'" alt="'.$p['alt'].'"'.
		(empty($p['width'])?'':' width="'.$p['width'].'"').
		(empty($p['height'])?'':' height="'.$p['height'].'"').
		(empty($p['title'])?'':' title="'.$p['title'].'"').
		(empty($p['style'])?'':' style="'.$p['style'].'"').
		(empty($p['ext'])?'':' '.$p['ext']).
	' />';
	return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function FormatSize($size)
{
	if ($size > 1073741824) {$size = $size / 1073741824; $units = 'Гб'; $z = 2;}
	elseif ($size > 1048576) {$size = $size / 1048576; $units = 'Мб'; $z = 2;}
	elseif ($size > 1024) {$size = $size / 1024; $units = 'Кб'; $z = 2;}
	else {$units = 'Байт'; $z = 0;}
	return number_format($size, $z, '.', ' ').' '.$units;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function Translit($s) #: String
{
	$s = strtr($s, $GLOBALS['translit_table']);
	$s = str_replace(
		array(' ','/','?'),
		array('_','-','7'),
		$s
	);
	$s = preg_replace('/(\s|_)+/', '$1', $s);
	return $s;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# ВНУТРЕННИЕ ФУНКЦИИ
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function __clearargs($args)
{
	global $Eresus;

	if (count($args)) foreach($args as $key => $value) if (gettype($args[$key]) == 'array') {
		$args[$key] = __clearargs($args[$key]);
	} else {
		if (get_magic_quotes_gpc()) $value = StripSlashes($value);
		if (strpos($key, 'wyswyg_') === 0) {
			unset($args[$key]);
			$key = substr($key, 7);
			$value = preg_replace('/(<[^>]+) ilo-[^\s>]*/i', '$1', $value);
			$value = str_replace(array('%28', '%29'), array('(',')'), $value);
			$value = str_replace($Eresus->root, '$(httpRoot)', $value);
			preg_match_all('/<img.*?>/', $value, $images, PREG_OFFSET_CAPTURE);
			if (count($images[0])) {
				$images = $images[0];
				$delta = 0;
				for($i = 0; $i < count($images); $i++) if (!preg_match('/alt=/i', $images[$i][0])) {
					$s = preg_replace('/(\/?>)/', 'alt="" $1', $images[$i][0]);
					$value = substr_replace($value, $s, $images[$i][1]+$delta, strlen($images[$i][0]));
					$delta += strlen($s) - strlen($images[$i][0]);
				}
			}
		}
		$args[$key] = $value;
	}
	return $args;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
/**
 * Определяет установлено ли свойство у элемента
 *
 * @param mixed  $object    Элемент
 * @param string $property  Свойство
 * @return bool Значение
 *
 * @see replaceMacros
 */
function __isset($object, $property)
{
	return
		is_object($object) ? isset($object->$property) : (
			is_array ($object) ? isset($object[$property]) :
			false
		);
}
//-----------------------------------------------------------------------------
/**
 * Возвращает свойство элемента
 *
 * @param mixed  $object    Элемент
 * @param string $property  Свойство
 * @return string Значение
 *
 * @see replaceMacros
 */
function __property($object, $property)
{
	return
		is_object($object) ? $object->$property : (
			is_array ($object) ? $object[$property] :
			''
		);
}
//-----------------------------------------------------------------------------

/**
* Основной класс приложения
*
* @var  function  $oldErrorHandler  Предыдущий обработчик ошибок
* @var  array     $conf             Конфигурация
* @var  array     $session          Данные сессии
* @var  object    $db               Интерфейс к СУБД
* @var  array     $user             Учётная запись пользователя
*/
class Eresus {
	var $oldErrorHandler;
	var $conf = array(
		'lang' => 'ru',
		'timezone' => '',
		'db' => array(
			'engine'   => 'mysql',
			'host'     => 'localhost',
			'user'     => '',
			'password' => '',
			'name'     => '',
			'prefix'   => '',
		),
		'session' => array(
			'timeout' => 30,
		),
		'extensions' => array(),
		'backward' => array(
			'TPlugins' => false,
			'TPlugin' => false,
			'TContentPlugin' => false,
			'TListContentPlugin' => false,
		),
		'debug' => array(
			'enable' => false,
			'mail' => true,
		),
	);
	var $session;
 /**
	* Интерфейс к расширениям системы
	*
	* @var unknown_type
	*/
	var $extensions;
	var $db;
	var $plugins;
 /**
	* Учётная запись пользователя
	*
	* @var EresusAccount
	*/
	var $user;

	var $host;
 /**
	* @deprecated since 2.11
	*/
	var $https;
	var $path;
	var $root; # Корневой URL
	var $data; # URL данных
	var $style; # URL стилей
	var $froot; # Корневая директория
	var $fdata; # Директория данных
	var $fstyle; # Директория стилей

	var $request;
	var $sections;

	var $PHP5 = false;

	/**
	* Конструктор
	*/
	function Eresus()
	{
		# Инициализация перехватчика ошибок
		$this->oldErrorHandler = set_error_handler('ErrorHandler');
		# Флаг использования PHP5
		$this->PHP5 = version_compare(PHP_VERSION, '5.0.0', '>=');
	}
	//------------------------------------------------------------------------------
	// Информация о системе
	//------------------------------------------------------------------------------
	/**
	* Взято из Limb3 - http://limb-project.com/
	*/
	function isWin32()  { return DIRECTORY_SEPARATOR == '\\'; }
	function isUnix()   { return DIRECTORY_SEPARATOR == '/'; }
	function isMac()    { return !strncasecmp(PHP_OS, 'MAC', 3); }
	function isModule() { return !$this->isCgi() && isset($_SERVER['GATEWAY_INTERFACE']); }
	function isCgi()    { return !strncasecmp(PHP_SAPI, 'CGI', 3); }
	function isCli()    { return PHP_SAPI == 'cli'; }
	#-------------------------------------------------------------------------------
	/**
	* Читает и применяет конфигурационный файл
	*
	* @access  private
	*/
	function init_config()
	{
		global $Eresus;

		$filename = realpath(dirname(__FILE__).'/..').'/cfg/main.php';
		if (is_file($filename)) include_once($filename);
		else FatalError("Main config file '$filename' not found!");
	}
	#-------------------------------------------------------------------------------
	/**
	* Инициирует сессии
	*
	* @access  private
	*/
	function init_session()
	{
		session_set_cookie_params(ini_get('session.cookie_lifetime'), $this->path);
		session_name('sid');
		###cut:start (testing purpose)
		session_start();
		###cut:end (testing purpose)
		$this->session = &$_SESSION['session'];
		if (!isset($this->session['msg'])) $this->session['msg'] = array('error' => array(), 'information' => array());
		$this->user = &$_SESSION['user'];

		# Обратная совместимость
		$GLOBALS['session'] = &$_SESSION['session'];
		$GLOBALS['user'] = &$_SESSION['user'];
	}
	#-------------------------------------------------------------------------------
	/**
	* Определяет пути
	*
	* @access  private
	*/
	function init_resolve()
	{
		if (is_null($this->froot)) $this->froot = realpath(dirname(__FILE__).'/..').'/';
		if ($this->isWin32()) {
			$this->froot = str_replace('\\', '/', substr($this->froot, 2));
		}
		$this->fdata = $this->froot.'data/';
		$this->fstyle = $this->froot.'style/';

		if (is_null($this->path)) {
			$s = $this->froot;
			$s = substr($s, strlen(realpath($_SERVER['DOCUMENT_ROOT']))-($this->isWin32()?2:0));
			if (!strlen($s) || $s{strlen($s)-1} != '/') $s .= '/';
			$this->path = ($s{0} != '/' ? '/' : '').$s;
		}

		# Обратная совместимость
		define('filesRoot', $this->froot);
		define('dataFiles', $this->fdata);
	}
	//------------------------------------------------------------------------------
	/**
	* Читает настройки
	*
	* @access  private
	*/
	function init_settings()
	{
		$filename = $this->froot.'cfg/settings.php';
		if (is_file($filename)) include_once($filename);
		else FatalError("Settings file '$filename' not found!");
	}
	//------------------------------------------------------------------------------
	/**
	* Первичный разбор запроса
	*
	* @access  private
	*/
	function init_request()
	{
		global $request;

		# Значения по умолчанию
		$request = array(
			'method' => $_SERVER['REQUEST_METHOD'],
			'scheme' => isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http',
			'host' => strtolower(is_null($this->host) ? $_SERVER['HTTP_HOST'] : $this->host),
			'port' => '',
			'user' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '',
			'pass' => isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '',
			'path' => '',
			'query' => '',
			'fragment' => '', # TODO: Можно ли узнать значение этого компонента?
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
		);

		$request['url'] = $request['scheme'].'://'.$request['host'].$_SERVER['REQUEST_URI'];

		$request = array_merge($request, parse_url($request['url']));
		$request['file'] = substr($request['path'], strrpos($request['path'], '/')+1);
		if ($request['file']) $request['path'] = substr($request['path'], 0, -strlen($request['file']));

		# Создаем заготовку URL для GET-запросов с параметрами
		$request['link'] = $request['url'];
		if (substr($request['link'], -1) == '/') $request['link'] .= '?';
		elseif (strpos($request['link'], '?') === false)  $request['link'] .= '?';
		else $request['link'] .= '&';

		if (is_null($this->path)) {
			$s = $this->froot;
			$s = substr($s, strlen(realpath($_SERVER['DOCUMENT_ROOT']))-($this->isWin32()?2:0));
			if (!strlen($s) || sbstr($s, -1) != '/') $s .= '/';
			$this->path = (substr($s, 0, 1) != '/' ? '/' : '').$s;
		}

		# Сбор аргументов вызова
		$request['arg'] = __clearargs(array_merge($_GET, $_POST));
		# Разбивка параметров вызова скрипта
		$s = substr($request['path'], strlen($this->path));
		$request['params'] = $s ? explode('/', substr($s, 0, -1)) : array();

		$root = $request['scheme'].'://'.$request['host'].($request['port'] ? ':'.$request['port'] : '');
		$request['path'] = $root.$request['path'];

		# Установка свойств объекта $Eresus
		$this->host = $request['host'];
		$this->root = $root.$this->path;
		$this->data = $this->root.'data/';
		$this->style = $this->root.'style/';

		# Обратная совместимость
		# <= 2.9
		$this->request = &$request;
		define('httpPath', $this->path);
		define('httpHost', $this->host);
		define('httpRoot', $this->root);
		define('styleRoot', $this->style);
		define('dataRoot', $this->data);
		define('cookieHost', $this->host);
		define('cookiePath', $this->path);
		# 2.10
		$this->https = $request['scheme'] == 'https';
	}
	//------------------------------------------------------------------------------
	/**
	* Инициализация локали
	*
	* @access private
	*/
	function init_locale()
	{
		global $locale;

		$locale['lang'] = $this->conf['lang'];
		$locale['prefix'] = '';

		# Подключение строковых данных
		$filename = $this->froot.'lang/'.$locale['lang'].'.php';
		if (is_file($filename)) include_once($filename);
		else FatalError("Locale file '$filename' not found!");
	}
	//------------------------------------------------------------------------------
 /**
	* Подключение базовых классов
	*
	* @access private
	*/
	function init_classes()
	{
		# Подключение строковых данных
		$filename = $this->froot.'core/classes.php';
		if (is_file($filename)) include_once($filename);
		else FatalError("Classes file '$filename' not found!");
		if ($this->conf['backward']['TPlugins']) useClass('backward/TPlugins');
		if ($this->conf['backward']['TListContentPlugin']) useClass('backward/TListContentPlugin');
		elseif ($this->conf['backward']['TContentPlugin']) useClass('backward/TContentPlugin');
		elseif ($this->conf['backward']['TPlugin']) useClass('backward/TPlugin');
	}
	//------------------------------------------------------------------------------
 /**
	* Инициализация расширений
	*/
	function init_extensions()
	{
		$filename = $this->froot.'cfg/extensions.php';
		if (is_file($filename)) include_once($filename);

		$this->extensions = new EresusExtensions();
	}
	//-----------------------------------------------------------------------------
	/**
	* Подключение к источнику данных
	*
	* @access private
	*/
	function init_datasource()
	{
		if (useLib($this->conf['db']['engine'])) {
			$this->db = new $this->conf['db']['engine'];
			$this->db->init($this->conf['db']['host'], $this->conf['db']['user'], $this->conf['db']['password'], $this->conf['db']['name'], $this->conf['db']['prefix']);
			if ($this->PHP5) $GLOBALS['db'] = $this->db; else $GLOBALS['db'] =& $this->db;
		} else FatalError(sprintf(errLibNotFound, $this->conf['db']['engine']));
	}
	//------------------------------------------------------------------------------
 /**
	* Инициализация механизма плагинов
	*/
	function init_plugins()
	{
		$this->plugins = new Plugins;
		#FIX Обратная совместимость
		if ($this->PHP5) $GLOBALS['plugins'] = $this->plugins; else $GLOBALS['plugins'] =& $this->plugins;;
	}
	//------------------------------------------------------------------------------
	/**
	* Инициализация учётной записи пользователя
	*
	*/
	function init_user()
	{
		useLib('accounts');
	}
	//-----------------------------------------------------------------------------
	/**
	* Проверка сессии
	*
	* @access private
	*/
	function check_session()
	{
		if (isset($this->session['time'])) {
			if ((time() - $this->session['time'] > $this->conf['session']['timeout']*3600)&&($this->user['auth'])) $this->logout(false);
			else $this->session['time'] = time();
		}
	}
	//------------------------------------------------------------------------------
 /**
	* Проверка на логин/логаут
	*
	*/
	function check_loginout()
	{
		switch (arg('action')) {
			case 'login':
				$this->login(arg('user', 'dbsafe'), $this->password_hash(arg('password')), arg('autologin', 'int'));
				goto($this->request['url']);
			break;
			case 'logout':
				$this->logout(true);
				goto($this->root.'admin/');
			break;
		}
	}
	//------------------------------------------------------------------------------
 /**
	* Попытка cookie-логина
	*/
	function check_cookies()
	{
		if (!$this->user['auth'] && isset($_COOKIE['eresus_login'])) {
			if (!$this->login($_COOKIE['eresus_login'], $_COOKIE['eresus_key'], true, true))
				$this->clear_login_cookies();
		}
	}
	//------------------------------------------------------------------------------
 /**
	* Обновление данных о пользователе
	*/
	function reset_login()
	{
		$this->user['auth'] = isset($this->user['auth'])?$this->user['auth']:false;
		if ($this->user['auth']) {
			$item = $this->db->selectItem('users', "`id`='".$this->user['id']."'");
			if (!is_null($item)) { # Если такой пользователь есть...
				if ($item['active']) { # Если учетная запись активна...
					$this->user['name'] = $item['name'];
					$this->user['mail'] = $item['mail'];
					$this->user['access'] = $item['access'];
					$this->user['profile'] = decodeOptions($item['profile']);
				} else {
					ErrorMessage(sprintf(errAccountNotActive, $item['login']));
					$this->logout();
				}
			} else $this->logout();
		} else $this->user['access'] = GUEST;
	}
	//------------------------------------------------------------------------------
 /**
	* Инициализация системы
	*
	* @access public
	*/
	function init()
	{
		# Отключение закавычивания передаваемых данных
		set_magic_quotes_runtime(0);
		# Читаем конфигурацию
		$this->init_config();
		# В PHP 5.1.0 должна быть установлена временная зона по умолчанию
		if (PHP_VERSION >= '5.1.0') date_default_timezone_set($this->conf['timezone']);
		# Определение путей
		$this->init_resolve();
		# Инициализация сессии
		$this->init_session();
		# Изменяем путь поиска подключаемых файлов
		set_include_path(dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.PATH_SEPARATOR.get_include_path());
		# Если установлен флаг отладки, подключаем отладочную библиотеку
		if ($this->conf['debug']) useLib('debug');
		# Читаем настройки
		$this->init_settings();
		# Первичный разбор запроса
		$this->init_request();
		# Настройка локали
		$this->init_locale();
		# Подключение базовых классов
		$this->init_classes();
		# Инициализация расширений
		$this->init_extensions();
		# Подключение к источнику данных
		$this->init_datasource();
		# Инициализация механизма плагинов
		$this->init_plugins();
		# Инициализация учётной записи пользователя
		$this->init_user();
		# Проверка сессии
		$this->check_session();
		# Проверка логина/логаута
		$this->check_loginout();
		# Попытка cookie-логина
		$this->check_cookies();
		# Обновление данных о пользователе
		$this->reset_login();
		# Подключение работы с разделами сайта
		useLib('sections');
		$this->sections = new Sections;
		$GLOBALS['KERNEL']['loaded'] = true; # Флаг загрузки ядра
	}
	//------------------------------------------------------------------------------
 /**
	* Хеширует пароль
	*
	* @param string $password  Пароль
	* @return string  Хеш
	*/
	function password_hash($password)
	{
		$result = md5($password);
		if (!$this->conf['backward']['weak_password']) $result = md5($result);
		return $result;
	}
	//-----------------------------------------------------------------------------
 /**
	* Устанавливает авторизационные кукисы
	*
	* @param string $login
	* @param string $key
	*/
	function set_login_cookies($login, $key)
	{
		setcookie('eresus_login', $login, time()+2592000, $this->path);
		setcookie('eresus_key', $key, time()+2592000, $this->path);
	}
	//-----------------------------------------------------------------------------
 /**
	* Удалаяет авторизационные кукисы
	*
	*/
	function clear_login_cookies()
	{
		setcookie('eresus_login', '', time()-3600, $this->path);
		setcookie('eresus_key', '', time()-3600, $this->path);
	}
	//-----------------------------------------------------------------------------
 /**
	* Авторизация пользователя
	*
	* @param string $login   Имя пользователя
	* @param string $key		 Ключ учётной записи
	* @param bool   $auto		 Сохранить авторизационные данные на комптютере посетителя
	* @param bool   $cookie  Авторизация при помощи cookie
	* @return bool Результат
	*/
	function login($login, $key, $auto = false, $cookie = false)
	{
		$result = false;
		$item = $this->db->selectItem('users', "`login`='$login'");
		if (!is_null($item)) { # Если такой пользователь есть...
			if ($item['active']) { # Если учетная запись активна...
				if (time() - $item['lastLoginTime'] > $item['loginErrors']) {
					if ($key == $item['hash']) { # Если пароль верен...
						if ($auto) $this->set_login_cookies($login, $key);
						else $this->clear_login_cookies();
						$setVisitTime = !isset($this->user['id']);
						$lastVisit = isset($this->user['lastVisit'])?$this->user['lastVisit']:'';
						$this->user = $item;
						$this->user['profile'] = decodeOptions($this->user['profile']);
						$this->user['auth'] = true; # Устанавливаем флаг авторизации
						$this->user['hash'] = $item['hash']; # Хэш пароля используется для подтверждения аутентификации
						if ($setVisitTime) $item['lastVisit'] = gettime(); # Записываем время последнего входа
						$item['lastLoginTime'] = time();
						$item['loginErrors'] = 0;
						$this->db->updateItem('users', $item,"`id`='".$item['id']."'");
						$this->session['time'] = time(); # Инициализируем время последней активности сессии.
						$result = true;
					} else { # Если пароль не верен...
						if (!$cookie) {
							ErrorMessage(errInvalidPassword);
							$item['lastLoginTime'] = time();
							$item['loginErrors']++;
							$this->db->updateItem('users', $item,"`id`='".$item['id']."'");
						}
					}
				} else { # Если авторизация проведена слишком рано
					ErrorMessage(sprintf(errTooEarlyRelogin, $item['loginErrors']));
					$item['lastLoginTime'] = time();
					$this->db->updateItem('users', $item,"`id`='".$item['id']."'");
				}
			} else ErrorMessage(sprintf(errAccountNotActive, $login));
		} else ErrorMessage(errInvalidPassword);
		return $result;
	}
	//-----------------------------------------------------------------------------
 /**
	* Завершение сеанса работы с системой
	*
	* @param bool $clearCookies
	*/
	function logout($clearCookies=true)
	{
		$this->user['auth'] = false;
		$this->user['access'] = GUEST;
		if ($clearCookies) $this->clear_login_cookies();
	}
	//-----------------------------------------------------------------------------
 /**
	* Исполнение
	*
	* @access public
	*/
	function execute()
	{
	}
	//------------------------------------------------------------------------------
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

###cut:start (testing purpose)

$GLOBALS['Eresus'] = new Eresus;
$GLOBALS['Eresus']->init();
$GLOBALS['Eresus']->execute();

###cut:end (testing purpose)

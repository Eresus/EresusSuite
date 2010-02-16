<?php
/**
 * Eresus 2.10.1
 *
 * Библиотека отладочныъ функций
 *
 * @copyright		2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright		2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */
function plaintext()
{
	if (!headers_sent()) header("Content-type: text/plain");
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function dbglog($msg)
{
	$hnd = fopen(DEBUG_LOG_FILENAME, 'a');
	fputs($hnd, "[".date('Y-m-d H:i:s').'] - '.$msg."\n");
	fclose($hnd);
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function renderTemplate($template)
{
	$template = file_get_contents(filesRoot.'templates/'.$template.'.tmpl');

		$template = str_replace(
			array(
				'$(httpHost)',
				'$(httpPath)',
				'$(httpRoot)',
				'$(styleRoot)',
				'$(dataRoot)',

				'$(siteName)',
				'$(siteTitle)',
				'$(siteKeywords)',
				'$(siteDescription)',
			),
			array(
				httpHost,
				httpPath,
				httpRoot,
				styleRoot,
				dataRoot,

				siteName,
				siteTitle,
				siteKeywords,
				siteDescription,
			),
			$template
		);

	echo $template;
	exit;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function callStack()
{
	$callstack = debug_backtrace();
	$result = '<div style="font-weight: normal; text-align: left;">';
	for ($i = 1; $i < count($callstack); $i++) if (strtolower($callstack[$i]['function']) != 'errorhandler') {
		$result .= 'File <b>'.$callstack[$i]['file'].'</b> line <b>'.$callstack[$i]['line'].'</b>:<br />';
		$args = '';
		if (isset($callstack[$i]['args']) && count($callstack[$i]['args'])) {
			$args = array();
			foreach($callstack[$i]['args'] as $arg) {
				ob_start();
				var_dump($arg);
				$args[] = htmlentities(ob_get_contents(), ENT_NOQUOTES, LOCALE_CHARSET);
				ob_end_clean();
			}
			$args = implode(', ', $args);
		}

		$result .= 'Call <b>'.(empty($callstack[$i]['class'])?'':$callstack[$i]['class']).(empty($callstack[$i]['type'])?'':$callstack[$i]['type']).$callstack[$i]['function'].'</b>('.$args.')<br /><br />';
	}
	$result .='</div>';
	return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
if (isset($_GET['template'])) renderTemplate($_GET['template']);
?>
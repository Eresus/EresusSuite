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

var UA = {ID: navigator.userAgent.toLowerCase()}

// *** BROWSER VERSION ***

// Note: On IE5, these return 4, so use is_ie5up to detect IE5.
UA.major = parseInt(navigator.appVersion);
UA.minor = parseFloat(navigator.appVersion);

// Gecko (Mozilla, FireFox, etc)
UA.Gecko  = UA.ID.indexOf('gecko') != -1;
// Presto (Opera)
UA.Opera  = UA.ID.indexOf('opera') != -1;
// Microsoft Internet Explorer
UA.MSIE   = (UA.ID.indexOf('msie') != -1) && (UA.ID.indexOf('opera') == -1) && (UA.ID.indexOf('webtv') == -1);
// Safari
UA.Safari = UA.ID.indexOf('safari') != -1;
// Konqueror
UA.Konqueror = UA.ID.indexOf('konqueror') != -1;


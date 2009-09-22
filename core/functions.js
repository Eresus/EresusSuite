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

var isIE = (navigator.userAgent.toLowerCase().indexOf('msie') != -1) && (navigator.userAgent.toLowerCase().indexOf('opera') == -1);
var HttpRequest = null;
var BrowseFileLast = '';

//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function replaceMacros(sURL)
{
	var macros = new Array();
	macros['httpRoot'] = '$(httpRoot)';
	macros['httpHost'] = '$(httpHost)';
	macros['httpPath'] = '$(httpPath)';
	macros['styleRoot'] = '$(styleRoot)';
	macros['dataRoot'] = '$(dataRoot)';

	function __replace(sMatch, sMacros)
	{
		return macros[sMacros];
	}

	sURL = sURL.replace(/\$\(([^\)]+)\)/, __replace);
	return sURL;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function pageLeft()
{
	return isIE ? (document.body.scrollLeft?document.body.scrollLeft:document.documentElement.scrollLeft) : window.pageXOffset;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function pageTop()
{
	return isIE ? (document.body.scrollTop?document.body.scrollTop:document.documentElement.scrollTop) : window.pageYOffset;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function toggleMenuBranch(Id)
{
	var branch = document.getElementById('branch'+Id);
	var root = document.getElementById('root'+Id);
	if (branch.style.display == 'none') {
		branch.style.display = 'block';
		root.src = "$(httpRoot)core/img/br_opened.gif";
	} else {
		branch.style.display = 'none';
		root.src = "$(httpRoot)core/img/br_closed.gif";
	}
	return false;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function askdel(objCaller)
{
	return confirm('Подверждаете удаление?');
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function formApplyClick(strForm)
{
	var objForm = document.forms[strForm];
	objForm.submitURL.value = document.URL;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function BrowseFileDialog(id, Folder)
{
	var hnd = window.open('$(httpRoot)core/dlg/BrowseFile.php?id='+id+'&root='+BrowseFileLast, 'OpenFileDialog', 'dependent=yes,width=500,height=550,resizable=yes,menubar=no,directories=no,personalbar=no,scrollbars=no,status=no,titlebar=no,toolbar=no');
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function SendRequest(url, handler)
{
	// branch for native XMLHttpRequest object
	if (window.XMLHttpRequest) {
		HttpRequest = new XMLHttpRequest();
		HttpRequest.onreadystatechange = handler;
		HttpRequest.open('GET', url, true);
		HttpRequest.send(null);
	// branch for IE/Windows ActiveX version
	} else if (window.ActiveXObject) {
		HttpRequest = new ActiveXObject('Microsoft.XMLHTTP');
		if (HttpRequest) {
			HttpRequest.onreadystatechange = handler;
			HttpRequest.open('GET', url, true);
			HttpRequest.send();
		}
	}
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

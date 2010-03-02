/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
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
 * $Id: files.js 555 2008-10-19 13:56:05Z mekras $
 */

var objRowSel = null;
var httpRoot;
var slctPanel;

function filesInit(root, panel)
{
	httpRoot = root;
	slctPanel = panel;
	var obj = document.getElementById(panel+'Panel');
	if (obj) rowSelect(iBrowser['Engine']=='IE'?obj.children[0].children[2]:obj.childNodes[1].childNodes[4]);
}

function setPanel(url)
{
	url = url.toString();
	if (url.indexOf('&sp=') != -1) url = url.replace(/sp=([lr])/,'sp='+slctPanel);
	else url += '&sp='+slctPanel;
	return url;
}

function buttonOver(objButton)
{
	objButton.bgColor='dddddd';
	objButton.style.border = 'solid 1px #777';
}

function buttonOut(objButton){
	objButton.bgColor='silver';
	objButton.style.border = 'solid 1px silver';
}

function keyboardEvents()
{
	alert('test');
}

function getCurrentFolder()
{
	var folder = objRowSel.parentNode.childNodes[0].childNodes[0].innerHTML.substr(2);
	//if (folder.length) folder += '/';
	return folder;
}

function rowSelect(objRow)
{
	if (objRowSel != null) {
		objRowSel.bgColor ='#ffffff';
		objRowSel.style.color = '#000050';
	}
	objRow.bgColor ='#4682B4';
	objRow.style.color = 'white';
	objRowSel = objRow;
	var objStatus = document.getElementById('SelFileName');
	objStatus.value = httpRoot+getCurrentFolder()+objRowSel.childNodes[1].innerHTML;
	slctPanel = objRowSel.parentNode.parentNode.id.substr(0,1);
	document.upload.action = document.upload.action.replace(/sp=\w/, 'sp='+slctPanel);
}

function Copy(strControlName)
{
	var ua = navigator.userAgent.toLowerCase();
	var isIE = ((ua.indexOf("msie") != -1) && (ua.indexOf("opera") == -1) && (ua.indexOf("webtv") == -1));
	if (isIE) {
		var objControl = document.getElementById(strControlName);
		objControl.createTextRange().execCommand("Copy");
		objControl.focus();
	} else alert('Эта функция доступна только Internet Explorer :(');
}

function filesCD(url)
{
	window.location = setPanel(url);
}

function filesMkDir()
{
	var folder = prompt('Имя папки','');
	if (folder != undefined && folder.length) window.location = setPanel(window.location)+'&mkdir='+folder;
}

function filesRename()
{
	if (objRowSel != null) {
		var filename = objRowSel.childNodes[1].innerHTML;
		if (filename.substr(-2) != '..') {
			var newname = prompt('Переименовать',filename);
			if (newname != undefined && newname.length && newname != filename)
				window.location = setPanel(window.location)+'&rename='+filename+'&newname='+newname;
		}
	}
}

function filesChmod()
{
	if (objRowSel != null) {
		var filename = objRowSel.childNodes[1].innerHTML;
		if (filename.substr(-2) != '..') {
			var perms = objRowSel.childNodes[4].innerHTML;
			var a = new Array(perms.substr(0, 3), perms.substr(3, 3), perms.substr(6, 3));
			perms = '0';
			var value;
			for (var i=0; i < 3; i++) {
				value = 0;
				if (a[i].substr(0, 1) == 'r') value += 4;
				if (a[i].substr(1, 1) == 'w') value += 2;
				if (a[i].substr(2, 1) == 'x') value += 1;
				perms += value.toString();
			}
			var newperms = prompt('Установить права', perms);
			if (newperms != undefined && newperms.length && newperms != perms)
				window.location = setPanel(window.location)+'&chmod='+filename+'&perms='+newperms;
		}
	}
}

function filesCopy()
{
	if (objRowSel != null) {
		var filename = objRowSel.childNodes[1].innerHTML;
		if (filename.substr(-2) != '..') {
			var obj = document.getElementById((slctPanel=='l'?'r':'l')+'Panel');
			obj = (iBrowser['Engine']=='IE')?obj.children[0].children:obj.childNodes[1].childNodes;
			for (var i=4; i < obj.length; i+=2) if (obj[i].childNodes[1].innerHTML == filename)
				if (confirm('Файл "'+filename+'" уже существует. Переписать?')) break;
				else return;
			window.location = setPanel(window.location)+'&copyfile='+filename;
		}
	}
}

function filesMove()
{
	if (objRowSel != null) {
		var filename = objRowSel.childNodes[1].innerHTML;
		if (filename.substr(-2) != '..') {
			var obj = document.getElementById((slctPanel=='l'?'r':'l')+'Panel');
			obj = (iBrowser['Engine']=='IE')?obj.children[0].children:obj.childNodes[1].childNodes;
			for (var i=4; i < obj.length; i+=2) if (obj[i].childNodes[1].innerHTML == filename)
				if (confirm('Файл "'+filename+'" уже существует. Переписать?')) break;
				else return;
			window.location = setPanel(window.location)+'&movefile='+filename;
		}
	}
}

function filesDelete()
{
	if (objRowSel != null) {
		var filename = objRowSel.childNodes[1].innerHTML;
		if ((filename.substr(-2) != '..') && confirm('Подтверждаете удаление "'+filename+'"?')) {
			window.location = setPanel(window.location)+'&delete='+filename;
		}
	}
}


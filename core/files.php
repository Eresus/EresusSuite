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

#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function files_compare($a, $b)
{
	if ($a['filename'] == $b['filename']) return 0;
	return ($a['filename'] < $b['filename']) ? -1 : 1;
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#

define('FILES_FILTER', '!\.\./!');

class TFiles {
	var
		$access = EDITOR,
		$icons = array(
			array('ext'=>'php|inc|js','icon'=>'script'),
			array('ext'=>'jpg|jpeg','icon'=>'jpeg'),
			array('ext'=>'gif','icon'=>'gif'),
			array('ext'=>'bmp','icon'=>'bmp'),
			array('ext'=>'swf','icon'=>'flash'),
			array('ext'=>'htm|html|shtml','icon'=>'html'),
			array('ext'=>'wav|mid|mp3','icon'=>'audio'),
			array('ext'=>'avi|mov|mpg|mpeg','icon'=>'video'),
			array('ext'=>'txt','icon'=>'text'),
			array('ext'=>'exe','icon'=>'app'),
			array('ext'=>'rar','icon'=>'rar'),
			array('ext'=>'zip','icon'=>'zip'),
			array('ext'=>'doc','icon'=>'word'),
			array('ext'=>'xls','icon'=>'excel'),
			array('ext'=>'pdf','icon'=>'pdf'),
		);
	var $root;
	var $panels = array('l'=>'', 'r'=>'');
	var $sp = 'l';
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function url($args = null)
	{
		global $Eresus;

		$basics = array('lf','rf','sp');
		$result = '';
		if (count($Eresus->request['arg'])) foreach($Eresus->request['arg'] as $key => $value) if (in_array($key,$basics)) $arg[$key] = $value;
		if (count($args)) foreach($args as $key => $value) $arg[$key] = $value;
		if (count($arg)) foreach($arg as $key => $value) if (!empty($value)) $result .= '&amp;'.$key.'='.$value;
		$result = httpRoot.'admin.php?mod=files'.$result;
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderMenu()
	{
		$menu = array (
			array (
				'name' => 'folder',
				'caption' => 'Папка',
				'action' => "javascript:filesMkDir()",
				'active' => true,
			),
			array (
				'name' => 'rename',
				'caption' => 'Переименовать',
				'action' => "javascript:filesRename()",
				'active' => true,
			),
			array (
				'name' => 'chmod',
				'caption' => 'Права',
				'action' => "javascript:filesChmod()",
				'active' => true,
			),
			array (
				'name' => 'copy',
				'caption' => 'Копировать',
				'action' => 'javascript:filesCopy()',
				'active' => true,
			),
			array (
				'name' => 'move',
				'caption' => 'Переместить',
				'action' => 'javascript:filesMove()',
				'active' => true #UserRights(ADMIN),
			),
			array (
				'name' => 'delete',
				'caption' => 'Удалить',
				'action' => "javascript:filesDelete()",
				'active' => true #UserRights(ADMIN),
			),
		);

		$result =
			"<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n".
			"<tr>";
		foreach($menu as $item) if ($item['active']) $result .= "<td onMouseOver=\"buttonOver(this)\" onMouseOut=\"buttonOut(this)\" onClick=\"".$item['action']."\">".$item['caption']."</td>\n";
		$result .=
			"</tr>".
			"</table>";
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function buildFileList($dir)
	{
		global $Eresus;

		$result = '';
		@$hnd=opendir(filesRoot.$this->root.$dir);
		if ($hnd) {
			$i = 0;
			while (($name = readdir($hnd))!==false) if ($name != '.') {
				if (empty($dir) && $name == '..') continue;
				$result[$i]['filename'] = $name;
				$perm = fileperms(filesRoot.$this->root.$dir.'/'.$name);
				$perm = $perm - 32768;
				if ($perm < 0) $perm += 16384;
				$result[$i]['perm'] = '';
				for($j=0; $j<3; $j++) {
					$x = $perm % 8;
					$perm /= 8;
					$result[$i]['perm'] = (($x % 2 == 1)?'x':'-').$result[$i]['perm'];
					$x = ($x - ($x % 2)) / 2;
					$result[$i]['perm'] = (($x % 2 == 1)?'w':'-').$result[$i]['perm'];
					$x = ($x - ($x % 2)) / 2;
					$result[$i]['perm'] = (($x % 2 == 1)?'r':'-').$result[$i]['perm'];
				}
				if (function_exists('posix_getpwuid') && !$Eresus->isWin32()) {
					$result[$i]['owner'] = posix_getpwuid(fileowner(filesRoot.$this->root.$dir.'/'.$name));
					$result[$i]['owner'] = $result[$i]['owner']['name'];
				} else $result[$i]['owner'] = 'unknown';
				switch (filetype(filesRoot.$this->root.$dir.'/'.$name)) {
					case 'dir':
						$result[$i]['icon'] = 'folder';
						$result[$i]['size'] = 'Папка';
						$result[$i]['link'] = ($name == '..')?preg_replace('![^/]+/$!', '', $dir):$dir.$name;
						$result[$i]['action'] = 'cd';
					break;
					case 'file':
						$result[$i]['link'] = httpRoot.$dir.'/'.$name;
						$result[$i]['size'] = number_format(filesize(filesRoot.$this->root.$dir.'/'.$name));
						$result[$i]['action'] = 'new';
						$result[$i]['icon'] = 'file';
						if (count($this->icons)) foreach($this->icons as $item) if (preg_match('/\.('.$item['ext'].')$/i', $name)) {
							$result[$i]['icon'] = $item['icon'];
							break;
						}
					break;
				}
				$result[$i]['date'] = strftime("%y-%m-%d %H:%I:%S", filemtime(filesRoot.$this->root.$dir.'/'.$name));
				$i++;
			}
			closedir($hnd);
			if (count($result)) {
				usort ($result, "files_compare");
				if (count($result) > 1) {
					for ($i=1; $i<count($result); $i++) {
						if ($result[$i]['icon'] == 'folder') {
							$k = $i;
							while (($k>0)&&(($result[$k-1]['icon'] != 'folder')||(($result[$k-1]['icon'] == 'folder')&&($result[$k-1]['filename'] > $result[$k]['filename'])))) {
								$tmp = $result[$k];
								$result[$k] = $result[$k-1];
								$result[$k-1] = $tmp;
								$k--;
							}
						}
					}
				}
			}
		}
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderFileList($side)
	{
		$path = $this->pannels[$side];
		$items = $this->BuildFileList($path);
		$result =
			"<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"filesList\" id=\"".$side."Panel\">\n".
			"<tr class=\"filesListPath\"><th colspan=\"5\">./".((empty($path)) ? '' : $path)."</th></tr>\n".
			"<tr class=\"filesListHdr\"><th>&nbsp;</th><th>Имя файла</th><th>Размер</th><th>Время</th><th>Доступ</th><th>Владелец</th><th style=\"width: 100%\">&nbsp;</th></tr>\n";
		for ($i = 0; $i < count($items);  $i++) {
			$result .= "<tr onClick=\"rowSelect(this)\" onDblClick=\"";
			switch ($items[$i]['action']) {
				case 'cd': $result .= "javascript:filesCD('".$this->url(array($side.'f'=>$items[$i]['link']))."')"; break;
				case 'new': $result .= "window.open('".$items[$i]['link']."');"; break;
			}
			$result .= "\"><td>".img('core/img/icon_'.$items[$i]['icon'].'.gif')."</td><td>".$items[$i]['filename']."</td><td align=\"right\">".$items[$i]['size']."</td><td>".$items[$i]['date']."</td><td>".$items[$i]['perm']."</td><td>".$items[$i]['owner']."</td><td>&nbsp;</td></tr>\n";
		}
		$result .= "</table>\n";
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderControls()
	{
		global $Eresus;
		$result =
			"<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n".
			"<tr><td align=\"center\">Загрузить файл</td><td><form name=\"upload\" action=\"".$Eresus->request['url']."\" method=\"post\" enctype=\"multipart/form-data\"><div id=\"fm_upload\"><input type=\"file\" name=\"upload\" size=\"50\"><input type=\"submit\" value=\"Загрузить\"> Максимальный размер файла: ".ini_get('upload_max_filesize')."</div></form></td></tr>".
			"<tr><td align=\"center\"><a href=\"javascript:Copy('SelFileName');\">Скопировать имя</a></td><td style=\"width: 100%;\"><input type=\"text\" id=\"SelFileName\" value=\"Нет выбранных объектов\" style=\"width: 100%;\"></td></tr>".
			"</table>";
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderStatus()
	{
		$result =
			"<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n".
			"<tr><td>Доступное место: ".FormatSize(disk_free_space(filesRoot.$this->root))."</td></tr>".
			"</table>";
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function upload()
	{
	global $Eresus;

		foreach($_FILES as $name => $file) upload($name, filesRoot.$this->root.$this->pannels[$this->sp]);
		goto($Eresus->request['referer']);
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function mkDir()
	{
		umask(0000);
		mkdir(filesRoot.$this->root.$this->pannels[$this->sp].arg('mkdir', FILES_FILTER), 0777);
		goto($this->url());
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function rmDir($path)
	{
		#if (UserRights(ADMIN)) {
			$hnd=@opendir($path);
			if ($hnd) {
				while (($name = readdir($hnd))!==false) if (($name != '.')&&($name != '..')) {
					switch (filetype($path.'/'.$name)) {
						case 'dir':
							$this->rmDir($path.'/'.$name);
							rmdir($path.'/'.$name);
						break;
						case 'file': unlink($path.'/'.$name); break;
					}
				}
				closedir($hnd);
			}
		#}
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renameEntry()
	{
		$filename = filesRoot.$this->root.$this->pannels[$this->sp].arg('rename', FILES_FILTER);
		$newname = filesRoot.$this->root.$this->pannels[$this->sp].arg('newname', FILES_FILTER);
			if (file_exists($filename)) rename($filename, $newname);
		goto($this->url());
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function chmodEntry()
	{
		$filename = filesRoot.$this->root.$this->pannels[$this->sp].arg('chmod', FILES_FILTER);
		if (file_exists($filename)) chmod($filename, octdec(arg('perms', '/\D/')));
		goto($this->url());
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function copyFile()
	{
		$filename = filesRoot.$this->root.$this->pannels[$this->sp].arg('copyfile', FILES_FILTER);
		$dest = filesRoot.$this->pannels[$this->sp=='l'?'r':'l'].arg('copyfile', FILES_FILTER);
		if (is_file($filename)) copy($filename, $dest);
		elseif (is_dir($filename)) {
		}
		goto($this->url());
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function moveFile()
	{
		#if (UserRights(ADMIN)) {
			$filename = filesRoot.$this->root.$this->pannels[$this->sp].arg('movefile', FILES_FILTER);
			$dest = filesRoot.$this->root.$this->pannels[$this->sp=='l'?'r':'l'].arg('movefile', FILES_FILTER);
			if (is_file($filename)) rename($filename, $dest);
			elseif (is_dir($filename)) {
			}
		#}
		goto($this->url());
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function deleteFile()
	{
		#if (UserRights(ADMIN)) {
			$filename = filesRoot.$this->root.$this->pannels[$this->sp].arg('delete', FILES_FILTER);
			if (is_file($filename)) unlink($filename);
			elseif (is_dir($filename)) {
				$this->rmDir($filename);
				rmdir($filename);
			}
		#}
		goto($this->url());
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	# Административные функции
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function adminRender()
	{
		global $Eresus, $page;

		$this->root = UserRights(ADMIN)?'':'data/';

		$this->pannels['l'] = (arg('lf')?preg_replace('!^/|/$!','',arg('lf')).'/':'');
		$this->pannels['l'] = str_replace('/..', '', $this->pannels['l']);
		$this->pannels['l'] = preg_replace('!^/!', '', $this->pannels['l']);
		while (!empty($this->pannels['l']) && !is_dir(filesRoot.$this->root.$this->pannels['l'])) $this->pannels['l'] = preg_replace('![^/]+/$!', '', $this->pannels['l']);
		$this->pannels['r'] = (arg('rf')?preg_replace('!^/|/$!','',arg('rf')).'/':'');
		$this->pannels['r'] = str_replace('/..', '', $this->pannels['r']);
		$this->pannels['r'] = preg_replace('!^/!', '', $this->pannels['r']);
		while (!empty($this->pannels['r']) && !is_dir(filesRoot.$this->root.$this->pannels['r'])) $this->pannels['r'] = preg_replace('![^/]+/$!', '', $this->pannels['r']);
		if ($this->sp) $this->sp = substr(arg('sp', '/[^lr]/'), 0, 1);
		if (count($_FILES)) $this->upload();
		elseif (arg('mkdir')) $this->mkDir();
		elseif (arg('rename')) $this->renameEntry();
		elseif (arg('chmod')) $this->chmodEntry();
		elseif (arg('copyfile')) $this->copyFile();
		elseif (arg('movefile')) $this->moveFile();
		elseif (arg('delete')) $this->deleteFile();
		else {
			$page->linkScripts(httpRoot.'core/files.js');
			$result =
				"<table id=\"fileManager\">\n".
				'<tr><td colspan="2" class="filesMenu">'.$this->renderMenu()."</td></tr>\n".
				'<tr><td colspan="2" class="filesControls">'.$this->renderControls()."</td></tr>".
				'<tr>'.
				'<td valign="top" class="filesPanel">'.$this->renderFileList('l')."</td>\n".
				'<td valign="top" class="filesPanel">'.$this->renderFileList('r')."</td>\n".
				"</tr>\n".
				'<tr><td colspan="2" class="filesControls">'.$this->renderStatus()."</td></tr>".
				"</table>".
				"<script type=\"text/javascript\"><!--\n".
				" filesInit('".httpRoot.$this->root."', '".$this->sp."');\n".
				"--></script>\n";
			return $result;
		}
	}
}

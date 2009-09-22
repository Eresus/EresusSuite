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

define('ADMINUI', true);

# Подключаем ядро системы #
$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'kernel.php';
if (is_file($filename)) include_once($filename); else {
	echo "<h1>Fatal error</h1>\n<strong>Kernel not available!</strong><br />\nThis error can take place during site update.<br />\nPlease try again later.";
	exit;
}

function __macroConst($matches) {
	return constant($matches[1]);
}
function __macroVar($matches) {
	$result = $GLOBALS[$matches[2]];
	if (!empty($matches[3])) eval('$result = $result'.$matches[3].';');
	return $result;
}

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# КЛАСС "СТРАНИЦА"
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
class TAdminUI extends WebPage {
	var $module; # Загружаемый модуль
	var $title; # Заголовок страницы
	var $styles; # Стили CSS
	var $scripts; # Скрипты
	var $menu; # Меню администратора
	var $extmenu; # Меню раширений
	var $sub; # Уровень вложенности
	var $headers; # Заголовки ответа сервера
	var $options; # Для совместимости с TClientUI
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function init()
	# Проводит инициализацию страницы
	{
		global $Eresus;

		$this->title = admControls;
		# Определяем уровень вложенности
		do {
			$this->sub++;
			$i = strpos($Eresus->request['url'], str_repeat('sub_', $this->sub).'id');
		} while ($i !== false);
		$this->sub--;
		# Загружаем плагины
		$Eresus->plugins->preload(array('admin'),array('ondemand'));
		# Создаем меню
		$this->menu = array(
			array(
				"access"  => EDITOR,
				"caption" => admControls,
				"items" => array (
					array ("link" => "pages", "caption"  => admStructure, "hint"  => admStructureHint, 'access'=>ADMIN),
					array ("link" => "files", "caption"  => admFileManager, "hint"  => admFileManagerHint, 'access'=>EDITOR),
					array ("link" => "plgmgr", "caption"  => admPlugins, "hint"  => admPluginsHint, 'access'=>ADMIN),
					array ("link" => "themes", "caption"  => admThemes, "hint"  => admThemesHint, 'access'=>ADMIN),
					array ("link" => "users", "caption"  => admUsers, "hint"  => admUsersHint, 'access'=>ADMIN),
					array ("link" => "settings", "caption"  => admConfiguration, "hint"  => admConfigurationHint, 'access'=>ADMIN),
				)
			),
		);
		$Eresus->plugins->adminOnMenuRender();
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	# Общие методы
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function replaceMacros($text)
	# Подставляет значения макросов
	{
		global $Eresus;

		$result = str_replace(
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
			$text
		);
		$result = preg_replace_callback('/\$\(const:(.*?)\)/i', '__macroConst', $result);
		$result = preg_replace_callback('/\$\(var:(([\w]*)(\[.*?\]){0,1})\)/i', '__macroVar', $result);
		$result = preg_replace('/\$\(\w+(:.*?)*?\)/', '', $result);
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function url($args = null, $clear = false)
	{
		global $Eresus, $locale;

		$basics = array('mod','section','id','sort','desc','pg');
		$result = '';
		if (count($Eresus->request['arg'])) foreach($Eresus->request['arg'] as $key => $value) if (in_array($key,$basics)|| strpos($key, 'sub_')===0) $arg[$key] = $value;
		if (count($args)) foreach($args as $key => $value) $arg[$key] = $value;
		if (count($arg)) foreach($arg as $key => $value) if (!empty($value)) $result .= '&'.$key.'='.$value;
		if (!empty($result)) $result[0] = '?';
		$result = str_replace('&', '&amp;', $result);
		$result = httpRoot.'admin.php'.$result;
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function addMenuItem($section, $item)
	{
		$item['link'] = 'ext-'.$item['link'];
		$ptr = null;
		for($i=0; $i<count($this->extmenu); $i++) if ($this->extmenu[$i]['caption'] == $section) {
			$ptr = &$this->extmenu[$i];
			break;
		}
		if (is_null($ptr)) {
			$this->extmenu[] = array(
				'access' => $item['access'],
				'caption' => $section,
				'items' => array()
			);
			$ptr = &$this->extmenu[count($this->extmenu)-1];
		}
		$ptr['items'][] = encodeHTML($item);
		if ($ptr['access'] < $item['access']) $ptr['access'] = $item['access'];
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	# Построение интерфейса
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function box($text, $class, $caption='')
	{
		$result = "<div".(empty($class)?'':' class="'.$class.'"').">\n".(empty($caption)?'':'<span class="'.$class.'Caption">'.$caption.'</span><br />').$text."</div>\n";
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function window($wnd)
	{
		$result =
		"<table border=\"0\" class=\"admWindow\"".(empty($wnd['width'])?'':' style="width: '.$wnd['width'].';"').">\n".
		(empty($wnd['caption'])?'':"<tr><th>".$wnd['caption']."</th></tr>\n").
		"<tr><td".(empty($wnd['style'])?'':' style="'.$wnd['style'].'"').">".$wnd['body']."</td></tr>\n</table>\n";
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	/**
	* Отрисовывает элемент управления
	*
	* @access  public
	*
	* @param  string  $type    Тип ЭУ (delete,toggle,move,custom...)
	* @param  string  $href    Ссылка
	* @param  string  $custom  Индивидуальные настройки
	*
	* @return  string  Отрисованный ЭУ
	*/
	function control($type, $href, $custom = array())
	{
		global $Eresus;

		switch($type) {
			case 'add':
				$control = array(
					'image' => $Eresus->root.'core/img/ctrl_add.gif',
					'title' => strAdd,
					'alt' => '+',
				);
			break;
			case 'edit':
				$control = array(
					'image' => $Eresus->root.'core/img/ctrl_edit.gif',
					'title' => strEdit,
					'alt' => '&plusmn;',
				);
			break;
			case 'delete':
				$control = array(
					'image' => $Eresus->root.'core/img/ctrl_delete.gif',
					'title' => strDelete,
					'alt' => 'X',
					'onclick' => 'return askdel(this)',
				);
			break;
			case 'setup':
				$control = array(
					'image' => $Eresus->root.'core/img/ctrl_setup.gif',
					'title' => strProperties,
					'alt' => '*',
				);
			break;
			case 'move':
				$control = array(
					'image' => $Eresus->root.'core/img/ctrl_move.gif',
					'title' => strMove,
					'alt' => '-&gt;',
				);
			break;
			case 'position':
				$control = array(
					'image' => $Eresus->root.'core/img/ctrl_up.gif',
					'title' => admUp,
					'alt' => '&uarr;',
				);
				$s = array_pop($href);
				$href = $href[0];
			break;
			case 'position_down':
				$control = array(
					'image' => $Eresus->root.'core/img/ctrl_down.gif',
					'title' => admDown,
					'alt' => '&darr;',
				);
			break;
			default:
				$control = array(
					'image' => '',
					'title' => '',
					'alt' => '',
				);
			break;
		}
		foreach($custom as $key => $value) $control[$key] = $value;
		$result = '<a href="'.$href.'"'.(isset($control['onclick'])?' onclick="'.$control['onclick'].'"':'').'><img src="'.$control['image'].'" alt="'.$control['alt'].'" title="'.$control['title'].'" /></a>';
		if ($type == 'position') $result .= ' '.$this->control('position_down', $s, $custom);
		return $result;
	}
	//------------------------------------------------------------------------------
	function renderTabs($tabs)
	{
		global $Eresus, $page;

		if (count($tabs)) {
			$result = "<table class=\"admTabs\"><tr>\n";
			$width = empty($tabs['width'])?'':' style="width: '.$tabs['width'].'"';
			if (isset($tabs['items']) && count($tabs['items'])) foreach($tabs['items'] as $item) {
				if (isset($item['url'])) {
					$url = $item['url'];
				} else {
					$url = $Eresus->request['url'];
					if (isset($item['name'])) {
						if (($p = strpos($url, $item['name'].'=')) !== false) $url = substr($url, 0, $p-1);
						$url .= (strpos($url, '?') !== false?'&':'?').$item['name'].'='.$item['value'];
					} else $url = $page->url();
				}
				$url = preg_replace('/&(?!amp;)/', '&amp;', $url);
				$result .= '<td'.$width.(isset($item['class'])?' class="'.$item['class'].'"':'').'><a href="'.$url.'">'.$item['caption'].'</a></td>';
			}

			$result .= "</tr></table>\n";
		}
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderPages($itemsCount, $itemsPerPage, $pageCount, $Descending = false, $sub_prefix='')
	{
		global $Eresus;

		$prefix = empty($sub_prefix)?str_repeat('sub_', $this->sub):$sub_prefix;
		if ($itemsCount > $itemsPerPage) {
			$result = '<div class="admListPages">'.strPages;
			if ($Descending) {
				$forFrom = $pageCount;
				$forTo = 0;
				$forDelta = -1;
			} else {
				$forFrom = 1;
				$forTo = $pageCount+1;
				$forDelta = 1;
			}
			$pageIndex = arg($prefix.'pg') ? arg($prefix.'pg', 'int') : $forFrom;
			for ($i = $forFrom; $i != $forTo; $i += $forDelta)
				if ($i == $pageIndex) $result .= '<span class="selected">&nbsp;'.$i.'&nbsp;</span>';
				else $result .= '<a href="'.$this->url(array($prefix.'pg' => $i)).'">&nbsp;'.$i.'&nbsp;</a>';
			$result .= "</div>\n";
			return $result;
		}
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderTable($table, $values=null, $sub_prefix='')
	{
		global $Eresus;

		$result = '';
		$prefix = empty($sub_prefix)?str_repeat('sub_', $this->sub):$sub_prefix;
		$itemsPerPage = isset($table['itemsPerPage'])?$table['itemsPerPage']:(isset($this->module->settings['itemsPerPage'])?$this->module->settings['itemsPerPage']:0);
		$pagesDesc = isset($table['sortDesc'])?$table['sortDesc']:false;
		if (isset($table['tabs']) && count($table['tabs'])) $result .= $this->renderTabs($table['tabs']);
		if (isset($table['hint'])) $result .= '<div class="admListHint">'.$table['hint']."</div>\n";
		$sortMode = arg($prefix.'sort') ? arg($prefix.'sort', 'word') : (isset($table['sortMode'])?$table['sortMode']:'');
		$sortDesc = arg($prefix.'desc') ? arg($prefix.'desc', 'int') : (arg($prefix.'sort')?'':(isset($table['sortDesc'])?$table['sortDesc']:false));
		if (is_null($values)) {
			$count = $Eresus->db->count($table['name'], isset($table['condition'])?$table['condition']:'');
			if ($itemsPerPage) {
				$pageCount = ((integer)($count / $itemsPerPage)+(($count % $itemsPerPage) > 0));
				if ($count > $itemsPerPage) $pages = $this->renderPages($count, $itemsPerPage, $pageCount, $pagesDesc, $sub_prefix); else $pages = '';
				$page = arg($prefix.'pg') ? arg($prefix.'pg', 'int') : ($pagesDesc ? $pageCount : 1);
			} else {
				$pageCount = $count;
				$pages = '';
				$page = 1;
			}
			$items = $Eresus->db->select(
				$table['name'],
				isset($table['condition'])?$table['condition']:'',
				$sortMode,
				$sortDesc,
				'',
				$itemsPerPage,
				($pagesDesc?($pageCount-$page)*$itemsPerPage:($page-1)*$itemsPerPage)
			);
		} else $items = $values;
		if (isset($pages)) $result .= $pages;
		$result .= "<table class=\"admList\">\n".
			'<tr><th style="width: 100px;">'.admControls.
			(isset($table['controls']['position'])?' <a href="'.$this->url(array($prefix.'sort' => 'position', $prefix.'desc' => '0')).'" title="'.admSortPosition.'">'.img('core/img/ard.gif', admSortPosition, admSortPosition).'</a>':'').
			"</th>";
		if (count($table['columns'])) foreach($table['columns'] as $column)
			$result .= '<th '.(isset($column['width'])?' style="width: '.$column['width'].'"':'').'>'.
				(arg($prefix.'sort') == $column['name'] ? '<span class="admSortBy">'.(isset($column['caption'])?$column['caption']:'&nbsp;').'</span>':(isset($column['caption'])?$column['caption']:'&nbsp;')).
				(isset($table['name'])?
				' <a href="'.$this->url(array($prefix.'sort' => $column['name'], $prefix.'desc' => '')).'" title="'.admSortAscending.'">'.img('core/img/ard.gif', admSortAscending, admSortAscending).'</a> '.
				'<a href="'.$this->url(array($prefix.'sort' => $column['name'], $prefix.'desc' => '1')).'" title="'.admSortDescending.'">'.img('core/img/aru.gif', admSortDescending, admSortDescending).'</a></th>':'');
		$result .= "</tr>\n";
		$url_delete = $this->url(array($prefix.'delete'=>"%s"));
		$url_edit = $this->url(array($prefix.'id'=>"%s"));
		$url_position = $this->url(array($prefix."%s"=>"%s"));
		$url_toggle = $this->url(array($prefix.'toggle'=>"%s"));
		$columnCount = count($table['columns'])+1;
		if (count($items)) foreach($items as $item) {
			$result .= '<tr><td class="ctrl">';
			if (isset($table['controls']['delete']) && (empty($table['controls']['delete']) || $this->module->$table['controls']['delete']($item))) $result .= ' <a href="'.sprintf($url_delete, $item[$table['key']]).'" title="'.admDelete.'" onclick="return askdel(this)">'.img('core/img/delete.gif', admDelete, admDelete, 16, 16).'</a>';
			if (isset($table['controls']['edit']) && (empty($table['controls']['edit']) || $this->module->$table['controls']['edit']($item)))  $result .= ' <a href="'.sprintf($url_edit, $item[$table['key']]).'" title="'.admEdit.'">'.img('core/img/edit.gif', admEdit, admEdit, 16, 16).'</a>';
			if (isset($table['controls']['position']) && (empty($table['controls']['position']) || $this->module->$table['controls']['position']($item)) && $sortMode == 'position')  {
				$result .= ' <a href="'.sprintf($url_position, 'up', $item[$table['key']]).'" title="'.admUp.'">'.img('core/img/up.gif', admUp, admUp).'</a>';
				$result .= ' <a href="'.sprintf($url_position, 'down', $item[$table['key']]).'" title="'.admDown.'">'.img('core/img/down.gif', admDown, admDown).'</a>';
			}
			if (isset($table['controls']['toggle']) && (empty($table['controls']['toggle']) || $this->module->$table['controls']['toggle']($item))) $result .= ' <a href="'.sprintf($url_toggle, $item[$table['key']]).'" title="'.($item['active']?admDeactivate:admActivate).'">'.img('core/img/'.($item['active']?'on':'off').'.gif', $item['active']?admDeactivate:admActivate, $item['active']?admDeactivate:admActivate).'</a>';
			$result .= '</td>';
			# Обрабатываем ячейки данных
			if (count($table['columns'])) foreach($table['columns'] as $column) {
				$value = isset($column['value'])?$column['value']:(isset($item[$column['name']])?$item[$column['name']]:'');
				if (isset($column['replace']) && count($column['replace']))
					$value = array_key_exists($value, $column['replace'])?$column['replace'][$value]:$value;
				if (isset($column['macros'])) {
					preg_match_all('/\$\((.+)\)/U', $value, $matches);
					if (count($matches[1])) foreach($matches[1] as $macros) if (isset($item[$macros])) $value = str_replace('$('.$macros.')', encodeHTML($item[$macros]), $value);
				}
				$value = $this->replaceMacros($value);
				if (isset($column['striptags'])) $value = strip_tags($value);
				if (isset($column['function'])) switch ($column['function']) {
					case 'isEmpty': $value = empty($value)?strYes:strNo; break;
					case 'isNotEmpty': $value = empty($value)?strNo:strYes; break;
					case 'isNull': $value = is_null($value)?strYes:strNo; break;
					case 'isNotNull': $value = is_null($value)?strNo:strYes; break;
					case 'length': $value = strlen($value); break;
				}
				if (isset($column['maxlength']) && (strlen($value) > $column['maxlength'])) $value = substr($value, 0, $column['maxlength']).'...';
				$style = '';
				if (isset($column['align'])) $style .= 'text-align: '.$column['align'].';';
				if (isset($column['wrap']) && !$column['wrap']) $style .=  'white-space: nowrap;';
				if (!empty($style)) $style = " style=\"$style\"";
				$result .= '<td'.$style.'>'.$value.'</td>';
			}
			$result .= "</tr>\n";
		}
		$result .= "</table>\n";
		if (isset($pages)) $result .= $pages;
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderForm($form, $values=array())
	{
		$result = '';
		if (isset($form['tabs'])) $result .= $this->renderTabs($form['tabs']);
		useLib('forms');
		$wnd['caption'] = $form['caption'];
		$wnd['width'] = isset($form['width'])?$form['width']:'';
		$wnd['style'] = 'padding: 0px;';
		$wnd['body'] = form($form, $values);
		$result .= $this->window($wnd);
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderContent()
	{
	global $Eresus;

		$result = '';
		if (arg('mod')) {
			$module = arg('mod', '/[^\w-]/');
			if(file_exists(filesRoot."core/$module.php")) {
				include_once(filesRoot."core/$module.php");
				$class = "T$module";
				$this->module = new $class;
			} elseif (substr($module, 0, 4) == 'ext-') {
				$name = substr($module, 4);
				$this->module = $Eresus->plugins->load($name);
			} else ErrorMessage(errFileNotFound.': "'.filesRoot."core/$module.php'");
			if (is_object($this->module)) {
				if (method_exists($this->module, 'adminRender')) $result .= $this->module->adminRender();
				else ErrorMessage(sprintf(errMethodNotFound, 'adminRender', get_class($this->module)));
			}
		}
		if (isset($Eresus->session['msg']['information']) && count($Eresus->session['msg']['information'])) {
			$messages = '';
			foreach($Eresus->session['msg']['information'] as $message) $messages .= InfoBox($message);
			$result = $messages.$result;
			$Eresus->session['msg']['information'] = array();
		}
		if (isset($Eresus->session['msg']['errors']) && count($Eresus->session['msg']['errors'])) {
			$messages = '';
			foreach($Eresus->session['msg']['errors'] as $message) $messages .= ErrorBox($message);
			$result = $messages.$result;
			$Eresus->session['msg']['errors'] = array();
		}
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderPagesMenu(&$opened, $owner = 0, $level = 0)
	{
		global $Eresus;

		$result = '';
		$ie = preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT']);
		$items = $Eresus->sections->children($owner, $Eresus->user['access'], SECTIONS_ACTIVE);
		if (count($items)) foreach($items as $item) {
			if (empty($item['caption'])) $item['caption'] = admNA;
			if (isset($Eresus->request['arg']['section']) && ($item['id'] == arg('section'))) $this->title = $item['caption']; # title - массив?
			$sub = $this->renderPagesMenu($opened, $item['id'], $level+1);
			$current = (arg('mod') == 'content') && (arg('section') == $item['id']);
			if ($current) $opened = $level;
			if ($opened==$level+1) {$display = 'block'; $opened--;} else $display = 'none';
			$icon = empty($sub)?img('core/img/br_empty.gif'):img('core/img/br_'.($display=='none'?'closed':'opened').'.gif', array('ext'=>'id="root'.$item['id'].'" class="link" onclick="toggleMenuBranch(\''.$item['id'].'\');"'));
			$result .= '<li'.($current?' class="selected"':(!$item['visible']?' class="hidden"':'')).'>'.$icon.'<a href="'.httpRoot.'admin.php?mod=content&amp;section='.$item['id'].'" title="ID: '.$item['id'].' ('.$item['name'].')">'.$item['caption']."</a>\n";
			if (!empty($sub)) $result .= '<ul id="branch'.$item['id'].'" style="margin-left: 10px; display: '.$display.';">'.$sub.'</ul>';
		}
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function renderMenu()
	{
		global $Eresus;

		$menu = '';
		for ($section = 0; $section < count($this->extmenu); $section++)
			if (UserRights($this->extmenu[$section]['access'])) {
				$menu .= "<tr><th>".$this->extmenu[$section]['caption']."</th></tr>\n<tr><td>";
				foreach ($this->extmenu[$section]['items'] as $item) if (UserRights(isset($item['access'])?$item['access']:$this->extmenu[$section]['access'])&&(!(isset($item['disabled']) && $item['disabled']))) {
					if ($item['link'] == arg('mod')) $this->title = $item['caption'];
					$menu .= '<div '.($item['link'] == arg('mod')?'class="selected"':'')."><a href=\"".httpRoot."admin.php?mod=".$item['link']."\" title=\"".$item['hint']."\">".$item['caption']."</a></div>\n";
				}
				$menu .= "</td></tr>\n";
			}

		for ($section = 0; $section < count($this->menu); $section++)
			if (UserRights($this->menu[$section]['access'])) {
				$menu .= "<tr><th>".$this->menu[$section]['caption']."</th></tr>\n<tr><td>";
				foreach ($this->menu[$section]['items'] as $item) if (UserRights(isset($item['access'])?$item['access']:$this->menu[$section]['access'])&&(!(isset($item['disabled']) && $item['disabled']))) {
					if ($item['link'] == arg('mod')) $this->title = $item['caption'];
					$menu .= '<div '.($item['link'] == arg('mod')?'class="selected"':'')."><a href=\"".httpRoot."admin.php?mod=".$item['link']."\" title=\"".$item['hint']."\">".$item['caption']."</a></div>\n";
				}
				$menu .= "</td></tr>\n";
			}

		$opened = -1;
		$result =
			'<table>'."\n".
			'  <tr><th>'.admContent."</th></tr>\n".
			"  <tr><td>\n<ul id=\"menuContent\">\n".$this->renderPagesMenu($opened)."</ul>\n</td></tr>\n".
			$menu.
			'  <tr><td align="center">'."\n".
			'    <a href="'.httpRoot.'admin.php?mod=users&amp;id='.$Eresus->user['id'].'">'.admUsersChangePassword.'</a>'."\n".
			'    <form action="'.httpRoot.'admin.php" method="post" style="margin:5px;">'."\n".
			'      <div>'."\n".
			'        <input type="hidden" name="action" value="logout">'."\n".
			'        <input type="submit" value="'.strExit.'" class="button">'."\n".
			'      </div>'."\n".
			'    </form>'."\n".
			'  </td></tr>'."\n".
			'</table>'."\n";

		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function render()
	# Отправляет созданную страницу пользователю.
	{
		global $locale;

		$menu = $this->renderMenu();

		$content = $this->renderContent();

		$logo = defined('CMSLOGO')
			?(CMSLOGO === false
				?''
				:'<a href="'.(defined('CMSLOGOHREF')?CMSLOGOHREF:'').'"><img src="'.CMSLOGO.'" alt="'.(defined('CMSLOGOALT')?CMSLOGOALT:'').'" style="border: none; float: right;"></a>'
			)
			:'  <div id="cmsLogo"><a href="http://eresus.ru/"><img src="'.httpRoot.'core/img/logo.png" alt="'.CMSNAME.' '.CMSVERSION.'" width="150" height="30" style="border: none;"></a></div>';
		$result =
			'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\n".
			'<html>'."\n".
			'<head>'."\n".
			'  <meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">'."\n".
			'  <title>'.option('siteName').' - '.strip_tags($this->title).'</title>'."\n".
			'  <link rel="StyleSheet" href="'.httpRoot.'core/admin.css" type="text/css">'."\n".
			(empty($this->styles)?'':"  <style type=\"text/css\">\n".$this->styles."  </style>\n").
			# TODO: Заменить определение браузера на подключение ua.js
			'  <script type="text/javascript">'."\n".
			'    var iBrowser = new Array();'."\n".
			'    iBrowser["UserAgent"] = navigator.userAgent.toLowerCase();'."\n".
			'    if ((iBrowser["UserAgent"].indexOf("msie") != -1) && (iBrowser["UserAgent"].indexOf("opera") == -1) && (iBrowser["UserAgent"].indexOf("webtv") == -1)) iBrowser["Engine"] = "IE";'."\n".
			'    if (iBrowser["UserAgent"].indexOf("gecko") != -1) iBrowser["Engine"] = "Gecko";'."\n".
			'    if (iBrowser["UserAgent"].indexOf("opera") != -1) iBrowser["Engine"] = "Opera";'."\n".
			'    if (iBrowser["UserAgent"].indexOf("safari") != -1) iBrowser["Engine"] = "Safari";'."\n".
			'    if (iBrowser["UserAgent"].indexOf("konqueror") != -1) iBrowser["Engine"] = "Konqueror";'."\n".
			'   </script>'."\n".
			'   <script src="'.httpRoot.'core/functions.js" type="text/javascript"></script>'."\n".
			'   <script type="text/javascript">'."\n".
			'     '.trim(str_replace("\n", "\n     ",$this->scripts))."\n".
			'   </script>'."\n".
			$this->renderHeadSection()."\n".
			'</head>'."\n".
			'<body class="admin">'."\n".
			'<div class="pageHeader">'."\n".
			$logo.
			'  <h1>'.option('siteName').' - '.$this->title.'</h1>CMS '.CMSNAME.' '.CMSVERSION."\n".
			'</div>'."\n".
			'<table width="100%" cellSpacing="0" cellPadding="0">'."\n".
			'  <tr>'."\n".
			'    <td id="adminMenu">'."\n".$menu."\n</td>\n".
			'    <td id="adminContent">'.$content."&nbsp;</td>\n".
			'  </tr>'."\n".
			'</table>'."\n".
			'</body>'."\n".
			'</html>'."\n";

		if (count($this->headers)) foreach ($this->headers as $header) Header($header);

		echo $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

# Проверям права доступа и если надо, проводим авторизацию
if (!UserRights(EDITOR)) {
	$messages = '';
	if (isset($Eresus->session['msg']['errors']) && count($Eresus->session['msg']['errors'])) {
		foreach($Eresus->session['msg']['errors'] as $message) $messages .= ErrorBox($message, errError);
		$Eresus->session['msg']['errors'] = array();
		$messages = '<div style="position: absolute; width: 100%; margin: 0;">'.$messages.'</div>';
	}
	echo
		"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n<html>\n".
		"<head>\n".
		"  <title>Авторизация</title>\n".
		"  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">\n".
		"  <link rel=\"StyleSheet\" href=\"".httpRoot."core/admin.css\" type=\"text/css\">".
		"</head>".
		"<body class=\"admin\" style=\"background-color: black; font-family: verdana;\" onload=\"javascript:document.auth.user.focus();\">".
		$messages.
		"<table border=\"0\" style=\"width: 100%; height: 100%; vertical-align: middle\">\n<tr>\n<td align=\"center\">".
		"<form name=\"auth\" action=\"\" method=\"post\"><input type=\"hidden\" name=\"action\" value=\"login\">\n".
		"<table style=\"background-color: #eee; font-size: 8pt;\">\n".
		"<tr><th colspan=\"2\" style=\"background-color: #25b; border: solid 1 black;	color: gold;\" title=\"".$_SERVER["HTTP_HOST"]."\">".option('siteName')."</th></tr>\n".
		"<tr><td>Пользователь:</td><td><input type=\"text\" name=\"user\"></td></tr>\n".
		"<tr><td>Пароль:</td><td><input type=\"password\" name=\"password\"></td></tr>\n".
		"<tr><td>Запомнить</td><td><input type=\"checkbox\" name=\"autologin\" value=\"1\" style=\"border-width: 0px; margin: 0px;\"></td></tr>\n".
		"<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Вход\" style=\"color: #25b; width: 8em; font-weight: bold; border: solid 1px #25b; background-color: white; font-family : 'MS Sans Serif', Geneva, sans-serif; \"></td></tr>\n".
		"</table>\n</form>\n".
		"</td>\n</tr>\n</table>\n</body></html>";
	exit;
}

$page = new TAdminUI;
$page->init();
$page->render();

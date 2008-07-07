<?php
useClass('backward/TContentPlugin');
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# ÊËÀÑÑ-ÏÐÅÄÎÊ "ÊÎÍÒÅÍÒ-ÑÏÈÑÎÊ"
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
class TListContentPlugin extends TContentPlugin {
var $table;
var $pagesCount = 0;
#---------------------------------------------------------------------------------------------------------------------#
function install()
{
	$this->createTable($this->table);
	parent::install();
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function uninstall()
{
	$this->dropTable($this->table);
	parent::uninstall();
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function createTable($table)
{
	global $Eresus;

	$Eresus->db->query('CREATE TABLE IF NOT EXISTS `'.$Eresus->db->prefix.$table['name'].'`'.$table['sql']);
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function dropTable($table)
{
	global $Eresus;

	$Eresus->db->query("DROP TABLE IF EXISTS `".$Eresus->db->prefix.$table['name']."`;");
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function toggle($id)
{
	global $Eresus, $page;

	$Eresus->db->update($this->table['name'], "`active` = NOT `active`", "`".$this->table['key']."`='".$id."'");
	$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."`='".$id."'");
	$caption = $item[isset($this->table['useCaption'])?$this->table['useCaption']:(isset($item['caption'])?'caption':$this->table['columns'][0]['name'])];
	sendNotify(($item['active']?admActivated:admDeactivated).': '.'<a href="'.str_replace('toggle',$this->table['key'],$Eresus->request['url']).'">'.$caption.'</a>', array('title'=>$this->title));
	goto($page->url());
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function delete($id)
{
	global $Eresus, $page;

	$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."`='".$id."'");
	$Eresus->db->delete($this->table['name'], "`".$this->table['key']."`='".$id."'");
	$caption = $item[isset($this->table['useCaption'])?$this->table['useCaption']:(isset($item['caption'])?'caption':$this->table['columns'][0]['name'])];
	sendNotify(admDeleted.': '.'<a href="'.str_replace('delete',$this->table['key'],$Eresus->request['url']).'">'.$caption.'</a>', array('title'=>$this->title));
	goto($page->url());
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function up($id)
{
	global $Eresus, $page;

	$sql_prefix = strpos($this->table['sql'], '`section`') ? "(`section`=".arg('section', 'int').") " : 'TRUE';
	dbReorderItems($this->table['name'], $sql_prefix);
	# FIXME: Escaping
	$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."`='".$id."'");
	if ($item['position'] > 0) {
			$temp = $Eresus->db->selectItem($this->table['name'],"$sql_prefix AND (`position`='".($item['position']-1)."')");
		$temp['position'] = $item['position'];
		$item['position']--;
		$Eresus->db->updateItem($this->table['name'], $item, "`".$this->table['key']."`='".$item['id']."'");
		$Eresus->db->updateItem($this->table['name'], $temp, "`".$this->table['key']."`='".$temp['id']."'");
	}
	goto($page->url());
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function down($id)
{
	global $Eresus, $page;

	$sql_prefix = strpos($this->table['sql'], '`section`') ? "(`section`=".arg('section', 'int').") " : 'TRUE';
	dbReorderItems($this->table['name'], $sql_prefix);
	$count = $Eresus->db->count($this->table['name'], $sql_prefix);
	#FIXME: Escaping
	$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."`='".$id."'");
	if ($item['position'] < $count-1) {
			$temp = $Eresus->db->selectItem($this->table['name'],"$sql_prefix AND (`position`='".($item['position']+1)."')");
		$temp['position'] = $item['position'];
		$item['position']++;
		$Eresus->db->updateItem($this->table['name'], $item, "`".$this->table['key']."`='".$item['id']."'");
		$Eresus->db->updateItem($this->table['name'], $temp, "`".$this->table['key']."`='".$temp['id']."'");
	}
	goto($page->url());
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function adminRenderContent()
{
global $Eresus, $page;

	$result = '';
	if (!is_null(arg('id'))) {
		$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."` = '".arg('id')."'");
		$page->title .= empty($item['caption'])?'':' - '.$item['caption'];
	}
	switch (true) {
		case !is_null(arg('update')) && isset($this->table['controls']['edit']):
			if (method_exists($this, 'update')) $result = $this->update(); else ErrorMessage(sprintf(errMethodNotFound, 'update', get_class($this)));
		break;
		case !is_null(arg('toggle')) && isset($this->table['controls']['toggle']):
			if (method_exists($this, 'toggle')) $result = $this->toggle(arg('toggle')); else ErrorMessage(sprintf(errMethodNotFound, 'toggle', get_class($this)));
		break;
		case !is_null(arg('delete')) && isset($this->table['controls']['delete']):
			if (method_exists($this, 'delete')) $result = $this->delete(arg('delete')); else ErrorMessage(sprintf(errMethodNotFound, 'delete', get_class($this)));
		break;
		case !is_null(arg('up')) && isset($this->table['controls']['position']):
			if (method_exists($this, 'up')) $result = $this->table['sortDesc']?$this->down(arg('up')):$this->up(arg('up')); else ErrorMessage(sprintf(errMethodNotFound, 'up', get_class($this)));
		break;
		case !is_null(arg('down')) && isset($this->table['controls']['position']):
			if (method_exists($this, 'down')) $result = $this->table['sortDesc']?$this->up(arG('down')):$this->down(arg('down')); else ErrorMessage(sprintf(errMethodNotFound, 'down', get_class($this)));
		break;
		case !is_null(arg('id')) && isset($this->table['controls']['edit']):
			if (method_exists($this, 'adminEditItem')) $result = $this->adminEditItem(); else ErrorMessage(sprintf(errMethodNotFound, 'adminEditItem', get_class($this)));
		break;
		case !is_null(arg('action')):
			switch (arg('action')) {
				case 'create': if (isset($this->table['controls']['edit']))
					if (method_exists($this, 'adminAddItem')) $result = $this->adminAddItem();
					else ErrorMessage(sprintf(errMethodNotFound, 'adminAddItem', get_class($this)));
				break;
				case 'insert':
					if (method_exists($this, 'insert')) $result = $this->insert();
					else ErrorMessage(sprintf(errMethodNotFound, 'insert', get_class($this)));
				break;
			}
		break;
		default:
			if (!is_null(arg('section'))) $this->table['condition'] = "`section`='".arg('section')."'";
			$result = $page->renderTable($this->table);
	}
	return $result;
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function clientRenderContent()
{
	global $Eresus, $page;

	$result = '';
	if (!isset($this->settings['itemsPerPage'])) $this->settings['itemsPerPage'] = 0;
	if ($page->topic) $result = $this->clientRenderItem(); else {
		$this->table['fields'] = $Eresus->db->fields($this->table['name']);
		$this->itemsCount = $Eresus->db->count($this->table['name'], "(`section`='".$page->id."')".(in_array('active', $this->table['fields'])?"AND(`active`='1')":''));
		if ($this->itemsCount) $this->pagesCount = $this->settings['itemsPerPage']?((integer)($this->itemsCount / $this->settings['itemsPerPage'])+(($this->itemsCount % $this->settings['itemsPerPage']) > 0)):1;
		if (!$page->subpage) $page->subpage = $this->table['sortDesc']?$this->pagesCount:1;
		if ($this->itemsCount && ($page->subpage > $this->pagesCount)) {
			$item = $page->httpError(404);
			$result = $item['content'];
		} else $result .= $this->clientRenderList();
	}
	return $result;
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function clientRenderList($options = null)
{
	global $Eresus, $page;

	if (is_null($options)) $options = array();
	$options['pages'] = isset($options['pages']) ? $options['pages'] : true;
	$options['oldordering'] = isset($options['oldordering']) ? $options['oldordering'] : true;

	$result = '';
	$items = $Eresus->db->select(
		$this->table['name'],
		"(`section`='".$page->id."')".(strpos($this->table['sql'], '`active`')!==false?"AND(`active`='1')":''),
		$this->table['sortMode'],
		$this->table['sortDesc'],
		'',
		$this->settings['itemsPerPage'],
		$this->table['sortDesc'] && $options['oldordering']
			?(($this->pagesCount-$page->subpage)*$this->settings['itemsPerPage'])
			:(($page->subpage-1)*$this->settings['itemsPerPage'])
	);
	if (count($items)) foreach($items as $item) $result .= $this->clientRenderListItem($item);
	if ($options['pages']) {
		$pages = $this->clientRenderPages();
		$result .= $pages;
	}
	return $result;
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function clientRenderListItem($item)
{
	$result = $item['caption']."<br />\n";
	return $result;
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function clientRenderItem()
{
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function clientRenderPages()
{
	global $page;

	$result = $page->pages($this->pagesCount, $this->settings['itemsPerPage'], $this->table['sortDesc']);
	return $result;
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
?>
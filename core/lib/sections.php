<?php
/**
 * Eresus� 2
 *
 * ���������� ��� ������ � ��������� �����
 *
 * @author: Mikhail Krasilnikov <mk@procreat.ru>
 * @version: 0.0.5
 *
 * TODO: ��������� ���������� �������� ��������� ������� ���� �� pages
 *
 */

define('SECTIONS_ACTIVE',  0x0001);
define('SECTIONS_VISIBLE', 0x0002);

class Sections {
	var $table = 'pages';
	var $index = array();
	var $cache = array();
	/**
	* ������ ������ ��������
	*
	* @access  private
	*
	* @param  bool  $force  ������������ �������������� ������
	*/
	function index($force = false)
	{
		global $Eresus;

		if ($force || !$this->index) {
			$items = $Eresus->db->select($this->table, '', '`position`', false, '`id`,`owner`');
			if ($items) {
				$this->index = array();
				foreach($items as $item) $this->index[$item['owner']][] = $item['id'];
			}
		}
	}
	//------------------------------------------------------------------------------
	/**
	* ������ ������ ID �������� ����������� �����
	*
	* @access  private
	*
	* @param  int  $owner  ID ��������� ������� �����
	*
	* @return  array  ������ ID ��������
	*/
	function branch_ids($owner)
	{
		$result = array();
		if (isset($this->index[$owner])) {
			$result = $this->index[$owner];
			foreach($result as $section) $result = array_merge($result, $this->branch_ids($section));
		}
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	* �������� ������� ����������� �����
	*
	* @access  public
	*
	* @param  int  $owner   ������������� ��������� ������� �����
	* @param  int  $access  ����������� ������� �������
	* @param  int  $flags   ����� (��. SECTIONS_XXX)
	*
	* @return  array  �������� ��������
	*/
	function branch($owner, $access = GUEST, $flags = 0)
	{
		global $Eresus;

		$result = array();
		# ������ ������
		if (!$this->index) $this->index();
		# ������� ID �������� �����.
		$set = $this->branch_ids($owner);
		if (count($set)) {
			$list = array();
			# ������ �� ����
			for($i=0; $i < count($set); $i++) if (isset($this->cache[$set[$i]])) {
				$list[] = $this->cache[$set[$i]];
				array_splice($set, $i, 1);
				$i--;
			}
			if (count($set)) {
				$fieldset = implode(',', array_diff($this->fields(), array('content')));
				# ������ �� ��
				$set = implode(',', $set);
				$items = $Eresus->db->select($this->table, "FIND_IN_SET(`id`, '$set') AND `access` >= $access", 'position', false, $fieldset);
				for($i=0; $i<count($items); $i++) {
					$this->cache[$items[$i]['id']] = $items[$i];
					$list[] = $items[$i];
				}
			}
			if ($flags) {
				for($i=0; $i<count($list); $i++) if
					(
						(!($flags & SECTIONS_ACTIVE) || $list[$i]['active']) &&
						(!($flags & SECTIONS_VISIBLE) || $list[$i]['visible'])
					) $result[] = $list[$i];
			} else $result = $list;
		}
		return $result;
	}
	function brunch($owner, $access = GUEST, $flags = 0)
	{
		return $this->branch($owner, $access, $flags);
	}
	//------------------------------------------------------------------------------
	/**
	* ���������� �������������� �������� �������� ����������
	*
	* @access public
	*
	* @param  int  $owner   ������������� ��������� ������� �����
	* @param  int  $access  ����������� ������� �������
	* @param  int  $flags   ����� (��. SECTIONS_XXX)
	*
	* @return  array  �������������� ��������
	*/
	function children($owner, $access = GUEST, $flags = 0)
	{
		$items = $this->branch($owner, $access, $flags);
		$result = array();
		for($i=0; $i<count($items); $i++) if ($items[$i]['owner'] == $owner) $result[] = $items[$i];
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	* ���������� �������������� ���� ������������ �������� ����������
	*
	* @access public
	*
	* @param  int  $id   ������������� �������
	*
	* @return  array  �������������� �������� ��� NULL ���� ������� $id �� ����������
	*/
	function parents($id)
	{
		$this->index();
		$result = array();
		while ($id) {
			foreach($this->index as $key => $value) if (in_array($id, $value)) {
				$result[] = $id = $key;
				break;
			}
			if (!$result) return null;
		}
		$result = array_reverse($result);
		return $result;
	}
	//------------------------------------------------------------------------------
 /**
	* ���������� ������ �����
	*
	* @access public
	*
	* @return  array  ������ �����
	*/
	function fields()
	{
		global $Eresus;

		if (isset($this->cache['fields'])) $result = $this->cache['fields']; else {
			$result = $Eresus->db->fields($this->table);
			$this->cache['fields'] = $result;
		}
		return $result;
	}
	//------------------------------------------------------------------------------
 /**
	* ���������� ������
	*
	* @access public
	*
	* @param  int     $id  ID �������
	* ���
	* @param  array   $id  ������ ���������������
	* ���
	* @param  string  $id  SQL-�������
	*
	* @return  array  �������� �������
	*/
	function get($id)
	{
		global $Eresus;

		if (is_array($id)) $what = "FIND_IN_SET(`id`, '".implode(',', $id)."')";
		elseif (is_numeric($id)) $what = "`id`=$id";
		else $what = $id;
		$result = $Eresus->db->select($this->table, $what);
		if ($result) for($i=0; $i<count($result); $i++) $result[$i]['options'] = decodeOptions($result[$i]['options']);
		if (is_numeric($id) && $result && count($result)) $result = $result[0];
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	* ��������� ������
	*
	* @access public
	*
	* @param  array  $item  ������
	*
	* @return  mixed  �������� ������ ������� ��� false � ������ �������
	*/
	function add($item)
	{
		global $Eresus;

		$result = false;
		if (isset($item['id'])) unset($item['id']);
		if (!isset($item['owner'])) $item['owner'] = 0;
		$item['created'] = gettime('Y-m-d H:i:s');
		$item['updated'] = $item['created'];
		$item['options'] = isset($item['options']) ? trim($item['options']) : '';
		$item['options'] = (empty($item['options']))?'':encodeOptions(text2array($item['options'], true));
		if (!isset($item['position']) || $item['position'] === '') $item['position'] = isset($this->index[$item['owner']]) ? count($this->index[$item['owner']]) : 0;
		if ($Eresus->db->insert($this->table, $item))
			$result = $this->get($Eresus->db->getInsertedId());
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	* �������� ������
	*
	* @access public
	*
	* @param  array  $item  ������
	*
	* @return  mixed  �������� ������ ������� ��� false � ������ �������
	*/
	function update($item)
	{
		global $Eresus;

		$result = false;
		$item['updated'] = gettime('Y-m-d H:i:s');
		$item['options'] = encodeOptions($item['options']);
		$result = $Eresus->db->updateItem($this->table, $item, "`id`={$item['id']}");
		return $result;
	}
	//------------------------------------------------------------------------------
	/**
	* ������� ������ � ����������
	*
	* @access public
	*
	* @param  int  $id  ������������� �������
	*
	* @return  bool  ��������� ��������
	*/
	function delete($id)
	{
		global $Eresus;

		$result = true;
		$children = $this->children($id);
		for($i=0; $i<count($children); $i++) if  (!$result = $this->delete($children[$i]['id'])) break;
		if ($result) {
			# ������� ������� �������
			$section = $this->get($id);
			if ($plugin = $Eresus->plugins->load($section['type'])) {
				if (method_exists($plugin, 'onSectionDelete')) $plugin->onSectionDelete($id);
			}
			$result = $Eresus->db->delete($this->table, "`id`=$id");
		}
		return $result;
	}
	//------------------------------------------------------------------------------
}

?>
<?php
/**
 * Eresus 2.10
 *
 * ������������ ����� ��� ��������, ����������� ������� � ���� ������
 *
 * ������� ���������� ��������� Eresus� 2
 * � 2004-2007, ProCreat Systems, http://procreat.ru/
 * � 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */

/**
 * ������������ ����� ��� ��������, ����������� ������� � ���� ������
 *
 * @var  string  $name        ��� �������
 * @var  string  $version	    ������ �������
 * @var  string  $kernel      ����������� ������ Eresus
 * @var  string  $title       �������� �������
 * @var  string  $description	�������� �������
 * @var  string  $type        ��� �������, ������������ ����� ������� �������� �����:
 *                              client   - ��������� ������ � ��
 *                              admin    - ��������� ������ � ��
 *                              content  - ������ ������������� ��� ��������
 *                              ondemand - �� ��������� ������ �������������
 * @var  array   $settings    ��������� �������
 */

class ListContentPlugin extends ContentPlugin {
	var $condition = '';
	var $total = 0;
 /**
  * �����������
  *
  * @return ListContentPlugin
  */
	function ListContentPlugin()
	{
		parent::ContentPlugin();
		if (!isset($this->settings['perpage'])) $this->settings['perpage'] = 0;
	}
	//-----------------------------------------------------------------------------
 /**
  * ��������� ����������� ��������
  *
  * @return string
  */
  function clientRenderContent()
  {
		$result = $this->clientListView();
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * ����� ����������� ������
  *
  * @return string
  */
  function clientListView()
  {
  	$result = '';

  	$view = array(
  		'items' => $this->clientRenderList($this->clientListItems()),
  		'pages' => $this->clientListPages(),
  		'add' => $this->clientAddItemControl(),
  	);
  	$result = $this->replaceMacros($this->settings['tmplListView'], $view);
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * ��������� ������ ���������
  *
  * @param array $items  ������ ���������
  *
  * @return string
  */
  function clientRenderList($items)
  {
		$result = '';
  	for($i = 0; $i < count($items); $i++)
			$result .= $this->replaceMacros($this->settings['tmplListItem'], $items[$i]);
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * ��������� ��������� ������
  *
  * @return array
  */
  function clientListItems()
  {
  	$this->condition = $this->clientListCondition();
  	$result = $this->dbSelect('', $this->condition, $this->settings['sort'], '', $this->settings['perpage'], 0);
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * ��������� ������������� �������
  *
  * @return string
  */
  function clientListPages()
  {
  	$result = '';
  	return $result;
  }
  //-----------------------------------------------------------------------------
 /**
  * ��������� �� ���������� ��������
  *
  * @return string
  */
  function clientAddItemControl()
  {
  	return '';
  }
  //-----------------------------------------------------------------------------
 /**
  * ���������� ������� ��� ������� ��������� ������
  *
  * @return string
  */
  function clientListCondition()
  {
  	global $page;

  	$result = "`section` = {$page->id} AND `active` = 1";
  	return $result;
  }
  //-----------------------------------------------------------------------------
}
?>

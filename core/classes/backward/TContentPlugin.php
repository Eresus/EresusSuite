<?php
/**
 * Eresus 2.11
 *
 * ������� ���������� ��������� Eresus 2
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� (�� ������ ������) � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 *
 * $Id$
 */

useClass('backward/TPlugin');
/**
* ������� ����� ��� ��������, ��������������� ��� ��������
*
*
*/
class TContentPlugin extends TPlugin {
/**
* �����������
*
* ������������� ������ � �������� ������� �������� � ������ ��������� ���������
*/
function TContentPlugin()
{
	global $page;

  parent::TPlugin();
  if (isset($page)) {
    $page->plugin = $this->name;
    if (count($page->options)) foreach ($page->options as $key=>$value) $this->settings[$key] = $value;
  }
}
//------------------------------------------------------------------------------
/**
* ��������� ������� �������� � ��
*
* @param  string  $content  �������
*/
function updateContent($content)
{
	global $Eresus, $page;

  $item = $Eresus->db->selectItem('pages', "`id`='".$page->id."'");
  $item['content'] = $content;
  $Eresus->db->updateItem('pages', $item, "`id`='".$page->id."'");
}
//------------------------------------------------------------------------------
/**
* ��������� ������� ��������
*/
function update()
{
	$this->updateContent(arg('content', 'dbsafe'));
  goto(arg('submitURL'));
}
//------------------------------------------------------------------------------
/**
* ��������� ���������� �����
*
* @return  string  �������
*/
function clientRenderContent()
{
	global $page;

  return $page->content;
}
//------------------------------------------------------------------------------
/**
* ��������� ���������������� �����
*
* @return  string  �������
*/
function adminRenderContent()
{
	global $page, $Eresus;

  $item = $Eresus->db->selectItem('pages', "`id`='".$page->id."'");
  $form = array(
    'name' => 'content',
    'caption' => $page->title,
    'width' => '100%',
    'fields' => array (
      array ('type'=>'hidden','name'=>'update'),
      array ('type' => 'memo', 'name' => 'content', 'label' => strEdit, 'height' => '30'),
    ),
    'buttons' => array('apply', 'reset'),
  );

  $result = $page->renderForm($form, $item);
  return $result;
}
//------------------------------------------------------------------------------
}
?>
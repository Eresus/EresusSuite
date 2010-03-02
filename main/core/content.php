<?php
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
 * $Id: content.php 703 2010-01-23 09:58:25Z mk $
 */

class TContent {
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function adminRender()
	{
		global $Eresus, $page;

		if (UserRights(EDITOR)) {
			$item = $Eresus->db->selectItem('pages', "`id`='".arg('section', 'int')."'");
			$page->id = $item['id'];
			if (!array_key_exists($item['type'], $Eresus->plugins->list)) {
				switch ($item['type']) {
					case 'default':
						$editor = new ContentPlugin;
						if (arg('update')) $editor->update();
						else $result = $editor->adminRenderContent();
					break;
					case 'list':
						if (arg('update')) {
							$original = $item['content'];
							$item['content'] = arg('content', 'dbsafe');
							$Eresus->db->updateItem('pages', $item, "`id`='".$item['id']."'");
							sendNotify(admUpdated.': <a href="'.$page->url().'">'.$item['caption'].'</a><br />'.$item['content']);
							HTTP::redirect(arg('submitURL'));
						} else {
							$form = array(
								'name' => 'editURL',
								'caption' => admEdit,
								'width' => '100%',
								'fields' => array (
									array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
									array ('type' => 'html', 'name' => 'content', 'label' => admTemplListLabel, 'height' => '300px', 'value'=>isset($item['content'])?$item['content']:'$(items)'),
								),
								'buttons' => array('apply', 'cancel'),
							);
							$result = $page->renderForm($form);
						}
					break;
					case 'url':
						if (arg('update')) {
							$original = $item['content'];
							$item['content'] = arg('url', 'dbsafe');
							$Eresus->db->updateItem('pages', $item, "`id`='".$item['id']."'");
							sendNotify(admUpdated.': <a href="'.$page->url().'">'.$item['caption'].'</a><br />'.$original.' &rarr; '.$item['content']);
							HTTP::redirect(arg('submitURL'));
						} else {
							$form = array(
								'name' => 'editURL',
								'caption' => admEdit,
								'width' => '100%',
								'fields' => array (
									array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
									array ('type' => 'edit', 'name' => 'url', 'label' => 'URL:', 'width' => '100%', 'value'=>isset($item['content'])?$item['content']:''),
								),
								'buttons' => array('apply', 'cancel'),
							);
							$result = $page->renderForm($form);
						}
					break;
					default:
					$result = $page->box(sprintf(errContentPluginNotFound, $item['type']), 'errorBox', errError);
				}
			} else {
				$Eresus->plugins->load($item['type']);
				$page->module = $Eresus->plugins->items[$item['type']];
				$result = $Eresus->plugins->items[$item['type']]->adminRenderContent();
			}
			return $result;
		}
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
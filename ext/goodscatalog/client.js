/**
 * ������� �������
 *
 * ���������� ������� ��
 *
 * @version 1.00
 *
 * @copyright 2010, ��� "��� �����", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author ������ ������������ <mk@3wstyle.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� �� ������ ������ � ��������� ����� �������
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
 * @package GoodsCatalog
 *
 * $Id: client.js 995 2010-10-04 11:53:34Z mk $
 */

jQuery('a[href$="#catalog-popup"]').live('click', function (e)
{
	e.stopPropagation();
	e.preventDefault();
	
	var img = jQuery('<img alt="" id="goodscatalog-popup" />');
	img.dialog({
		autoOpen: false,
		closeText: '�������',
		draggable: false,
		modal: true,
		resizable: false,
		width: 'auto',

		close: function(event)
		{ 
			jQuery(event.target).remove().closest('div.ui-dialog').remove();
		}
	});
	
	img.
		load(function () { jQuery('#goodscatalog-popup').dialog('open'); }).
		attr('src', e.currentTarget.href);
});

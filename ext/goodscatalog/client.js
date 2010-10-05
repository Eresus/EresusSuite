/**
 * Каталог товаров
 *
 * Клиентские скрипты КИ
 *
 * @version 1.00
 *
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Михаил Красильников <mk@3wstyle.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо по вашему выбору с условиями более поздней
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
		closeText: 'Закрыть',
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

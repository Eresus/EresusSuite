/**
 * Галерея изображений
 *
 * Клиентские скрипты АИ
 *
 * @version 2.00
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
 * @package gallery
 *
 * $Id: admin.js 970 2010-09-29 11:17:35Z mk $
 */


jQuery('#content div.image a.delete').live('click', function (e)
{
	return confirm("Подтверждаете удаление изображения?");
});


/**
 * Заправшивает список групп в указанном разделе в диалоге изменения изображения
 * 
 * @param {Event} e
 * @type void
 */
jQuery('#input-section').live('change', function (e)
{
	var form = jQuery('#editImage').get(0);
	var blockGroup = jQuery('#input-group-block');
	var inputSection = jQuery('#input-section');
	var inputGroup = jQuery('#input-group');
	
	if (inputSection.val() != form.original_section.value)
	{
		blockGroup.show();
		inputGroup.attr('disabled', 'disabled');
		jQuery('*', inputGroup).remove();
		window.Eresus.galleryRequest('gallery', 'getGroups', galleryImageEditLoadGroups, inputSection.val());
	}
	else
	{
		blockGroup.hide();
	}
});


/**
 * Загружает в форму список групп
 * 
 * @param {Object|XMLHttpRequest}    data
 * @param {String}                   textStatus
 * @param {XMLHttpRequest|Exception} extra
 * @type void
 */
function galleryImageEditLoadGroups(data, textStatus, extra)
{
	switch (textStatus)
	{
		case 'success':
			var inputGroup = jQuery('#input-group');
			inputGroup.removeAttr('disabled');
			for (var i = 0; i < data.length; i++)
			{
				jQuery('<option />').text(data[i].title).attr('value', data[i].id).appendTo(inputGroup);
			}
		break;
		
		default:
			alert('Не удалось получить список групп с сервера. Обновите страницу и попробуйте ещё раз.');
	}
}


/**
 * Выполняет XMLHttpRequest к АИ плагина
 * 
 * @param {String}   module    Имя модуля (всегда должно быть "gallery")
 * @param {String}   action    Запрашиваемое действие
 * @param {Function} callback  Обработчик ответа
 * @param дополнительные аргусенты
 * 
 * @type void
 */
window.Eresus.galleryRequest = function (module, action, callback)
{
	var url = this.siteRoot + '/admin.php?mod=ext-' + module + '&args=/' + action + '/';
	for (var i = 3; i < arguments.length; i++)
	{
		url += arguments[i] + '/';
	}
	jQuery.ajax({url: url, success: callback, error: callback, dataType: 'json'});
};

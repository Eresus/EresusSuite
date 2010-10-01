/**
 * Галерея изображений
 *
 * Клиентские скрипты
 *
 * @version 2.00
 *
 * @copyright 2008, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Александр Гаврилюк
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
 * @package Gallery
 *
 * $Id: fgallery.php 267 2010-06-22 14:54:37Z mk $
 */


jQuery('#Content a').live('click', function (e)
{
	/* Если нажата не левая кнопка мыши */
	if (e.which != 1)
	{
		return;
	}
	
	/* Если кликнули не по <a> */
	if (e.currentTarget.nodeName.toLowerCase() != 'a')
	{
		return;
	}
	
	var anchor = jQuery(e.currentTarget);
	
	/* Если в href нет маркера */
	if (anchor.attr('href').substr(-14) != '#gallery-popup')
	{
		return;
	}
	
	var stub = anchor.clone();
	stub.lightBox({
		fixedNavigation: false,
		imageLoading: '$(httpRoot)ext/gallery/lightbox/lightbox-ico-loading.gif',
		imageBtnPrev: '$(httpRoot)ext/gallery/lightbox/lightbox-btn-prev.gif',
		imageBtnNext: '$(httpRoot)ext/gallery/lightbox/lightbox-btn-next.gif',
		imageBtnClose: '$(httpRoot)ext/gallery/lightbox/lightbox-btn-close.gif',
		imageBlank: '$(httpRoot)ext/gallery/lightbox/lightbox-blank.gif'
	});
	stub.click();
	
	return false;
});

/**
 * Каталог товаров
 *
 * Клиентские скрипты АИ
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
 * $Id: admin.js 514 2010-09-15 08:04:36Z mk $
 */

/**
 * Реестр переменных каталога
 */
var GoodsCatalog = {
	// Признак изменения данных в диалоге
	dialogDataChanged: false
};

/* Запросы подтверждения на удаление объектов */
jQuery('#content .goods-list-item a.delete').live('click', function (e)
{
	return confirm("Подтверждаете удаление товара?");
});

jQuery('#content .photo-list-item a.delete').live('click', function (e)
{
	return confirm("Подтверждаете удаление фотогарфии?");
});

jQuery('#content .brand-list-item a.delete').live('click', function (e)
{
	return confirm("Подтверждаете удаление бренда?");
});


/**
 * Отслеживание изменений на вкладке "Основные совйства товара"
 */
jQuery('#catalogEdit-main :input').live('change', function ()
{
	GoodsCatalog.dialogDataChanged = true;
});

jQuery(document).ready(function () 
{
	jQuery('#catalogEdit-btn-images a').click(function (e) 
	{
		if (GoodsCatalog.dialogDataChanged)
		{
			alert('На вкладке "Основные свойства" есть несохранённые изменения. Сохраните их прежде чем перейти к дополнительным фотографиям.');
			jQuery(e).stopPropagation().preventDefault();
		}
	});
});
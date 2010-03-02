<?php
/**
 * HTML-страница
 *
 * Eresus 2
 *
 * Плагин обеспечивает визуальное редактирование текстографических страниц
 *
 * @version 3.00
 *
 * @copyright 	2005-2006, ProCreat Systems, http://procreat.ru/
 * @copyright   2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо (по вашему выбору) с условиями более поздней
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
 */

class Html extends ContentPlugin {
	var $version = '3.00';
	var $kernel = '2.10rc';
	var $title = 'HTML';
	var $description = 'HTML страница';
	var $type = 'client,content,ondemand';
 /**
	* Обновление контента
	*
	* @param string $content  Новый контент
	*/
	function updateContent($content)
	{
		global $Eresus, $page;

		$item = $Eresus->sections->get($page->id);
		$item['content'] = arg('content');
		$item['options']['allowGET'] = arg('allowGET', 'int');
		$Eresus->sections->update($item);
	}
	//------------------------------------------------------------------------------
 /**
	* Отрисовка административной части
	*
	* @return  string  Контент
	*/
	function adminRenderContent()
	{
		global $Eresus, $page;

		if (arg('action') == 'update') $this->adminUpdate();
		$item = $Eresus->sections->get($page->id);
		$url = $page->clientURL($item['id']);
		$form = array(
		'name' => 'contentEditor',
		'caption' => $page->title,
		'width' => '100%',
		'fields' => array (
			array ('type'=>'hidden','name'=>'action', 'value' => 'update'),
				array ('type' => 'html','name' => 'content','height' => '400px', 'value'=>$item['content']),
				array ('type' => 'text', 'value' => 'Адрес страницы: <a href="'.$url.'">'.$url.'</a>'),
				array ('type' => 'checkbox','name' => 'allowGET', 'label' => 'Разрешить передавать аргументы в адресе страницы', 'value'=>isset($item['options']['allowGET'])?$item['options']['allowGET']:false),
		 ),
		'buttons' => array('apply', 'reset'),
		);

		$result = $page->renderForm($form, $item);
		return $result;
	}
	//------------------------------------------------------------------------------
	function clientRenderContent()
	{
		global $Eresus, $page;

		$extra_arguments = $Eresus->request['url'] != $Eresus->request['path'];
		$is_GET_request = count($Eresus->request['arg']);
		$GET_requests_alowed = isset($page->options['allowGET']) && $page->options['allowGET'];

		if ($extra_arguments && !$is_GET_request) $page->httpError(404);
		if ($is_GET_request && !$GET_requests_alowed) $page->httpError(404);

		$result = parent::clientRenderContent();

		return $result;
	}
	//------------------------------------------------------------------------------
}

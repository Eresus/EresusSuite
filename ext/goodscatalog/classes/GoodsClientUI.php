<?php
/**
 * Каталог товаров
 *
 * Интерфейс просмотра товаров
 *
 * @version 1.00
 *
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author Михаил Красильников <mk@3wstyle.ru>
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
 *
 * @package GoodsCatalog
 *
 * $Id: GoodsClientUI.php 990 2010-09-30 13:38:35Z mk $
 */


/**
 * Клиентский интерфейс к товарам
 *
 * @package GoodsCatalog
 */
class GoodsCatalogGoodsClientUI
extends GoodsCatalogAbstractUI
{
	/**
	 * (non-PHPdoc)
	 * @see src/goodscatalog/classes/GoodsCatalogAbstractUI::getActiveRecordClass()
	 */
	protected function getActiveRecordClass()
	{
		return 'GoodsCatalogGood';
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает HTML интерфейса
	 *
	 * @return string
	 */
	public function getHTML()
	{
		global $page;

		if ($page->topic)
		{
			$html = $this->renderItem();
		}
		else
		{
			$html = $this->renderList();
		}

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отрисовывает интерфейс списка товаров
	 *
	 * @return string  HTML
	 *
	 * @since 1.00
	 */
	private function renderList()
	{
		global $page;

		// Данные для подстановки в шаблон
		$data = $this->plugin->getHelper()->prepareTmplData();
		// Определяем текущую страницу списка
		$pg = $page->subpage ? $page->subpage : 1;
		$maxCount = $this->plugin->settings['goodsPerPage'];
		$startFrom = ($pg - 1) * $maxCount;

		$data['goods'] = GoodsCatalogGood::find($page->id, $maxCount, $startFrom, true);
		$totalPages = ceil(GoodsCatalogGood::count($page->id, true) / $maxCount);

		if ($pg > $totalPages)
		{
			$page->httpError(404);
		}

		if ($totalPages > 1)
		{
			$data['pagination'] = new PaginationHelper($totalPages, $pg);
		}
		else
		{
			$data['pagination'] = null;
		}

		// Создаём экземпляр шаблона
		$tmpl = $this->plugin->getHelper()->getClientTemplate('goods-list.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает разметку описания товара
	 *
	 * @return string  HTML
	 *
	 * @since 1.00
	 */
	private function renderItem()
	{
		global $page;

		try
		{
			$good = new GoodsCatalogGood(intval($page->topic));
		}
		catch (DomainException $e)
		{
			$page->httpError(404);
			$e = $e; // PHPMD hack
		}
		// Данные для подстановки в шаблон
		$data = $this->plugin->getHelper()->prepareTmplData();
		$data['good'] = $good;
		$data['listURL'] = $page->clientURL($page->id);
		if ($page instanceof TClientUI && $page->subpage)
		{
			$data['listURL'] .= 'p' . $page->subpage . '/';
		}

		// Создаём экземпляр шаблона
		$tmpl = $this->plugin->getHelper()->getClientTemplate('goods-item.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		$this->plugin->getHelper()->linkJQuery();
		$this->plugin->getHelper()->linkJQueryUI();
		$page->linkScripts($this->plugin->getCodeURL() . 'client.js');
		$page->linkStyles($this->plugin->getCodeURL() . 'client.css');

		return $html;
	}
	//-----------------------------------------------------------------------------

}
<?php
/**
 * Каталог товаров
 *
 * Интерфейс управления товарами
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
 * $Id: GoodsAdminUI.php 559 2010-09-29 08:31:22Z mk $
 */


/**
 * Интерфейс управления товарами
 *
 * @package GoodsCatalog
 */
class GoodsCatalogGoodsAdminUI extends GoodsCatalogAbstractAdminUI
{
	/**
	 * (non-PHPdoc)
	 * @see src/goodscatalog/classes/GoodsCatalogAbstractAdminUI::getActiveRecordClass()
	 */
	protected function getActiveRecordClass()
	{
		return 'GoodsCatalogGood';
	}
	//-----------------------------------------------------------------------------

	/**
	 * (non-PHPdoc)
	 * @see src/goodscatalog/classes/GoodsCatalogAbstractAdminUI::extendedActions()
	 */
	protected function extendedActions()
	{
		switch (true)
		{
			case arg('up'):
				$this->moveUp();
			break;

			case arg('down'):
				$this->moveDown();
			break;

			default:
				return false;
			break;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отрисовывает интерфейс списка товаров
	 *
	 * @return string  HTML
	 *
	 * @since 1.00
	 */
	protected function renderList()
	{
		global $page;

		// Данные для подстановки в шаблон
		$data = $this->plugin->getHelper()->prepareTmplData();
		$data['section'] = arg('section', 'int');

		/* Шаблоны адресов действий */
		$data['urlEdit'] = str_replace('&', '&amp;', $page->url(array('id' => '%s')));
		$data['urlToggle'] = str_replace('&', '&amp;', $page->url(array('toggle' => '%s')));
		$data['urlUp'] = str_replace('&', '&amp;', $page->url(array('up' => '%s')));
		$data['urlDown'] = str_replace('&', '&amp;', $page->url(array('down' => '%s')));
		$data['urlDelete'] = str_replace('&', '&amp;', $page->url(array('delete' => '%s')));

		// Определяем текущую страницу списка
		$pg = arg('pg') ? arg('pg', 'int') : 1;
		$maxCount = $this->plugin->settings['goodsPerPage'];
		$startFrom = ($pg - 1) * $maxCount;

		$data['goods'] = GoodsCatalogGood::find($data['section'], $maxCount, $startFrom);
		$totalPages = ceil(GoodsCatalogGood::count($data['section']) / $maxCount);
		if ($totalPages > 1)
		{
			$data['pagination'] = new PaginationHelper($totalPages, $pg, $page->url(array('pg' => '%s')));
		}
		else
		{
			$data['pagination'] = null;
		}

		// Создаём экземпляр шаблона
		$tmpl = $this->plugin->getHelper()->getAdminTemplate('goods-list.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог добавления товара
	 *
	 * @return string  HTML
	 *
	 * @since 1.00
	 */
	protected function renderAddDialog()
	{
		/*
		 * Имитируем использование старых форм на основе массивов.
		 * Это требуется для правильного подключения WYSIWYG.
		 */
		$wysiwyg = $GLOBALS['Eresus']->extensions->load('forms', 'html');
		$fakeForm = array('values' => array());
		$fakeField = array(
			'name' => 'description',
			'value' => '',
			'label' => '',
			'height' => null,
		);
		$wysiwyg->forms_html($fakeForm, $fakeField);

		// Данные для подстановки в шаблон
		$data = $this->plugin->getHelper()->prepareTmplData();
		$data['section'] = arg('section', 'int');
		$data['brands'] = GoodsCatalogBrand::find(null, null, true);

		// Создаём экземпляр шаблона
		$tmpl = $this->plugin->getHelper()->getAdminTemplate('goods-add.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет товар
	 *
	 * @return void
	 *
	 * @since 1.00
	 * @uses HTTP::redirect
	 */
	protected function addItem()
	{
		$good = new GoodsCatalogGood();
		$good->section = arg('section', 'int');
		$good->article = arg('article');
		$good->title = arg('title');
		$good->about = arg('about');
		$good->description = arg('description');
		$good->cost = arg('cost');
		$good->active = arg('active', 'int');
		$good->special = arg('special', 'int');
		$good->description = arg('description');
		$good->brand = arg('brand', 'int');
		$good->photo = 'photo'; // $_FILES['photo'];
		try
		{
			$good->save();
		}
		catch (EresusRuntimeException $e)
		{
			ErrorMessage($e->getMessage());
		}
		catch (RuntimeException $e)
		{
			Core::logException($e);
			throw $e;
		}
		catch (Exception $e)
		{
			Core::logException($e);
			ErrorMessage(iconv('utf-8', 'cp1251', 'Произошла внутренняя ошибка при добавлении товара.'));
		}

		/*
		 *  Для перехода используем arg() потому что $good->section может быть неопределённым
		 *  если добавление прошло с ошибками.
		 */
		HTTP::redirect('admin.php?mod=content&section=' . arg('section', 'int'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет товар
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function deleteItem()
	{
		$id = arg('delete', 'int');

		try
		{
			$good = new GoodsCatalogGood($id);

			try
			{
				$good->delete();
			}
			catch (Exception $e)
			{
				ErrorMessage(iconv('utf-8', 'cp1251', 'Не удалось удалить товар: ') .
					$e->getMessage());
			}
		}
		catch (DomainException $e)
		{
			$this->reportBadURL($e);
		}

		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает разметку диалога изменения товара
	 *
	 * @return string  HTML
	 *
	 * @since 1.00
	 */
	protected function renderEditDialog()
	{
		global $page;

		$id = arg('id', 'int');

		try
		{
			$good = new GoodsCatalogGood($id);
		}
		catch (DomainException $e)
		{
			$this->reportBadURL($e);
			return;
		}

		if ($this->plugin->settings['extPhotosEnabled'])
		{
			$ext = $this->photoActions($good);
			if ($ext !== false)
			{
				return $ext;
			}
		}
		/*
		 * Основные свойства
		 */

		/*
		 * Имитируем использование старых форм на основе массивов.
		 * Это требуется для правильного подключения WYSIWYG.
		 */
		$wysiwyg = $GLOBALS['Eresus']->extensions->load('forms', 'html');
		$fakeForm = array('values' => array());
		$fakeField = array(
			'name' => 'description',
			'value' => '',
			'label' => '',
			'height' => null,
		);
		$wysiwyg->forms_html($fakeForm, $fakeField);

		$form = new EresusForm('ext/' . $this->plugin->name . '/templates/goods-edit-form.html' ,
			LOCALE_CHARSET);

		// Данные для подстановки в шаблон
		$data = $this->plugin->getHelper()->prepareTmplData();
		foreach ($data as $key => $value)
		{
			$form->setValue($key, $value);
		}

		$form->setValue('good', $good);
		$form->setValue('brands', GoodsCatalogBrand::find(null, null, true));

		$form->setValue('sections', $this->buildSectionTree());

		/*
		 * Дополнительные фотографии
		 */
		if ($this->plugin->settings['extPhotosEnabled'])
		{
			$form->setValue('photos', GoodsCatalogPhoto::find($good->id));

			/* Шаблоны адресов действий */
			$form->setValue('urlEdit', str_replace('&', '&amp;', $page->url(array('photo_id' => '%s'))));
			$form->setValue('urlUp', str_replace('&', '&amp;', $page->url(array('photo_up' => '%s'))));
			$form->setValue('urlDown',
				str_replace('&', '&amp;', $page->url(array('photo_down' => '%s'))));
			$form->setValue('urlDelete',
				str_replace('&', '&amp;', $page->url(array('photo_delete' => '%s'))));
		}

		$data['form'] = $form->compile();
		$data['listURL'] = str_replace('&', '&amp;', $page->url(array('id' => false)));

		// Создаём экземпляр шаблона
		$tmpl = $this->plugin->getHelper()->getAdminTemplate('goods-edit.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Изменяет товар
	 *
	 * @return void
	 *
	 * @since 1.00
	 * @uses HTTP::redirect
	 */
	protected function updateItem()
	{
		$id = arg('update', 'int');
		try
		{
			$good = new GoodsCatalogGood($id);
		}
		catch (DomainException $e)
		{
			$this->reportBadURL($e);
		}

		$good->section = arg('section', 'int');
		$good->article = arg('article');
		$good->title = arg('title');
		$good->about = arg('about');
		$good->description = arg('description');
		$good->cost = arg('cost');
		$good->active = arg('active', 'int');
		$good->special = arg('special', 'int');
		$good->description = arg('description');
		$good->brand = arg('brand', 'int');
		$good->photo = 'photo'; // $_FILES['photo'];

		try
		{
			$good->save();
		}
		catch (RuntimeException $e)
		{
			ErrorMessage($e->getMessage());
		}
		catch (Exception $e)
		{
			Core::logException($e);
			ErrorMessage(iconv('utf-8', 'cp1251', 'Произошла внутренняя ошибка при изменении товара.'));
		}

		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещение товара вверх по списку
	 *
	 * @return void
	 */
	private function moveUp()
	{
		try
		{
			$good = new GoodsCatalogGood(arg('up', 'int'));
		}
		catch (DomainException $e)
		{
			$this->reportBadURL($e);
		}

		$good->moveUp();
		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещение товара вниз по списку
	 *
	 * @return void
	 */
	private function moveDown()
	{
		try
		{
			$good = new GoodsCatalogGood(arg('down', 'int'));
		}
		catch (DomainException $e)
		{
			$this->reportBadURL($e);
		}

		$good->moveDown();
		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает массив разделов
	 *
	 * @param int $root[optional]   Идентификатор корневого раздела
	 * @param int $level[optional]  Уровень раздела
	 *
	 * @return array
	 *
	 * @since 1.00
	 */
	private function buildSectionTree($root = 0, $level = 0)
	{
		global $Eresus;

		$sections = $Eresus->sections->children($root);
		$result = array();
		foreach ($sections as $section)
		{
			$section['selectable'] = $section['type'] == $this->plugin->name;
			$children = $this->buildSectionTree($section['id'], $level + 1);
			if ($section['selectable'] || $children)
			{
				$section['padding'] = str_repeat('-', $level);
				$result []= $section;
				if ($children)
				{
					$result = array_merge($result, $children);
				}
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Действия с дополнительными фотографиями
	 *
	 * @param GoodsCatalogGood $good
	 *
	 * @return string|false
	 */
	private function photoActions($good)
	{
		switch (true)
		{
			case arg('action') == 'photo_add':
				return $this->renderPhotoAddDialog($good);
			break;

			case arg('action') == 'photo_insert':
				return $this->addPhoto($good);
			break;

			case arg('photo_up'):
				return $this->movePhotoUp();
			break;

			case arg('photo_down'):
				return $this->movePhotoDown();
			break;

			case arg('photo_delete'):
				return $this->deletePhoto();
			break;

			default:
				return false;
			break;
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог добавления дополнительной фотографии
	 *
	 * @param GoodsCatalogGood $good
	 */
	private function renderPhotoAddDialog($good)
	{
		// Данные для подстановки в шаблон
		$data = $this->plugin->getHelper()->prepareTmplData();
		$data['good'] = $good;

		// Создаём экземпляр шаблона
		$tmpl = $this->plugin->getHelper()->getAdminTemplate('photo-add.html');

		// Компилируем шаблон и данные
		$html = $tmpl->compile($data);

		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет дополнительную фотографию
	 *
	 * @param GoodsCatalogGood $good
	 *
	 * @return void
	 *
	 * @since 1.00
	 * @uses HTTP::redirect
	 */
	protected function addPhoto($good)
	{
		/*
		 * Пользователь может указать до 5-ти фотографий за один раз.
		 * Это количество задано в шаблоне photo-add.html.
		 */
		for ($i = 1; $i <= 5; $i++)
		{
			$name = 'file' . $i;
			if (!isset($_FILES[$name]) || $_FILES[$name]['error'] == UPLOAD_ERR_NO_FILE)
			{
				continue;
			}
			$photo = new GoodsCatalogPhoto();
			$photo->good = $good->id;
			$photo->photo = $name; // $_FILES[$name];
			try
			{
				$photo->save();
			}
			catch (EresusRuntimeException $e)
			{
				ErrorMessage($e->getMessage());
			}
			catch (Exception $e)
			{
				Core::logException($e);
				ErrorMessage(iconv('utf-8', 'cp1251',
					'Произошла внутренняя ошибка при добавлении фотографии.'));
			}
		}

		HTTP::redirect('admin.php?mod=content&section=' . $good->section . '&id=' . $good->id);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещение фотографии вверх по списку
	 *
	 * @return void
	 */
	private function movePhotoUp()
	{
		try
		{
			$photo = new GoodsCatalogPhoto(arg('photo_up', 'int'));
		}
		catch (DomainException $e)
		{
			$this->reportBadURL($e);
		}

		$photo->moveUp();
		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещение фотографии вниз по списку
	 *
	 * @return void
	 */
	private function movePhotoDown()
	{
		try
		{
			$photo = new GoodsCatalogPhoto(arg('photo_down', 'int'));
		}
		catch (DomainException $e)
		{
			$this->reportBadURL($e);
		}

		$photo->moveDown();
		HTTP::goback();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет фотографию
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	private function deletePhoto()
	{
		$id = arg('photo_delete', 'int');

		try
		{
			$photo = new GoodsCatalogPhoto($id);

			try
			{
				$photo->delete();
			}
			catch (Exception $e)
			{
				ErrorMessage(iconv('utf-8', 'cp1251', 'Не удалось удалить фотографию: ') .
					$e->getMessage());
			}
		}
		catch (DomainException $e)
		{
			$this->reportBadURL($e);
		}

		HTTP::goback();
	}
	//-----------------------------------------------------------------------------
}
<?php
/**
 * Галерея изображений
 *
 * Прототип EresusAdminXHRController
 *
 * @version 2.00
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
 * @package Gallery
 *
 * $Id: AbstractActiveRecord.php 356 2010-09-08 08:08:15Z mk $
 */


/**
 * Прототип EresusAdminXHRController
 *
 * @package Gallery
 */
class GalleryEresusAdminXHRController
{
	/**
	 * Выполняет действия контроллера
	 *
	 * @param string $args  Аргументы запроса
	 *
	 * @return void
	 */
	public function execute($args)
	{
		$args = explode('/', $args);

		/* Отрбрасываем пустые элементы с концов */
		array_shift($args);
		array_pop($args);

		// Получаем имя действия
		$method = 'action' . array_shift($args);

		if (!method_exists($this, $method))
		{
			throw new BadMethodCallException("Method $method does not exists in class " .
				get_class($this));
		}

		$result = call_user_func_array(array($this, $method), $args);

		$result = $this->prepareResponseData($result);

		$json = json_encode($result);
		die($json);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Подготавливает данные для передачи json_encode
	 *
	 * @param mixed unknown_type $data
	 * @return mixed
	 */
	private function prepareResponseData($data)
	{
		switch (true)
		{
			case is_object($data):
				if (method_exists($data, 'toArray'))
				{
					$data = $data->toArray();
				}
				else
				{
					$data = get_object_vars($data);
				}
				$data = $this->prepareResponseData($data);
			break;

			case is_array($data):
				foreach ($data as $key => $value)
				{
					$data[$key] = $this->prepareResponseData($value);
				}
			break;

			case is_string($data):
				$data = iconv('cp1251', 'utf-8', $data);
			break;
		}
		return $data;
	}
	//-----------------------------------------------------------------------------
}
